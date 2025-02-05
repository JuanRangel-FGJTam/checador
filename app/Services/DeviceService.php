<?php

namespace App\Services;

use App\Models\ClientStatusLog;
use Illuminate\Support\Carbon;

class DeviceService
{

    /**
     * return the logs of the device
     *
     * @return array
     */
    public function getDevicesLog(){
        // * return the devices logs
        $logs = ClientStatusLog::get()->all();

        // * process the logs and compare the last connetion
        $devicesLogs = array();
        foreach ($logs as $key => $value) {
            $dLog = array();
            $dLog["id"] = $value->id;
            $dLog["name"] = $value->name;
            $dLog["address"] = $value->address;
            $dLog["last-connection"] = Carbon::parse($value->updated_at)->format("Y-m-d H:i:s");
            if(Carbon::parse($value->updated_at)->addMinutes(2) >= Carbon::now()){
                $dLog["status"] = 1;
            }else{
                $dLog["status"] = 0;
            }
            array_push($devicesLogs, $dLog);
        }

        // * return the data
        return $devicesLogs;
    }

    public function deleteDevicesLog(int $logId)
    {
        $log = ClientStatusLog::find($logId);
        if(isset($log))
        {
            $log->delete();
        }
    }
}
