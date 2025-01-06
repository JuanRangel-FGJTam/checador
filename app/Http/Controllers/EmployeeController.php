<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
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
    EmployeeStatusHistory,
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
    UpdateEmployeeRequest,
    UpdateEmployeeStatusRequest
};
use App\Helpers\EmployeeKardexRecords;
use App\Helpers\EmployeeKardexExcel;
use Illuminate\Support\Facades\DB;

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
        $generalDirectionId = null;
        $directionId = 0;
        $subdirectionId = 0;

        // * set by defaul the user general direction asigneds
        $generalDirectionId = Auth::user()->general_direction_id;

        if(Auth::user()->level_id > 1){
            if( Auth::user()->level_id > 2){
                $directionId = Auth::user()->direction_id;
            }else{
                $directionId = $request->filled('d') ?$request->query("d") :null;
            }

            $subdirectionId = $request->query("sd");

        }else{
            if( $request->filled('gd')){
                $generalDirectionId = $request->query("gd");
            }
            if( $request->filled('d')){
                $directionId = $request->query("d");
            }
            if( $request->filled('sd')){
                $subdirectionId = $request->query("sd");
            }
        }

        // * get catalogs
        $generalDirections = GeneralDirection::select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();

        $directions = Direction::select('id', 'name', 'general_direction_id')
            ->orderBy('name', 'asc')
            ->get();

        $subdirectorate = Subdirectorate::select('id', 'name', 'direction_id')
            ->orderBy('name', 'asc')
            ->get();

        // * prepare the filters
        $filters = array();

        if( isset($generalDirectionId)){
            $filters[ EmployeeFiltersEnum::GD ] = $generalDirectionId;
            $directions = $directions->where('general_direction_id', $generalDirectionId);
        }

        if( isset($directionId)){
            $filters[ EmployeeFiltersEnum::D ] = $directionId;
            $subdirectorate = $subdirectorate->where('direction_id', $directionId);
        }

        if( isset($subdirectionId)){
            $filters[ EmployeeFiltersEnum::SD ] = $subdirectionId;
        }

        if( $request->filled("se")){
            $filters['search'] = $request->query("se");
        }

        // If user is not admin load active employees only
        if(Auth::user()->level_id != 1) { 
            $filters['active'] = 1;
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
            "general_direction" => $generalDirections,
            "directions" => array_values( $directions->toArray() ),
            "subdirectorate" => array_values( $subdirectorate->toArray() ),
            "showPaginator" => $showPaginator,
            "filters" => [
                "gd" => $generalDirectionId,
                "d" => $directionId,
                "sd" => $subdirectionId,
                "page" => $currentPage,
                "search" => $request->filled("se") ?$request->input("se") :null
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

           abort(404);
        } catch (\Throwable $th) {
            Log::error("Unhandle exception at attempting to get the employee at EmployeeController.show: {message}", [
                "employee_number" => $employee_number,
                "message" => $th->getMessage(),
            ]);

            abort(500);
        }

        // calculate status
        $status = array(
            'name' => 'BAJA',
            'class' => 'text-xs p-1 rounded border border-red-500 text-red-600'
        );
        if ($employee->active) {
            $status = array(
                'name' => 'ACTIVO',
                'class' => 'text-xs p-1 rounded border border-green-500 text-green-600'
            );
        }

        // calculate status checa
        $checa = array(
            'name' => 'REGISTRA ASISTENCIA',
            'class' => 'text-xs p-1 rounded border border-green-500 text-green-600'
        );
        if ($employee->checa != 1) {
            $checa = array(
                'name' => 'NO REGISTRA ASISTENCIA',
                'class' => 'text-xs p-1 rounded border border-red-500 text-red-600'
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
            ["name"=> "Inicio", "href"=> route('employees.index') ],
            ["name"=> "Empleado: $employee->employeeNumber", "href"=> route('employees.show', $employee->employeeNumber)],
        );

        if( parse_url( $request->headers->get('referer'), PHP_URL_PATH ) == '/inactive' ){
            $breadcrumbs[1] = [
                "name"=> "Inactivos", "href"=> $request->headers->get('referer'),
            ];
        }

        // Validate if photo employee exists $employee->photo in public folder
        $employeePhoto = '/images/unknown.png';
        // validate if photo exists in directory
        if($employee->photo != null) {
            $employeePhoto = public_path($employee->photo);
            if (file_exists($employeePhoto)) {
                $employeePhoto = asset($employee->photo);
            }
        }

        // * return the view
        return Inertia::render('Employees/Show', [
            "employeeNumber" => $employee_number,
            "employee" => isset($employee) ?$employee :null,
            "status" => (object) $status,
            "checa" => (object) $checa,
            "workingHours" => $hours,
            "breadcrumbs" => $breadcrumbs,
            "employeePhoto" => $employeePhoto,
        ]);
    }

    /**
     * show the form for editing the employee
     *
     * @param  string $employee_number
     * @return void
     */
    public function edit(Request $request, string $employee_number)
    {
        // * retrive the employee
        $employee = $this->findEmployee($employee_number);
        if($employee instanceof \Illuminate\Http\RedirectResponse){
            return $employee;
        }

        // * retrieve the query parameters to filter the catalogs if is necessary
        $_gd = $employee->generalDirectionId ?? 1;
        $_di = $employee->directionId ?? 1;
        $_sd = $employee->subDirectionId ?? 1;

        if($request->filled('gd')){
            $_gd = $request->query('gd');
            $_di = $request->query('di');
            $_sd = $request->query('sd');
        }

        // * retrive the catalogs
        $generalDirections = GeneralDirection::select('id','name')->get()->sortBy('name')->all();

        $directions = Direction::select('id','name', 'general_direction_id')
            ->where('general_direction_id', $_gd)
            ->orWhere('general_direction_id', 1) // include "1|DESCONOCIDO"
            ->get()->sortBy('name')->all();

            $subdirectorates = Subdirectorate::select('id', 'name', 'direction_id')
            ->where('direction_id', $_di)
            ->orWhere('direction_id', 1) // include "1|DESCONOCIDO"
            ->get()->sortBy('name')->all();

            $deparments = Department::select('id', 'name', 'subdirectorate_id')
            ->where('subdirectorate_id', $_sd)
            ->orWhere('subdirectorate_id', 1) // include "1|DESCONOCIDO"
            ->get()->sortBy('name')->all();

        // * return the view
        return Inertia::render('Employees/Edit', [
            "employeeNumber" => $employee->employeeNumber,
            "employee" => $employee,
            "generalDirections" => array_values($generalDirections),
            "directions" => array_values($directions),
            "subdirectorates" => array_values($subdirectorates),
            "deparments" => array_values($deparments),
            "defaultValues" => (object) array(),
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

    /**
     * Update the employee status in storage.
     *
     * @param  UpdateEmployeeStatusRequest $request
     * @param  string $employee_number
     * @return void
     */
    public function updateStatus(UpdateEmployeeStatusRequest $request, string $employee_number)
    {
        // * retrive the employee
        $employee = $this->findEmployee($employee_number);
        if($employee instanceof \Illuminate\Http\RedirectResponse){
            return $employee;
        }
        // * retrive the current user
        $user = Auth::user();

        Log::info("Attemtp to update the status of the employee '{employeeNumber}' to '{statusId}' by '{userName}'", [
            "employeeNumber" => $employee->employeeNumber,
            "statusId" => $request->status_id,
            "userName" => $user->name
        ]);

        DB::beginTransaction();

        // * change the employee status
        try
        {
            $this->employeeService->updateEmployeeStatus( $employee->employeeNumber, $request->status_id);
        }
        catch (\Throwable $th)
        {
            DB::rollback();
            return redirect()->back()->withErrors([
                "message" => "Error al cambiar el estatus del empleado, No se pudo cambiar el estatus."
            ])->withInput();
        }

        // * store the document if exists
        $filePath = null;
        try
        {
            if($request->hasFile('file'))
            {
                $filePath = $this->storeJustificationFile(
                    $request->file('file'),
                    $employee->employeeNumber
                );
            }
        }
        catch (\Throwable $th)
        {
            Log::error("Fail to store the justification file at EmployeeController.updateStatus: {message}", [
                "message" => $th->getMessage()
            ]);
            DB::rollback();
            return redirect()->back()->withErrors([
                "message" => "Error al cambiar el estatus del empleado, No se pudo almacenar el archivo de justificacion."
            ])->withInput();
        }

        try
        {
            // * create a record of EmployeeStatushistory with the new status and the file path of the document
            $histoyRecord = new EmployeeStatusHistory([
                'user_id' => $user->id,
                'employee_id' => $employee->id,
                'comments' => $request->comments,
                'file' => $filePath,
                'status' => $request->status_id == 1 ?"Activo" :"Baja"
            ]);
            $histoyRecord->save();
        } catch (\Throwable $th) {
            Log::error("Fail to store the history record at EmployeeController.updateStatus: {message}", [
                "message" => $th->getMessage()
            ]);
            DB::rollBack();
            return redirect()->back()->withErrors([
                "message" => "Error al cambiar el estatus del empleado, No se pudo registrar el cambio en la bitacora."
            ])->withInput();
        }

        DB::commit();
        Log::info("Successfull updated the status of the employee '{employeeNumber}' to '{statusId}' by '{userName}'", [
            "employeeNumber" => $employee->employeeNumber,
            "statusId" => $request->status_id,
            "userName" => $user->name
        ]);

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
            ->whereDate('check', '>=', $from->format('Y-m-d'))
            ->whereDate('check', '<=', $to->format('Y-m-d'))
            ->get();

        // * get the incidents
        $incidents = Incident::with(['type', 'state'])
            ->where('employee_id', $employee->id)
            ->whereDate('date', '>=', $from->format('Y-m-d'))
            ->whereDate('date', '<=', $to->format('Y-m-d'))
            ->get();

        // * get the justifications
        $justifications = $this->justificationService->getJustificationsEmployee( $employee, $from->format('Y-m-d'), $to->format('Y-m-d') );

        // * parse events
        $events = array();

        foreach($records as $record) {
            $event = new CalendarEvent(" ", $record->check, $record->check);
            $event->color = "#27ae60";
            $event->type = "RECORD";
            array_push( $events, $event);
        }

        foreach($incidents as $incident){
            $title = $incident->type->name;
            $event = new CalendarEvent($title, $incident->date, $incident->date);
            $event->color = "#ef8b11";
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
                    $event->color = "#3ea1e7";
                    $event->type = "JUSTIFY";
                    array_push( $events, $event);
                }
            }
            else{
                $event = new CalendarEvent($justify_title, $justify->date_start->format('Y-m-d'), $justify->date_start->format('Y-m-d'));
                $event->color = "#3ea1e7";
                $event->type = "JUSTIFY";
                array_push( $events, $event);
            }
        }

        return response()->json($events, 200);
    }

    public function kardexEmployee(Request $request, string $employee_number){
        // * retrive the employee
        $employee = null;
        try {
            $employeeVM = $this->employeeService->getEmployee($employee_number);
            $employee = Employee::with(['workingHours', 'workingDays', 'generalDirection'])->findOrFail($employeeVM->id);
        } catch (ModelNotFoundException $nf) {
            Log::warning("Employee with employee number '$employee_number' not found");

            // * redirect back
            return redirect()->back()->withErrors([
                "employee_number" => "Empleado no encontrado",
                "message" => "Empleado no encontrado"
            ])->withInput();
        }

        $workingHours = $employee->workingHours;
        $year = $request->input('year');
        $today = new \DateTime();


        // * attempt to get the cache kardex record from the mongodb
        $recordMongo = \App\Models\KardexRecord::where('employee_id', $employee->id)
            ->where('report_date', '=', $today->format('Y-m-d'))
            ->where('year', '=', $year)
            ->first();

        if ($recordMongo) {
            $dataUser = $recordMongo->data;
        } else {
            if ($workingHours) {
                if (!$workingHours->checkin || $workingHours->checkin == '') {
                    throw new \Exception("The employee has no working schedule assigned.");
                }
            }

            // * make the records of the employee kardex
            $employeeKardexRecords = new EmployeeKardexRecords($employee);
            $dataUser = $employeeKardexRecords->makeRecords($year);
        }

        // * make the excel file
        $employeeKardexExcel = new EmployeeKardexExcel($dataUser, $employee->generalDirection->name);
        $documentContent = $employeeKardexExcel->create();
        if( $documentContent === false){
            // TODO: Log fail
            throw new \Exception("Fail to make the report document");
        }

        // * store the file
        $fileName = sprintf("%s.xlsx", (string) Str::uuid() );
        $filePath = sprintf("tmp/kardex/$fileName");
        if( Storage::disk('local')->put( $filePath, $documentContent ) ){
            Log::info('User ' . Auth::user()->name . ' generate daily report for year ' . $request->input('year'));
        }else {
            Log::warning('Fail at stored the report of the employee kardex by User ' . Auth::user()->name);
        }

        // * download the file
        $name = "kardex-empleado.xlsx";
        return Storage::disk('local')->download($filePath, $name);
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

    /**
     * store the justification file and return the path
     *
     * @param  mixed $file
     * @param  string $employee_number
     * @param  string $date
     * @return string
     */
    private function storeJustificationFile($file, $employee_number): string {
        $cDate = Carbon::now();
        $name = sprintf("%s-%s.pdf", $employee_number, $cDate->timestamp);
        return Storage::disk("local")->putFileAs('justificantes-cambio-estatus', $file, $name );
    }
    #endregion

}

class EmployeeFiltersEnum {
    const GD = 'general_direction_id';
    const D = 'direction_id';
    const SD = 'subdirectorate_id';
}