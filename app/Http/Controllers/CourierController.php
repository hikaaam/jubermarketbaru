<?php

namespace App\Http\Controllers;

use App\Models\courier;
use App\Models\profile;
use App\Models\ref_courier;
use Illuminate\Http\Request;

class CourierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //dont need this 
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
            $checking = courier::where("idrs", $request["idrs"])->where("courier_id", $request["courier_id"])->count();
            if ($checking > 0) {
                return getRespond(false, "Kurir ini sudah ditambahkan menjadi pilihan", ["createdField" => "0"]);
            }
            $profile  = profile::where("idrs", $request["idrs"])->get();
            if (count($profile) < 1) {
                return getRespond(false, "Idrs tidak ditemukan", ["createdField" => "0"]);
            }
            $courier  = ref_courier::findOrFail($request["courier_id"]);
            $profile = $profile[0];
            $dataTable["user_id"] = $profile->id;
            $dataTable["courier_name"] = $courier->name;
            $dataTable = addData("idrs", "idrs", $request, $dataTable);
            $dataTable = addData("courier_id", "courier_id", $request, $dataTable);
            $data = courier::create($dataTable);
            return getRespond(true, "berhasil menambah data kurir", ["createdField" => "1", "data" => $data]);
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), ["createdField" => "0"]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\courier  $courier
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $data = courier::where("idrs", $id)->get();
            $real_data = ["data" => [], "unactive" => []];
            foreach ($data as $key => $value) {
                $id_ = $value["courier_id"];
                $ref = ref_courier::findOrFail($id_);
                if ($ref->active == 1) {
                    array_push($real_data["data"], $value);
                } else {
                    array_push($real_data["unactive"], $value);
                }
            }
            return getRespond(true, "berhasil fetch data", $real_data);
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\courier  $courier
     * @return \Illuminate\Http\Response
     */
    public function edit(courier $courier)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\courier  $courier
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, courier $courier)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\courier  $courier
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            courier::findOrFail($id)->delete();
            return getRespond(true, "berhasil menghapus data", ["deletedField" => "1"]);
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), ["deletedField" => "0"]);
        }
    }
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
