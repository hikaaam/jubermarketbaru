<?php

namespace App\Http\Controllers;

use App\Models\trans_head;
use App\Models\trans;
use App\Models\store;
use App\Models\profile;
use Illuminate\Http\Request;

use function PHPUnit\Framework\isEmpty;

class TransHeadController extends Controller
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\trans_head  $trans_head
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $trans_head = trans_head::where('id', $id)->with('store')->get();
            $trans_head = $trans_head[0];
            $trans = trans::where("transaction_id", $id)->with("item")->get();
            return getRespond(true, "Berhasil Fetching Data", ["head" => $trans_head, "body" => $trans]);
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
        // return $data;
    }
    public function byStoreRecent($id)
    {
        try {
            $trans_head = trans_head::where("store_id", $id)->where("status", "<", "4")->where("status", "!=", "0")->with('profile')->orderBy('id', 'desc')->get();
            $data = [];
            foreach ($trans_head as $key => $value) {
                $trans = trans::where("transaction_id", $value["id"])->with("item")->get();
                array_push($data, ["head" => $value, "body" => $trans]);
            }
            return getRespond(true, "Berhasil Fetching Data", $data);
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
        // return $data;
    }
    public function byStorePast($id)
    {
        try {
            $trans_head = trans_head::where("store_id", $id)->where("status", 4)->with('profile')->orderBy('id', 'desc')->get();
            $data = [];
            foreach ($trans_head as $key => $value) {
                $trans = trans::where("transaction_id", $value["id"])->with("item")->get();
                array_push($data, ["head" => $value, "body" => $trans]);
            }
            return getRespond(true, "Berhasil Fetching Data", $data);
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
        // return $data;
    }
    public function byUserRecent($id)
    {
        try {
            $trans_head = trans_head::where("user_idrs", $id)->where("status", "<", "4")->where("status", "!=", "0")->with('store')->orderBy('id', 'desc')->get();
            $data = [];
            foreach ($trans_head as $key => $value) {
                $trans = trans::where("transaction_id", $value["id"])->with("item")->get();
                array_push($data, ["head" => $value, "body" => $trans]);
            }
            return getRespond(true, "Berhasil Fetching Data", $data);
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
        // return $data;
    }
    public function byUserPast($id)
    {
        try {
            $trans_head = trans_head::where("user_idrs", $id)->where("status", 4)->with('store')->orderBy('id', 'desc')->get();
            $data = [];
            foreach ($trans_head as $key => $value) {
                $trans = trans::where("transaction_id", $value["id"])->with("item")->get();
                array_push($data, ["head" => $value, "body" => $trans]);
            }
            return getRespond(true, "Berhasil Fetching Data", $data);
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
        // return $data;
    }
    public function byUserUnreviewed($id)
    {
        try {
            $trans = trans::select('market_transaction.*')->where("market_transaction.reviewed", 0)->where('market_transaction_head.user_idrs', $id)
                ->with('trans_head')->with('item')
                ->join('market_transaction_head', 'market_transaction_head.id', '=', 'market_transaction.transaction_id')->get();
            return getRespond(true, "Berhasil Fetching Data", $trans);
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
        // return $data;
    }
    public function byStoreUnreviewed($id)
    {
        try {
            $trans = trans::select('market_transaction.*')->where("market_transaction.reviewed", 0)->where('market_transaction_head.store_id', $id)
                ->with('trans_head')->with('item')
                ->join('market_transaction_head', 'market_transaction_head.id', '=', 'market_transaction.transaction_id')->get();
            return getRespond(true, "Berhasil Fetching Data", $trans);
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
        // return $data;
    }
    public function allOrderStatus()
    {
        try {
            $data = [
                "status" => [
                    "[0]" => "deleted/canceled",
                    "[1]" => "Paid",
                    "[2]" => "packing",
                    "[3]" => "sending",
                    "[4]" => "done",
                    "[5]" => "on return/refund process"
                ],
                "reviewed" => [
                    "[0]" => "unreviewed",
                    "[1]" => "reviewed"
                ]
            ];
            return getRespond(true, "Semua status order", $data);
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
        // return $data;
    }

    public function sellerToken($id)
    {
        try {
            $seller = store::findOrFail($id);
            if (is_null($seller->idrs)) {
                return getRespond(false, "Seller ini tidak terdaftar di juber \n Seller Tidak Memiliki idrs", ["token" => null]);
            }
            $idrs_user = $seller->idrs;
            $profile = profile::select("token")->where("idrs", $idrs_user)->get();
            $profile = $profile[0];
            return getRespond(true, "Berhasil Mendapatkan Token \n Pastikan Token Selalu Diupdate", $profile);
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
        // return $data;
    }

    public function updatePacking($id)
    {
        try {
            $trans_head = trans_head::findOrFail($id);
            // return $trans_head;
            if ($trans_head->status == 1) {
                $data = $trans_head->update(["status" => "2"]);
                return getRespond(true, "Berhasil update status order", ["updatedField" => 1]);
            } else {
                return getRespond(false, "Barang sudah pernah dipacking sebelumnya", ["updatedField" => 0]);
            }
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
        // return $data;
    }
    public function updateSending(Request $request, $id)
    {

        $request = json_decode($request->payload, true);
        $dataTable = [];
        try {
            $dataTable = addData("nomor_resi", "nomor_resi", $request, $dataTable);
            $dataTable["status"] = "3";
            $trans_head = trans_head::findOrFail($id);
            // return $trans_head;
            if ($trans_head->status == 2) {
                $data = $trans_head->update($dataTable);
                return getRespond(true, "Berhasil update status order", ["updatedField" => 1]);
            } else if ($trans_head->status > 2) {
                return getRespond(false, "Barang sudah dikirim sebelumnya", ["updatedField" => 0]);
            } else {
                return getRespond(false, "Barang harus dipacking terlebih dahulu", ["updatedField" => 0]);
            }
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
        // return $data;
    }
    public function updateCancel(Request $request, $id)
    {

        $request = json_decode($request->payload, true);

        try {
            $dataTable = [];
            $dataTable = addData("note", "note", $request, $dataTable);
            $dataTable["status"] = "0";
            $trans_head = trans_head::findOrFail($id);
            // return $trans_head;
            if ($trans_head->status == 1) {
                $data = $trans_head->update($dataTable);
                return getRespond(true, "Berhasil Membatalkan order", ["updatedField" => 1]);
            } else if ($trans_head->status > 1) {
                return getRespond(false, "Barang  yang sudah diproses oleh seller tidak dapat dicancel", ["updatedField" => 0]);
            } else {
                return getRespond(false, "Barang Sudah pernah dicancel sebelumnya", ["updatedField" => 0]);
            }
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
        // return $data;
    }
    public function updateAccept($id)
    {
        try {
            $dataTable["status"] = "4";
            $trans_head = trans_head::findOrFail($id);
            // return $trans_head;
            if ($trans_head->status == 3) {
                $data = $trans_head->update($dataTable);
                return getRespond(true, "Barang berhasil diterima", ["updatedField" => 1]);
            } else if ($trans_head->status == 4) {
                return getRespond(false, "Barang sudah pernah diterima", ["updatedField" => 0]);
            } else if ($trans_head->status > 3) {
                return getRespond(false, "Barang sedang dalam proses pengembalian", ["updatedField" => 0]);
            } else {
                return getRespond(false, "Pastikan barang sudah dikirim ke pembeli", ["updatedField" => 0]);
            }
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
        // return $data;
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\trans_head  $trans_head
     * @return \Illuminate\Http\Response
     */
    public function edit(trans_head $trans_head)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\trans_head  $trans_head
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, trans_head $trans_head)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\trans_head  $trans_head
     * @return \Illuminate\Http\Response
     */
    public function destroy(trans_head $trans_head)
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
