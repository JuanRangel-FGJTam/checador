<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Services\{
    EmployeeService,
    JustificationService
};
use App\ViewModels\EmployeeViewModel;
use App\Models\TypeJustify;
use App\Http\Requests\NewJustificationRequest;
use Psy\Readline\Hoa\Console;

class JustificationController extends Controller
{

    protected EmployeeService $employeeService;
    protected JustificationService $justificationService;

    function __construct( EmployeeService $employeeService, JustificationService $justificationService ) {
        $this->employeeService = $employeeService;
        $this->justificationService = $justificationService;
    }


    /**
     * show the view for display the justifications of the employee
     *
     * @param  string $employee_number
     * @return mixed
     */
    function showJustificationOfEmployee(string $employee_number) {
        
        // * get the employee
        $employee =  $this->findEmployee($employee_number);
        if( $employee instanceof RedirectResponse ){
            return $employee;
        }

        // TODO: get the justifications of the employee
        $justifications = array(
            (object)[
                'id' => 1,
                'employee_id' => 666,
                'type_justify_id' => 1,
                'date_start' => '2024-08-17 09:10',
                'date_finish' => '2024-08-17 09:12',
                'file' => 'path/file/document.pdf',
                'details' => 'some descriptions 17'
            ],
            (object)[
                'id' => 2,
                'employee_id' => 666,
                'type_justify_id' => 2,
                'date_start' => '2024-08-21 09:24',
                'date_finish' => '2024-08-21 09:28',
                'file' => 'path/file/document2.pdf',
                'details' => 'some descriptions 21'
            ],
        );

        // TODO: calculate the breadcrumns based on where the request come from
        $breadcrumbs = array(
            ["name"=> "Inicio", "href"=> "/dashboard"],
            ["name"=> "Vista Empleados", "href"=> route('employees.index') ],
            ["name"=> "Empleado: $employee->employeeNumber", "href"=> route('employees.show', $employee->employeeNumber)],
            ["name"=> "Justificantes", "href"=>""],
        );

        // * return the view
        return Inertia::render('Justifications/EmployeeIndex', [
            "employeeNumber" => $employee->employeeNumber,
            "employee" => $employee,
            "justifications" => $justifications,
            "breadcrumbs" => $breadcrumbs
        ]);

    }


    /**
     * show the view for justify a day
     *
     * @param  Request $request
     * @param  string $employee_number
     * @return mixed
     */
    function showJustifyDay( Request $request, string $employee_number) {

        // * get the employee
        $employee =  $this->findEmployee($employee_number);
        if( $employee instanceof RedirectResponse ){
            return $employee;
        }

        $initialDay = Carbon::today();
        if( $request->query('day') != null){
            $initialDay = Carbon::parse($request->query('day'));
        }

        // * get the justifications
        $justificationsType = TypeJustify::select('id', 'name')->get()->toArray();

        // TODO: calculate the breadcrumns based on where the request come from
        $breadcrumbs = array (
            ["name"=> "Inicio", "href"=> "/dashboard"],
            ["name"=> "Vista Empleados", "href"=> route('employees.index') ],
            ["name"=> "Empleado: $employee->employeeNumber", "href"=> route('employees.show', $employee->employeeNumber)],
            ["name"=> "Justificar dia", "href"=>""],
        );

        // * return the view
        return Inertia::render('Justifications/JustifyDay', [
            "employeeNumber" => $employee->employeeNumber,
            "employee" => $employee,
            "justificationsType" => $justificationsType,
            "initialDay" => $initialDay->format('Y-m-d'),
            "breadcrumbs" => $breadcrumbs
        ]);

    }

    /**
     * store a justification
     *
     * @return mixed
     */
    function storeJustification(NewJustificationRequest $request, string $employee_number) {

        // * get the employee
        $employee =  $this->findEmployee($employee_number);
        if( $employee instanceof RedirectResponse ){
            return $employee;
        }

        // * attempt to justify the day
        try {

            $this->justificationService->justify(
                request: $request,
                employee: $employee
            );

        } catch (\Throwable $th) {
            Log::error("Fail to justify the day: {message}", [
                "message" => $th->getMessage()
            ]);

            return redirect()->back()->withErrors([
                "Error al justificar el día, intente de nuevo o comuníquese con el administrador."
            ])->withInput();
        }

        // * redirect to employee show
        return redirect()->route('employees.show', $employee->employeeNumber );

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
