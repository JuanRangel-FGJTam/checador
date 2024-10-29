<?php

namespace App\Console\Commands;

use \DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Employee;
use App\Services\IncidentService;

class CreateIncidentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:incident-create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create incidents of the employees';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = new DateTime();
        $now->modify('-1 day');

        Log::info('Comenzó la creación de incidencias del día ' . $now->format('Y-m-d') . ' - incident:create Command.');

        // * get the day of the week as a number (7 = Sunday, 6 = Saturday)
        $dayOfWeek = $now->format('N');
        if ($dayOfWeek < 6) {
            $dayIs = 'week';
        } else {
            $dayIs = 'weekend';
        }
        
        // * get the active employees
        $employees = Employee::with(['workingDays'])
            ->select('id', 'status_id', 'active')
            ->where('active', 1)
            ->where('status_id', 1)
            ->get()
            ->all();

        foreach ($employees as $employee){
            $this->handleEmployee($employee);
        }

        $this->info('Terminó el calculo de incidencias del día ' . $now->format('Y-m-d') . ' - incident:create Command.');
        Log::info('Terminó el calculo de incidencias del día ' . $now->format('Y-m-d') . ' - incident:create Command.');

    }

    private function handleEmployee($employee){

        $now = new DateTime();
        $now->modify('-1 day');

        $workingHours = WorkingHours::where('employee_id', $employee->id)->first();
        $workingDays = WorkingDays::where('employee_id', $employee->id)->first();

        if (!$workingHours) {
            Log::debug("CreateIncidents: Employee id {employeeId} has not workingHours", [
                "employeeId" => $employee->id,
            ]);
            return;
        }

        if (!$workingHours->checkin || !$workingHours->checkout) {
            Log::debug("CreateIncidents: Employee id {employeeId} has not workingHours", [
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
                    $now->format('Y-m-d')
                );

                $incidentService->calculateAndStoreIncidents();
            } catch (Exception $e) {
                Log::error('CreateIncidents: Error creating incident '.$employee->id.': '.$e->getMessage());
            }
        }
    }
}
