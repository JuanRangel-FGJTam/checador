<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\Record;
use App\Models\WorkingHours;
use DateTime;

class DailyReportFactory {

    /**
     * @var array<Employee> $employees
     */
    protected array $employees = [];
    protected DateTime $dateReport;
    
    /**
     *
     * @param  array<Employee>|Collection<Employee> $employees
     * @param  string|DateTime|null $dateReport
     * @return void
     */
    function __construct($employees, $dateReport) {
        if( $employees instanceof Collection){
            $this->employees = $employees->all();
        }else{
            $this->employees = $employees;
        }

        if( $dateReport != null){
            if( $dateReport instanceof DateTime){
                $this->dateReport = $dateReport;
            }else{
                $this->dateReport = new DateTime($dateReport);
            }
        }else{
            $this->dateReport = new DateTime();
        }
    }
    
    /**
     * makeReportData
     *
     * @return array
     */
    function makeReportData(){
        $data = array();

        foreach( $this->employees as $employee) {
            $employeeData = $employee->toArray();
            array_push( $data, $this->makeEmployeeRow( $employeeData ));
        }

        return $data;
    }

    /**
     * makeEmployeeRow
     *
     * @param  Employee|Array $employee
     * @return mixed
     */
    private function makeEmployeeRow($employee){

        if( $employee instanceof Employee){
            $employee = $employee->toArray();
        }
        
        // * prepare response data
        $responseData = array();
        $responseData['name'] = $employee['name'];
        $responseData['employee_number'] = substr( $employee['plantilla_id'], 1);
        $responseData['checkin'] = 'S/H';
        $responseData['toeat'] = 'S/H';
        $responseData['toarrive'] = 'S/H';
        $responseData['checkout'] = 'S/H';

        $checaComida = false;
        
        // TODO:  Get general data from RH DB
        // $employee_info = EmployeeRh::select('NUMEMP', 'NOMBRE', 'APELLIDO', 'RFC')->where('NUMEMP', $employee_number)->first();
        // if ($employee_info) {
        //     $name = Str::title( $employee_info->APELLIDO.' '.$employee_info->NOMBRE );
        // }

        // * validate if the employee has working hours assigned
        if( empty($employee['working_hours']) ) {
            return $responseData;
        }

        $workingHours = (object) $employee['working_hours'];
        
        // * validate if the employee has a check record on the day
        if ( !$workingHours->checkin && !$workingHours->checkout){
            return $responseData;
        }
        

        // * validate if the employee has multiple working hours
        if (!empty($workingHours->toeat) && !empty($workingHours->toarrive)) {
            $checaComida = true;
        }

        
        // * get check records of the employee
        $records = Record::select('check')
            ->where('employee_id', $employee['id'])
            ->whereDate('check', $this->dateReport->format('Y-m-d'))
            ->get();
        
        
        // * horario corrido
        $hour1 = strtotime($workingHours->checkin);
        $hour2 = strtotime($workingHours->checkout);
        
        // * horario quebrado
        $hour3 = strtotime($workingHours->toeat);
        $hour4 = strtotime($workingHours->toarrive);
        

        // * get the hours checked in the day
        $recordsArray = [];
        foreach ($records as $record) {
            $timeRecord = Carbon::parse( $record->check)->format("H:i");
            if (!in_array($timeRecord, $recordsArray)) {
                $recordsArray[] = $timeRecord;
            }
        }

        // * iterate records
        $checkin = false;
        $checkout = false;
        $eat = false;
        $arrive = false;
        foreach ($recordsArray as $timeRecord) {

            $diffCheckin = round( abs(strtotime($timeRecord) - $hour1) / 3600, 2);
            $diffCheckout = round(abs(strtotime($timeRecord) - $hour2) / 3600, 2);

            $diffToEat = round(abs(strtotime($timeRecord) - $hour3) / 3600, 2);
            $diffToArrive = round(abs(strtotime($timeRecord) - $hour4) / 3600, 2);

            if (!$checaComida) {
                if ($diffCheckin < $diffCheckout && !$checkin) {
                    $checkin = $timeRecord;
                } else {
                    $checkout = $timeRecord;
                }
                $eat = '---';
                $arrive = '---';
            } else {
                if ($diffCheckin < $diffToEat && !$checkin) {
                    $checkin = $timeRecord;
                } elseif ($diffToEat < $diffToArrive) {
                    $eat = $timeRecord;
                } elseif ($diffToArrive < $diffCheckout) {
                    $arrive = $timeRecord;
                } else {
                    $checkout = $timeRecord;
                }
            }
        }
        
        $responseData['checkin'] = $checkin;
        $responseData['toeat'] = $eat;
        $responseData['toarrive'] = $arrive;
        $responseData['checkout'] = $checkout;

        return $responseData;
        
    }

}