<?php

namespace App\Http\Controllers;

use App\Models\alamat;
use App\Models\item;
use App\Models\profile;
use App\Models\store;
use App\Models\trans;
use App\Models\trans_head;
use App\Models\Variant;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class paymentController extends Controller
{
    public function checkoutPayment(Request $request)
    {
        try {
            $req = json_decode($request->payload, true);
            //validate request
            helper::validateArray($req, [
                "uuid:string",
                "user_idrs:string",
                "promo",
                "address_id:string",
                "store_id:integer",
                "courier_id:integer",
                "courier_name:string",
                "courier_package:string",
                "products:array",
                "cart_id",
                "note:string"
            ]);
            $typevar = strtolower(gettype($req["cart_id"]));
            if ($typevar !== "null" && $typevar !== "integer") {
                throw new Error("cart_id($typevar) must be integer OR null");
            }

            $prods = $req["products"];
            if (count($prods) < 1) {
                throw new Error("Products tidak boleh kosong");
            }

            $profile = profile::where("idrs", $req["user_idrs"])->first(); //get user and validate
            if (!$profile) {
                throw new Error("Pembeli tidak ditemukan");
            }

            $address = alamat::find($req["address_id"]); //get address and validate
            if (!$address) {
                throw new Error("Alamat tidak ditemukan");
            }

            $store = store::find($req["store_id"]); //get store and validate
            if (!$store) {
                throw new Error("Toko tidak ditemukan");
            }
            if ($store->idrs == $req["user_idrs"]) {
                throw new Error("Pembeli tidak boleh membeli ditoko sendiri"); //simple validation but deadly
            }

            $courier = store::find($req["courier_id"]); //courier_id and validate
            if (!$courier) {
                throw new Error("Courier tidak ditemukan");
            }

            if ($req["cart_id"] !== null) {
                $cart = store::find($req["cart_id"]); //cart and validate
                if (!$cart) {
                    throw new Error("Cart tidak ditemukan");
                }
            }

            $validatedProducts = [];
            $total = []; //IDR
            $total_weight = []; //in KG

            foreach ($prods as $key => $value) {
                //validate products
                helper::validateArray($value, [
                    "id:integer",
                    "qty:integer",
                    "variant_id",
                    "note:string"
                ], "Products[{$key}]");
                $var_id = $value["variant_id"];
                $typevar = helper::typeLower($var_id);

                if ($typevar !== "null" && $typevar !== "integer") {
                    throw new Error("Products[$key] variant_id($typevar) must be integer OR null");
                }

                $haveVariant = $var_id !== null;
                $product = item::find($value["id"]);

                if (!$product) {
                    throw new Error("Products[$key] tidak ditemukan");
                }

                $price = intval($product->selling_price);
                $weigth = intval($product->weight);
                $weight_unit = $product->weight_unit;
                $weigth = $weight_unit == "KG" ? $weigth : round($weigth / 1000, 2);
                $variant_name = "";

                if ($haveVariant) {
                    $variant = Variant::where("item_id", $product->id)->where("id", $var_id)->first();

                    if (!$variant) {
                        throw new Error("Products[$key] tidak punya variant ini");
                    }

                    if ($variant->stock < $value["qty"]) {
                        throw new Error("Stock {$product->name} dengan variant {$variant->name} kurang dari {$value['qty']}");
                    }

                    $price = intval($variant->harga);
                    $variant_name = $variant->name;
                }

                if (!$haveVariant) {
                    if ($product->minimal_stock < $value["qty"]) {
                        throw new Error("Stock {$product->name} kurang dari {$value['qty']}");
                    }
                    $var_id = null;
                }

                $sub_total = $price * $value['qty'];
                $sub_weight = $weigth * $value['qty'];

                array_push($validatedProducts, [
                    "id" => $product->id,
                    "variant_id" => $var_id,
                    "qty" => $value["qty"],
                    "note" => $value["note"],
                    "sub_total" => $sub_total,
                    "sub_weight" => $sub_weight,
                    "price" => $price,
                    "weight" => $weigth,
                    "idbarangjbcore" => $product->juber_id,
                    "jumlah" => $value["qty"],
                    "berat" => $weigth,
                    "item_id" => $product->id,
                    "variant_name" => $variant_name
                ]);
                array_push($total, $sub_total);
                array_push($total_weight, $sub_weight);
            }

            // validation end payment process here
            $weight = array_sum($total_weight);
            $juberPayload = [
                "uuid" => $req["uuid"],
                "pembeli" => $req["user_idrs"],
                "promo" => $req["promo"],
                "alamatAntar" => $address->name,
                "latAntar" => $address->lat,
                "lonAntar" => $address->long,
                "kodeWilayahASal" => $address->juber_place_code,
                "kodeWilayahTujuan" => $store->juber_place_code,
                "kurir" => $req["courier_name"],
                "kurir_package" => $req["courier_package"],
                "berat" => $weight,
                "barangs" => $validatedProducts
            ];

            $paid = self::juberPay($juberPayload);
            // return helper::resp(true, "store", "pembayaran test", $paid);
            if (!$paid["success"]) {
                throw new Error($paid["msg"]);
            }
            $uniqueId = time() . mt_rand(1000, 9000);
            $transaction_number = $uniqueId;
            $transactionPayload = [
                "device_id" => $req["uuid"],
                "user_id" => $profile->id,
                "store_id" => $store->id,
                "courier_id" => $courier->id,
                "address_id" => $address->id,
                "user_idrs" => $profile->idrs,
                "note" => $req["note"],
                "currency" => "IDR",
                "promo" => $req["promo"],
                "weight" => $weight,
                "courier_name" => $req["courier_name"],
                "courier_package" => $req["courier_package"],
                "products" => $validatedProducts,
                "status" => 1,
                "transaction_number" => $transaction_number
            ];
            $transaction = self::makeTransaction($transactionPayload);
            if (!$transaction["success"]) {
                throw new Error($transaction["msg"]);
            }

            return helper::resp(true, "store", "pembayaran berhasil", $transaction["data"]);
            //TO DO DECREASE THE STOCK

        } catch (\Throwable $th) {
            return helper::resp(false, "store", $th->getMessage(), [
                "payload" => $req
            ]);
        }
    }
    private function juberPay(array $data)
    {
        try {
            helper::validateArray($data, [
                "uuid:string",
                "pembeli:string",
                "promo",
                "alamatAntar:string",
                "latAntar",
                "lonAntar",
                "kodeWilayahASal:string",
                "kodeWilayahTujuan:string",
                "kurir:string",
                "kurir_package:string",
                "berat", //KG only
                "barangs:array"
            ], "Juberpay");

            foreach ($data["barangs"] as $key => $value) {
                helper::validateArray($value, [
                    "idbarangjbcore",
                    "jumlah:integer",
                    "berat" //KG only
                ], "Juberpay:barangs[$key]");
            }
            $url = "http://192.168.2.45:9888/createtrxjbmarket";
            $response =  http::post($url, $data);
            // return $response->json();
            $response = $response->json();
            if (intval($response["code"]) != 200) {
                throw new Error($response["msg"]);
            }
            return ["success" => true, "data" => $response];
        } catch (\Throwable $th) {
            return ["success" => false, "msg" => $th->getMessage()];
        }
    }

    private function makeTransaction(array $data)
    {
        try {
            helper::validateArray($data, [
                "status:integer",
                "currency:string",
                "note:string",
                "user_idrs:string",
                "device_id:string",
                "store_id:integer",
                "user_id:integer",
                "promo",
                "weight", //KG only
                "courier_name:string",
                "courier_package:string",
                "address_id:integer",
                "courier_id:integer",
                "transaction_number:string",
                "products:array"
            ], "Transaction");
            $products = $data["products"];
            unset($data["products"]);
            $head = trans_head::create($data);
            foreach ($products as $key => $value) {
                helper::validateArray($value, [
                    "item_id:integer",
                    "note:string",
                    "qty:integer", //KG only
                    "variant_id",
                    "variant_name:string",
                    "sub_weight",
                    "sub_total"
                ], "Transaction:barangs[$key]");
                trans::create($value);
            }
            return ["success" => true, "data" => $head];
        } catch (\Throwable $th) {
            return ["success" => false, "msg" => $th->getMessage()];
        }
    }
}
