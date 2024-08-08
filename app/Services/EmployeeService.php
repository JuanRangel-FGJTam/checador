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
     * @return Array<EmployeeViewModel>
     */
    public function getEmployees(int $count = 0, int $offset = 0){

        $employees = array();
        
        // * get the local employees
        if( $count > 0){
            $employeesRaw = Employee::all();
        }else {
            $employeesRaw = Employee::all();
        }

        foreach ($employeesRaw as $employeeData) {
            array_push( $employees, EmployeeViewModel::fromEmployeeModel($employeeData) );
        }

        return $employees;
    }
    
}