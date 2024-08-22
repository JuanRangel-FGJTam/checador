<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use App\Services\EmployeeService;
use App\ViewModels\EmployeeViewModel;
use Inertia\Inertia;

class JustificationController extends Controller
{

    protected EmployeeService $employeeService;

    function __construct( EmployeeService $employeeService ) {
        $this->employeeService = $employeeService;
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
