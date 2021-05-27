<?php

namespace App\Http\Controllers;

use App\Jobs\blockProduct;
use App\Jobs\notification;
use App\Jobs\tokopediaChangeVisible;
use App\Models\item;
use App\Models\profile;
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
            $this->sendBlockNotification($id, "item", $req["blocked"]);
            if ($item->tokopedia_id) {
                tokopediaChangeVisible::dispatch($item->tokopedia_id, $req["blocked"]);
            }
            $msg = $req["blocked"] ? "memblokir" : "unblock";
            return helper::resp(true, "update", "sukses {$msg} product", $item, 200);
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
            $this->sendBlockNotification($id, "store", $req["blocked"]);
            $store->update(["bloked" => $req["blocked"], "block_reason" => $req["block_reason"]]);
            $items = item::where("store_id", $id)->get();
            $totalProduct = count($items);
            blockProduct::dispatch($req, $items);
            $msg = $req["blocked"] ? "diblockir" : "diunblock";
            return helper::resp(
                true,
                "update",
                "toko {$msg}",
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

    public static function sendBlockNotification(int $id, string $type, bool $bool)
    {
        try {
            switch ($type) {
                case 'store':
                    $store = store::findOrFail($id);
                    $picture = $store->picture;
                    $idrs = $store->idrs;
                    if (!$idrs) {
                        throw new Error("Don't Have Profile");
                    }
                    $profile = profile::where("idrs", $idrs)->first();
                    $token = $profile->token;
                    if (!$token) {
                        throw new Error("Don't Have Token");
                    }
                    $title = $bool ? "Toko diblokir" : "Toko diunblock";
                    $msg = $bool ? "Toko kamu telah diblokir oleh admin!" : "Toko kamu telah di unblock oleh admin!";
                    notification::dispatch(["title" => $title, "msg" => $msg, "image" => $picture, "token" => $token, "markup" => $msg]);
                    break;
                default:
                    $product = item::findOrFail($id);
                    $picture = $product->picture;
                    $store_id = $product->store_id;
                    $store = store::findOrFail($store_id);
                    $idrs = $store->idrs;
                    if (!$idrs) {
                        throw new Error("Don't Have Profile");
                    }
                    $profile = profile::where("idrs", $idrs)->first();
                    $token = $profile->token;
                    if (!$token) {
                        throw new Error("Don't Have Token");
                    }
                    $title = $bool ? "Product diblokir" : "Product diunblock";
                    $msg = $bool ? "Product {$product->name} telah diblokir oleh admin!" : "Product {$product->name} telah unblock oleh admin!";
                    $markup = $bool ? "Product %i{$product->name}%i telah diblokir oleh admin!" : "Product %i{$product->name}%i telah unblock oleh admin!";
                    notification::dispatch(["title" => $title, "msg" => $msg, "image" => $picture, "token" => $token, "markup" => $markup]);
                    break;
                    break;
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
