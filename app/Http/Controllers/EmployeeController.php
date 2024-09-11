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

class EmployeeController extends Controller
{

    protected EmployeeService $employeeService;
    protected JustificationService $justificationService;

    function __construct( EmployeeService $employeeService, JustificationService $justificationService )
    {
        $this->employeeService = $employeeService;
        $this->justificationService = $justificationService;
    }

    /**
     * Display a listing of the resource.
     */
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
        if(!empty($request->query())){
            if($request->query("gd")){
                $filters[ EmployeeFiltersEnum::GD ] = $request->query("gd");
                
                // filter the subdirectorates 
                $directions = $directions->reject(fn($query) => $query->general_direction_id != $request->query("gd") );
            }

            if($request->query("d")){
                $filters[ EmployeeFiltersEnum::D ] = $request->query("d");
                
                // filter the subdirectorates 
                $subdirectorate = $subdirectorate->reject(fn($query) => $query->direction_id != $request->query("d") );
            }

            if($request->query("sd")){
                $filters[ EmployeeFiltersEnum::SD ] = $request->query("sd");
            }
        }

        // * get employees
        $totalEmployees = 0;
        $data = $this->employeeService->getEmployees(
            take: $elementsToTake,
            skip: ($elementsToTake * ($currentPage - 1)),
            filters:$filters,
            total:$totalEmployees
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
        return Inertia::render('Employees/Index', [
            "employees" => $data,
            "general_direction" => $general_direction,
            "directions" => array_values( $directions->toArray() ),
            "subdirectorate" => array_values( $subdirectorate->toArray() ),
            "showPaginator" => $showPaginator,
            "filters" => [
                "gd" => $request->query('gd', null),
                "d" => $request->query('d', null),
                "sd" => $request->query('sd', null),
                "page" => $currentPage
            ],
            "paginator" => $paginator
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $employee_number)
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


        // * calculate the breadcrumns based on where the request come from
        $breadcrumbs = array(
            ["name"=> "Inicio", "href"=> "/dashboard"],
            ["name"=> "Vista Empleados", "href"=> route('employees.index') ],
            ["name"=> "Empleado: $employee->employeeNumber", "href"=> route('employees.show', $employee->employeeNumber)],
        );
        if( parse_url( $request->headers->get('referer'), PHP_URL_PATH ) == '/inactive' ){
            $breadcrumbs[1] = [
                "name"=> "Inactivos", "href"=> $request->headers->get('referer'),
            ];
        }

        // * return the view
        return Inertia::render('Employees/Show', [
            "employeeNumber" => $employee_number,
            "employee" => isset($employee) ?$employee :null,
            "status" => (object) $status,
            "checa" => (object) $checa,
            "workingHours" => $hours,
            "breadcrumbs" => $breadcrumbs
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
        // TODO: retrive the query params to filter the catalogs

        // * retrive the employee
        $employee = $this->findEmployee($employee_number);
        if($employee instanceof \Illuminate\Http\RedirectResponse){
            return $employee;
        }

        // * retrive the catalogs
        $generalDirections = GeneralDirection::select('id','name')->get()->toArray();
        $directions = Direction::select('id','name', 'general_direction_id')->get()->toArray();
        $subdirectorates = Subdirectorate::select('id', 'name', 'direction_id')->get()->toArray();
        $deparments = Department::select('id', 'name', 'subdirectorate_id')->get()->toArray();


        // * return the view
        return Inertia::render('Employees/Edit', [
            "employeeNumber" => $employee->employeeNumber,
            "employee" => $employee,
            "generalDirections" => $generalDirections,
            "directions" => $directions,
            "subdirectorates" => $subdirectorates,
            "deparments" => $deparments,
            "defaultValues" =>  (object) array(),
        ]);
    }

    /**
     * Update the employee in storage.
     *
     * @param  UpdateEmployeeRequest $request
     * @param  string $employee_number
     * @return void
     */
    public function update(UpdateEmployeeRequest $request, string $employee_number)
    {
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


    public function eventsJson(Request $request, string $employee_number): JsonResponse{

        // * get the range day from the querys
        $from = Carbon::now()->startOfMonth();
        $to = Carbon::now()->endOfMonth();
        if($request->has("from") && $request->has("to")){
            $from = Carbon::parse($request->query("from"));
            $to = Carbon::parse($request->query("to"));
        }

        // * retrive the employee
        $employee = $this->employeeService->getEmployee($employee_number);

        // * get the records
        $records = Record::where('employee_id', $employee->id)
            ->whereBetween('check', [ $from->format('Y-m-d'), $to->format('Y-m-d') ])
            ->get();

        // * get the incidents
        $incidents = Incident::with(['type', 'state'])
            ->where('employee_id', $employee->id)
            ->whereBetween('date', [ $from->format('Y-m-d'), $to->format('Y-m-d') ])
            ->get();

        // * get the justifications
        $justifications = $this->justificationService->getJustificationsEmployee( $employee, $from->format('Y-m-d'), $to->format('Y-m-d') );


        // * parse events
        $events = array();

        foreach($records as $record) {
            $event = new CalendarEvent("Registro", $record->check, $record->check);
            $event->color = "#27ae60";
            $event->type = "RECORD";
            array_push( $events, $event);
        }

        foreach($incidents as $incident){
            $title = $incident->type->name;
            $event = new CalendarEvent($title, $incident->date, $incident->date);
            $event->color = "#dc7633";
            $event->type = "INCIDENT";
            array_push( $events, $event);
        }

        foreach($justifications as $justify) {
            $justify_title = $justify->type->name;
            if( $justify->date_finish != null ){
                $_from = Carbon::parse($justify->date_start);
                $_to = Carbon::parse($justify->date_finish);
                // Loop through each day from start to end date
                for ($date = $_from; $date->lte($_to); $date->addDay()) {
                    $event = new CalendarEvent($justify_title, $date->format('Y-m-d'), $date->format('Y-m-d'));
                    $event->color = "#5499c7";
                    $event->type = "JUSTIFY";
                    array_push( $events, $event);
                }
            }
            else{
                $event = new CalendarEvent($justify_title, $justify->date_start->format('Y-m-d'), $justify->date_start->format('Y-m-d'));
                $event->color = "#5499c7";
                $event->type = "JUSTIFY";
                array_push( $events, $event);
            }

        }

        return response()->json($events, 200);
    }


    #region Incidents
    public function incidentCreate(Request $request, string $employee_number) {

        // * retrive the employee
        $employee = $this->findEmployee($employee_number);
        if($employee instanceof \Illuminate\Http\RedirectResponse){
            return $employee;
        }

        // * return the view
        return Inertia::render('Employees/Incidents/Create', [
            "employeeNumber" => $employee->employeeNumber,
            "employee" => $employee,
            "date" => $request->filled('date') ?$request->query('date') :null
        ]);

    }
    #endregion

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

class EmployeeFiltersEnum {
    const GD = 'general_direction_id';
    const D = 'direction_id';
    const SD = 'subdirectorate_id';
}