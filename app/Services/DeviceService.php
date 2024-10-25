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
            $dLog["name"] = $value->name;
            $dLog["address"] = $value->address;
            $dLog["last-connection"] = $value->updated_at;
            if(Carbon::parse($value->updated_at)->addMinutes(5) >= Carbon::now()){
                $dLog["status"] = 1;
            }else{
                $dLog["status"] = 0;
            }
            array_push($devicesLogs, $dLog);
        }

        // * return the data
        return $devicesLogs;
    }
}
