<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use App\Models\{
    Justify,
    Incident
};
use App\Http\Requests\NewJustificationRequest;
use App\ViewModels\EmployeeViewModel;

use function Laravel\Prompts\form;

class JustificationService {

    /**
     * justify
     *
     * @param  NewJustificationRequest $request
     * @param  EmployeeViewModel $employee
     * @return void
     */
    public function justify(NewJustificationRequest $request, EmployeeViewModel $employee)
    {
        
        // * store the file
        $filePath = $this->storeJustificationFile(
            file: $request->file('file'),
            employee_number: $employee->employeeNumber,
            date: $request->input('initialDay')
        );
        
        DB::beginTransaction();

        // get the current user
        $currentUser = Auth::user();
        $startDay = Carbon::parse($request->input("initialDay"));
        $finishDay = Carbon::parse($request->input("endDay"));
        

        // * create the justification record(s)
        $message = "Día ". $startDay->format('d-m-Y')." justificado correctamente";
        try {

            $justification = Justify::create([
                'employee_id' => $employee->id,
                'type_justify_id' => $request->input('type_id'),
                'date_start' => $request->input('initialDay'),
                'date_finish' => $request->input('endDay'),
                'file' => $filePath,
                'details' => $request->input('comments'),
                'user_id' => $currentUser->id
            ]);

            // * calculate the message 
            if( $request->input("endDay") != null ){
                $message = "Días ". $startDay ." al " . $finishDay->format('d-m-Y')." justificados correctamente";
            }

        }catch(\Throwable $th){
            Log::error("Fail to create the incident record of the employee {employeeName} from {startData} to {endDate} at JustificationService.justify: {message}", [
                "employeeName" => $employee->name,
                "startData" => $request->input('initialDay'),
                "endDate" => $request->input('endDay'),
                "message" => $th->getMessage()
            ]);
            DB::rollback();
            throw $th;
        }

        // TODO: Delete data from Mongo to re create object
        // $mongoRecord = \App\Models\KardexRecord::where('employee_id', (int)$employee_id)
        //     ->where('year', $today->format('Y'))
        //     ->first();
        // if ($mongoRecord) {
        //     $mongoRecord->delete();
        // }

        try {

            // * get the incidents
            $incidents = Collection::empty();
            $dateRange = "";
            if( !$request->input('multipleDays')) {
                $incidents = Incident::where('employee_id', $employee->id)
                    ->where('date', $request->input('initialDay') )
                    ->get();

                $dateRange = sprintf("del %s", $startDay );
            } else {
                $incidents = Incident::where('employee_id', $employee->id)
                    ->whereBetween('date', [ $startDay->format("Y-m-d"), $finishDay->format("Y-m-d") ])
                    ->get();

                $dateRange = sprintf("del %s al %s", $startDay, $finishDay );
            }
        

            // *  delete the incidens
            $flag = 0;
            if($incidents){
                foreach ($incidents as $inc) {
                    $inc->delete();
                    $flag++;
                }
            }


        }catch(\Throwable $th){
            Log::error("Fail to delet the incidents of the employee {employeeName} from {startData} to {endDate} at JustificationService.justify: {message}", [
                "employeeName" => $employee->name,
                "startData" => $request->input('initialDay'),
                "endDate" => $request->input('endDay'),
                "message" => $th->getMessage()
            ]);
            DB::rollback();
            throw $th;
        }
        

        DB::commit();

        // * prinf some logs
        Log::notice('El usuario '.Auth::user()->name.' justificó al empleado '. $employee->name .': ' . $message);
        if( isset($flag) ){
            Log::notice("Se eliminó la incidencia (Total: $flag) $dateRange del empleado {employeeName} por el usuario {userName}", [
                "employeeName" => $employee->name,
                "employeeId" => $employee->id,
                "userName" => $currentUser->name,
                "userId" => $currentUser->id
            ]);
        }

    }

    
    /**
     * store the justification file and return the path
     *
     * @param  mixed $file
     * @param  string $employee_number
     * @param  string    $date
     * @return string
     */
    private function storeJustificationFile($file, $employee_number, $date): string {
        $cDate = Carbon::parse($date);
        $name = sprintf("%s-%s.pdf", $employee_number, $cDate->format("Y-m-d"));
        return Storage::disk("local")->putFileAs('justificantes', $file, $name );
    }
    
}