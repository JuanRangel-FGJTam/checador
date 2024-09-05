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
    Subdirectorate
};

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

}
