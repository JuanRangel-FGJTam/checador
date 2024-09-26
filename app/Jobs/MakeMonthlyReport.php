<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;
use App\Models\MonthlyRecord;
use App\Models\Process;
use App\Helpers\MonthlyReportFactory;

class MakeMonthlyReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected MonthlyRecord $monthlyRecord;
    
    protected array $employees;

    /**
     * Create a new job instance.
     */
    public function __construct(MonthlyRecord $monthlyRecord, array $employees)
    {
        $this->monthlyRecord = $monthlyRecord;
        $this->employees = $employees;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // * create the process record and attach to the monthReportRecord
        $process = new Process();
        $process->status = 'processing';
        $process->output = null;
        $process->started_at = Carbon::now();
        $process->ended_at = null;
        $this->monthlyRecord->process()->save($process);

        try {

            // * get the report data
            $monthlyReportFactory = new MonthlyReportFactory(
                $this->employees,
                $this->monthlyRecord->year,
                $this->monthlyRecord->month
            );
            $this->monthlyRecord->data = $monthlyReportFactory->makeReportData();
            $this->monthlyRecord->save();

            // TODO: with the data make the excel document

            // * set the process as finish
            $process->status = 'success';
            $process->output = null;
            $process->ended_at = Carbon::now();
            $process->save();

        } catch(\Throwable $exception) {
            $process->status = 'error';
            $process->output = $exception->getMessage();
            $process->ended_at = Carbon::now();
            $process->save();
        }

    }
}
