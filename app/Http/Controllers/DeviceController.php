<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use App\Models\ClientStatusLog;
use App\Services\DeviceService;
use Carbon\Carbon;

class DeviceController extends Controller
{

    protected DeviceService $deviceService;

    function __construct(DeviceService $deviceService)
    {
        $this->deviceService = $deviceService;
    }

    function index(){

        // * retrive the log of the devices
        $deviceLogs = $this->deviceService->getDevicesLog();

        $lastCheckout = Carbon::now()->timezone('America/Monterrey')->format("Y-m-d H:i:s");

        // * return the view
        return Inertia::render('Devices/Index',[
            "devices" => $deviceLogs,
            "lastCheckout" => $lastCheckout
        ]);

    }

    
    /**
     * get the status of the devices in json 
     *
     * @return JsonResponse
     */
    function getDevicesLogRaw(){
        $devicesLogs = $this->deviceService->getdevicesLog();
        return response()->json($devicesLogs, 200);
    }
}
