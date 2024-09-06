<?php

namespace App\Http\Controllers;

use \Date;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Inertia\Inertia;
use App\Services\EmployeeService;
use App\Interfaces\EmployeeIncidentInterface;
use App\ViewModels\EmployeeViewModel;
use App\Models\{
    Employee,
    GeneralDirection,
    Incident,
    IncidentState,
    WorkingHours
};

class IncidentController extends Controller
{
    protected EmployeeService $employeeService;
    protected EmployeeIncidentInterface $employeeIncidentService;

    static $INCIDENT_STATE_PENDING = 1;

    function __construct( EmployeeService $employeeService, EmployeeIncidentInterface $employeeIncidentService) {
        $this->employeeService = $employeeService;
        $this->employeeIncidentService = $employeeIncidentService;
    }

    function index(Request $request){

        // * attempt to retrive the query params
        $generalDirecctionId = $request->filled('gdi') ? $request->input("gdi") :null;
        $repType = $request->filled('t') ? $request->input("t") : 'monthly';
        $year = $request->filled('y') ? $request->input("y") : Carbon::now()->year;
        $period = $request->filled('p') ? $request->input("p") : null;

        // * get the incidents
        $employees = array();
        if( $period != null && $generalDirecctionId != null ){
            // * prepare options
            if($repType == 'monthly'){
                $startOfMonth = Carbon::parse("$year-$period-01")->startOfDay();
                $endOfMonth = Carbon::parse("$year-$period-01")->endOfMonth()->endOfDay();

            }else{
                $month = explode("-", $period)[0];
                $quin = explode("-", $period)[1];
                if( $quin == 1){
                    $startOfMonth = Carbon::parse("$year-$month-01");
                    $endOfMonth = Carbon::parse("$year-$month-15");
                }else{
                    $startOfMonth = Carbon::parse("$year-$month-15");
                    $endOfMonth = Carbon::parse("$year-$month-01")->endOfMonth()->endOfDay();
                }
            }

            $employees = $this->getEmployeesWithIncidentsByDirection($generalDirecctionId, $startOfMonth, $endOfMonth);

        }


        // * catalog incident status
        $incidentStatuses = IncidentState::where("id", ">", 1)->select('id', 'name')->get()->toArray();
        $generalDirections = GeneralDirection::select(['id', 'name'])->get()->all();
        $reportTypes = [
            "monthly" => "Mensual",
            "fortnight" => "Quincenal"
        ];
        if($repType == 'monthly'){
            $periods = [
                "1"  => "Enero",
                "2"  => "Febrero",
                "3"  => "Marzo",
                "4"  => "Abril",
                "5"  => "Mayo",
                "6"  => "Junio",
                "7"  => "Julio",
                "8"  => "Agosto",
                "9"  => "Septiembre",
                "10" => "Octubre",
                "11" => "Noviembre",
                "12" => "Diciembre"
            ];
        }else{
            $periods = [
                "1-1"  => "1ra Quincena de Enero",
                "1-2"  => "2da Quincena de Enero",
                "2-1"  => "1ra Quincena de Febrero",
                "2-2"  => "2da Quincena de Febrero",
                "3-1"  => "1ra Quincena de Marzo",
                "3-2"  => "2da Quincena de Marzo",
                "4-1"  => "1ra Quincena de Abril",
                "4-2"  => "2da Quincena de Abril",
                "5-1"  => "1ra Quincena de Mayo",
                "5-2"  => "2da Quincena de Mayo",
                "6-1"  => "1ra Quincena de Junio",
                "6-2"  => "2da Quincena de Junio",
                "7-1"  => "1ra Quincena de Julio",
                "7-2"  => "2da Quincena de Julio",
                "8-1"  => "1ra Quincena de Agosto",
                "8-2"  => "2da Quincena de Agosto",
                "9-1"  => "1ra Quincena de Septiembre",
                "9-2"  => "2da Quincena de Septiembre",
                "10-1" => "1ra Quincena de Octubre",
                "10-2" => "2da Quincena de Octubre",
                "11-1" => "1ra Quincena de Noviembre",
                "11-2" => "2da Quincena de Noviembre",
                "12-1" => "1ra Quincena de Diciembre",
                "12-2" => "2da Quincena de Diciembre"
            ];
        }

        // * return the view
        return Inertia::render('Incidents/Index', [
            "incidentStatuses" => array_values($incidentStatuses),
            "generalDirections" => $generalDirections,
            "employees" => $employees,
            "reportTypes" => $reportTypes,
            "periods" => $periods,
            "options" => [
                "generalDirecctionId" => $generalDirecctionId,
                "year" => $year,
                "period" => $period,
                "type" => $repType
            ]
        ]);
    }


