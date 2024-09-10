<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Services\EmployeeService;
use App\Models\{
    TypeJustify,
    GeneralDirection
};

class HollidaysController extends Controller
{

    protected EmployeeService $employeeService;

    function __construct(EmployeeService $employeeService) {
        $this->employeeService = $employeeService;
    }


    public function create(){

        // * get catalogs
        $justificationsType = TypeJustify::select('id', 'name')->get()->all();
        $generalDirections = GeneralDirection::select('id', 'name')->get()->all();

        return Inertia::render('Hollidays/Create', [
            "justificationsType" => $justificationsType,
            "generalDirections" => $generalDirections,
            "breadcrumbs" => null
        ]);
    }

    public function preStore(Request $request){

        // * validate the request
        $request->validate([
            "initialDay" => "required|date",
            "endDay" => "nullable|date|after:initialDay",
            "type_id" => "required|integer|exists:type_justifies,id",
            "file" => "required|file|mimes:pdf|max:15240",
            "general_direction" => 'required|integer|exists:general_directions,id'
        ],[],[
            "initialDay" => "fecha inicial",
            "endDay" => "fecha final",
            "type_id" => "tipo de justificacion",
            "general_direction" =>"direccion general",
            "file" => "oficio"
        ]);

        // * validate the days are in the same month
        if($request->filled('endDay')){
            $request->validate([
                'endDay' => [
                    'date',
                    function ($attribute, $value, $fail) use ($request) {
                        $initialDay = \Carbon\Carbon::parse($request->initialDay);
                        $endDay = \Carbon\Carbon::parse($value);
                        if ($initialDay->month !== $endDay->month || $initialDay->year !== $endDay->year) {
                            $fail('De momento no es posible justificar fechas de meses diferentes. Por favor, intente justificar mes por mes.');
                        }
                    }
                ]
            ]);
        }

        $sessionId = Str::uuid();

        // * store the file uploaded
        $filepath = "/tmp/$sessionId/" . $request->file('file')->getClientOriginalName();
        Storage::disk('local')->put($filepath, $request->file('file')->get() );

        // * get employees of the user
        $employees = $this->employeeService->getEmployeesOfUser()
            ->where('active',1)
            ->where('status_id',1);

        if( Auth::user()->level_id == 1){
            $employees = $employees->where('general_direction_id', $request->input('general_direction'));
        }

        // * store flash data (only available for the next request)
        $request->session()->flash( $sessionId, [
            "justifyType" => TypeJustify::find($request->input('type_id')),
            "generalDirection" => GeneralDirection::find( $request->input('general_direction')),
            "startDay" => Carbon::parse( $request->input('initialDay') ),
            "endDay" => $request->filled('endDay') ?Carbon::parse( $request->input('endDay') ) :null,
            "filepath" => $filepath,
            "employees" => $employees
        ]);

        // * redirect to the next view
        return redirect()->route('hollidays.validateEmployees', ['session' => $sessionId]);
    }

    public function validateEmployees(Request $request, string $session){

        // * retrive the data from the previous step
        $data = session($session);
        if( $data == null){
            return redirect()->route('hollidays.create');
        }

        // * return the view
        return Inertia::render('Hollidays/ValidateEmployees', [
            "employees" => array_values( $data['employees']->toArray() )
        ]);
    }

}
