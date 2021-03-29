<?php

namespace App\Http\Controllers;

use App\Models\store;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public $data = [
        "success" => "true",
        "message" => "Berhasil",
        "code" => 200,
        "data" => []
    ];
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $result = store::all();
            $data["success"] = true;
            $data["code"] = 200;
            $data["message"] = "berhasil";
            $data["data"] = $result;
        } catch (\Throwable $th) {
            $data["data"] = [];
            $data["success"] = false;
            $data["code"] = 500;
            $data["message"] = $th->getMessage();
        }
        return $data;
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
        $request = json_decode($request->payload, true);
        $dataTable = [];

        try {
            $dataTable = addData("store_name", "store_name", $request, $dataTable);
            $dataTable = addData("membership_type", "membership_type", $request, $dataTable);
            $dataTable = addData("owner", "owner", $request, $dataTable);
            $dataTable = addData("idrs", "idrs", $request, $dataTable);
            $dataTable = checkifexist("address", "address", $request, $dataTable);
            $dataTable = checkifexist("city", "city", $request, $dataTable);
            $dataTable = checkifexist("city_code", "city_code", $request, $dataTable);
            $dataTable = checkifexist("district", "district", $request, $dataTable);
            $dataTable = checkifexist("district_code", "district_code", $request, $dataTable);
            $dataTable = checkifexist("email", "email", $request, $dataTable);
            $dataTable = checkifexist("fax", "fax", $request, $dataTable);
            $dataTable = checkifexist("latitude", "latitude", $request, $dataTable);
            $dataTable = checkifexist("longitude", "longitude", $request, $dataTable);
            $dataTable = checkifexist("outlet_type", "outlet_type", $request, $dataTable);
            $dataTable = checkifexist("phone", "phone", $request, $dataTable);
            $dataTable = checkifexist("state_code", "state_code", $request, $dataTable);
            $dataTable = checkifexist("state", "state", $request, $dataTable);
            $dataTable = checkifexist("store_type", "store_type", $request, $dataTable);
            $dataTable = checkifexist("sub_district", "sub_district", $request, $dataTable);
            $dataTable = checkifexist("sub_district_code", "sub_district_code", $request, $dataTable);
            $dataTable = checkifexist("parent_id", "parent_id", $request, $dataTable);
            $items = store::create($dataTable);
            $data["success"] = true;
            $data["code"] = 202;
            $data["message"] = "berhasil";
            $data["data"] = ["request_data" => $items];
        } catch (\Throwable $th) {
            $data["data"] = [];
            $data["success"] = false;
            $data["code"] = 500;
            $data["message"] = $th->getMessage();
        }
        return $data;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\store  $store
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $result = store::findOrFail($id);
            $data["success"] = true;
            $data["code"] = 200;
            $data["message"] = "berhasil";
            $data["data"] = $result;
        } catch (\Throwable $th) {
            $data["data"] = [];
            $data["success"] = false;
            $data["code"] = 500;
            $data["message"] = $th->getMessage();
        }
        return $data;
    }
    public function lastActive($id)
    {
        try {
            $data = store::findOrFail($id);
            $data->update(["last_active" => time()]);
            return getRespond(true, "berhasil update data", ["updatedField" => "1", "timestamp" => time()]);
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), ["updatedField" => "0"]);
        }
    }
    public function getByOwner(Request $request, $id)
    {
        try {
            $result = store::where('owner', $id)->get();
            $data["success"] = true;
            $data["code"] = 200;
            $data["message"] = "berhasil";
            $data["data"] = $result;
        } catch (\Throwable $th) {
            $data["data"] = [];
            $data["success"] = false;
            $data["code"] = 500;
            $data["message"] = $th->getMessage();
        }
        return $data;
    }

    public function getByIdrs(Request $request, $id)
    {
        try {
            $result = store::where('idrs', $id)->get();
            if ($result->count() > 0) {
                $data["success"] = true;
                $data["code"] = 200;
                $data["message"] = "berhasil";
                $data["data"] = $result;
            } else {
                $data["success"] = true;
                $data["code"] = 200;
                $data["message"] = "Belum Punya Toko";
                $data["data"] = $result;
            }
        } catch (\Throwable $th) {
            $data["data"] = [];
            $data["success"] = false;
            $data["code"] = 500;
            $data["message"] = $th->getMessage();
        }
        return $data;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\store  $store
     * @return \Illuminate\Http\Response
     */
    public function edit(store $store)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\store  $store
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request = json_decode($request->payload, true);
        $dataTable = [];
        try {
            $dataTable = checkifexist("store_name", "store_name", $request, $dataTable);
            $dataTable = checkifexist("membership_type", "membership_type", $request, $dataTable);
            $dataTable = checkifexist("owner", "owner", $request, $dataTable);
            $dataTable = checkifexist("idrs", "idrs", $request, $dataTable);
            $dataTable = checkifexist("address", "address", $request, $dataTable);
            $dataTable = checkifexist("city", "city", $request, $dataTable);
            $dataTable = checkifexist("city_code", "city_code", $request, $dataTable);
            $dataTable = checkifexist("district", "district", $request, $dataTable);
            $dataTable = checkifexist("district_code", "district_code", $request, $dataTable);
            $dataTable = checkifexist("email", "email", $request, $dataTable);
            $dataTable = checkifexist("fax", "fax", $request, $dataTable);
            $dataTable = checkifexist("latitude", "latitude", $request, $dataTable);
            $dataTable = checkifexist("longitude", "longitude", $request, $dataTable);
            $dataTable = checkifexist("outlet_type", "outlet_type", $request, $dataTable);
            $dataTable = checkifexist("phone", "phone", $request, $dataTable);
            $dataTable = checkifexist("state_code", "state_code", $request, $dataTable);
            $dataTable = checkifexist("state", "state", $request, $dataTable);
            $dataTable = checkifexist("store_type", "store_type", $request, $dataTable);
            $dataTable = checkifexist("sub_district", "sub_district", $request, $dataTable);
            $dataTable = checkifexist("sub_district_code", "sub_district_code", $request, $dataTable);
            $dataTable = checkifexist("parent_id", "parent_id", $request, $dataTable);
            $items = store::findOrFail($id)->update($dataTable);
            $data["success"] = true;
            $data["code"] = 202;
            $data["message"] = "berhasil";
            $data["data"] = ["request_data" => $dataTable];
        } catch (\Throwable $th) {
            $data["data"] = [];
            $data["success"] = false;
            $data["code"] = 500;
            $data["message"] = $th->getMessage();
        }
        return $data;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\store  $store
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $result = store::findOrFail($id)->delete();
            $data["success"] = true;
            $data["code"] = 200;
            $data["message"] = "berhasil dihapus";
            $data["data"] = [];
        } catch (\Throwable $th) {
            $data["data"] = [];
            $data["success"] = false;
            $data["code"] = 500;
            $data["message"] = $th->getMessage();
        }
        return $data;
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
function checkNull($var)
{
    if ($var == null) {
        return true;
    } else {
        return false;
    }
}
