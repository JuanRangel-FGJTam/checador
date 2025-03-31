<?php

namespace App\ViewModels;

use App\Models\Employee;
use App\Models\EmployeeRh;
use App\Models\WorkingHours;
use App\Services\EmployeeRHService;

class EmployeeViewModel
{

    public int $id;
    public string $employeeNumber;
    public string $name;
    public ?string $curp;
    public string $abbreviation;  // general direction abbreviation
    public string $generalDirection;
    public int $generalDirectionId;
    public string $direction = "";
    public int $directionId;
    public int $checa;
    public ?string $photo;
    public string $horario;
    public string $days;
    public bool $active = true;
    public ?string $subDirection = "";
    public ?int $subDirectionId = null;
    public ?string $department = "";
    public ?int $departmentId = null;
    public bool $isRH = false;
    
    public function __construct(int $id, string $employeeNumber, string $name) {
        $this->id = $id;
        $this->employeeNumber = $employeeNumber;
        $this->name = $name;
        $this->abbreviation = "";
        $this->generalDirection = "";
        $this->direction = "";
        $this->checa = 0;
        $this->horario = 'Sin horario asignado';
        $this->days = 'Días no asignados';
    }
    
    /**
     * create a employee view model from the local model
     *
     * @param  Employee $employee
     * @return EmployeeViewModel
     */
    public static function fromEmployeeModel( Employee $employee) : EmployeeViewModel 
    {
        // create the view model
        $model = new EmployeeViewModel(
            $employee->id,
            substr($employee->plantilla_id, 1),
            $employee->name
        );

        // * validate if photo exists in directory
        if($employee->photo != null) {
            $photo = public_path($employee->photo);
            if (file_exists($photo)) {
                $model->photo = $employee->photo;
            }
        }

        // * attempt to updat the photo from the RH
        if( !isset($model->photo)){
            $photo = EmployeeRHService::duplicatePhotoEmployee($employee->id, $employee->plantilla_id);
            if ($photo) {
                $model->photo = $photo;
            } else {
                $model->photo = '/images/unknown.png';
            }
        }
        $model->checa = $employee->status_id ?? 0;
        // $model->photo = $employee->photo;

        if(isset($employee->active)){
            if(is_bool($employee->active)) {
                $model->active = $employee->active;
            }else{
                $model->active = $employee->active == 1;
            }
        }

        $employee->load(['generalDirection', 'direction', 'workingDays', 'subdirectorate', 'department']);

        // * manually get the working hours
        $workingHours = WorkingHours::where('employee_id', $employee->id)->first();

        if( isset($employee->generalDirection) ){
            $model->abbreviation = $employee->generalDirection->abbreviation;
            $model->generalDirection = $employee->generalDirection->name;
            $model->generalDirectionId = $employee->generalDirection->id;
        }

        if( isset($employee->direction) && $employee->direction->id != 1 ){
            $model->direction = $employee->direction->name;
            $model->directionId = $employee->direction->id;
        }

        if($workingHours != null) {
            if ($workingHours->checkin) {
                $model->horario = $workingHours->checkin . ' a ' . $workingHours->checkout;
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

        if( isset($employee->subdirectorate) && $employee->subdirectorate->id != 1 ) {
            $model->subDirection = $employee->subdirectorate->name;
            $model->subDirectionId = $employee->subdirectorate->id;
        }

        if( isset($employee->department) && $employee->department->id != 1 ) {
            $model->department = $employee->department->name;
            $model->departmentId = $employee->department->id;
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

    /**
     * create a employee view model from the local model
     *
     * @param  Employee $employee
     * @return EmployeeViewModel
     */
    public static function fromRHModel(EmployeeRh $employee) : EmployeeViewModel
    {
        // create the view model
        $model = new EmployeeViewModel(
            $employee->IDEMPLEADO,
            $employee->NUMEMP,
            $employee->NOMBRE . ' ' . $employee->APELLIDO
        );

        // * validate if photo exists in directory
        if($employee->photo != null)
        {
            $photo = public_path($employee->photo);
            if (file_exists($photo))
            {
                $model->photo = $employee->photo;
            }
        }

        $model->curp = $employee->CURP;
        $model->photo = '/images/unknown.png';
        $model->checa = 0;
        $model->active = false;
        $model->isRH = true;

        return $model;
    }

}