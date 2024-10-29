<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Services\EmployeeService;
use App\Models\{
    TypeJustify,
    GeneralDirection,
    Employee,
    Justify,
    Incident
};
use App\Http\Requests\HollidaysRequest;

class HollidaysController extends Controller
{

    protected EmployeeService $employeeService;

    function __construct(EmployeeService $employeeService) {
        $this->employeeService = $employeeService;
    }

    public function create(Request $request){

        // * get catalogs
        $justifyIds = array(10, 25, 26);
        if (Auth::user()->level_id == 1) { // Admin General can justify error by system
            $justifyIds = array(1, 10, 25, 26, 27, 28);
        }
        $justificationsType = TypeJustify::select('id', 'name')->whereIn('id', $justifyIds)->get()->all();


        // * retrive the general direction and the catalog
        $generalDirection = null;
        $generalDirections = array();
        if( Auth::user()->level_id == 1){
            $generalDirection = $request->filled('gd') ?GeneralDirection::find($request->query('gd')) :null;
            $generalDirections = GeneralDirection::select('id', 'name')->get()->all();
        }else {
            $generalDirection = GeneralDirection::find(Auth::user()->general_direction_id);
            $generalDirections = GeneralDirection::select('id', 'name')
                ->get()
                ->where('id', Auth::user()->general_direction_id)
                ->all();
        }


        // * attempt to get the employees based on the user level
        $employees = array();
        if($generalDirection != null){
            $tmpEmployees = $this->employeeService->getEmployeesOfUser()
                ->where('active', 1)
                ->where('status_id', 1)
                ->where('general_direction_id', $generalDirection->id)
                ->all();
            $employees = array_map(function($emp){
                return [
                    "id" => $emp->id,
                    "name" => $emp->name
                ];
            }, $tmpEmployees);
        }

        // * return the view
        return Inertia::render('Hollidays/Create', [
            "justificationsType" => $justificationsType,
            "generalDirections" => array_values($generalDirections),
            "generalDirection" => $generalDirection,
            "breadcrumbs" => null,
            "employees" => empty($employees) ?null :array_values($employees),
            "admin" => Auth::user()->level_id == 1
        ]);
    }

    public function store(HollidaysRequest $request){

        // * validate the days are in the same month
        if($request->filled('endDay')){
            $request->validate([
                'endDay' => [
                    'date',
                    function ($attribute, $value, $fail) use ($request) {
                        $initialDay = \Carbon\Carbon::parse($request->initialDay);
                        $endDay = \Carbon\Carbon::parse($value);
                        if ($initialDay->month !== $endDay->month || $initialDay->year !== $endDay->year) {
                            $fail('De momento no es posible justificar fechas de meses diferentes. Por favor, intente justificar mes por mes.');
                        }
                    }
                ]
            ]);
        }


        // * store the file uploaded
        $start = Carbon::parse( $request->input('initialDay') );
        $filepath = "/justificantes/justificante_general-" . $start->format('Ymd') . '-' . $request->file('file')->hashName();
        Storage::disk('local')->put($filepath, $request->file('file')->get() );


        // * get employees of the user and filtered by the request employees
        $employees = $this->employeeService->getEmployeesOfUser()->all();
        $employeesFiltered = array_filter( $employees, function($employee) use($request){
            return in_array( $employee->id, $request->input('employees'));
        });


        // * todo justify each employee
        $employeesResult = array();
        foreach ($employeesFiltered as $employee) {

            $response = $this->justifyEmployee($employee, $request->request->all(), $filepath);

            if( $response == null ){
                $employeesResult[$employee->id] = [ "name" => $employee->name, "ok" => true ];
            }else{
                $employeesResult[$employee->id] = [ "name" => $employee->name, "ok" => false ];
            }
        }

        // * redirecto to done view
        session()->flash('employeesResult', $employeesResult);
        return redirect()->route('hollidays.create.done');
    }

    public function createDone(Request $request){

        // * retrive the employees results from the session data
        $employeesResult = session("employeesResult");
        if( $employeesResult == null ){
            return redirect()->route('dashboard');
        }

        // * return the view
        return Inertia::render('Hollidays/Results',[
            "employeesResult" => $employeesResult
        ]);
    }

