<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\{
    Employee,
    WorkingHours,
    WorkingDays
};
use App\ViewModels\EmployeeViewModel;

class EmployeeService {
    
    
    /**
     * get the employees
     *
     * @param  int $take elemento to return, if its 0 return all
     * @param  int $skip record to skip when take
     * @param  array<string,mixed> $filters filter to apply ['general_direction_id', 'subdirectorate_id', 'direction_id', 'active']
     * @param  int $total out of total
     * @return Array<EmployeeViewModel>
     */
    public function getEmployees(int $take = 0, int $skip = 0, array $filters = [], &$total)
    {
        $employees = array();
        $query = Employee::query();

        // * filter the employees by the user level
        if( Auth::user()->level_id == 1) /* Admin */{
            // * apply some filters
            if( !empty($filters) ){
                if(isset($filters['general_direction_id'])){
                    $query->where('general_direction_id', $filters['general_direction_id'] );
                }

                if(isset($filters['subdirectorate_id'])){
                    $query->where('subdirectorate_id', $filters['subdirectorate_id'] );
                }
                if(isset($filters['direction_id'])){
                    $query->where('direction_id', $filters['direction_id'] );
                }

                if( isset($filters['search'])){
                    $query->where(function($q) use ($filters) {
                        $q->where('name', 'like', "%".$filters['search']."%")
                          ->orWhere('plantilla_id', 'like', "%".$filters['search']."%");
                    });
                }

                if( isset($filters['active'])){
                    $query->where('active', $filters['active']);
                }
            }
        }else{
            $__authUser = Auth::user();
            $__currentLevel = Auth::user()->level_id;

            if($__currentLevel > 2){
                $query->where('general_direction_id', $__authUser->general_direction_id );
            }else{
                if(isset($filters['general_direction_id'])){
                    $query->where('general_direction_id', $filters['general_direction_id'] );
                }
            }

            if($__currentLevel > 3){
                $query->where('direction_id', $__authUser->direction_id);
            }else{
                if(isset($filters['direction_id'])){
                    $query->where('direction_id', $filters['direction_id'] );
                }
            }

            if($__currentLevel > 4){
                $query->where('subdirectorate_id', $__authUser->subdirectorates_id);
            }else {
                if(isset($filters['subdirectorate_id'])){
                    $query->where('subdirectorate_id', $filters['subdirectorate_id'] );
                }
            }

            if( isset($filters['search'])){
                $query->where(function($q) use ($filters) {
                    $q->where('name', 'like', "%".$filters['search']."%")
                      ->orWhere('plantilla_id', 'like', "%".$filters['search']."%");
                });
            }

            if( isset($filters['active'])){
                $query->where('active', $filters['active']);
            }

        }

        // * set the total people
        $total = $query->count();

        // * get the local employees
        if($take > 0){
            $query->orderBy('name', 'ASC')->skip($skip)->take($take);
        }

        $employeesRaw = $query->get();

        /**
         * @var Employee $employeeData
         */
        foreach ($employeesRaw as $employeeData) {
            array_push($employees, EmployeeViewModel::fromEmployeeModel($employeeData));
        }

        return $employees;
    }

    /**
     * get the list of employees assigned to the current user
     *
     * @return Collection<Employee>
     */
    public function getEmployeesOfUser(){
        $query = Employee::query();

        // * filter the employees by the user level
        if( Auth::user()->level_id > 1) {
            $__authUser = Auth::user();
            $__currentLevel = Auth::user()->level_id;

            if($__currentLevel >= 2){
                $query->where('general_direction_id', $__authUser->general_direction_id );
            }

            if($__currentLevel >= 3){
                $query->where('direction_id', $__authUser->direction_id);
            }

            if($__currentLevel >= 4){
                $query->where('subdirectorate_id', $__authUser->subdirectorates_id);
            }
        }

        return $query->orderBy('name', 'ASC')->get();
    }


