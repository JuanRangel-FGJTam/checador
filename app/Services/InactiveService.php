<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use App\Models\{
    Employee,
    EmployeeStatusHistory
};
use Exception;

class InactiveService
{

    function changeStatus(EmployeeService $employeeService, Employee $employee, string $comments, int $status, $file)
    {
        // * retrive the current user
        $user = Auth::user();

        Log::info("Attemtp to update the status of the employee '{employeeNumber}' to '{statusId}' by '{userName}'", [
            "employeeNumber" => $employee->employeeNumber(),
            "statusId" => $status,
            "userName" => $user->name
        ]);

        DB::beginTransaction();

        // * change the employee status
        try
        {
            $employeeService->updateEmployeeStatus( $employee->employeeNumber(), $status);
        }
        catch (\Throwable $th)
        {
            Log::error("Fail at change the status of the employee: {message}", [
                "message" => $th->getMessage()
            ]);
            DB::rollback();
            throw new Exception("Error al cambiar el estatus del empleado, No se pudo cambiar el estatus.");
        }

        // * store the document if exists
        $filePath = null;
        try
        {
            if(isset($file))
            {
                $filePath = $this->storeJustificationFile(
                    $file,
                    $employee->computed_employee_number
                );
            }
        }
        catch (\Throwable $th)
        {
            Log::error("Fail to store the justification file at EmployeeController.updateStatus: {message}", [
                "message" => $th->getMessage()
            ]);
            DB::rollback();
            throw new Exception("Error al cambiar el estatus del empleado, No se pudo almacenar el archivo de justificacion.");
        }

        try
        {
            // * create a record of EmployeeStatushistory with the new status and the file path of the document
            $histoyRecord = new EmployeeStatusHistory([
                'user_id' => $user->id,
                'employee_id' => $employee->id,
                'comments' => $comments,
                'file' => $filePath,
                'status' => $status == 1 ?"Activo" :"Baja"
            ]);
            $histoyRecord->save();
        } catch (\Throwable $th) {
            Log::error("Fail to store the history record at EmployeeController.updateStatus: {message}", [
                "message" => $th->getMessage()
            ]);
            DB::rollBack();
            throw new Exception("Error al cambiar el estatus del empleado, No se pudo registrar el cambio en la bitacora.");
        }

        DB::commit();
        Log::info("Successfull updated the status of the employee '{employeeNumber}' to '{statusId}' by '{userName}'", [
            "employeeNumber" => $employee->employeeNumber(),
            "statusId" => $status,
            "userName" => $user->name
        ]);
    }

    /**
     * getHistoryInactive
     * @param  mixed $take number of record to take, default 25
     * @param  mixed $page page number, default 0
     * @param  mixed $orderBy propertie used to order the data, default `created_at`
     * @param  mixed $orderDesc ordering the data in descending, default true
     * @return array<EmployeeStatusHistory>
     */
    function getHistoryInactive(int $take = 25, int $page = 0, string $orderBy = 'created_at', bool $orderDesc = true): array
    {
        $history = array();
        $historyQuery = EmployeeStatusHistory::with(['employee', 'employee.generalDirection', 'employee.Direction', 'user'])
            ->orderBy($orderBy, $orderDesc ? 'desc' : 'asc');

        if(Auth::user()->level_id > 1)
        {
            $generalDirectionId = Auth::user()->general_direction_id;
            $historyQuery->whereHas('employee', function($query) use ($generalDirectionId)
            {
                $query->where('general_direction_id', $generalDirectionId);
            });
        }

        $history = $historyQuery
            ->take($take)
            ->skip( $page * $take)
            ->get()
            ->toArray();

        return $history;
    }

    #region Private Methods
    /**
     * store the justification file and return the path
     *
     * @param  mixed $file
     * @param  string $employee_number
     * @param  string $date
     * @return string
     */
    private function storeJustificationFile($file, $employee_number): string {
        $cDate = Carbon::now();
        $name = sprintf("%s-%s.pdf", $employee_number, $cDate->timestamp);
        return Storage::disk("local")->putFileAs('justificantes-cambio-estatus', $file, $name );
    }
    #endregion
}
