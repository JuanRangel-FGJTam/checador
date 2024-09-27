<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Exception;
use App\Services\{
    EmployeeService,
    JustificationService
};
use App\Models\{
    Department,
    Employee,
    GeneralDirection,
    Direction,
    Subdirectorate,
    WorkingHours,
    Record,
    Incident,
    Justify
};
use App\ViewModels\{
    CalendarEvent
};
use App\Http\Requests\{
    UpdateEmployeeRequest
};


class StaffController extends Controller
{
    protected EmployeeService $employeeService;
    protected JustificationService $justificationService;

    function __construct( EmployeeService $employeeService, JustificationService $justificationService )
    {
        $this->employeeService = $employeeService;
        $this->justificationService = $justificationService;
    }
    
    public function index(Request $request)
    {
        $currentPage = $request->query('p', 1);
        $elementsToTake = 50;

        // * get catalogs
        $general_direction = GeneralDirection::select('id', 'name')->get();
        $directions = Direction::select('id', 'name', 'general_direction_id')->get();
        $subdirectorate = Subdirectorate::select('id', 'name', 'direction_id')->get();

        // * prepare the filters
        $filters = array();
        if($request->filled("s")){
            $filters['search'] = $request->query("s");
        }

        
        // * get employees
        $totalEmployees = 0;
        $data = $this->employeeService->getEmployees(
            take: $elementsToTake,
            skip: ($elementsToTake * ($currentPage - 1)),
            filters: $filters,
            total: $totalEmployees
        );


        // * verify if display paginator
        $showPaginator = $elementsToTake < $totalEmployees;


        // * make paginator
        $paginator = [
            "from" => $elementsToTake * ($currentPage - 1),
            "to" =>  $elementsToTake * $currentPage,
            "total" => $totalEmployees,
            "pages" =>  range(1, ceil( $totalEmployees / $elementsToTake))
        ];


        // * return the viewe
        return Inertia::render('Staff/Index', [
            "employees" => $data,
            "general_direction" => $general_direction,
            "directions" => array_values( $directions->toArray() ),
            "subdirectorate" => array_values( $subdirectorate->toArray() ),
            "showPaginator" => $showPaginator,
            "paginator" => $paginator,
            "filters" => [
                "search" => $request->query('s', null),
                "page" => $currentPage
            ],
        ]);
    }

    public function show(string $employee_number)
    {

        // * attempt to get the employee
        try {
            $employee = $this->employeeService->getEmployee($employee_number);
        } catch (ModelNotFoundException $nf) {
            Log::warning("Employee with employee number '$employee_number' not found");

            // * redirect back
            return redirect()->back()->withErrors([
                "employee_number" => "Empleado no encontrado",
                "message" => "Empleado no encontrado"
            ])->withInput();

        } catch (\Throwable $th) {
            Log::error("Unhandle exception at attempting to get the employee at EmployeeController.show: {message}", [
                "employee_number" => $employee_number,
                "message" => $th->getMessage(),
            ]);

            //TODO: Redirect to error page
            throw new Exception("Not implemented");
        }

        // calculate status
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

        // calculate status checa
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

        // Validate if photo employee exists $employee->photo in public folder
        $employeePhoto = '/images/unknown.png';
        if($employee->photo != null) {
            $employeePhoto = public_path($employee->photo);
            if (file_exists($employeePhoto)) {
                $employeePhoto = asset($employee->photo);
            }
        }

        // * return the view
        return Inertia::render('Staff/Show', [
            "employeeNumber" => $employee_number,
            "employee" => isset($employee) ?$employee :null,
            "status" => (object) $status,
            "checa" => (object) $checa,
            "workingHours" => $hours,
            "employeePhoto" => $employeePhoto,
        ]);
    }

}