    /**
     * get the employee by the employee number
     *
     * @param  string $employeeNumber
     * @return EmployeeViewModel
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getEmployee(string $employeeNumber) {
        $employee = Employee::where('plantilla_id', '1' . $employeeNumber)->first();
        if( $employee == null){
            throw new ModelNotFoundException("Employee not fount");
        }

        return EmployeeViewModel::fromEmployeeModel($employee);
    }


    /**
     * update employee data
     *
     * @param  string $employeeNumber
     * @param  array $data
     * @return void
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Throwable
     */
    public function updateEmployee(string $employeeNumber, array $data )
    {
        // * get the employee
        $employee = Employee::where('plantilla_id', '1' . $employeeNumber)->first();
        if( $employee == null){
            throw new ModelNotFoundException("Employee not fount");
        }

        // * attempt to update the employee
        try {
            $employee->general_direction_id = $data['general_direction_id'];
            $employee->direction_id = $data['direction_id'];
            $employee->subdirectorate_id = $data['subdirectorate_id'];
            $employee->department_id = $data['department_id'];

            if( isset($data['name'])){
                $employee->name = $data['name'];
            }

            if( isset($data['canCheck'])){
                $employee->status_id = $data['canCheck'];
            }

            if( isset($data['status_id'])){
                $employee->active = $data['status_id'];
            }

            $employee->save();
            Log::notice("Employee '$employeeNumber:$employee->name' was updated.");

        } catch (\Throwable $th) {
            Log::error("Fail to update the employee '{employeeNumber}': {message}", [
                "employeeNumber" => $employee->employeeNumber,
                "message" => $th->getMessage(),
                "request" => $data,
            ]);
            throw $th;
        }

    }

    /**
     * return the employees that dont have assigned a general-direction, direction or subdirection
     *
     * @return array<EmployeeViewModel>
     */
    public function getNewEmployees() {
        $employeesRaw = Employee::where('general_direction_id', 1)
            ->orWhere('general_direction_id', null)
            ->orWhere('general_direction_id', '')
            ->get()
            ->all();

        $employees = array();

        foreach ($employeesRaw as $employee) {
            array_push($employees, EmployeeViewModel::fromEmployeeModel($employee));
        }

        return $employees;
    }

    #region schedule

    /**
     * update employee schedule
     *
     * @param  string $employeeNumber
     * @param  array $scheduleRequest {
     * An array of schedule data.
     *     @type int    $scheduleType Required. The type of schedule.
     *     @type string $checkin      Required. The check-in time (format: H:i).
     *     @type string $toeat        Required. The time allocated for eating (format: H:i).
     *     @type string $toarrive     Required. The arrival time (format: H:i).
     *     @type string $checkout     Required. The check-out time (format: H:i).
     *     @type bool   $midweek      Required. Indicates if the schedule applies to midweek.
     *     @type bool   $weekend      Required. Indicates if the schedule applies to the weekend.
     * }
     * @return void
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException employee not found
     * @throws \Exception fail to updat the schedule
     */
    public function updateEmployeeSchedule(string $employeeNumber, $scheduleRequest ){
        // * get the employee
        $employee = Employee::where('plantilla_id', '1' . $employeeNumber)->first();
        if( $employee == null){
            throw new ModelNotFoundException("Employee not fount");
        }

        DB::beginTransaction();

        // * attempt to update the working hours
        try {
            // * remove the old working hours
            WorkingHours::where('employee_id', $employee->id)->delete();

            // * store new working hours
            if( $scheduleRequest['scheduleType'] == 1) /* Horario corrido */{
                WorkingHours::create([
                    'employee_id' => $employee->id,
                    'checkin' => $scheduleRequest['checkin'],
                    'checkout' => $scheduleRequest['toeat'],
                ]);
            }else /* Horario quebrado */ {
                WorkingHours::create([
                    'employee_id' => $employee->id,
                    'checkin' => $scheduleRequest['checkin'],
                    'toeat' => $scheduleRequest['toeat'],
                    'toarrive' => $scheduleRequest['toarrive'],
                    'checkout' => $scheduleRequest['checkout']
                ]);
            }
        } catch (\Throwable $th) {
            DB::rollback();
            Log::error("Fail to update the employee working hours: {message}", [
                "employee_id" => $employee->id,
                "message" => $th->getMessage(),
                "request" => $scheduleRequest
            ]);
            throw new \Exception($th->getMessage());
        }

        // * attempt tp update the working days
        try {
            $workingDays = WorkingDays::where('employee_id', $employee->id)->first();
            if( $workingDays == null){
                WorkingDays::create([
                    'employee_id' => $employee->id,
                    'week' => $scheduleRequest['midweek'],
                    'weekend' => $scheduleRequest['weekend'],
                ]);
            }else{
                $workingDays->week = $scheduleRequest['midweek'];
                $workingDays->weekend = $scheduleRequest['weekend'];
                $workingDays->save();
            }

        } catch (\Throwable $th) {
            DB::rollback();
            Log::error("Fail to update the employee working days: {message}", [
                "employee_id" => $employee->id,
                "message" => $th->getMessage(),
                "request" => $scheduleRequest
            ]);
            throw new \Exception($th->getMessage());
        }

        DB::commit();
    }

    #endregion

}