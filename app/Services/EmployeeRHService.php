<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use App\Models\{
    Employee
};
use App\Models\EmployeeRh;
use \stdClass;

class EmployeeRHService {

    /**
     * getEmployeeData
     *
     * @param  string|int $employee_number
     * @return mixed
     */
    public static function getEmployeeData($employee_number)
    {
        // TODO: retrive the data using EmployeeRh model, temporally return a random data
        // $employeeRh = EmployeeRh::select('NUMEMP', 'NOMBRE', 'APELLIDO', 'RFC')->where('NUMEMP', $employee_number)->first();

        try {
            $_employee = Employee::where('plantilla_id', '1'.$employee_number)->firstOrFail();
            $employee = new stdClass();
            $employee->NUMEMP = (int) $employee_number;
            $employee->NOMBRE = $_employee->name;
            $employee->APELLIDO = "Apellidos Prueba";
            $employee->RFC = "RAAJ931217";
            $employee->CURP = "RAAJ931217HGTNLN03";
            return $employee;

        }catch(\Throwable $th) {
            Log::error("Fail to get the employee '{employee_number}' data from the RH service: {message} ", [
                "employee_number" => $employee_number,
                "message" => $th->getMessage(),
            ]);
            return null;
        }
    }
    
}