<?php

namespace App\Http\Controllers;

use App\Models\favorite;
use App\Models\profile;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return favorite::with('item')->get();
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
        // return $request;
        try {
            $request = json_decode($request->payload, true);
            $dataTable = [];
            $dataTable = addData("user_id", "user_id", $request, $dataTable);
            $dataTable = addData("item_id", "item_id", $request, $dataTable);
            $item_id = $dataTable["item_id"];
            $user_id = $dataTable["user_id"];
            $profile = profile::findOrFail($user_id);
            if (checkNull($profile->idrs)) {
                return getRespond(false, "idrs user tidak ada", []);
            }
            $dataTable["idrs"] = $profile->idrs;

            $isExist = favorite::where("item_id", $item_id)->where("user_id", $user_id)->get();
            // return $isExist;
            if (count($isExist) == 0) {
                $data = favorite::create($dataTable);
                // return $data;
                return getRespond(true, "berhasil favorite", ["favorite" => true, "item_id" => $item_id]);
            } else {
                favorite::where("item_id", $item_id)->where("user_id", $user_id)->delete();
                return getRespond(true, "berhasil unfavorite", ["favorite" => false, "item_id" => $item_id]);
            }
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\favorite  $favorite
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $data = favorite::where("idrs", $id)->with('item');
            // $data = favorite::where("favorite.idrs", $id)->join("item", "item.id", "favorite.item_id");
            return getRespond(true, "Berhasil fetch data", $data->get());
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
    }

    public function isFavorite($id)
    {
        try {
            $data = favorite::where("item_id", $id)->count();
            if ($data > 0) {
                return getRespond(true, "Berhasil fetch data", ["favorite" => true, "item_id" => $id]);
            } else {
                return getRespond(true, "Berhasil fetch data", ["favorite" => false, "item_id" => $id]);
            }
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\favorite  $favorite
     * @return \Illuminate\Http\Response
     */
    public function edit(favorite $favorite)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\favorite  $favorite
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\favorite  $favorite
     * @return \Illuminate\Http\Response
     */
    public function destroy(favorite $favorite)
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
