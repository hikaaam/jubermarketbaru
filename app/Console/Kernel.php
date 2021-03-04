<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Http;
use App\Models\category;
use App\Models\ref_cat;
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
        $schedule->call(function () {
            $app_id = 14523;
            $url = "https://fs.tokopedia.net/inventory/v1/fs/$app_id/product/category";
            $token = "c:vYRluIJoR0a0I1cnlItwyg";
            $start_time =  new \DateTime('NOW');
               try {
                    ref_cat::where('id','>',0)->delete();
                    $response = http::withToken($token)->get($url);
                    $res_data = $response->json();
                    $data = $res_data['data']['categories'];
                    foreach ($data as $key => $value) {
                        $ref_name = $value['name'];
                        $ref_id = $value['id'];
                        if(array_key_exists("child",$value)){
                            $child = $value['child'];
                            $ref_cat = ["name"=>$ref_name,"id"=>$ref_id];
                            ref_cat::create($ref_cat);
                            foreach ($child as $child_key => $child_value) {
                                $child_name = $child_value['name'];
                                $child_id = $child_value['id'];
                                $cat = ["name"=>$child_name,"id"=>$child_id,"ref_category"=>$ref_id];
                                category::create($cat);
                            }
                        }
                    }
                   
                    $end_time = new \DateTime('NOW');
                    return ["run time"=>($end_time-$start_time),"response"=>$response];
                } catch (\Throwable $th) {
                    return ["start"=>$start_time,"end"=>$end_time,"msg"=>$th->getMessage()];
                }
        })->weekly();
        
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
