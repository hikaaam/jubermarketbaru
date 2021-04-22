<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\ScheduleController as schedule;
use App\Models\tokopedia_token;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class helper extends Controller
{
    public static function getAuth($token)
    {
        return [
            'Authorization' => "Bearer {$token}",
            'Content-Type' => 'application/json'
        ];
    }

    public static function getToken()
    {
        $app_id = 14523;
        $now = Carbon::now()->timestamp;
        $partner = tokopedia_token::find(1);
        $token = $partner->access_token;
        $updated_at = $partner->updated_at;
        $updated_at_second  = Carbon::parse($updated_at)->timestamp;
        if ($updated_at == null) {
            $is_expired = true;
        } else {
            $is_expired = $updated_at_second <= $now;
        }
        if ($is_expired) {
            $response =  Http::withHeaders([
                'Authorization' => 'Basic YzY0MDYyNjNmYmY1NDMxZWE3OTNiOWFkYzUxNTg3NDk6ZTcyOTk4YWRlMDYwNDNkYjk4ZTllYmJjOTBlOWM1NmM=',
                'Content-Length' => '0',
                'User-Agent' => 'PostmanRuntime/7.17.1'
            ])->post('https://accounts.tokopedia.com/token?grant_type=client_credentials');
            $res_data = $response->json();
            // return $res_data;
            $token = $res_data["access_token"];
            $expired_at = $res_data["expires_in"];
            $last_login_type = $res_data["last_login_type"];
            $refresh = true;
            $updated_at = Carbon::now()->toDateTimeString();
            tokopedia_token::find(1)->update(
                [
                    "access_token" => $token,
                    "expires_in" => $expired_at,
                    "updated_at" => $updated_at,
                    "last_login_type" => $last_login_type
                ]
            );
        } else {
            $refresh = false;
        }
        return ["token" => $token, "fs_id" => $app_id, "refresh" => $refresh, "last_updated_at" => $updated_at];
    }

    public static function isPicture($pict)
    {
        return $pict !== "null" && $pict !== "" && $pict !== null;
    }

    public static function shopid()
    {
        return 10408203;
    }
}
