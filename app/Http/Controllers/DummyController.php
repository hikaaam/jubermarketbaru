<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\cart;
use App\Models\cart_ref;
use App\Models\trans_head;
use App\Models\trans;
use App\Models\profile;
use App\Models\model;
use App\Models\store;
use App\Models\Variant;
use App\Models\item;

class DummyController extends Controller
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
        $request = json_decode($request->payload, true);
        $msg = ["success", "waiting", "failed"];
        try {
            // $result = area::select('area_code','name')->where('id','<',35)->get();
            $id_cart = $request['id_cart'];
            $cart = cart_ref::where('id', $id_cart)->get();
            $isexist = count($cart) == 1;
            if ($isexist) {
                $cart = $cart[0];
                $cart_id = $cart->id;
                $dataTable = [
                    "currency" => $cart->currency,
                    "note" => $cart->note,
                    "voucher_id" => $cart->voucher_id,
                    "voucher_code" => $cart->voucher_code,
                    "device_id" => $cart->device_id,
                    "user_idrs" => $cart->idrs,
                    "store_id" => $cart->store_id,
                    "total_brutto" => $cart->total_brutto,
                    "total_commision_fee" => $cart->total_commision_fee,
                    "transaction_number" => $cart->transaction_number,
                    "total_payment" => $cart->total_payment,
                    "total_net" => $cart->total_net,
                    "total_before_rounding" => $cart->total_before_rounding,
                    "user_id" => $cart->user_id,
                    "status" => "1"
                ];
                $trans_head = trans_head::create($dataTable);
                // $trans_head = ["id" => "1"];
                $items = cart::where("transaction_id", $cart_id)->get();
                foreach ($items as $key => $value) {
                    if ($value["variant_id"] == 0) {
                        $variant_id = null;
                    } else {
                        $variant_id = $value["variant_id"];
                    }
                    $itemdata = [
                        "item_id" => $value["item_id"],
                        "note" => $value["note"],
                        "qty" => $value["qty"],
                        "variant_id" => $variant_id,
                        "variant_name" => $value["variant_name"],
                        "sub_total" => $value["sub_total"],
                        "transaction_id" => $trans_head["id"]
                    ];

                    trans::create($itemdata);
                }
                cart_ref::findOrFail($id_cart)->delete();
                $data["success"] = false;
                $data["code"] = 200;
                $data["message"] = "Berhasil";
                $data["data"] = [
                    "stspayment" => $msg[0],
                    "dummy_pay" => true,
                    "order_id" => $trans_head->id
                ];
            } else {
                $data["success"] = false;
                $data["code"] = 500;
                $data["message"] = "Tidak ada cart, pastikan data yang dimasukan benar";
                $data["data"] = [
                    "stspayment" => $msg[2],
                    "dummy_pay" => true
                ];
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
    public function destroy($id)
    {
        //
    }
}
