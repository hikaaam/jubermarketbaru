<?php

namespace App\Http\Controllers;

use App\Models\head;
use App\Models\trans;
use App\Models\trans_head;
use App\Models\Transaction;
use Illuminate\Http\Request;

class PinDriverController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroyDetail($id)
    {
        try {
            Transaction::findOrFail($id)->delete();
            return getRespond(true, "Berhasil Menghapus Data dengan ID : $id", []);
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
    }

    public function destroyHead($id)
    {
        try {
            head::findOrFail($id)->delete();
            return getRespond(true, "Berhasil Menghapus Data dengan ID : $id", []);
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
    }

    public function generatePin(Request $request)
    {
        $req = json_decode($request->payload, true);
        try {
            $pin = rand(100000, 999999);
            $dataTable = [];
            $dataTable["pin"] = $pin;
            $dataTable["currency"] = "IDR";
            $dataTable["store_id"] = 1; //n
            $dataTable["transaction_number"] = time() . rand(1000, 9000); //n
            $dataTable = checkifexist("outlet_id", "outlet_id", $req, $dataTable); //n
            $dataTable = checkifexist("transaction_date_time", "transaction_date_time", $req, $dataTable); //n
            $dataTable = checkifexist("trx_id", "trx_id", $req, $dataTable);
            $dataTable = checkifexist("idrs", "user_idrs", $req, $dataTable);
            $dataTable = checkifexist("note", "note", $req, $dataTable);
            $dataTable = checkifexist("device_id", "device_id", $req, $dataTable);
            $dataTable = checkifexist("voucher_id", "voucher_id", $req, $dataTable);
            $dataTable = checkifexist("status", "status", $req, $dataTable);
            $dataTable = checkifexist("total_brutto", "total_brutto", $req, $dataTable);
            $dataTable = checkifexist("total_commission_fee", "total_commission_fee", $req, $dataTable);
            $dataTable = checkifexist("total_net", "total_net", $req, $dataTable);
            $dataTable = checkifexist("total_before_rounding", "total_before_rounding", $req, $dataTable);
            $dataTable = checkifexist("total_payment", "total_payment", $req, $dataTable);
            $dataTable = checkifexist("voucher_code", "voucher_code", $req, $dataTable);
            // insert to goota
            head::create($dataTable);

            $trx = head::orderby('id', 'desc')->first();
            $id = $trx->id;
            $transaction_number = $trx->transaction_number;
            if (count($req["detail_trans"]) > 0) {
                foreach ($req["detail_trans"] as $key => $value) {
                    $detail = [
                        "transaction_id" => $id, //n
                        "transaction_number" => $transaction_number, //n
                        "selling_price" => $value["selling_price"], //n
                        "item_code" => $value["item_code"], //n
                        "item_id" => $value["item_id"], //n
                        "item_name" => $value["item_name"], //n
                        "subtotal" => $value["subtotal"], //n
                        "note" => $value["note"],
                        "quantity" => $value["qty"],
                        "transaction_id" => $value["transaction_id"],
                    ];
                    Transaction::create($detail);
                }
            }
            return getRespond(true, "Berasil menyimpan data", ["pin" => $pin]);
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
    }

    public function check(Request $request)
    {
        $req = json_decode($request->payload, true);

        try {
            $dataTable = [];
            $dataTable = checkifexist("trx_id", "trx_id", $req, $dataTable);
            $dataTable = checkifexist("pin", "pin", $req, $dataTable);
            $data = head::where([['trx_id', $dataTable["trx_id"]], ['pin', $dataTable["pin"]]])->get();
            if (count($data) > 0) {
                return getRespond(true, "Pin Cocok", ["res" => true]);
            } else {
                return getRespond(false, "Pin Tidak Cocok", ["res" => false]);
            }
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
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
