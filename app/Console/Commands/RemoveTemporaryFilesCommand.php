<?php

namespace App\Console\Commands;

use App\Tasks\RemoveTemporalFiles;
use Illuminate\Console\Command;

class RemoveTemporaryFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:remove-temporary-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $task = new RemoveTemporalFiles();
        $task();
    }
}
