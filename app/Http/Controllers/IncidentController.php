<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Services\EmployeeService;
use App\Interfaces\EmployeeIncidentInterface;
use App\ViewModels\EmployeeViewModel;
use App\Models\{
    Employee,
    WorkingHours
};

class IncidentController extends Controller
{
    protected EmployeeService $employeeService;
    protected EmployeeIncidentInterface $employeeIncidentService;

    function __construct( EmployeeService $employeeService, EmployeeIncidentInterface $employeeIncidentService) {
        $this->employeeService = $employeeService;
        $this->employeeIncidentService = $employeeIncidentService;
    }

    function index(){
        throw new \Exception("Not implemnted");
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
            ["name"=> "Justificantes", "href"=>""],
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



        // * return the view
        return Inertia::render('Incidents/Index', [
            "employeeNumber" => $employee->employeeNumber,
            "employee" => $employee,
            "breadcrumbs" => $breadcrumbs,
            "status" => (object) $status,
            "checa" => (object) $checa,
            "workingHours" => $hours
        ]);
    }


    /**
     * retrive the incidents of the employee in json format
     *
     * @param  string $employee_number
     * @return void
     */
    function employeeIncidentsJson(string $employee_number): JsonResponse{
        try {
            // * attempt to retrive the incidents
            $data = $this->employeeIncidentService->getIncidents($employee_number);
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
    #endregion

}
