<?php

namespace App\Http\Controllers;

use App\Models\profile;
use App\Models\review;
use App\Models\store;
use App\Models\trans;
use App\Models\trans_head;
use Illuminate\Http\Request;

class ReviewController extends Controller
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
            $result = review::with("profile")->get();
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
            $isexist = review::where("item_id", $request["id_barang"])->where("order_id", $request["id_order"])->count();
            if ($isexist == 0) {

                $dataTable = addData("order_id", "id_order", $request, $dataTable);
                $dataTable = addData("star", "star", $request, $dataTable);
                $dataTable = addData("item_id", "id_barang", $request, $dataTable);
                $dataTable = checkifexist("review", "review", $request, $dataTable);
                $dataTable = checkifexist("is_user", "is_user", $request, $dataTable);
                $trans_head = trans_head::findOrFail($request["id_order"]);
                $dataTable["user_id"] = $trans_head->user_id;
                $dataTable["store_id"] = $trans_head->store_id;
                trans::where("item_id", $request["id_barang"])->where("transaction_id", $request["id_order"])->update(["reviewed" => "1"]);
                $totalUnreviewed = trans::where("transaction_id", $request["id_order"])->where("reviewed", 0)->count();
                if ($totalUnreviewed == 0) {
                    $trans_head->update(["reviewed" => "1"]);
                }
                $items = review::create($dataTable);
                $data["success"] = true;
                $data["code"] = 202;
                $data["message"] = "berhasil";
                $data["data"] = [$items];
            } else {
                return getRespond(false, "Review ini sudah pernah dibuat", ["totalField" => $isexist]);
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
     * Display the specified resource.
     *
     * @param  \App\Models\alamat  $alamat
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $result = review::findORFail($id);
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

    public function getByUser($id)
    {
        try {
            $result = review::where('user_id', $id)->with("profile")->get();
            if ($result->count() > 0) {
                $data["success"] = true;
                $data["code"] = 200;
                $data["message"] = "berhasil";
                $data["data"] = $result;
            } else {

                $data["success"] = true;
                $data["code"] = 200;
                $data["message"] = "Belum ada review";
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
    public function getByIdBarang($id)
    {
        try {
            $result = review::where('item_id', $id)->with("profile")->get();
            if ($result->count() > 0) {
                $data["success"] = true;
                $data["code"] = 200;
                $data["message"] = "berhasil";
                $data["data"] = $result;
            } else {

                $data["success"] = true;
                $data["code"] = 200;
                $data["message"] = "Belum ada review";
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

    public function getByStore($id)
    {
        try {
            $result = review::where('store_id', $id)->with("profile")->get();
            if ($result->count() > 0) {
                $data["success"] = true;
                $data["code"] = 200;
                $data["message"] = "berhasil";
                $data["data"] = $result;
            } else {

                $data["success"] = true;
                $data["code"] = 200;
                $data["message"] = "Belum ada review";
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
     * @param  \App\Models\alamat  $alamat
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
     * @param  \App\Models\alamat  $alamat
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request = json_decode($request->payload, true);
        $dataTable = [];

        try {
            $dataTable = checkifexist("star", "star", $request, $dataTable);
            $dataTable = checkifexist("review", "review", $request, $dataTable);

            review::findOrFail($id)->update($dataTable);
            $data["success"] = true;
            $data["code"] = 202;
            $data["message"] = "berhasil";
            $data["data"] = ["updatedField" => "1"];
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
     * @param  \App\Models\alamat  $alamat
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $result = review::findOrFail($id)->delete();
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
