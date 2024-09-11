<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\{
    EmployeeService,
};
use App\Models\{
    Employee,
    GeneralDirection
};


class InactiveController extends Controller
{

    protected EmployeeService $employeeService;
    
    function __construct( EmployeeService $employeeService)
    {
        $this->employeeService = $employeeService;
    }


    function index(Request $request){

        $currentPage = $request->query('p', 1);
        $elementsToTake = 50;

        // * get catalogs
        $general_direction = GeneralDirection::select('id', 'name')->get();
        

        // * prepare the filters
        $filters = array();
        $filters['active'] = 0;


        if( $request->filled('gd')){
            $filters[ "general_direction_id" ] = $request->query("gd");
        }


        // * get employees
        $totalEmployees = 0;
        $data = $this->employeeService->getEmployees(
            take: $elementsToTake,
            skip: ($elementsToTake * ($currentPage - 1)),
            filters:$filters,
            total:$totalEmployees
        );


        // * make paginator
        $showPaginator = $elementsToTake < $totalEmployees;
        $paginator = [
            "from" => $elementsToTake * ($currentPage - 1),
            "to" =>  $elementsToTake * $currentPage,
            "total" => $totalEmployees,
            "pages" =>  range(1, ceil( $totalEmployees / $elementsToTake))
        ];


        // * return the viewe
        return Inertia::render('Inactive/Index', [
            "employees" => $data,
            "general_direction" => $general_direction,
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

}
