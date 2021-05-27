<?php

namespace App\Jobs;

use App\Http\Controllers\AdminController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class blockProduct implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $data;
    private $items;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $items)
    {
        $this->data = $data;
        $this->items = $items;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        AdminController::blockProductJob($this->data, $this->items);
    }
}
