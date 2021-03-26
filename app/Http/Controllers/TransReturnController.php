<?php

namespace App\Http\Controllers;

use App\Models\trans_head;
use App\Models\trans_return;
use Illuminate\Http\Request;

class TransReturnController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $data = trans_return::with(["profile", "problem", "trans_head"])->get();
            return getRespond(true, "Berhasil fetching data", $data);
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
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\trans_return  $trans_return
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $data = trans_return::where("id", $id)
                ->with(["profile", "problem", "trans_head"])->get();
            $data = $data[0];
            return getRespond(true, "Berhasil fetching data", $data);
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\trans_return  $trans_return
     * @return \Illuminate\Http\Response
     */
    public function edit(trans_return $trans_return)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\trans_return  $trans_return
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }
    public function getAllStatus()
    {
        $data = [
            "status" => [
                "[0]" => "deleted/canceled",
                "[1]" => "created",
                "[2]" => "on_admin_watch",
                "[3]" => "approved",
                "[4]" => "packing",
                "[5]" => "sending",
                "[6]" => "done"
            ]
        ];
        try {
            return getRespond(true, "status pengembalian", $data);
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
    }
    public function updatePacking($id)
    {
        try {
            $trans_return = trans_return::findOrFail($id);
            // return $trans_head;
            if ($trans_return->status == 3) {
                $trans_return->update(["status" => "4"]);
                return getRespond(true, "Berhasil update status pengembalian", ["updatedField" => 1]);
            } else if ($trans_return->status >= 4) {
                return getRespond(false, "Barang sudah pernah dipacking sebelumnya", ["updatedField" => 0]);
            } else {
                return getRespond(false, "Pastikan barang sudah di approve", ["updatedField" => 0]);
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
            $trans_return = trans_return::findOrFail($id);
            // return $trans_head;
            if ($trans_return->status == 4) {
                $dataTable["status"] = "5";
                addData("nomor_resi", "nomor_resi", $request, $dataTable);
                $trans_return->update($dataTable);
                return getRespond(true, "Berhasil update status pengembalian", ["updatedField" => 1]);
            } else if ($trans_return->status >= 5) {
                return getRespond(false, "Barang sudah pernah dikirim", ["updatedField" => 0]);
            } else {
                return getRespond(false, "Pastikan barang sudah dipacking sebelumnya", ["updatedField" => 0]);
            }
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
        // return $data;
    }
    public function updateCancel($id)
    {

        try {
            $trans_return = trans_return::findOrFail($id);
            // return $trans_head;
            if ($trans_return->status == 1 || $trans_return->status == 2) {
                $trans_return->update(["status" => "0"]);
                return getRespond(true, "Berhasil update status pengembalian", ["updatedField" => 1]);
            } else if ($trans_return->status >= 3) {
                return getRespond(false, "Barang yang sudah diproses tidak dapat dibatalkan", ["updatedField" => 0]);
            } else {
                return getRespond(false, "Barang sudah pernah dibatalkan", ["updatedField" => 0]);
            }
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
        // return $data;
    }

    public function updateDone($id)
    {

        try {
            $trans_return = trans_return::findOrFail($id);
            // return $trans_head;
            if ($trans_return->status == 5) {
                $trans_return->update(["status" => "6"]);
                $trans_head = (["status" => 6, "reviewed" => "2"]);
                trans_head::findoRFail($trans_return->order_id)->update($trans_head);
                return getRespond(true, "Pengembalian Berhasil", ["updatedField" => 2]);
            } else if ($trans_return->status == 6) {
                return getRespond(false, "barang sudah selesai dikembalikan", ["updatedField" => 0]);
            } else {
                return getRespond(false, "Pastikan barang sudah dikirim", ["updatedField" => 0]);
            }
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
        // return $data;
    }

    public function updateJuber($id)
    {
        try {
            $trans_return = trans_return::findOrFail($id);
            // return $trans_head;
            if ($trans_return->status == 1) {
                $trans_return->update(["status" => "2"]);
                $trans_head = (["status" => 4, "reviewed" => "0"]);
                trans_head::findoRFail($trans_return->order_id)->update($trans_head);
                return getRespond(true, "Pengembalian dilaporkan kejuber", ["updatedField" => 1]);
            } else if ($trans_return->status == 2) {
                return getRespond(false, "pengembalian telah dilaporakan ke juber", ["updatedField" => 0]);
            } else if ($trans_return->status > 2) {
                return getRespond(false, "pengembalian telah diproses", ["updatedField" => 0]);
            } else {
                return getRespond(false, "pengembalian telah dicancel oleh user", ["updatedField" => 0]);
            }
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        };
    }
    public function updateAccept($id)
    {
        try {
            $trans_return = trans_return::findOrFail($id);
            // return $trans_head;
            if ($trans_return->status == 1 || $trans_return->status == 2) {
                $trans_return->update(["status" => "3"]);
                return getRespond(true, "pengembalian telah disetujui", ["updatedField" => 1]);
            } else if ($trans_return->status >= 3) {
                return getRespond(false, "pengembalian telah disetujui sebelumnya", ["updatedField" => 0]);
            }
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        };
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\trans_return  $trans_return
     * @return \Illuminate\Http\Response
     */
    public function destroy(trans_return $trans_return)
    {
        //
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
