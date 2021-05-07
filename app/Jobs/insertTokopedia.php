<?php

namespace App\Jobs;

use App\Http\Controllers\helper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class insertTokopedia implements ShouldQueue
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
        $dataTable = $this->data["data"];
        $withVariant = $this->data["withVariant"];
        $id = $this->data["id"];
        $variant = $this->data["variant"];
        helper::tokopediaUpload($dataTable, $id, $withVariant, $variant);
    }
}
