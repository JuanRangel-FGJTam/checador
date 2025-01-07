<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\{
    EmployeeService,
    InactiveService
};
use App\Models\{
    Employee,
    GeneralDirection
};

class InactiveHistoyController extends Controller
{

    protected EmployeeService $employeeService;
    protected InactiveService $inactiveService;
    
    function __construct(EmployeeService $employeeService, InactiveService $inactiveService)
    {
        $this->employeeService = $employeeService;
        $this->inactiveService = $inactiveService;
    }

    function index(Request $request)
    {
        $currentPage = $request->query('p', 1);
        $elementsToTake = 25;

        // * get the history of inactive employees
        $data = $this->inactiveService->getHistoryInactive(
            take: $elementsToTake,
            page: ($currentPage - 1)
        );
        
        // * return the viewe
        return Inertia::render('InactiveHistory/Index', [
            "title" => "Historial de inactivos",
            "data" => $data,
            "filters" => [
                "page" => $currentPage,
                "take" => $elementsToTake
            ]
        ]);

    }

}