    #region private functions
    /**
     * create a justify record for the employee and attemp to remove the incidents
     *
     * @param  Employee $employee
     * @param  array $request
     * @param  string $filepath
     * @return void|false
     */
    private function justifyEmployee(Employee $employee, $request, $filepath){
        DB::beginTransaction();

        // * make a justify record
        try {
            $justify = new Justify();
            $justify->employee_id = $employee->id;
            $justify->type_justify_id = $request['type_id'];
            $justify->date_start = $request['initialDay'];
            $justify->date_finish = $request['endDay'];
            $justify->file = $filepath;
            $justify->details = $request['comments'];
            $justify->user_id = Auth::id();
            $justify->save();

            $message = "del dia ". $request['initialDay'] . ( isset($request['endDay']) ?" al " . $request['endDay'] :"");
            Log::notice("El usuario '{userName}' justificó al empleado {employeeName}: {message}", [
                "userName" => Auth::user()->name,
                "employeeName" => $employee->name,
                "message" => $message
            ]);
        }catch(\Throwable $th){
            DB::rollback();
            Log::error("Fail to make the jusify record for the employee {employeeId} : {message}", [
                "employeeId" => $employee->id,
                "message" => $th->getMessage()
            ]);
            return false;
        }

        // * attempt to delete the employe kardex data from Mongo to re create object
        try {
            $mongoRecord = \App\Models\KardexRecord::where('employee_id', $employee->id)
                ->where('year', Carbon::now()->year )
                ->first();
            if ($mongoRecord) {
                $mongoRecord->delete();
            }
        }catch(\Throwable $th){
            Log::error("Fail at attempting to delete the kardex-record of the mongodb after justify the employee {employeeId}:{employeeName}: {message}",[
                "employeeId" => $employee->id,
                "employeeName" => $employee->name,
                "message" => $th->getMessage()
            ]);
        }

        // * retrieve the incidents to delete them after
        try {
            $incidents = null;
            if(!isset($request['endDay'])){
                if ($justify->type_justify_id == 27) { // Justificacion de entrada por error en el sistema
                    $incidents = Incident::where('employee_id', $employee->id)
                        ->where('date', $request['initialDay'])
                        ->where(function ($query) {
                        $query->where('incident_type_id', 2) // Retardo
                            ->orWhere('incident_type_id', 3) // Retardo Mayor
                            ->orWhere('incident_type_id', 4) // Omision de entrada
                            ->orWhere('incident_type_id', 6) // Retardo Entrada (comida)
                            ->orWhere('incident_type_id', 7); // Omision de entrada (comida)
                        })
                        ->get();
                } elseif ($justify->type_justify_id == 28) { // Justificacion de salida por error en el sistema
                    $incidents = Incident::where('employee_id', $employee->id)
                        ->where('date', $request['initialDay'])
                        ->where(function ($query) {
                        $query->where('incident_type_id', 5) // Omision de salida
                            ->orWhere('incident_type_id', 8) // Omision de salida (comida)
                            ->orWhere('incident_type_id', 10); // Falta (comida)
                        })
                        ->get();
                } else {
                    $incidents = Incident::where('employee_id', $employee->id)
                        ->where('date', $request['initialDay'])
                        ->get();
                }
            }
            else {
                $incidents = Incident::where('employee_id', $employee->id)
                    ->where('date', '>=', $request['initialDay'])
                    ->where('date', '<=', $request['endDay'])
                    ->get();
            }

            // * attempt to delete the incidents
            if (count($incidents) > 0) {
                $flag = 0;
                foreach ($incidents as $inc) {
                    $inc->delete();
                    $flag ++;
                }
                Log::notice( "Se eliminó la incidencia (Total: {flag}) {message} del empleado {employeeName} por el usuario {username} ", [
                    "flag" => $flag,
                    "message" => $message,
                    "employeeName" => $employee->name,
                    "userName" => Auth::user()->name
                ]);
            }

        }catch(\Throwable $th){
            DB::rollback();
            Log::error("Fail to remove the incidents of the employee {employeeId} : {message}", [
                "employeeId" => $employee->id,
                "message" => $th->getMessage()
            ]);
            return false;
        }

        DB::commit();
    }
    #endregion

}
