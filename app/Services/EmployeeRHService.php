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
            return EmployeeRh::select('NUMEMP', 'NOMBRE', 'APELLIDO', 'RFC', 'CURP')->where('NUMEMP', $employee_number)->first();
        } catch (\Throwable $th) {
            Log::error("Fail to get the employee of the RH: {message}", [
                "message" => $th->getMessage(),
                "employeeNumber" => $employee_number
            ]);
            return null;
        }


        // try {
        //     $_employee = Employee::where('plantilla_id', '1'.$employee_number)->firstOrFail();
        //     $employee = new stdClass();
        //     $employee->NUMEMP = (int) $employee_number;
        //     $employee->NOMBRE = $_employee->name;
        //     $employee->APELLIDO = "";
        //     $employee->RFC = "*******";
        //     $employee->CURP = "*******";
        //     return $employee;

        // }catch(\Throwable $th) {
        //     Log::error("Fail to get the employee '{employee_number}' data from the RH service: {message} ", [
        //         "employee_number" => $employee_number,
        //         "message" => $th->getMessage(),
        //     ]);
        //     return null;
        // }
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

                    // file_put_contents('/var/www/html/public/'.$path, $rowRh->FOTO);
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