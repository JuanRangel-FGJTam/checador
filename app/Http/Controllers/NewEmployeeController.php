<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Inertia\Inertia;
use App\Services\EmployeeService;
use App\Models\{
    Employee,
    GeneralDirection,
    Direction,
    Department,
    Subdirectorate
};
use App\Http\Requests\AssignAreaRequest;

class NewEmployeeController extends Controller
{
    protected EmployeeService $employeeService;

    function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }


    public function index(Request $request){

        // * get catalogs
        $general_direction = GeneralDirection::select('id', 'name')->get();
        $directions = Direction::select('id', 'name', 'general_direction_id')->get();
        $subdirectorate = Subdirectorate::select('id', 'name', 'direction_id')->get();

        // * get employees
        $employees = $this->employeeService->getNewEmployees();

        if ($request->filled('s')) {
            $employees = array_filter( $employees, fn($emp) => str_contains( strtolower($emp->name), strtolower($request->input('s'))) );
        }
        
        // * return the view
        return Inertia::render('NewEmployees/Index',[
            "employees" => array_values($employees),
            "general_direction" => $general_direction,
            "directions" => array_values( $directions->toArray() ),
            "subdirectorate" => array_values( $subdirectorate->toArray() ),
            "searchString" => $request->filled('s') ? $request->input('s') : null
        ]);
    }

    /**
     * show the form for editing the employee
     *
     * @param  string $employee_number
     * @return void
     */
    public function edit(string $employee_number)
    {

        // * retrive the employee
        $employee = $this->findEmployee($employee_number);
        if($employee instanceof \Illuminate\Http\RedirectResponse){
            return $employee;
        }

        // * retrive the catalogs
        // TODO: filter the catalogs by the query params
        $generalDirections = GeneralDirection::select('id','name')->get()->toArray();
        $directions = Direction::select('id','name', 'general_direction_id')->get()->toArray();
        $subdirectorates = Subdirectorate::select('id', 'name', 'direction_id')->get()->toArray();
        $deparments = Department::select('id', 'name', 'subdirectorate_id')->get()->toArray();

        // * return the view
        return Inertia::render('NewEmployees/Edit', [
            "employeeNumber" => $employee->employeeNumber,
            "employee" => $employee,
            "generalDirections" => $generalDirections,
            "directions" => $directions,
            "subdirectorates" => $subdirectorates,
            "deparments" => $deparments,
            "defaultValues" => (object) array(),
        ]);
    }

    public function update(AssignAreaRequest $request, string $employee_number){

        // * retrive the employee
        $employee = $this->findEmployee($employee_number);
        if($employee instanceof \Illuminate\Http\RedirectResponse){
            return $employee;
        }

        // * update the employee data
        try {
            $this->employeeService->updateEmployee( $employee->employeeNumber, $request->request->all());
        }catch (\Throwable $th) {
            return redirect()->back()->withErrors([
                "message" => $th->getMessage()
            ])->withInput();
        }

        // * redirect to show view
        return redirect()->route('employees.show', ['employee_number' => $employee->employeeNumber ]);

    }

    #region private functions
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
