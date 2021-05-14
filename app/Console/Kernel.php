<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Http;
use App\Models\category;
use App\Models\ref_cat;
use App\Models\partner;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Route;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command("jbfood:cron")->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
