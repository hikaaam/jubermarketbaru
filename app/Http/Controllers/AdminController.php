<?php

namespace App\Http\Controllers;

use App\Jobs\blockProduct;
use App\Jobs\tokopediaChangeVisible;
use App\Models\item;
use App\Models\store;
use Error;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function blockProduct(Request $request, $id)
    {
        try {
            $req = json_decode($request->payload, true);
            helper::validateArray($req, [
                "block_reason:string",
                "blocked:boolean"
            ]);
            $item = item::find($id);
            if (!$item) {
                throw new Error("Product tidak ditemukan");
            }
            $item->update(["bloked" => $req["blocked"], "block_reason" => $req["block_reason"]]);
            if ($item->tokopedia_id) {
                tokopediaChangeVisible::dispatch($item->tokopedia_id, $req["blocked"]);
            }
            return helper::resp(true, "update", "sukses memblokir product", $item, 200);
        } catch (\Throwable $th) {
            return helper::resp(false, "update", $th->getMessage(), ["payload" => $req], 400);
        }
    }

    public function blockStore(Request $request, $id)
    {
        try {
            $req = json_decode($request->payload, true);
            helper::validateArray($req, [
                "block_reason:string",
                "blocked:boolean"
            ]);
            $store = store::find($id);
            if (!$store) {
                throw new Error("Toko tidak ditemukan");
            }
            $store->update(["bloked" => $req["blocked"], "block_reason" => $req["block_reason"]]);
            $items = item::where("store_id", $id)->get();
            $totalProduct = count($items);
            blockProduct::dispatch($req, $items);
            return helper::resp(
                true,
                "update",
                "toko diblokir",
                [
                    "payload" => $req,
                    "msg" => "memproses blok/unblock {$totalProduct} produk dari juber dan tokopedia",
                    "detail" => "proses pemblokiran product ke tokopedia akan memakan waktu di queue server jadi blok/unblock lah toko dengan bijak",
                    "items" => $items,
                    "store" => $store,
                    "etc" => "response with 202 mean success and the server return the response to the user as fast as possible but the server still progressing tasks in the background"
                ],
                202
            );
        } catch (\Throwable $th) {
            return helper::resp(false, "update", $th->getMessage(), ["payload" => $req], 400);
        }
    }

    public static function blockProductJob($data, $items)
    {
        try {
            foreach ($items as $key => $value) {
                $req = $data;
                $item = item::find($value->id);
                if (!$item) {
                    throw new Error("Product tidak ditemukan");
                }
                $item->update(["bloked" => $req["blocked"], "block_reason" => $req["block_reason"]]);
                if ($item->tokopedia_id) {
                    helper::tokopediaChangeVisibility($item->tokopedia_id, $req["blocked"]);
                }
            }
        } catch (\Throwable $th) {
        }
    }
}
