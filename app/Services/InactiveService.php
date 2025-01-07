<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use App\Models\{
    Employee,
    EmployeeStatusHistory
};

class InactiveService
{

    function changeStatus(int $employeeId, string $comments, string $file, int $status): bool
    {
        $employee = Employee::find($employeeId);
        if ($employee == null) {
            return false;
        }
        $employee->status = $status;
        $employee->save();

        $history = new EmployeeStatusHistory();
        $history->employee_id = $employeeId;
        $history->user_id = auth()->user()->id;
        $history->comments = $comments;
        $history->file = $file;
        $history->status = $status;
        $history->save();
        return true;
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
}
