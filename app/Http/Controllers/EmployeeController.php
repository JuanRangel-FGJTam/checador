<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\EmployeeService;
use App\Models\{
    Employee,
    GeneralDirection,
    Direction,
    Subdirectorate

};

class EmployeeController extends Controller
{

    protected EmployeeService $employeeService;

    function __construct( EmployeeService $employeeService )
    {
        $this->employeeService = $employeeService;
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
    public function show(string $employee_number)
    {
        dd( "Show employee", $employee_number);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee, $employeeId)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $employeeId)
    {
        //
    }

}

class EmployeeFiltersEnum {
    const GD = 'general_direction_id';
    const D = 'direction_id';
    const SD = 'subdirectorate_id';
}