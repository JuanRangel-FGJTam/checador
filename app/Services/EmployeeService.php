<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\{
    Employee
};
use App\ViewModels\EmployeeViewModel;

use function PHPUnit\Framework\isEmpty;

class EmployeeService {
    
    
    /**
     * get the employees
     *
     * @return Array<EmployeeViewModel>
     */
    public function getEmployees(int $take = 0, int $skip = 0, array $filters = []){

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
    
}