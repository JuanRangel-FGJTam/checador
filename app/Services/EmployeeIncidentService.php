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
use Carbon\Carbon;

class EmployeeIncidentService implements EmployeeIncidentInterface {

    
    /**
     * getIncidents
     *
     * @param  mixed $employee_number
     * @return array
     */
    public function getIncidents(string $employee_number, string $date_from, string $date_to): array
    {
        $from = Carbon::parse($date_from);
        $to = Carbon::parse($date_to);

        // * get the employee
        $employee = Employee::where("plantilla_id", "1".$employee_number)->first();
        if( $employee == null){
            throw new ModelNotFoundException("The employee wast not found on the database");
        }

        // * get the incidents
        return Incident::with(['state', 'type'])
            ->where("employee_id", $employee->id)
            ->whereBetween("date", [$from->format("Y-m-d"), $to->format("Y-m-d")])
            ->get()
            ->toArray();
    }


}