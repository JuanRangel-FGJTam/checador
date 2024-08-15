<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\{
    Employee
};
use App\ViewModels\EmployeeViewModel;

class EmployeeService {
    
    
    /**
     * get the employees
     *
     * @param  int $take elemento to return, if its 0 return all
     * @param  int $skip record to skip when take
     * @param  array<string,mixed> $filters filter to apply ['general_direction_id', 'subdirectorate_id', 'direction_id']
     * @param  int $total out of total
     * @return Array<EmployeeViewModel>
     */
    public function getEmployees(int $take = 0, int $skip = 0, array $filters = [], &$total){

        $employees = array();
        $query = Employee::query();

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
        }

        // * set the total people
        $total = $query->count();

        
        // * get the local employees
        if($take > 0){
            $query->skip($skip)->take($take);
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
     * get the employee by the employee number
     *
     * @param  string $employeeNumber
     * @return EmployeeViewModel
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getEmployee(string $employeeNumber) {
        $employee = Employee::where('plantilla_id', '9' . $employeeNumber)->first();
        if( $employee == null){
            throw new ModelNotFoundException("Employee not fount");
        }
        return EmployeeViewModel::fromEmployeeModel($employee);
    }

    
}