    function getIncidentsByEmployee(string $employee_number) {

        $employee =  $this->findEmployee($employee_number);
        if( $employee instanceof RedirectResponse ){
            return $employee;
        }
        
        // TODO: calculate the breadcrumns based on where the request come from
        $breadcrumbs = array(
            ["name"=> "Inicio", "href"=> "/dashboard"],
            ["name"=> "Vista Empleados", "href"=> route('employees.index') ],
            ["name"=> "Empleado: $employee->employeeNumber", "href"=> route('employees.show', $employee->employeeNumber)],
            ["name"=> "Incidencias", "href"=>""],
        );

        // * calculate status
        $status = array(
            'name' => 'BAJA',
            'class' => 'border border-red-400 text-red-600'
        );
        if ($employee->active) {
            $status = array(
                'name' => 'ACTIVO',
                'class' => 'border border-green-400 text-green-600'
            );
        }

        // * calculate status checa
        $checa = array(
            'name' => 'REGISTRA ASISTENCIA',
            'class' => 'border border-green-400 text-green-600'
        );
        if ($employee->checa != 1) {
            $checa = array(
                'name' => 'NO REGISTRA ASISTENCIA',
                'class' => 'border border-red-400 text-red-600'
            );
        }

        // * get working hours
        $hours = array();
        $workingHours = WorkingHours::where("employee_id", $employee->id)->first();
        if( $workingHours != null){
            if( $workingHours->toeat == null){
                array_push($hours, $workingHours->checkin . "-" . $workingHours->checkout);
            }else {
                array_push($hours, $workingHours->checkin . "-" . $workingHours->toeat);
                array_push($hours, $workingHours->toarrive . "-" . $workingHours->checkout);
            }
        }

        // * catalog incident status
        $incidentStatuses = IncidentState::where("id", ">", 1)->select('id', 'name')->get()->toArray();

        // * return the view
        return Inertia::render('Incidents/Employee', [
            "employeeNumber" => $employee->employeeNumber,
            "employee" => $employee,
            "breadcrumbs" => $breadcrumbs,
            "status" => (object) $status,
            "checa" => (object) $checa,
            "workingHours" => $hours,
            "incidentStatuses" => array_values($incidentStatuses)
        ]);
    }


    /**
     * retrive the incidents of the employee in json format
     *
     * @param  string $employee_number
     * @return void
     */
    function employeeIncidentsJson(Request $request, string $employee_number): JsonResponse{

        // * retrive the querys
        $year = $request->query('year');
        $month = $request->query('month');

        // * make to range of dates
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();

        try {
            // * attempt to retrive the incidents
            $data = $this->employeeIncidentService->getIncidents($employee_number, $startOfMonth, $endOfMonth);

            // * validate if the data need to be filtered
            if( $request->query->has("onlyPendings") ){
                $data = array_filter($data, fn($item)=> $item['incident_state_id'] == self::$INCIDENT_STATE_PENDING );
            }

            return response()->json($data, 200);
        }
        catch (ModelNotFoundException $nf) {
            Log::error("Employee not found at attempting to retrive the incidents of the employee '{employeeNmber}'",[
                "employeeNmber" => $employee_number,
                "message" => $nf->getMessage()
            ]);
            return response()->json([
                "message" => "Employee not found"
            ], 404);
        }
        catch (\Throwable $th) {
            Log::error("Fail at attempting to retrive the incidents of the employee '{employeeNmber}: {message}'",[
                "employeeNmber" => $employee_number,
                "message" => $th->getMessage()
            ]);
            return response()->json([
                "message" => "Fail at attempting to retrive the incidents"
            ], 409);
        }
    }


