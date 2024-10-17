<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\MonthlyRecord;
use App\Models\Process;
use App\Models\GeneralDirection;
use App\Helpers\MonthlyReportFactory;
use Illuminate\Support\Facades\Log;

class MakeMonthlyReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 900; // 15 minutes

    protected MonthlyRecord $monthlyRecord;
    protected array $employees;
    protected GeneralDirection $generalDirection;
    protected Process $process;

    const PATH = 'tmp/monthlyreports';

    /**
     * Create a new job instance.
     */
    public function __construct(MonthlyRecord $monthlyRecord, array $employees)
    {
        $this->monthlyRecord = $monthlyRecord;
        $this->employees = $employees;
        $this->generalDirection = GeneralDirection::find($this->monthlyRecord->general_direction_id);

        $this->process = new Process();
        $this->process->status = 'pending';
        $this->process->save();
        $this->monthlyRecord->process()->save($this->process);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("MakeMonthlyReport: Start the process to make the monthly report");
        // * create the process record and attach to the monthReportRecord
        $this->process->status = 'processing';
        $this->process->output = null;
        $this->process->started_at = Carbon::now();
        $this->process->ended_at = null;
        $this->process->save();


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

            $directory = storage_path('app/' . self::PATH);
            if (!is_dir($directory)) {
                mkdir($directory, 0775, true);
            }

            $fileName = sprintf("%s.xlsx", (string) Str::uuid() );
            // $filePath = sprintf("tmp/monthlyreports/$fileName");
            $filePath = self::PATH . "/$fileName";

            if (!Storage::disk('local')->put( $filePath, $documentContent )) {
                throw new \Exception("Fail to store the report document on" . $filePath);
            }
            
            // * save the file path on the record data
            $this->monthlyRecord->filePath = $filePath;
            $this->monthlyRecord->save();

            // * set the process as finish
            $this->process->status = 'success';
            $this->process->output = null;
            $this->process->ended_at = Carbon::now();
            $this->process->save();

            Log::info("MakeMonthlyReport: The process to make the monthly report has finished");
        } catch(\Throwable $exception) {
            Log::error("MakeMonthlyReport: Error on the process to make the monthly report");
            Log::error($exception->getMessage());

            $this->process->status = 'error';
            $this->process->output = $exception->getMessage();
            $this->process->ended_at = Carbon::now();
            $this->process->save();
        }

    }

    public function failed($exception) {
        Log::error("MakeMonthlyReport: Error on the process to make the monthly report");
        Log::error($exception->getMessage());

        $this->process->status = 'error';
        $this->process->output = $exception->getMessage();
        $this->process->ended_at = Carbon::now();
        $this->process->save();
    }
}
