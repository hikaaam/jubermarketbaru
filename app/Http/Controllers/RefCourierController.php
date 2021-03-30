<?php

namespace App\Http\Controllers;

use App\Models\ref_courier;
use Illuminate\Http\Request;

class RefCourierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $data = ref_courier::all();
            $real_data = ["data" => [], "unactive" => []];
            foreach ($data as $key => $value) {
                if ($value["active"] == 1) {
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
    public function unactive()
    {
        try {
            $data = ref_courier::where("active", 0)->get();
            return getRespond(true, "berhasil fetch data", $data);
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
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
        try {
            $request = json_decode($request->payload, true);
            $dataTable = [];
            $dataTable = addData("name", "name", $request, $dataTable);
            $data = ref_courier::create($dataTable);
            return getRespond(true, "berhasil menambah data kurir", ["createdField" => "1", "data" => $data]);
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), ["createdField" => "0"]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ref_courier  $ref_courier
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $dataTable = [];
            $courier = ref_courier::findOrFail($id);
            if ($courier->active == 1) {
                $dataTable["active"] = 0;
                $courier->update($dataTable);
                return getRespond(true, "berhasil mengupdate data kurir", ["updatedField" => "1", "active" => false]);
            } else {
                $dataTable["active"] = 1;
                $courier->update($dataTable);
                return getRespond(true, "berhasil mengupdate data kurir", ["updatedField" => "1", "active" => true]);
            }
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), ["createdField" => "0"]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ref_courier  $ref_courier
     * @return \Illuminate\Http\Response
     */
    public function edit(ref_courier $ref_courier)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ref_courier  $ref_courier
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        try {
            $request = json_decode($request->payload, true);
            $dataTable = [];
            $dataTable = checkifexist("name", "name", $request, $dataTable);
            $data = ref_courier::findOrFail($id)->update($dataTable);
            return getRespond(true, "berhasil mengupdate data kurir", ["updatedField" => "1"]);
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), ["updatedField" => "0"]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ref_courier  $ref_courier
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $data = ref_courier::findOrFail($id)->delete();
            return getRespond(true, "berhasil menghapus kurir", ["deletedField" => "1"]);
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
