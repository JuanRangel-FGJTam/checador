<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use App\Models\Employee;
use App\Models\EmployeeRh;
use stdClass;

class EmployeeRHService {

    /**
     * getEmployeeData
     *
     * @param  string|int $employee_number
     * @return mixed
     */
    public static function getEmployeeData($employee_number)
    {
        try {
            return EmployeeRh::select('NUMEMP', 'NOMBRE', 'APELLIDO', 'RFC', 'CURP')
                ->where('NUMEMP', $employee_number)
                ->first();
        } catch (\Throwable $th) {
            Log::error("Fail to get the employee of the RH: {message}", [
                "message" => $th->getMessage(),
                "employeeNumber" => $employee_number
            ]);
            return null;
        }
    }

    public static function getPlazaByEmployeeNumber($employee_number)
    {
        $employee = EmployeeRh::select('NUMEMP', 'IDPLAZA')
            ->where('NUMEMP', $employee_number)
            ->first();

        if ($employee) {
            if ($employee->plaza) {
                $employee->plaza->nivel = $employee->plaza->nivel;
                $employee->plaza->puesto = $employee->plaza->puesto;

                return $employee->plaza;
            }
        }

        return null;
    }

    public static function duplicatePhotoEmployee($employee_id, $plantilla_id)
    {
        $employee_number = (int)substr($plantilla_id, 1);
        $rowRh = EmployeeRh::select('FOTO', 'RFC')->where('NUMEMP', '=', $employee_number)->first();

        if ($rowRh) {
            $path = null;

            if ($rowRh->FOTO) {
                $path = 'photos/'.$rowRh->RFC.'.jpg';

                try {
                    file_put_contents(public_path($path), $rowRh->FOTO);
                } catch (\Throwable $th) {
                    Log::error('Error saving photo '.$path.': '.$th->getMessage());
                }
            }

            if ($path) {
                $employee = Employee::find($employee_id);
                if ($employee) {
                    $employee->photo = $path;
                    $employee->save();
                    return $path;
                }
            }
        }

        return null;
    }
}