<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Inertia\Inertia;
use Exception;
use App\Services\EmployeeService;
use App\Http\Requests\{
    EmployeeScheduleRequest
};
use App\Models\{
    Employee,
    WorkingHours,
    WorkingDays
};

class EmployeeScheduleController extends Controller
{

    protected EmployeeService $employeeService;

    function __construct( EmployeeService $employeeService )
    {
        $this->employeeService = $employeeService;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $employee_number)
    {
        // * attempt to get the employee
        $employee = null;
        try {
            $employee = $this->employeeService->getEmployee($employee_number);
        } catch (ModelNotFoundException $nf) {
            Log::warning("Employee with employee number '$employee_number' not found");
            return redirect()->route('employees.index');
        }

        
        // * get current schedule
        $defaultValues = [
            "scheduleType" => 1,
            "checkin" => null,
            "toeat" => null,
            "toarrive" => null,
            "checkout" => null,
            "midweek" => null,
            "weekend" => null,
        ];
        $workingHours = WorkingHours::where("employee_id", $employee->id)->first();
        if( $workingHours != null){
            if( $workingHours->toeat == null){
                $defaultValues["scheduleType"] = 1;
                $defaultValues["checkin"] = $workingHours->checkin;
                $defaultValues["toeat"] = $workingHours->checkout;
            }else {
                $defaultValues["scheduleType"] = 2;
                $defaultValues["checkin"] = $workingHours->checkin;
                $defaultValues["toeat"] = $workingHours->toeat;
                $defaultValues["toarrive"] = $workingHours->toarrive;
                $defaultValues["checkout"] = $workingHours->checkout;
            }
        }
        $workinDays = WorkingDays::where("employee_id", $employee->id)->first();
        if( $workinDays != null){
            $defaultValues["midweek"] = $workinDays->week;
            $defaultValues["weekend"] = $workinDays->weekend;
        }
        

        // * return view
        return Inertia::render('Employees/Schedule/Edit', [
            "employeeNumber" => $employee->employeeNumber,
            "employee" => $employee,
            "defaultValues" => $defaultValues
        ]);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EmployeeScheduleRequest $request, string $employee_number)
    {
        // * attempt to get the employee
        $employee = null;
        try {
            $employee = $this->employeeService->getEmployee($employee_number);
        } catch (ModelNotFoundException $nf) {
            Log::warning("Employee with employee number '$employee_number' not found");
            return redirect()->back()->withErrors([
                "message" => "El numero de empleado no existe o esta inactivo"
            ])->withInput();
        }

        // * attempt to update the employee schedule
        try {
            $this->employeeService->updateEmployeeSchedule(
                employeeNumber: $employee->employeeNumber,
                scheduleRequest: $request->request->all()
            );
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors([
                "message" => $th->getMessage()
            ])->withInput();
        }

        // * redirecto to employee view
        return redirect()->route('employees.show', ['employee_number' => $employee_number]);

    }

}