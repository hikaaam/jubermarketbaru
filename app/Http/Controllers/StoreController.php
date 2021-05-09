<?php

namespace App\Http\Controllers;

use App\Models\store;
use Error;
use Illuminate\Database\QueryException;
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
            $dataTable = helper::addData("store_name", "store_name", $request, $dataTable);
            $dataTable = helper::addData("membership_type", "membership_type", $request, $dataTable);
            $dataTable = helper::addData("owner", "owner", $request, $dataTable);
            $dataTable = helper::addData("idrs", "idrs", $request, $dataTable);
            $dataTable = helper::addData("address", "address", $request, $dataTable);
            $dataTable = helper::addData("city", "city", $request, $dataTable);
            $dataTable = helper::addData("city_code", "city_code", $request, $dataTable);
            $dataTable = helper::addData("district", "district", $request, $dataTable);
            $dataTable = helper::addData("district_code", "district_code", $request, $dataTable);
            $dataTable = helper::checkifexist("email", "email", $request, $dataTable);
            $dataTable = helper::checkifexist("fax", "fax", $request, $dataTable);
            $dataTable = helper::addData("latitude", "latitude", $request, $dataTable);
            $dataTable = helper::addData("longitude", "longitude", $request, $dataTable);
            $dataTable = helper::checkifexist("outlet_type", "outlet_type", $request, $dataTable);
            $dataTable = helper::addData("phone", "phone", $request, $dataTable);
            $dataTable = helper::addData("state_code", "state_code", $request, $dataTable);
            $dataTable = helper::addData("state", "state", $request, $dataTable);
            $dataTable = helper::checkifexist("store_type", "store_type", $request, $dataTable);
            $dataTable = helper::checkifexist("sub_district", "sub_district", $request, $dataTable);
            $dataTable = helper::checkifexist("sub_district_code", "sub_district_code", $request, $dataTable);
            $dataTable = helper::checkifexist("parent_id", "parent_id", $request, $dataTable);
            $dataTable = helper::addData("picture", "picture", $request, $dataTable);
            $dataTable = helper::addData("cover_picture", "cover_picture", $request, $dataTable);
            $isEmailValid = helper::validateEmail($dataTable["email"]);
            if (!$isEmailValid) {
                throw new Error("email tidak valid");
            }
            // if (array_key_exists("district", $dataTable)) {
            //     $location = helper::getLocationCode($dataTable["district"]);
            //     if (!$location["success"]) {
            //         throw new Error($location["msg"]);
            //     }
            //     $dataTable["juber_place_code"] = $location["data"];
            // }
            $items = store::create($dataTable);
            return helper::resp(true, 'store', "berhasil membuat toko", $items);
        } catch (\Throwable $th) {
            $msg = $th->getMessage();
            if (str_contains($msg, "duplicate")) {
                if (str_contains($msg, "(idrs)")) {
                    $str = "IDRS sudah ada";
                } else if (str_contains($msg, "(email")) {
                    $str = "Email sudah ada";
                } else if (str_contains($msg, "(phone")) {
                    $str = "Nomor hp sudah ada";
                } else {
                    $str = "Duplicate entry";
                }
                $msg = $str;
            }
            return helper::resp(false, 'store', $msg, [], 400);
        }
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
            return helper::resp(true, "get", "berhasil update data", ["updatedField" => "1", "timestamp" => time()]);
        } catch (\Throwable $th) {
            return helper::resp(false, "get", $th->getMessage(), ["updatedField" => "0"]);
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
            $dataTable = helper::checkifexist("store_name", "store_name", $request, $dataTable);
            $dataTable = helper::checkifexist("membership_type", "membership_type", $request, $dataTable);
            $dataTable = helper::checkifexist("owner", "owner", $request, $dataTable);
            $dataTable = helper::checkifexist("idrs", "idrs", $request, $dataTable);
            $dataTable = helper::checkifexist("address", "address", $request, $dataTable);
            $dataTable = helper::checkifexist("city", "city", $request, $dataTable);
            $dataTable = helper::checkifexist("city_code", "city_code", $request, $dataTable);
            $dataTable = helper::checkifexist("district", "district", $request, $dataTable);
            $dataTable = helper::checkifexist("district_code", "district_code", $request, $dataTable);
            $dataTable = helper::checkifexist("email", "email", $request, $dataTable);
            $dataTable = helper::checkifexist("picture", "picture", $request, $dataTable);
            $dataTable = helper::checkifexist("cover_picture", "cover_picture", $request, $dataTable);
            $dataTable = helper::checkifexist("fax", "fax", $request, $dataTable);
            $dataTable = helper::checkifexist("latitude", "latitude", $request, $dataTable);
            $dataTable = helper::checkifexist("longitude", "longitude", $request, $dataTable);
            $dataTable = helper::checkifexist("outlet_type", "outlet_type", $request, $dataTable);
            $dataTable = helper::checkifexist("phone", "phone", $request, $dataTable);
            $dataTable = helper::checkifexist("state_code", "state_code", $request, $dataTable);
            $dataTable = helper::checkifexist("state", "state", $request, $dataTable);
            $dataTable = helper::checkifexist("store_type", "store_type", $request, $dataTable);
            $dataTable = helper::checkifexist("sub_district", "sub_district", $request, $dataTable);
            $dataTable = helper::checkifexist("sub_district_code", "sub_district_code", $request, $dataTable);
            $dataTable = helper::checkifexist("parent_id", "parent_id", $request, $dataTable);
            if (array_key_exists("district", $dataTable)) {
                $location = helper::getLocationCode($dataTable["district"]);
                if (!$location["success"]) {
                    throw new Error($location["msg"]);
                }
                $dataTable["juber_place_code"] = $location["data"];
            }
            $items = store::findOrFail($id)->update($dataTable);
            $dataTable["id"] = $id;
            return helper::resp(true, 'update', 'berhasil update toko', $dataTable);
        } catch (\Throwable $th) {
            return helper::resp(false, 'update', $th->getMessage(), []);
        }
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


function checkNull($var)
{
    if ($var == null) {
        return true;
    } else {
        return false;
    }
}
