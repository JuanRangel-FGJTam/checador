<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Incident;


interface EmployeeIncidentInterface {

    
    /**
     * get the incidents of the employee
     *
     * @param  string $employee_number
     * @return array<Incident>
     */
    public function getIncidents(string $employee_number): array;


    
}