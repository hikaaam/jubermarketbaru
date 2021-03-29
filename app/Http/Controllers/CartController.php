<?php

namespace App\Http\Controllers;

use App\Models\cart;
use App\Models\cart_ref;
use App\Models\profile;
use App\Models\model;
use App\Models\store;
use App\Models\Variant;
use App\Models\item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use \stdClass;

class CartController extends Controller
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
        // try {
        //     $users = DB::table('cart_header')
        //     ->join('cart', 'cart_header.id', '=', 'cart.transaction_id')
        //     ->select('cart_header.*', 'cart.*')
        //     ->paginate(6)->get();
        //     $data["success"] = true;
        //     $data["code"] = 200;
        //     $data["message"] = "berhasil";
        //     $data["data"] = $result->setPath(\config('app.url').":8001/api/cart");

        // } catch (\Throwable $th) {
        //     $data["data"] = [];
        //     $data["success"] = false;
        //     $data["code"] = 500;
        //     $data["message"] = $th->getMessage();
        // }
        // return $data;
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
        try {
            $user_id = $request['user_id'];
            $store_id = $request['store_id'];
            $product = $request['product'];
            $note = $request['note'];
            $uuid = $request['uuid'];
            $uniqueId = time() . mt_rand(1000, 9000);
            $profile = profile::findOrFail($user_id);
            $c_head = cart_ref::where("user_id", $user_id)->where("store_id", $store_id)->get();

            if (count($c_head) > 0) {
                $total = [];

                $c_head = $c_head[0];
                $trxId = $c_head->id;
                $total_before = $c_head->total_payment;
                // array_push($total,$total_before);
                $pp = cart::where('transaction_id', $trxId);
                $prevProduct = $pp->get();
                // cart::where('transaction_id', $trxId)->delete();
                foreach ($product as $key => $value) {
                    $variant_id = $value['variant_id'];
                    $id_p = $value['id'];
                    // return $id_p;
                    $qty = $value['qty'];
                    if ($qty < 1) {
                        $data["success"] = false;
                        $data["code"] = 402;
                        $data["message"] = "Jumlah product tidak boleh kosong";
                        $data["data"] = ["qty" => $qty, "item_id" => $id_p, "variant_id" => $variant_id];
                        cart::where('transaction_id', $trxId)->delete();
                        foreach ($prevProduct as $key => $value) {
                            $value = objToarray($value);
                            cart::create($value);
                        }
                        return $data;
                    }
                    $preVal = $pp->where("item_id", $id_p)->get();
                    if (count($preVal) > 0) {
                        $preVal = $preVal[0];
                        $id_cb = $preVal->id;
                        $qty_cb = $preVal->qty;
                        // $subTotalBfr = $preVal->sub_total;
                        $note_ = $value['note'];

                        $dataproduct = item::findOrFail($id_p);
                        if (variant::where('item_id', $id_p)->count() > 0 && $variant_id > 0) {
                            # code...
                            $variant = variant::findOrFail($variant_id);
                            $harga = $variant->harga;
                            if ((intval($qty) + intval($qty_cb)) > $variant->stock) {
                                $data["success"] = false;
                                $data["code"] = 402;
                                $data["message"] = "Maaf stock untuk variant " . $variant->name . " kurang dari " . (intval($qty) + intval($qty_cb));
                                $data["data"] = ["qty" => (intval($qty) + intval($qty_cb)), "item_id" => $id_p, "variant_id" => $variant_id, "stock" => $variant->stock];
                                cart::where('transaction_id', $trxId)->delete();
                                foreach ($prevProduct as $key => $value) {
                                    $value = objToarray($value);
                                    cart::create($value);
                                }
                                return $data;
                            }
                        } else {
                            $harga = $dataproduct->selling_price;
                            $variant = new stdClass();
                            $variant->name = 'null';
                            if ($qty > $dataproduct->minimal_stock) {
                                $data["success"] = false;
                                $data["code"] = 402;
                                $data["message"] = "Maaf stock untuk product " . $dataproduct->name . " kurang dari " . (intval($qty) + intval($qty_cb));
                                $data["data"] = ["qty" => (intval($qty) + intval($qty_cb)), "item_id" => $id_p, "variant_id" => $variant_id, "stock" => $dataproduct->minimal_stock];
                                cart::where('transaction_id', $trxId)->delete();
                                foreach ($prevProduct as $key => $value) {
                                    $value = objToarray($value);
                                    cart::create($value);
                                }
                                return $data;
                            }
                        }
                        $subtotal = $harga * (intval($qty) + intval($qty_cb));
                        $data_ = [
                            'item_id' => $id_p, 'item_name' => $dataproduct->name, 'item_code' => $dataproduct->item_code, 'selling_price' => $harga,
                            'note' => $note_, 'sub_total' => $subtotal, 'transaction_id' => $trxId, 'variant_id' => $variant_id, 'variant_name' => $variant->name, 'qty' => (intval($qty) + intval($qty_cb))
                        ];
                        cart::find($id_cb)->update($data_);
                        array_push($total, $subtotal);
                    } else {
                        $note_ = $value['note'];

                        $dataproduct = item::findOrFail($id_p);
                        if (variant::where('item_id', $id_p)->count() > 0 && $variant_id > 0) {
                            # code...
                            $variant = variant::findOrFail($variant_id);
                            $harga = $variant->harga;
                            if ($qty > $variant->stock) {
                                $data["success"] = false;
                                $data["code"] = 402;
                                $data["message"] = "Maaf stock untuk variant " . $variant->name . " kurang dari " . $qty;
                                $data["data"] = ["qty" => $qty, "item_id" => $id_p, "variant_id" => $variant_id, "stock" => $variant->stock];
                                cart::where('transaction_id', $trxId)->delete();
                                foreach ($prevProduct as $key => $value) {
                                    $value = objToarray($value);
                                    cart::create($value);
                                }
                                return $data;
                            }
                        } else {
                            $harga = $dataproduct->selling_price;
                            $variant = new stdClass();
                            $variant->name = 'null';
                            if ($qty > $dataproduct->minimal_stock) {
                                $data["success"] = false;
                                $data["code"] = 402;
                                $data["message"] = "Maaf stock untuk product " . $dataproduct->name . " kurang dari " . $qty;
                                $data["data"] = ["qty" => $qty, "item_id" => $id_p, "variant_id" => $variant_id, "stock" => $dataproduct->minimal_stock];
                                cart::where('transaction_id', $trxId)->delete();
                                foreach ($prevProduct as $key => $value) {
                                    $value = objToarray($value);
                                    cart::create($value);
                                }
                                return $data;
                            }
                        }
                        $subtotal = $harga * $qty;
                        $data_ = [
                            'item_id' => $id_p, 'item_name' => $dataproduct->name, 'item_code' => $dataproduct->item_code, 'selling_price' => $harga,
                            'note' => $note_, 'sub_total' => $subtotal, 'transaction_id' => $trxId, 'variant_id' => $variant_id, 'variant_name' => $variant->name, 'qty' => $qty
                        ];
                        cart::create($data_);
                        array_push($total, $subtotal);
                    }
                }
                $total = array_sum($total);
                $data_ = ['total_payment' => $total, 'note' => $note];
                cart_ref::findOrFail($trxId)->update($data_);
                $data["success"] = true;
                $data["code"] = 202;
                $data["message"] = "berhasil update data";
                $data["data"] = ["cart_header_id" => $trxId];
            } else {
                $cartHeader = ["currency" => 'IDR', 'note' => $note, 'device_id' => $uuid, 'store_id' => $store_id, 'user_id' => $user_id, 'idrs' => $profile->idrs, 'total_payment' => 0, 'transaction_number' => $uniqueId];
                $dataCartHeader  = cart_ref::create($cartHeader);
                $trxId = $dataCartHeader->id;


                $total = [];

                foreach ($product as $key => $value) {
                    $id_p = $value['id'];
                    $qty = $value['qty'];
                    if ($qty < 1) {
                        $data["success"] = false;
                        $data["code"] = 402;
                        $data["message"] = "Jumlah product tidak boleh kosong";
                        $data["data"] = ["qty" => $qty, "item_id" => $id_p, "variant_id" => $value['variant_id']];
                        cart_ref::findOrFail($trxId)->delete();
                        return $data;
                    }
                    $note_ = $value['note'];
                    $variant_id = $value['variant_id'];
                    $dataproduct = item::findOrFail($id_p);
                    if (variant::where('item_id', $id_p)->count() > 0 && $variant_id > 0) {
                        # code...
                        $variant = variant::findOrFail($variant_id);
                        $harga = $variant->harga;
                        if ($qty > $variant->stock) {
                            $data["success"] = false;
                            $data["code"] = 402;
                            $data["message"] = "Maaf stock untuk variant " . $variant->name . " kurang dari " . $qty;
                            $data["data"] = ["qty" => $qty, "item_id" => $id_p, "variant_id" => $variant_id, "stock" => $variant->stock];
                            cart_ref::findOrFail($trxId)->delete();
                            return $data;
                        }
                    } else {
                        $harga = $dataproduct->selling_price;
                        $variant = new stdClass();
                        $variant->name = 'null';
                        if ($qty > $dataproduct->minimal_stock) {
                            $data["success"] = false;
                            $data["code"] = 402;
                            $data["message"] = "Maaf stock untuk product " . $dataproduct->name . " kurang dari " . $qty;
                            $data["data"] = ["qty" => $qty, "item_id" => $id_p, "variant_id" => $variant_id, "stock" => $dataproduct->minimal_stock];
                            cart_ref::findOrFail($trxId)->delete();
                            return $data;
                        }
                    }
                    $subtotal = $harga * $qty;
                    $data_ = [
                        'item_id' => $id_p, 'item_name' => $dataproduct->name, 'item_code' => $dataproduct->item_code, 'selling_price' => $harga,
                        'note' => $note_, 'sub_total' => $subtotal, 'transaction_id' => $trxId, 'variant_id' => $variant_id, 'variant_name' => $variant->name, 'qty' => $qty
                    ];
                    cart::create($data_);
                    array_push($total, $subtotal);
                }
                $total = array_sum($total);
                $data_ = ['total_payment' => $total];
                cart_ref::findOrFail($trxId)->update($data_);
                $data["success"] = true;
                $data["code"] = 202;
                $data["message"] = "berhasil";
                $data["data"] = ["cart_header_id" => $trxId];
            }
        } catch (\Throwable $th) {
            $data["data"] = [];
            $data["success"] = false;
            $data["code"] = 500;
            $data["message"] = $th->getMessage();
            $data["request"] = $request;
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

            $header = cart_ref::where('user_id', $id)->with("store")->get();
            $data_cart = [];
            foreach ($header as $key => $value) {
                $cart = cart::where('transaction_id', $value->id)->with("item")->get();
                array_push($data_cart, $cart);
            }
            $result = [];
            $i = 0;
            foreach ($header as $key => $value) {
                $data_ = ['cart_header' => $value, 'cart_item' => $data_cart[$i]];
                array_push($result, $data_);
                $i++;
            }
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

        try {
            $product = $request['product'];
            $note = $request['note'];
            $trxId = $id;

            $total = [];
            $prevProduct = cart::where('transaction_id', $trxId)->get();
            cart::where('transaction_id', $trxId)->delete();
            foreach ($product as $key => $value) {
                $id_p = $value['id'];
                $qty = $value['qty'];
                $variant_id = $value['variant_id'];
                if ($qty < 1) {
                    $data["success"] = false;
                    $data["code"] = 402;
                    $data["message"] = "Jumlah product tidak boleh kosong";
                    $data["data"] = ["qty" => $qty, "item_id" => $id_p, "variant_id" => $variant_id];
                    cart::where('transaction_id', $trxId)->delete();
                    foreach ($prevProduct as $key => $value) {
                        $value = objToarray($value);
                        cart::create($value);
                    }
                    return $data;
                }
                $note_ = $value['note'];

                $dataproduct = item::findOrFail($id_p);
                if (variant::where('item_id', $id_p)->count() > 0 && $variant_id > 0) {
                    # code...
                    $variant = variant::findOrFail($variant_id);
                    $harga = $variant->harga;
                    if ($qty > $variant->stock) {
                        $data["success"] = false;
                        $data["code"] = 402;
                        $data["message"] = "Maaf stock untuk variant " . $variant->name . " kurang dari " . $qty;
                        $data["data"] = ["qty" => $qty, "item_id" => $id_p, "variant_id" => $variant_id, "stock" => $variant->stock];
                        cart::where('transaction_id', $trxId)->delete();
                        foreach ($prevProduct as $key => $value) {
                            $value = objToarray($value);
                            cart::create($value);
                        }
                        return $data;
                    }
                } else {
                    $harga = $dataproduct->selling_price;
                    $variant = new stdClass();
                    $variant->name = 'null';
                    if ($qty > $dataproduct->minimal_stock) {
                        $data["success"] = false;
                        $data["code"] = 402;
                        $data["message"] = "Maaf stock untuk product " . $dataproduct->name . " kurang dari " . $qty;
                        $data["data"] = ["qty" => $qty, "item_id" => $id_p, "variant_id" => $variant_id, "stock" => $dataproduct->minimal_stock];
                        cart::where('transaction_id', $trxId)->delete();
                        foreach ($prevProduct as $key => $value) {
                            $value = objToarray($value);
                            cart::create($value);
                        }
                        return $data;
                    }
                }
                $subtotal = $harga * $qty;
                $data_ = [
                    'item_id' => $id_p, 'item_name' => $dataproduct->name, 'item_code' => $dataproduct->item_code, 'selling_price' => $harga,
                    'note' => $note_, 'sub_total' => $subtotal, 'transaction_id' => $trxId, 'variant_id' => $variant_id, 'variant_name' => $variant->name, 'qty' => $qty
                ];
                cart::create($data_);
                array_push($total, $subtotal);
            }
            $total = array_sum($total);
            $data_ = ['total_payment' => $total, 'note' => $note];
            cart_ref::findOrFail($trxId)->update($data_);
            $data["success"] = true;
            $data["code"] = 202;
            $data["message"] = "berhasil update data";
            $data["data"] = ["cart_header_id" => $trxId];
        } catch (\Throwable $th) {
            $data["data"] = [];
            $data["success"] = false;
            $data["code"] = 500;
            $data["message"] = $th->getMessage();
        }
        return $data;
    }

    public function updateStatus(Request $request, $id)
    {
        $request = json_decode($request->payload, true);
        $dataTable = [];
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
        try {
            $status = $request['status'];
            $trxId = $id;
            $cart_ref = cart_ref::findOrFail($id);
            $cartHeader = [
                "currency" => 'IDR', 'note' => $cart_ref->note, 'device_id' => $cart_ref->device_id, 'store_id' => $cart_ref->store->id,
                'idrs' => $cart_ref->idrs, 'total_payment' => $cart_ref->total_payment, 'transaction_number' => $cart_ref->transaction_number, "status" => $status
            ];
            $head = head::create($cartHeader);
            $cart_product = cart::where('transaction_id', $trxId)->get();

            //lanjutin dari sini tinggal isi tabel transaction_detail sesuai dengan tabel cart where trx_id
            foreach ($cart_product as $key => $value) {
                $id_p = $value['id'];
                $qty = $value['qty'];
                $note_ = $value['note'];
                $variant_id = $value['variant_id'];
                $dataproduct = item::findOrFail($id_p);
                if (variant::where('item_id', $id_p)->count() > 0 && $variant_id > 0) {
                    # code...
                    $variant = variant::findOrFail($variant_id);
                    $harga = $variant->harga;
                } else {
                    $harga = $dataproduct->selling_price;
                    $variant = new stdClass();
                    $variant->name = 'null';
                }
                $subtotal = $harga * $qty;
                $data_ = [
                    'item_id' => $id_p, 'item_name' => $dataproduct->name, 'item_code' => $dataproduct->item_code, 'selling_price' => $harga,
                    'note' => $note_, 'sub_total' => $subtotal, 'transaction_id' => $trxId, 'variant_id' => $variant_id, 'variant_name' => $variant->name, 'qty' => $qty
                ];
                cart::create($data_);
                array_push($total, $subtotal);
            }
            $total = array_sum($total);
            $data_ = ['total_payment' => $total, 'note' => $note];
            cart_ref::findOrFail($trxId)->update($data_);
            $data["success"] = true;
            $data["code"] = 202;
            $data["message"] = "berhasil update data";
            $data["data"] = ["cart_header_id" => $trxId];
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
            $result = cart_ref::findOrFail($id)->delete();
            $data["success"] = true;
            $data["code"] = 200;
            $data["message"] = "berhasil";
            $data["data"] = $result . ' data';
        } catch (\Throwable $th) {
            $data["data"] = [];
            $data["success"] = false;
            $data["code"] = 500;
            $data["message"] = $th->getMessage();
        }
        return $data;
    }
}
function objToArray($value)
{
    return [
        "id" => $value->id,
        "item_id" => $value->item_id,
        "item_name" => $value->item_name,
        "item_code" => $value->item_code,
        "note" => $value->note,
        "selling_price" => $value->selling_price,
        "qty" => $value->qty,
        "sub_total" => $value->sub_total,
        "transaction_id" => $value->transaction_id,
        "variant_id" => $value->variant_id,
        "variant_name" => $value->variant_name
    ];
}
