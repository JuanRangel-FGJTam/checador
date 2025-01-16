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
    EmployeeStatusHistory,
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
        $elementsToTake = 100;

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

    /**
     * retrive the document`
     *
     * @param  int $inactiveHistoryId
     * @return void
     */
    function getJustificationFile(int $inactiveHistoryId){

        // * get the model
        $employeeStatusHistory = EmployeeStatusHistory::find($inactiveHistoryId);
        if( $employeeStatusHistory == null)
        {
            return response()->json([ "message" => "History record not found on the system." ], 404);
        }

        // * get the document
        if (Storage::disk('local')->exists($employeeStatusHistory->file))
        {
            $fileContents = Storage::disk('local')->get($employeeStatusHistory->file);
            $fileName = basename($employeeStatusHistory->file);

            return response($fileContents, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="'.$fileName.'"');
        }
        else
        {
            return response()->json(['message' => 'File not found'], 404);
        }

    }

}
