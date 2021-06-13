<?php

namespace App\Http\Controllers;

use App\Models\alamat;
use App\Models\trans;
use App\Models\trans_head;
use Error;
use Exception;
use Illuminate\Http\Request;

class AlamatController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public $data = [
        "success" => "true",
        "message" => "Berhasil",
        "code" => 200,
        "data" => []
    ];
    public function index()
    {
        try {
            $result = alamat::where('soft_delete', 0)->get();
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
            $dataTable = helper::addData("name", "name", $request, $dataTable);
            $dataTable = helper::addData("address_title", "address_title", $request, $dataTable);
            $dataTable = helper::addData("long", "long", $request, $dataTable);
            $dataTable = helper::addData("lat", "lat", $request, $dataTable);
            $dataTable = helper::addData("idrs", "idrs", $request, $dataTable);
            $haveDefault = alamat::where("idrs", $dataTable["idrs"])->where("is_default", true)->first();
            if (!$haveDefault) {
                $dataTable["is_default"] = true;
            }
            $dataTable = helper::addData("state", "state", $request, $dataTable);
            $dataTable = helper::addData("state_code", "state_code", $request, $dataTable);
            $dataTable = helper::addData("district", "district", $request, $dataTable);
            $dataTable = helper::addData("district_code", "district_code", $request, $dataTable);
            $dataTable = helper::addData("city", "city", $request, $dataTable);
            $dataTable = helper::addData("city_code", "city_code", $request, $dataTable);
            $dataTable = helper::addData("receiver_name", "receiver_name", $request, $dataTable);
            $dataTable = helper::addData("phone_number", "phone_number", $request, $dataTable);
            $dataTable = helper::checkifexist("description", "description", $request, $dataTable);
            $location = helper::getLocationCode($dataTable["district"]);
            if (!$location["success"]) {
                throw new Error($location["msg"]);
            }
            $dataTable["juber_place_code"] = $location["data"];
            $items = alamat::create($dataTable);
            $data = helper::resp(true, 'store', "berhasil membuat alamat", $items);
            return $data;
        } catch (\Throwable $th) {
            $data = helper::resp(false, 'store', $th->getMessage(), [], 400);
            return $data;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\alamat  $alamat
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $result = alamat::findOrFail($id);
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
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\alamat  $alamat
     * @return \Illuminate\Http\Response
     */
    public function edit(alamat $alamat)
    {
        //
    }
    public function getByIdrs($id)
    {

        try {
            $result = alamat::where('idrs', $id)->where('soft_delete', 0)->get();
            return helper::resp(true, 'get', 'berhasil', $result);
        } catch (\Throwable $th) {
            return helper::resp(false, 'get', $th->getMessage(), []);
        }
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\alamat  $alamat
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request = json_decode($request->payload, true);
        $dataTable = [];
        try {
            helper::validateArray($request,["is_default:boolean"]);
            $dataTable = helper::addData("name", "name", $request, $dataTable);
            $dataTable = helper::addData("address_title", "address_title", $request, $dataTable);
            $dataTable = helper::addData("long", "long", $request, $dataTable);
            $dataTable = helper::addData("lat", "lat", $request, $dataTable);
            $dataTable = helper::addData("idrs", "idrs", $request, $dataTable);
            $dataTable = helper::addData("state", "state", $request, $dataTable);
            $dataTable = helper::addData("state_code", "state_code", $request, $dataTable);
            $dataTable = helper::addData("district", "district", $request, $dataTable);
            $dataTable = helper::addData("district_code", "district_code", $request, $dataTable);
            $dataTable = helper::addData("city", "city", $request, $dataTable);
            $dataTable = helper::addData("city_code", "city_code", $request, $dataTable);
            $dataTable = helper::addData("receiver_name", "receiver_name", $request, $dataTable);
            $dataTable = helper::addData("phone_number", "phone_number", $request, $dataTable);
            $dataTable = helper::addData("is_default", "is_default", $request, $dataTable);
            if ($dataTable["is_default"]) {
                alamat::where("idrs",$dataTable["idrs"])->where("is_default",true)->update(["is_default",false)];
            }
            $dataTable = helper::checkifexist("description", "description", $request, $dataTable);
            $location = helper::getLocationCode($dataTable["district"]);
            if (!$location["success"]) {
                throw new Error($location["msg"]);
            }
            $dataTable["juber_place_code"] = $location["data"];
            $items = alamat::findOrFail($id)->update($dataTable);
            $dataTable["id"] = $id;
            return helper::resp(true, 'update', "success", $dataTable);
        } catch (\Throwable $th) {
            return helper::resp(false, 'update', $th->getMessage(), $dataTable, 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\alamat  $alamat
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $result = alamat::findOrFail($id)->delete();
            return helper::resp(true, 'destroy', 'berhasil menghapus alamat', $result);
        } catch (\Throwable $th) {
            return helper::resp(false, 'destroy', $th->getMessage(), []);
        }
    }
}
