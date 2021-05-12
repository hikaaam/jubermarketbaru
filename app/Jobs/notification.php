<?php

namespace App\Jobs;

use App\Http\Controllers\helper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class notification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $title = $this->data["title"];
        $msg = $this->data["msg"];
        $image = $this->data["image"] ?? null;
        $token = $this->data["token"];
        $type = $this->data["type"];
        helper::sendNotification($token, $msg, $type, $title, $image);
    }
}
