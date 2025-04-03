<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use App\Services\EmployeeService;
use App\Services\EmployeeRHService;
use App\Models\{
    Employee,
    GeneralDirection,
    Direction,
    Department,
    Subdirectorate
};
use App\Http\Requests\AssignAreaRequest;
use App\Http\Requests\NewEmployeeRequest;
use App\ViewModels\EmployeeViewModel;
use Exception;

class NewEmployeeController extends Controller
{
    protected EmployeeService $employeeService;

    function __construct(EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }


    public function index(Request $request)
    {
        // * properties
        $currentPage = $request->query('p', 1);
        $elementsToTake = 50;

        // * get catalogs
        $general_direction = GeneralDirection::select('id', 'name')->get();
        $directions = Direction::select('id', 'name', 'general_direction_id')->get();
        $subdirectorate = Subdirectorate::select('id', 'name', 'direction_id')->get();

        // * get employees with no area assigned
        /** @var EmployeeViewModel[] $employees */
        $employees = $this->employeeService->getNewEmployees();


        if ($request->filled('se'))
        {
            $searchInput = $request->input('se');

            // * retrive the RH Employees and filter the employees, use a cache AND
            $employeesRH = Cache::remember('new_employees', 3600, function()
            {
                return EmployeeRHService::getMissingEmployees()->map(fn($emp) => EmployeeViewModel::fromRHModel($emp))->toArray();
            });
            $employeesRH = array_filter($employeesRH, function($emp) use ($searchInput) {
                return str_contains( strtolower($emp->name), strtolower($searchInput)) || str_contains( $emp->employeeNumber, $searchInput);
            });

            // * filter the local employees
            $employees = array_filter( $employees, function($emp) use($searchInput) {
                return str_contains( strtolower($emp->name), strtolower($searchInput)) || str_contains( $emp->employeeNumber, $searchInput);
            });

            $employees = array_merge($employees, $employeesRH);
        }

        // * make paginator
        $paginator = [
            "from" => $elementsToTake * ($currentPage - 1),
            "to" =>  $elementsToTake * $currentPage,
            "total" => count($employees),
            "pages" =>  range(1, ceil( count($employees) / $elementsToTake))
        ];

        // * take n elements and skip m
        $employees = array_slice($employees, $paginator['from'], $elementsToTake);
        
        // * return the view
        return Inertia::render('NewEmployees/Index',[
            "employees" => array_values($employees),
            "general_direction" => $general_direction,
            "directions" => array_values( $directions->toArray() ),
            "subdirectorate" => array_values( $subdirectorate->toArray() ),
            "paginator" => $paginator,
            "filters" => [
                "page" => $currentPage,
                "search" => $request->filled('se') ? $request->input('se') : null,
            ],
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
        if($employee instanceof \Illuminate\Http\RedirectResponse)
        {
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

    public function registerNewEmployee(string $employee_number)
    {
        // * check if employee dosent exist
        if(Employee::where('plantilla_id', "1" . trim($employee_number))->exists())
        {
            return redirect()->route('newEmployees.edit', ['employee_number' => $employee_number]);
        }

        // * retrive the employee
        try
        {
            $employeeRH = EmployeeRHService::getEmployeeData($employee_number, $columns = [
                "IDEMPLEADO",
                "IDESTADOEMPLEADO",
                "NUMEMP",
                "NOMBRE",
                "APELLIDO",
                "APELLIDOPATERNO",
                "APELLIDOMATERNO",
                "FECHABAJA",
                "IDSEXO",
                "FECHA_NAC",
                "RFC",
                "FECHA_ALTA",
                "CURP",
                "CUIP",
            ])
                ?? throw new \Exception("Employee not found");
        }
        catch (\Throwable $th)
        {
            abort(404, $th->getMessage());
        }

        // * check th employee photo
        $employeePhoto = '/images/unknown.png';
        try
        {
            $employeePhoto = EmployeeRHService::duplicatePhotoEmployee($employeeRH->IDEMPLEADO, "1" . $employeeRH->NUMEMP) ?? throw new Exception("Fail at retrive the employee photo");
        }
        catch (\Throwable $th)
        {
            //
        }

        // * retrive the catalogs
        $generalDirections = GeneralDirection::select('id','name')->get()->toArray();
        $directions = Direction::select('id','name', 'general_direction_id')->get()->toArray();
        $subdirectorates = Subdirectorate::select('id', 'name', 'direction_id')->get()->toArray();
        $deparments = Department::select('id', 'name', 'subdirectorate_id')->get()->toArray();

        // * return the view
        return Inertia::render('NewEmployees/New', [
            "employeeNumber" => $employeeRH->NUMEMP,
            "employeePhoto" => "/" . $employeePhoto,
            "employee" => $employeeRH,
            "generalDirections" => $generalDirections,
            "directions" => $directions,
            "subdirectorates" => $subdirectorates,
            "deparments" => $deparments,
            "defaultValues" => (object) array(),
        ]);
    }

    public function storeNewEmployee(NewEmployeeRequest $request, string $employee_number)
    {
        DB::beginTransaction();
        try
        {
            // * Create employee
            if(Employee::where('plantilla_id', "1" . trim($employee_number))->exists())
            {
                // * Update the employee Area
                $data = array_merge($request->toArray(), [
                    "canCheck" => 1,
                    "status_id" => 1
                ]);
                $this->employeeService->updateEmployee($employee_number, $data);

                $employee = Employee::where('plantilla_id', "1" . trim($employee_number))->first();
            }
            else
            {
                $employee = Employee::create([
                    'plantilla_id' => "1" . trim($employee_number),
                    'employee_number' =>  intval(trim($employee_number)),
                    'general_direction_id' => request('general_direction_id'),
                    'direction_id' => request('direction_id') ?? 0,
                    'subdirectorate_id' => request('subdirectorate_id'),
                    'department_id' => request('department_id'),
                    'name' => request('name'),
                    'active' => 1,
                    'status_id' => 1
                ]);
            }

            // * Update the schedulle
            $this->employeeService->updateEmployeeSchedule($employee_number, $request->toArray());

            DB::commit();
            Log::notice("New employee registered", [
                "employeeId" => $employee->id,
                "employeeNumber" => $employee_number
            ]);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            Log::error("Error at attempt to register the new employee: {message}", [
                "message" => $th->getMessage(),
                "payload" => $request->toArray()
            ]);
            abort(409, 'Error al registrar el empleado');
        }

        // * clear the cache
        Cache::forget('new_employees');

        return redirect()->route('newEmployees.index');
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
