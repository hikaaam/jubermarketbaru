<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\ScheduleController as schedule;

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
        return \App::call('App\Http\Controllers\ScheduleController@getToken');
    }
    public static function isPicture($pict)
    {
        return $pict !== "null" && $pict !== "" && $pict !== null;
    }
}
