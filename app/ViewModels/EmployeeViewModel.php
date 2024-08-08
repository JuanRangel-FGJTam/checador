<?php

namespace App\ViewModels;

use App\Models\Employee;

class EmployeeViewModel
{

    public int $id;
    public string $employeeNumber;
    public string $name;
    public ?string $curp;
    public string $abbreviation;  // general direction abbreviation
    public string $generalDirection;
    public int $generalDirectionId;
    public string $direction;
    public int $directionId;
    public int $statusId;
    public ?string $photo;
    public string $horario;
    public string $days;
    
    public function __construct(int $id, string $employeeNumber, string $name) {
        $this->id = $id;
        $this->employeeNumber = $employeeNumber;
        $this->name = $name;

        $this->abbreviation = "";
        $this->generalDirection = "";
        $this->direction = "";
        $this->statusId = 0;
        $this->horario = 'Sin horario asignado';
        $this->days = 'Días no asignados';
    }
    
    /**
     * create a employee view model from the local model
     *
     * @param  Employee $employee
     * @return EmployeeViewModel
     */
    public static function fromEmployeeModel( Employee $employee) : EmployeeViewModel {

        // create the view model
        $model = new EmployeeViewModel(
            $employee->id,
            substr($employee->plantilla_id, 1),
            $employee->name
        );
        $model->statusId = $employee->status_id;

        $employee->load(['generalDirection', 'direction', 'workingHours', 'workingDays']);

        if( isset($employee->generalDirection) ){
            $model->abbreviation = $employee->generalDirection->abbreviation;
            $model->generalDirection = $employee->generalDirection->name;
            $model->generalDirectionId = $employee->generalDirection->id;
        }

        if( isset($employee->direction) ){
            $model->direction = $employee->direction->name;
            $model->directionId = $employee->direction->id;
        }

        if($employee->workingHours)  {
            if ($employee->workingHours->checkin) {
                $model->horario = $employee->workingHours->checkin . ' a ' . $employee->workingHours->checkout;
            }
        }

        if($employee->workingDays)  {
            if ($employee->workingHours->checkin) {
                $model->horario = $employee->workingHours->checkin . ' a ' . $employee->workingHours->checkout;
            }
        }
        
        if ($employee->workingDays) {
            $allWeek = 0;
            if ($employee->workingDays->week == 1) {
                $model->days = 'Lun - Vier ';
                $allWeek ++;
            }
            if ($employee->workingDays->weekend == 1) {
                $model->days = ' Sab y Dom';
                $allWeek ++;
            }
            if ($allWeek == 2) {
                $model->days = 'Lun - Dom';
            }
        }

        // TODO: retrive the data from the RH database
        // $employeeRh = EmployeeRh::select('NUMEMP', 'NOMBRE', 'APELLIDO', 'RFC')->where('NUMEMP', $employee_number)->first();
        $employeeRh = \App\Services\EmployeeRHService::getEmployeeData( $model->employeeNumber );
        if($employeeRh){
            $model->name = $employeeRh->NOMBRE . ' ' . $employeeRh->APELLIDO;
            $model->curp = $employeeRh->CURP;
        }

        return $model;
    }

}