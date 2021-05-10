<?php

namespace App\Jobs;

use App\Http\Controllers\paymentController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class paymentNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $profile, $store;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($profile, $store)
    {
        $this->profile = $profile;
        $this->store = $store;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $profile = $this->profile;
        $store = $this->store;
        paymentController::paymentNotification($profile, $store);
    }
}