    /**
     * update the status of the incident
     *
     * @param  mixed $request
     * @param  mixed $incident_id
     * @return void
     */
    function updateIncidentState(Request $request, int $incident_id){

        // * validate the request
        $request->validate([
            "state_id" => "required|exists:incident_states,id"
        ]);

        // * retrive the incident id
        $incident = Incident::with('employee')->find($incident_id);
        if( $incident == null){
            Log::warning("Incident id '{incidentId}' not found when attempting to update the status at IncidentController.updateIncidentStatus", [ "incidentId" => $incident_id ]);
            return redirect()->back()->withErrors([
                "message" => "La incidencia no se encuentra en el sistema o no está disponible."
            ])->withInput();
        }

        // tmp data for loggin
        $oldStateValue = $incident->incident_state_id;
        $employee = $incident->employee;

        // * attempt to update the state of the incident
        try {

            $incident->incident_state_id = $request->input('state_id');
            $incident->save();

            Log::notice("The state of the incident with id '{incident_id}' of the employee with id '{employee_id}' was updated from '{old_value}' to '{new_value}'.", [
                "incident_id" => $incident->id,
                "employee_id" => $employee->id,
                "old_value" => $oldStateValue,
                "new_value" => $request->input('state_id')
            ]);

        }catch(\Throwable $th){

            Log::error("Fail to update the state of the incident id '{incident_id}' at IncidentController.updateIncidentStatus: {message}", [
                "message" => $th->getMessage(),
                "incident_id" => $incident->id
            ]);

            return redirect()->back()->withErrors([
                "message" => "Error al actualizar el estado de la incidencia, intente de nuevo o comuníquese con el administrador."
            ])->withInput();

        }

        // TODO: Calculate where to redirect based on where the request is come from
        return redirect()->back()->with('success', 'Estado de la incidencia actualizada.');

    }

    #region private methods
    /**
     * find Employee
     *
     * @param  string $employee_number
     * @return \App\ViewModels\EmployeeViewModel|\Illuminate\Http\RedirectResponse
     */
    private function findEmployee(string $employee_number){

        // * attempt to get the employee
        try {
            return $this->employeeService->getEmployee($employee_number);
        } catch (ModelNotFoundException $nf) {

            Log::warning("Employee with employee number '$employee_number' not found");

            //TODO: redirect to not found page

            // * redirect back
            return redirect()->back()->withErrors([
                "employee_number" => "Empleado no encontrado",
                "message" => "Empleado no encontrado"
            ])->withInput();
        }
    }

    /**
     * get incidents of direction by range date
     *
     * @param  int|string $generalDirecctionId
     * @param  string|Date|Carbon $from
     * @param  string|Date|Carbon $to
     * @return array
     */
    private function getEmployeesWithIncidentsByDirection(int $generalDirecctionId, $from, $to){

        // * get the incidents
        $incidents = Incident::whereBetween('date', [$from, $to])
        ->whereHas("employee", function($employee) use($generalDirecctionId) {
            return $employee->where('general_direction_id', $generalDirecctionId);
        })->get();


        // * group by employee
        $groupedByEmployee = $incidents->groupBy('employee_id')->all();

        // * get the employees
        $employees = Employee::with([
            'generalDirection',
            'direction'
        ])->whereIn('id', array_keys( $groupedByEmployee ))->get()->toArray();

        // * append the total of incidences on the period
        foreach($employees as &$empl){
            try {
                $empl["totalIncidents"] = count( $groupedByEmployee[$empl['id']] );
            } catch (\Throwable $th) {
                $empl["totalIncidents"] = 0;
            }
        }

        return $employees;
    }

    #endregion

}
