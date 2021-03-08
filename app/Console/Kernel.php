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
        $schedule->call(function () {
            $app_id = 14523;
            $url = "https://fs.tokopedia.net/inventory/v1/fs/".$app_id."/product/category";
            $now = Carbon::now()->timestamp;
               try {
                  
                    $partner = partner::find(1);
                    $token = $partner->token;
                    $updated_at = $partner->updated_at;
                    $expired_at = $partner->expired_at;
                    $updated_at_second  = Carbon::parse($updated_at)->timestamp;
                    $diff = $now-$updated_at_second;
                    $is_expired = $diff>$expired_at;
                    if($is_expired){
                        return "token_expired";
                       $response =  http::withHeaders([
                        'Authorization' => 'Basic e72998ade06043db98e9ebbc90e9c56c',
                        'Content-Length' => '0',
                        'User-Agent' => 'PostmanRuntime/7.17.1'
                    ])->post('https://accounts.tokopedia.com/token?grant_type=c6406263fbf5431ea793b9adc5158749');
                    $res_data = $response->json();
                    $token = $res_data["data"]["access_token"];
                    $expired_at = $res_data["data"]["expired_in"];
                    $updated_at = Carbon::now()->toDateTimeString();
                    partner::find(1)->update(["token"=>$token,"expired_at"=>$expired_at,"updated_at"=>$updated_at]);
                    }
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
                    $end_time = Carbon::now()->timestamp;;
                    $runTime = $end_time-$now." detik";
                    return ["runTime"=>$runTime];
                } catch (\Throwable $th) {
                    $end_time = Carbon::now()->timestamp;
                    $runTime = $end_time-$now." detik";
                    return ["msg"=>$th->getMessage()];
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
