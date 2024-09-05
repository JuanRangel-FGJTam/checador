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

}
