<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\MonthlyRecord;
use App\Models\Process;
use App\Models\GeneralDirection;
use App\Helpers\MonthlyReportFactory;

class MakeMonthlyReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected MonthlyRecord $monthlyRecord;
    protected array $employees;
    protected GeneralDirection $generalDirection;

    /**
     * Create a new job instance.
     */
    public function __construct(MonthlyRecord $monthlyRecord, array $employees)
    {
        $this->monthlyRecord = $monthlyRecord;
        $this->employees = $employees;
        $this->generalDirection = GeneralDirection::find($this->monthlyRecord->general_direction_id);
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


            // * make the excel document
            $monthlyReportMaker = new \App\Helpers\MonthlyReportExcel( $this->monthlyRecord->data, $this->generalDirection->name);
            $documentContent = $monthlyReportMaker->make();
            if( $documentContent === false){
                throw new \Exception("Fail to make the report document");
            }

            // * store the file
            $fileName = sprintf("%s.xlsx", (string) Str::uuid() );
            $filePath = sprintf("tmp/monthlyreports/$fileName");
            Storage::disk('local')->put( $filePath, $documentContent );


            // * save the file path on the record data
            $this->monthlyRecord->filePath = $filePath;
            $this->monthlyRecord->save();

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
