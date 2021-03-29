<?php

namespace App\Http\Controllers;

use App\Models\follow;
use App\Models\profile;
use App\Models\store;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        try {
            $request = json_decode($request->payload, true);
            $dataTable = [];
            $dataTable = addData("user_id", "user_id", $request, $dataTable);
            $dataTable = addData("store_id", "store_id", $request, $dataTable);
            $store_id = $dataTable["store_id"];
            $store = store::findOrFail($store_id);
            $user_id = $dataTable["user_id"];
            $profile = profile::findOrFail($user_id);
            if (checkNull($profile->idrs)) {
                return getRespond(false, "idrs user tidak ada", []);
            }
            $dataTable["idrs"] = $profile->idrs;

            $isExist = follow::where("store_id", $store_id)->where("user_id", $user_id)->get();
            // return $isExist;
            if (count($isExist) == 0) {
                $data = follow::create($dataTable);
                // return $data;
                $followerTotal = intval($store->follower) + 1;
                $followingTotal = intval($profile->following) + 1;
                $store->update(["follower" => $followerTotal]);
                $profile->update(["following" => $followingTotal]);
                return getRespond(true, "berhasil follow toko", ["following" => true, "store_id" => $store_id]);
            } else {
                follow::where("store_id", $store_id)->where("user_id", $user_id)->delete();
                $followerTotal = intval($store->follower) - 1;
                $followingTotal = intval($profile->following) - 1;
                $store->update(["follower" => $followerTotal]);
                $profile->update(["following" => $followingTotal]);
                return getRespond(true, "berhasil unfollow toko", ["following" => false, "store_id" => $store_id]);
            }
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
    }
    public function isFollowing($id)
    {
        try {
            $data = follow::where("store_id", $id)->count();
            if ($data > 0) {
                return getRespond(true, "Berhasil fetch data", ["following" => true, "item_id" => $id]);
            } else {
                return getRespond(true, "Berhasil fetch data", ["following" => false, "item_id" => $id]);
            }
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\follow  $follow
     * @return \Illuminate\Http\Response
     */
    public function show(follow $follow)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\follow  $follow
     * @return \Illuminate\Http\Response
     */
    public function edit(follow $follow)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\follow  $follow
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, follow $follow)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\follow  $follow
     * @return \Illuminate\Http\Response
     */
    public function destroy(follow $follow)
    {
        //
    }
}
function getRespond($success, $msg, $datas)
{
    if ($success) {
        $data["code"] = 200;
    } else {
        $data["code"] = 500;
    }
    $data["success"] = $success;
    $data["message"] = $msg;
    $data["data"] = $datas;
    return $data;
}
function checkifexist($column, $request_name, $request, $dataTable)
{
    if (array_key_exists($request_name, $request)) {
        $databaru = addData($column, $request_name, $request, $dataTable);
        return $databaru;
    } else {
        return $dataTable;
    }
}
function addData($column, $request_name, $request, $dataTable)
{
    $dataTable[$column] = $request[$request_name];
    return $dataTable;
}
function checkNull($var)
{
    if ($var == null) {
        return true;
    } else {
        return false;
    }
}
