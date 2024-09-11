<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
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

        // * get the general direction based on the user level
        $generalDirectionId = null;
        if(Auth::user()->level_id > 1){
            $generalDirectionId = Auth::user()->general_direction_id;
        }else{
            if( $request->filled('gd')){
                $generalDirectionId = $request->query("gd");
            }
        }


        // * get catalogs
        $generalDirections = GeneralDirection::select('id', 'name')->get();
        

        // * prepare the filters
        $filters = array();
        $filters['active'] = 0;
        if( $generalDirectionId != null && $generalDirectionId > 0){
            $filters["general_direction_id"] = $generalDirectionId;
        }
        if( $request->filled("se")){
            $filters['search'] = $request->query("se");
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
            "general_direction" => $generalDirections,
            "showPaginator" => $showPaginator,
            "filters" => [
                "gd" => $generalDirectionId,
                "page" => $currentPage,
                "search" => $request->filled("se") ?$request->input("se") :null
            ],
            "paginator" => $paginator
        ]);

    }

}
