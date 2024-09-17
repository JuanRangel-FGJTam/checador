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
            Log::info("Start task of deleting temporal files");
            Storage::disk('local')->deleteDirectory('tmp');
            Log::info("Finish task of deleting temporal files");
        }catch(\Throwable $th){
            Log::error("Failed to delete temporal files: " . $th->getMessage());
        }
    }

}