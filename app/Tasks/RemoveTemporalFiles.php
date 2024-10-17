<?php

namespace App\Tasks;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Override;

class RemoveTemporalFiles {

    public function __invoke()
    {
        $this->exec();
    }

    protected function exec(){
        try{
            Storage::disk('local')->deleteDirectory('tmp');
            Log::info("Temporal files deleted successfully");
        }catch(\Throwable $th){
            Log::error("Failed to delete temporal files: " . $th->getMessage());
        }
    }

}