<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use App\Interfaces\EmployeeIncidentInterface;
use App\Models\{
    Employee,
    Incident
};

class EmployeeIncidentService implements EmployeeIncidentInterface {

    
    /**
     * getIncidents
     *
     * @param  mixed $employee_number
     * @return array
     */
    public function getIncidents(string $employee_number): array
    {
        // * get the employee
        $employee = Employee::where("plantilla_id", "9".$employee_number)->first();
        if( $employee == null){
            throw new ModelNotFoundException("The employee wast not found on the database");
        }

        // * get the incidents
        return Incident::where("employee_id", $employee->id)->get()->toArray();
    }


}