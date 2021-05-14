<?php

namespace App\Console\Commands;

use App\Http\Controllers\globalController;
use App\Http\Controllers\helper;
use Illuminate\Console\Command;

class jbfoodCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jbfood:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        globalController::syncJuberFood();
        helper::Logger("Running cron ~> syncing juber food", 'jbr');
    }
}
