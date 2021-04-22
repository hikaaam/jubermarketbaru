<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Models\category;
use App\Models\ref_cat;
use DateTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\tokopedia_token;
use App\Models\catTokpedChild as child_cat;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getToken()
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
            $response =  http::withHeaders([
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

    public function index()
    {

        $now = Carbon::now()->timestamp;
        try {
            $getToken = $this->getToken();
            $token = $getToken["token"];
            $app_id = $getToken["fs_id"];
            $url = "https://fs.tokopedia.net/inventory/v1/fs/" . $app_id . "/product/category";
            ref_cat::where('id', '>', 0)->delete();
            $response = http::withToken($token)->get($url);
            $res_data = $response->json();
            $data = $res_data['data']['categories'];
            foreach ($data as $key => $value) {
                $ref_name = $value['name'];
                $ref_id = $value['id'];
                $ref_cat = ["name" => $ref_name, "id" => $ref_id];
                ref_cat::create($ref_cat);
                if (array_key_exists("child", $value)) {
                    $child = $value['child'];
                    foreach ($child as $child_key => $child_value) {
                        $child_name = $child_value['name'];
                        $child_id = $child_value['id'];
                        $cat = ["name" => $child_name, "id" => $child_id, "ref_category" => $ref_id];
                        category::create($cat);
                        if ($child_value["child"] == null) {
                            // return $child_value;
                            $_child = ["name" => $child_name, "id" => $child_id, "ref_category" => $ref_id, "parent_category" => $child_id];
                            child_cat::create($_child);
                        } else {
                            foreach ($child_value["child"] as $key => $_child_value) {
                                # code...
                                // return $_child_value;
                                $_child = ["name" => $_child_value["name"], "id" => $_child_value["id"], "ref_category" => $ref_id, "parent_category" => $child_id];
                                child_cat::create($_child);
                            }
                        }
                    }
                } else {
                    $cat = ["name" => $ref_name, "id" => $ref_id, "ref_category" => $ref_id];
                    category::create($cat);
                    $_child = ["name" => $ref_name, "id" => $ref_id, "ref_category" => $ref_id, "parent_category" => $ref_id];
                    child_cat::create($_child);
                }
            }
            $end_time = Carbon::now()->timestamp;
            $runTime = ($end_time - $now) . " detik";
            return ["runTime" => $runTime];
        } catch (\Throwable $th) {
            $end_time = Carbon::now()->timestamp;
            $runTime = $end_time - $now . " detik";
            return ["msg" => $th->getMessage(), "runTime" => $runTime];
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
