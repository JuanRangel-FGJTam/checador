<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\Employee;
use App\Models\WorkingDays;
use App\Models\WorkingHours;
use App\Services\IncidentService;
use Exception;

class CreateIncidentsDate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $targetDate = null;
    protected $dayIs = 'weekend';

    /**
     * Create a new job instance.
     */
    public function __construct($date)
    {
        $this->targetDate = $date;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $now = Carbon::parse($this->targetDate);

        Log::info("CreateIncidents: Start the process to make incidents of '{date}'", [
            "date" => $now->format("Y-m-d")
        ]);
        
        try {

            // * get the day of the week as a number (7 = Sunday, 6 = Saturday)
            $dayOfWeek = $now->format('N');
            if ($dayOfWeek < 6) {
                $this->dayIs = 'week';
            } else {
                $this->dayIs = 'weekend';
            }

            // * get the active employees
            $employees = Employee::with(['workingDays'])
                ->select('id', 'status_id', 'active')
                ->where('active', 1)
                ->where('status_id', 1)
                ->get()
                ->all();
            Log::debug("CreateIncidents: total employees {total}", [
                "total" => count($employees)
            ]);


            // * process each employee
            foreach ($employees as $key=>$employee) {
                Log::debug("CreateIncidents: Employee ID `{employeeId}`, Proccessing {index} of {total}", [
                    "employeeId" => $employee->id,
                    "index" => $key,
                    "total" => count($employees),
                    "targetDate" => $this->targetDate
                ]);
                $this->handleEmployee($employee, $now);
            }

            Log::info("CreateIncidents: The process has finished");

        } catch(\Throwable $exception) {
            Log::error("CreateIncidents: Error on the process to make the incidents");
            Log::error($exception->getMessage());
        }
    }

    private function handleEmployee($employee, $targetDate){

        $workingHours = WorkingHours::where('employee_id', $employee->id)->first();
        $workingDays = WorkingDays::where('employee_id', $employee->id)->first();

        if (!$workingHours) {
            Log::debug("CreateIncidents: Employee id {employeeId} has not workingHours (A)", [
                "employeeId" => $employee->id,
            ]);
            return;
        }

        if (!$workingHours->checkin || !$workingHours->checkout) {
            Log::debug("CreateIncidents: Employee id {employeeId} has not workingHours (B)", [
                "employeeId" => $employee->id,
            ]);
            return;
        }

        // * get the working days of the employee
        $workDays = array('week');
        if ($workingDays) {
            if ($workingDays->weekend == 1) {
                $workDays[] = 'weekend';
            }
            if ($workingDays->week == 0) {
                $key = array_search('week', $workDays);
                array_splice($workDays, $key, 1);
            }
        }else{
            Log::debug("CreateIncidents: Employee id {employeeId} has not workingDays", [
                "employeeId" => $employee->id,
            ]);
        }

        // * validate if the employee works on the target date
        if (in_array($this->dayIs, $workDays)) {
            try {
                $incidentService = new IncidentService(
                    $employee->id,
                    $workingHours,
                    $targetDate->format('Y-m-d')
                );

                $incidentService->calculateAndStoreIncidentsV2();
                Log::notice('Se creó las incidencias para el empleado id: '. $employee->id.' del día ' . $targetDate->format('Y-m-d'));

            } catch (Exception $e) {
                Log::error('CreateIncidents: Error creating incident '.$employee->id.': '.$e->getMessage());
            }
        }
    }
}
