<?php

namespace App\Http\Controllers;

use App\Jobs\juberCoreSyncStatus;
use App\Jobs\notification;
use App\Models\item;
use App\Models\trans_head;
use App\Models\trans;
use App\Models\store;
use App\Models\profile;
use App\Models\trans_return;
use App\Models\Variant;
use Error;
use Illuminate\Http\Request;


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
    public function byStoreAll($id)
    {
        try {
            $trans_head = trans_head::where("store_id", $id)->with('profile')->orderBy('id', 'desc')->get();
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
            $trans_head_cancel = trans_head::where("store_id", $id)->where("status", 0)->with('profile')->orderBy('id', 'desc')->get();
            $data = [];
            $data["done"] = [];
            $data["canceled"] = [];
            foreach ($trans_head as $key => $value) {
                $trans = trans::where("transaction_id", $value["id"])->with("item")->get();
                array_push($data["done"], ["head" => $value, "body" => $trans]);
            }
            foreach ($trans_head_cancel as $key => $value) {
                $trans = trans::where("transaction_id", $value["id"])->with("item")->get();
                array_push($data["canceled"], ["head" => $value, "body" => $trans]);
            }
            return getRespond(true, "Berhasil Fetching Data", $data);
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
        // return $data;
    }
    public function byStoreReturn($id)
    {
        try {
            $trans_head = trans_head::where("store_id", $id)->where("status", 5)->with('profile')->orderBy('id', 'desc')->get();
            $trans_head_return = trans_head::where("store_id", $id)->where("status", 6)->with('profile')->orderBy('id', 'desc')->get();
            $data = [];
            $data["proses"] = [];
            $data["done"] = [];
            foreach ($trans_head as $key => $value) {
                $trans = trans::where("transaction_id", $value["id"])->with("item")->get();
                array_push($data["proses"], ["head" => $value, "body" => $trans]);
            }
            foreach ($trans_head_return as $key => $value) {
                $trans = trans::where("transaction_id", $value["id"])->with("item")->get();
                array_push($data["done"], ["head" => $value, "body" => $trans]);
            }
            return getRespond(true, "Berhasil Fetching Data", $data);
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
        // return $data;
    }
    public function byUserAll($id)
    {
        try {
            $trans_head = trans_head::where("user_idrs", $id)->with('store')->orderBy('id', 'desc')->get();
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
            $trans_head_cancel = trans_head::where("user_idrs", $id)->where("status", 0)->with('store')->orderBy('id', 'desc')->get();

            $data = [];
            $data["done"] = [];
            $data["canceled"] = [];
            foreach ($trans_head as $key => $value) {
                $trans = trans::where("transaction_id", $value["id"])->with("item")->get();
                array_push($data["done"], ["head" => $value, "body" => $trans]);
            }
            foreach ($trans_head_cancel as $key => $value) {
                $trans = trans::where("transaction_id", $value["id"])->with("item")->get();
                array_push($data["canceled"], ["head" => $value, "body" => $trans]);
            }
            return getRespond(true, "Berhasil Fetching Data", $data);
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
        // return $data;
    }
    public function byUserReturn($id)
    {
        try {
            $trans_head = trans_head::where("user_idrs", $id)->where("status", 5)->with('store')->orderBy('id', 'desc')->get();
            $trans_head_return = trans_head::where("user_idrs", $id)->where("status", 6)->with('store')->orderBy('id', 'desc')->get();
            $data = [];
            $data["proses"] = [];
            $data["done"] = [];
            foreach ($trans_head as $key => $value) {
                $trans = trans::where("transaction_id", $value["id"])->with("item")->get();
                array_push($data["proses"], ["head" => $value, "body" => $trans]);
            }
            foreach ($trans_head_return as $key => $value) {
                $trans = trans::where("transaction_id", $value["id"])->with("item")->get();
                array_push($data["done"], ["head" => $value, "body" => $trans]);
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
            $trans = trans::select('market_transaction.*')->where("market_transaction.reviewed", 0)->where('market_transaction_head.user_idrs', $id)->where('market_transaction_head.status', "4")
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
            $trans = trans::select('market_transaction.*')->where("market_transaction.reviewed", 0)->where('market_transaction_head.store_id', $id)->where('market_transaction_head.status', "4")
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
                    "[5]" => "on return/refund process",
                    "[6]" => "on return success"
                ],
                "reviewed" => [
                    "[0]" => "unreviewed",
                    "[1]" => "reviewed",
                    "[2]" => "product canceled/returned"
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

                $store = self::getStoreNameAndTokenFromStoreId($trans_head->store_id);
                $user = self::getUserNameAndTokenFromUserId($trans_head->user_id);
                $image = self::getImageFromIdTrx($id);

                $userTitle = "Pesanan diproses penjual";
                $userMsg = "Pesanan {$trans_head->transaction_number} telah diproses penjual.";
                $userMsgMark = "Pesanan %i{$trans_head->transaction_number}%i telah diproses penjual.";

                $notifUser = self::notificationFormat($userTitle, $userMsg, $user["token"], $image, "user", $userMsgMark);
                juberCoreSyncStatus::dispatch(["id" => $id, "status" => 8]);
                notification::dispatch($notifUser);

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
            $dataTable["status"] = "3";
            $trans_head = trans_head::findOrFail($id);
            if ($trans_head->status == 2) {
                $data = $trans_head->update($dataTable);

                $store = self::getStoreNameAndTokenFromStoreId($trans_head->store_id);
                $user = self::getUserNameAndTokenFromUserId($trans_head->user_id);
                $image = self::getImageFromIdTrx($id);


                $userTitle = "Pesanan dibatalkan";
                $userMsg = "Pesanan {$trans_head->transaction_number} sedang dalam pengiriman.";
                $userMsgMark = "Pesanan %i{$trans_head->transaction_number}%i sedang dalam pengiriman.";
                $notifUser = self::notificationFormat($userTitle, $userMsg, $user["token"], $image, "user", $userMsgMark);
                juberCoreSyncStatus::dispatch(["id" => $id, "status" => 3]);
                notification::dispatch($notifUser);

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
        // return $request;
        try {
            $dataTable = [];
            $dataTable = addData("note", "note", $request, $dataTable);
            $dataTable["status"] = "0";
            $dataTable["reviewed"] = "2";
            $trans_head = trans_head::findOrFail($id);
            // return $trans_head;
            if ($trans_head->status == 1) {
                $data = $trans_head->update($dataTable);

                $store = self::getStoreNameAndTokenFromStoreId($trans_head->store_id);
                $user = self::getUserNameAndTokenFromUserId($trans_head->user_id);
                $image = self::getImageFromIdTrx($id);

                $sellerTitle = "Pesanan dibatalkan pembeli";
                $userTitle = "Pesanan dibatalkan";
                $sellerMsg = "Pesanan {$trans_head->transaction_number} dibatalkan {$user['name']}.
                 Mohon tidak proses pesanan ini.";
                $sellerMsgMark = "Pesanan %i{$trans_head->transaction_number}%i dibatalkan %i{$user['name']}%i.
                 Mohon tidak proses pesanan ini.";
                $userMsg = "Pesanan {$trans_head->transaction_number} telah dibatalkan.";
                $userMsgMark = "Pesanan %i{$trans_head->transaction_number}%i telah dibatalkan.";

                juberCoreSyncStatus::dispatch(["id" => $id, "status" => 2]);
                $notifUser = self::notificationFormat($userTitle, $userMsg, $user["token"], $image, "user", $userMsgMark);
                notification::dispatch($notifUser);
                $notifSeller = self::notificationFormat($sellerTitle, $sellerMsg, $store["token"], $image, "seller", $sellerMsgMark);
                notification::dispatch($notifSeller);

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
                $trans = trans::select("item_id")->where("transaction_id", $trans_head->id)->get();
                foreach ($trans as $key => $value) {
                    $id_ = $value["item_id"];
                    $item = item::find($id_);
                    $sold = $item->sold;
                    $newSold =  intval($sold) + 1;
                    $item->update(["sold" => $newSold]);
                }
                $store = self::getStoreNameAndTokenFromStoreId($trans_head->store_id);
                $user = self::getUserNameAndTokenFromUserId($trans_head->user_id);
                $image = self::getImageFromIdTrx($id);

                juberCoreSyncStatus::dispatch(["id" => $id, "status" => 1]);

                $sellerTitle = "Pesanan telah diterima pembeli";
                $sellerMsg = "Pesanan {$trans_head->transaction_number} telah diterima oleh {$user['name']}";
                $sellerMsgMark = "Pesanan %i{$trans_head->transaction_number}%i telah diterima oleh %i{$user['name']}%i";
                $notifSeller = self::notificationFormat($sellerTitle, $sellerMsg, $store["token"], $image, "seller", $sellerMsgMark);
                notification::dispatch($notifSeller);

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
    public function returnOrder(Request $request, $id)
    {
        // return $request;
        $request = json_decode($request->payload, true);
        try {
            $dataTable["status"] = "5";
            $dataTable["reviewed"] = "2";
            $trans_head = trans_head::findOrFail($id);
            // return $trans_head;
            if ($trans_head->status == 3) {
                $data = $trans_head->update($dataTable);
                $return_table = [
                    "note" => $request["note"], "order_id" => $id,
                    "user_id" => $trans_head->user_id,
                    "store_id" => $trans_head->store_id,
                    "problem_id" => $request["problem_id"],
                    "status" => "1"
                ];
                $return_table = checkifexist("picture_one", "picture_one", $request, $return_table);
                $return_table = checkifexist("picture_two", "picture_two", $request, $return_table);
                $return_table = checkifexist("picture_three", "picture_three", $request, $return_table);
                $return_table = checkifexist("picture_four", "picture_four", $request, $return_table);
                $return_table = checkifexist("picture_five", "picture_dive", $request, $return_table);

                $store = self::getStoreNameAndTokenFromStoreId($trans_head->store_id);
                $user = self::getUserNameAndTokenFromUserId($trans_head->user_id);
                $image = self::getImageFromIdTrx($id);

                $sellerTitle = "Pengajuan pengembalian";
                $userTitle = "Pengajuan pengembalian";
                $sellerMsg = "{$user['name']} telah mengajukan pengembalian terhadap pesanan {$trans_head->transaction_number}.
                 Mohon tidak proses pesanan ini.";
                $sellerMsgMark = "%i{$user['name']}%i telah mengajukan pengembalian terhadap pesanan %i{$trans_head->transaction_number}%i.
                 Mohon tidak proses pesanan ini.";
                $userMsg = "Pesanan {$trans_head->transaction_number} dalam proses pengajuan pengembalian";
                $userMsgMark = "Pesanan %i{$trans_head->transaction_number}%i dalam proses pengajuan pengembalian";

                juberCoreSyncStatus::dispatch(["id" => $id, "status" => 8]);
                $notifUser = self::notificationFormat($userTitle, $userMsg, $user["token"], $image, "user", $userMsgMark);
                notification::dispatch($notifUser);
                $notifSeller = self::notificationFormat($sellerTitle, $sellerMsg, $store["token"], $image, "seller", $sellerMsgMark);
                notification::dispatch($notifSeller);

                $trans_return = trans_return::create($return_table);
                return getRespond(true, "Berhasil mengembalikan barang", ["updatedField" => 1, "return_order_id" => $trans_return->id]);
            } else if ($trans_head->status == 4) {
                return getRespond(false, "Barang yang sudah diterima tidak dapat dikembalikan", ["updatedField" => 0]);
            } else if ($trans_head->status == 5) {
                return getRespond(false, "Barang yang sedang dalam proses pengembalian", ["updatedField" => 0]);
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
    public static function getImageFromIdTrx($id)
    {
        try {
            $trans_body = trans::where("transaction_id", $id)->first();
            if ($trans_body) {
                $haveVariant = $trans_body->variant_id != null;
                if ($haveVariant) {
                    $var = $trans_body->variant_id;
                    $variant = Variant::findOrFail($var);
                    $image = $variant->picture;
                    return $image;
                }
                $pid = $trans_body->item_id;
                $product = item::findOrFail($pid);
                $image = $product->picture;
                return $image;
            }
            throw new Error("transaction not found!");
        } catch (\Throwable $th) {
            return null;
        }
    }

    public static function getUserNameAndTokenFromUserId($id)
    {
        try {
            $profile = profile::find($id);
            $token = $profile->token;
            if ($profile) {
                $name = $profile->name;
                $name = $name ?? "pembeli";
                return ["token" => $token, "name" => $name];
            }
            return ["token" => $token, "name" => "pembeli"];
        } catch (\Throwable $th) {
            return ["token" => null, "name" => "pembeli"];
        }
    }

    public static function getStoreNameAndTokenFromStoreId($id)
    {
        try {
            $store = store::find($id);
            $idrs = $store->idrs;
            if ($store) {
                $profile = profile::where("idrs", $idrs)->first();
                return ["token" => $profile->token, "name" => $store->name];
            }
            throw new Error("store not found!");
        } catch (\Throwable $th) {
            return ["token" => null, "name" => "penjual"];
        }
    }
    public static function notificationFormat($title, $msg, $token, $image, $type, $markup)
    {
        return [
            "type" => $type,
            "msg" => $msg,
            "image" => $image,
            "token" => $token,
            "title" => $title,
            "markup" => $markup
        ];
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
