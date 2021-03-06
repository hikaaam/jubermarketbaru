<?php

namespace App\Http\Controllers;

use App\Jobs\decreaseStock;
use App\Jobs\paymentNotification;
use App\Models\alamat;
use App\Models\cart_ref;
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
            $scs = false;
            $req = json_decode($request->payload, true);
            //validate request
            helper::validateArray($req, [
                "uuid:string",
                "user_idrs:string",
                "promo",
                "address_id:integer",
                "store_id:integer",
                'shipment_fee:integer',
                "courier_name:string",
                "courier_package:string",
                "products:array",
                "cart_id",
                "note:string",
                "market_courier_type:string",
                "stringify_selected_courier:string",
                "market_courier_type:string"
            ]);
            $typevar = strtolower(gettype($req["cart_id"]));
            if ($typevar !== "null" && $typevar !== "integer") {
                throw new Error("cart_id($typevar) must be integer OR null");
            }
            $market_type = $req["market_courier_type"];

            if ($market_type != "all" && $market_type != "instant" && $market_type !== "sap") {
                throw new Error("Tipe pengiriman {$market_type} tidak didukung oleh juber, tipe yang di dukung adalah (sap, instant dan all)");
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



            if ($req["cart_id"] !== null) {
                $cart = cart_ref::find($req["cart_id"]); //cart and validate
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
                    "name:string",
                    "variant_id",
                    "note:string"
                ], "Products[{$key}]");
                $var_id = $value["variant_id"];
                $typevar = helper::typeLower($var_id);
                $name = $value["name"];
                if ($typevar !== "null" && $typevar !== "integer") {
                    throw new Error("Products[$key] variant_id($typevar) must be integer OR null");
                }

                $haveVariant = $var_id !== null;
                $product = item::find($value["id"]);

                if (!$product) {
                    throw new Error("Product {$name} tidak ditemukan atau telah dihapus");
                }

                if ($product->is_shown === 0) {
                    throw new Error("Product {$product->name} sudah di nonaktifkan oleh penjual");
                }

                if ($product->blocked) {
                    throw new Error("Product {$product->name} diblokir karena {$product->block_reason}");
                }

                if ($product->deleted_at) {
                    throw new Error("Product {$product->name} suddah dihapus oleh penjual");
                }

                if ($product->discount_price) {
                    $price = intval($product->discount_price);
                } else {
                    $price = intval($product->selling_price);
                }
                $weigth = intval($product->weight);
                $weight_unit = $product->weight_unit;
                $weigth = $weight_unit == "KG" ? intval(ceil($weigth * 1000)) :  intval($product->weight);
                $variant_name = "";

                if ($haveVariant) {
                    $variant = Variant::where("item_id", $product->id)->where("id", $var_id)->first();
                    $jbid = $variant->juber_id;
                    if (!$variant) {
                        throw new Error("Products[$key] tidak punya variant ini");
                    }

                    if ($variant->stock < $value["qty"]) {
                        throw new Error("Stock {$product->name} dengan variant {$variant->name} kurang dari {$value['qty']}");
                    }

                    $variant_name = $variant->name;
                    if ($variant->discount_price) {
                        $price = intval($variant->discount_price);
                    } else {
                        $price = intval($variant->harga);
                    }
                }

                if (!$haveVariant) {
                    if ($product->minimal_stock < $value["qty"]) {
                        throw new Error("Stock {$product->name} kurang dari {$value['qty']}");
                    }
                    $jbid = $product->juber_id;
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
                    "idbarangjbcore" => $jbid,
                    "jumlah" => $value["qty"],
                    "berat" => $weigth,
                    "item_id" => $product->id,
                    "variant_name" => $variant_name
                ]);
                array_push($total, $sub_total);
                array_push($total_weight, $sub_weight);
            }

            // validation end payment process here
            $weight = intval(ceil(array_sum($total_weight) / 1000)); //KG only

            if ($market_type == "all") {
                $kodeOrg =  $store->juber_place_code;
                $kodeDest = $address->juber_place_code;
            }

            if ($market_type == "sap") {
                $kodeOrg =  $store->sap_place_code;
                $kodeDest = $address->sap_place_code;
            }

            $juberPayload = [
                "uuid" => $req["uuid"],
                "pembeli" => $req["user_idrs"],
                "merchant" => $store->idrs,
                "promo" => $req["promo"],
                "alamatAntar" => $address->name,
                "latAntar" => $address->lat,
                "lonAntar" => $address->long,
                "latAsal" => $store->latitude,
                "lonAsal" => $store->longitude,
                "kodeWilayahASal" => $kodeOrg,
                "kodeWilayahTujuan" => $kodeDest,
                "kurir" => $req["courier_name"],
                "kurir_package" => $req["courier_package"],
                "berat" => $weight,
                "barangs" => $validatedProducts
            ];


            $paid = self::juberPay($juberPayload);

            // return helper::resp(false, "store", "cek respoonse", $paid); //for trial purpose

            if (!$paid["success"]) {
                // $req = $paid["data"];
                throw new Error($paid["msg"]);
            }

            $transaction_number = $paid["data"];
            $nomorResi = "";

            // $transaction_number = "testasdsad"; //for trial purpose
            // $nomorResi = "asdasdas"; //for trial purpose

            $transactionPayload = [
                "device_id" => $req["uuid"],
                "user_id" => $profile->id,
                "store_id" => $store->id,
                "address_id" => $address->id,
                "user_idrs" => $profile->idrs,
                "note" => $req["note"],
                "currency" => "IDR",
                "promo" => $req["promo"],
                "weight" => $weight,
                "total_payment" => array_sum($total) + $req["shipment_fee"],
                "courier_name" => $req["courier_name"],
                "courier_package" => $req["courier_package"],
                "products" => $validatedProducts,
                "status" => 1,
                "transaction_number" => $transaction_number,
                "nomor_resi" => $nomorResi,
                "shipment_fee" => $req["shipment_fee"],
                "stringify_selected_courier" => $req["stringify_selected_courier"],
                "courier_code_org" => $kodeOrg,
                "courier_code_dest" => $kodeDest,
                "market_courier_type" => $market_type
            ];
            $transaction = self::makeTransaction($transactionPayload);
            if (!$transaction["success"]) {
                throw new Error($transaction["msg"]);
            }
            $cart->delete();
            $scs = true;
            return helper::resp(true, "store", "pembayaran berhasil", $transaction["data"]);
        } catch (\Throwable $th) {
            $scs = false;
            return helper::resp(false, "store", $th->getMessage(), [
                "payload" => $req
            ]);
        } finally {
            if ($scs) {
                //TO DO DECREASE THE STOCK ON VARIANT
                paymentNotification::dispatch($profile, $store);
                decreaseStock::dispatch($validatedProducts);
            }
        }
    }


    private function juberPay(array $data)
    {
        try {
            helper::validateArray($data, [
                "uuid:string",
                "pembeli:string",
                "merchant:string",
                "promo",
                "alamatAntar:string",
                "latAntar",
                "lonAntar",
                "kodeWilayahASal:string",
                "kodeWilayahTujuan:string",
                "kurir:string",
                "kurir_package:string",
                "berat:integer", //KG only
                "barangs:array"
            ], "Juberpay");
            // return $data;
            foreach ($data["barangs"] as $key => $value) {
                helper::validateArray($value, [
                    "idbarangjbcore",
                    "jumlah:integer",
                    "berat:integer" //KG only
                ], "Juberpay:barangs[$key]");
            }
            $url = "http://192.168.2.45:9888/createtrxjbmarket";
            $stringifyJson = json_encode($data);
            $response =  http::post($url, ["json" => $stringifyJson]);
            $response = $response->json();
            // return ["success" => false, "data" => $response, "msg" => "untracked Error"];
            if (array_key_exists("code", $response)) {
                if (intval($response["code"]) != 200) {
                    throw new Error($response["msg"]);
                }
            } else {
                throw new Error($response["message"]);
            }
            return ["success" => true, "data" => $response["lobj"][0]];
        } catch (\Throwable $th) {
            return ["success" => false, "msg" => $th->getMessage()];
        }
    }

    private static function stupidArrayToObject(array $arr)
    {
        $obj = [];
        foreach ($arr as $key => $value) {
            $explode = explode($value, ": ");
            $obj[$explode[0]] = $explode[1];
        }
        return $obj;
    }

    public static function decreaseProductStock(array $data)
    {
        foreach ($data as $key => $value) {
            if ($value["variant_id"]) {
                $variant = Variant::find($value["variant_id"]);
                $current_stock = intval($variant->stock) - $value["qty"];
                $current_stock = $current_stock <= 0 ? 0 : $current_stock;
                $variant->update(["stock" => $current_stock]);
                //TODO TOKOPEDIA REDUCE STOCK
            } else {
                $product = item::find($value["id"]);
                $current_stock = intval($product->minimal_stock) - $value["qty"];
                $current_stock = $current_stock <= 0 ? 0 : $current_stock;
                $tokopedia_id = $product->tokopedia_id;
                $product->update(["minimal_stock" => $current_stock]);
                if ($tokopedia_id) {
                    $getToken = helper::getToken();
                    $token = $getToken["token"];
                    $fs_id = $getToken["fs_id"];
                    $shop_id = helper::shopid();
                    $headers = helper::getAuth($token);
                    $url = "https://fs.tokopedia.net/inventory/v1/fs/{$fs_id}/stock/update?shop_id={$shop_id}";
                    http::withHeaders($headers)->post($url, [[
                        "product_id" => $tokopedia_id,
                        "new_stock" => $current_stock
                    ]]);
                }
            }
        }
    }

    public static function paymentNotification(object $profile, object $store)
    {
        $tokenPembeli = $profile->token;
        if ($tokenPembeli) {
            helper::sendNotification($tokenPembeli, "Pesanan anda berhasil diproses.", "user", "JuberPay");
        }
        $idrs = $store->idrs;
        if ($idrs) {
            $seller = profile::where("idrs", $idrs)->first();
            if ($seller) {
                $tokenPenjual = $seller["token"];
                if ($tokenPenjual) {
                    helper::sendNotification($tokenPenjual, "Anda memiliki pesanan baru.", "seller", "Juber Marketplace");
                }
            }
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
                "weight", //Gram
                "courier_name:string",
                "courier_package:string",
                "address_id:integer",
                "transaction_number:string",
                "nomor_resi:string",
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
                $dataBarang = [
                    "item_id" => $value["id"],
                    "note" => $value["note"],
                    "qty" => $value["qty"],
                    "variant_id" => $value["variant_id"],
                    "variant_name" => $value["variant_name"],
                    "sub_weight" => $value["sub_weight"],
                    "sub_total" => $value["sub_total"],
                    "transaction_id" => $head->id
                ];
                trans::create($dataBarang);
            }
            return ["success" => true, "data" => $head];
        } catch (\Throwable $th) {
            return ["success" => false, "msg" => $th->getMessage()];
        }
    }
}
