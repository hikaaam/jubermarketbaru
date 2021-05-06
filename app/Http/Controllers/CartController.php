<?php

namespace App\Http\Controllers;

use App\Models\cart;
use App\Models\cart_ref;
use App\Models\head;
use App\Models\profile;
use App\Models\model;
use App\Models\store;
use App\Models\Variant;
use App\Models\item;
use Error;
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
        //READ STOREME INSTEAD
        return "oke";
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
            $cart = cart_ref::where('id', $id)->with("store", "body.item")->get();
            if (count($cart) <= 0) {
                throw new Error("Cart tidak ditemukan");
            }
            return helper::resp(true, "GET", "berhasil get cart", $cart[0]);
        } catch (\Throwable $th) {
            return helper::resp(false, "GET", $th->getMessage(), []);
        }
    }
    public function detailCart($id)
    {
        try {
            $cart = cart_ref::where('id', $id)->with("store", "body.item")->get();
            if (count($cart) <= 0) {
                throw new Error("Cart tidak ditemukan");
            }
            return helper::resp(true, "GET", "berhasil get cart", $cart[0]);
        } catch (\Throwable $th) {
            return helper::resp(false, "GET", $th->getMessage(), []);
        }
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
            $data_ = ['total_payment' => $total]; //deleted note
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
            $result = cart_ref::findOrFail($id);
            $result->delete();
            return helper::resp(true, "destroy", "Berhasil hapus cart", $result);
        } catch (\Throwable $th) {
            return helper::resp(false, "destroy", $th->getMessage(), []);
        }
    }
    public function storeme(Request $request)
    {
        try {
            $request = json_decode($request->payload, true);
            $dataTable = [];
            //addData validate and insert required column to $dataTable
            //addData will return error if request empty
            //checkifexist if request exist then will be inserted into $dataTable
            $dataTable = helper::addData("user_id", "user_id", $request, $dataTable);
            $dataTable = helper::addData("store_id", "store_id", $request, $dataTable);
            $dataTable = helper::addData("device_id", "uuid", $request, $dataTable);
            $dataTable = helper::addData("idrs", "idrs", $request, $dataTable);
            $dataTable = helper::checkifexist("note", "note", $request, $dataTable);
            $dataTable["currency"] = "IDR";
            helper::validateArray($request, ["product"]);
            //validate if request have product u can also use this to validate all request
            //just add more request name in $rules ["product","user_id", ...]
            // but doesnt return anything only throw error when empty
            $product = $request["product"];

            $uniqueId = time() . mt_rand(1000, 9000);
            $dataTable["transaction_number"] = $uniqueId;

            $oldCart = cart_ref::where("store_id", $dataTable["store_id"])
                ->where("user_id", $dataTable["user_id"])->get(); //get the old cart

            $isCartNew = count($oldCart) <= 0;

            if ($isCartNew) {
                $total = [];
                $cart_body = [];
                $cart_header = $dataTable;

                foreach ($product as $key => $value) {
                    helper::validateArray($value, ["qty", "id", "variant_id"]); //throw error if one of the rules is doesn't exist

                    $findProduct = item::find($value['id']);
                    if (helper::isEmpty($findProduct)) {
                        throw new Error("Produk tidak ditemukan!");
                    }
                    //find product with this id and throw custom error if not found

                    if (helper::isEmpty($value['qty'])) { //check if quantity is empty
                        throw new Error("Jumlah produk tidak boleh kosong!");
                    }
                    $dontHaveVariant = helper::isEmpty($value["variant_id"]);
                    //mean will use the product price instead of variant price
                    if (!$dontHaveVariant) {
                        $variant = Variant::where("id", $value["variant_id"])->where("item_id", $value["id"])->get();
                        if (count($variant) <= 0) { //if variant don't belong to this product then throw error
                            throw new Error("Product {$findProduct->name} tidak memiliki variant dengan id {$value['variant_id']}");
                        }
                        if (intval($variant[0]->stock) < intval($value['qty'])) { //if variant stock is lesser than quantity then throw error
                            throw new Error("Stock variant {$variant[0]->name} kurang dari {$value['qty']}");
                        }
                        $variant = $variant[0]; //get the variant on first array
                    } else {
                        if (intval($findProduct->minimal_stock) < intval($value['qty'])) { //if stock is lesser than quantity then throw error
                            throw new Error("Stock produk {$findProduct->name} kurang dari {$value['qty']}");
                        }
                    }
                    $productPrice = $dontHaveVariant ? $findProduct->selling_price : $variant->harga;

                    array_push($cart_body, array(
                        "item_name" => $findProduct->name,
                        "item_code" => $findProduct->item_code,
                        "note" => $value["note"] ?? "",
                        "selling_price" => $productPrice,
                        "qty" => $value["qty"],
                        "sub_total" => intval($value["qty"]) * intval($productPrice),
                        "item_id" => $value["id"],
                        "variant_id" => $dontHaveVariant ? null : $variant->id,
                        "variant_name" => $dontHaveVariant ? null : $variant->name
                    )); //push to cart body
                    array_push($total, intval($value["qty"]) * intval($productPrice)); //push price to total
                }

                //if it reach here then there is no validation error time to insert cart to database
                $total_payment = array_sum($total);
                $cart_header["total_payment"] = $total_payment;
                $cart_header_db = cart_ref::create($cart_header); //insert cart_header to DB and get the id

                foreach ($cart_body as $key => $value) {
                    $validated_cart_body = $value;
                    $validated_cart_body["transaction_id"] = $cart_header_db->id;
                    cart::create($validated_cart_body); //insert cart_body to DB
                }

                return helper::resp(true, "store", "berhasil menambahkan cart", ["cart_header_id" => $cart_header_db->id]); //return success response
            }
            //if the code runs here mean cart is not new

            $cart_header = $oldCart[0]; //getting the old cart
            $old_cart_body = cart::where("transaction_id", $cart_header->id)->get();
            $old_total = intval($cart_header->total_payment);
            $cart_body = [];
            $total = [];
            foreach ($product as $key => $value) { //validate the product first
                helper::validateArray($value, ["qty", "id", "variant_id"]); //throw error if one of the rules is doesn't exist

                $findProduct = item::find($value['id']);
                if (helper::isEmpty($findProduct)) {
                    throw new Error("Produk tidak ditemukan!");
                }
                //find product with this id and throw custom error if not found

                if (helper::isEmpty($value['qty'])) { //check if quantity is empty
                    throw new Error("Jumlah produk tidak boleh kosong!");
                }
                $dontHaveVariant = helper::isEmpty($value["variant_id"]);
                //mean will use the product price instead of variant price
                if (!$dontHaveVariant) {
                    $variant = Variant::where("id", $value["variant_id"])->where("item_id", $value["id"])->get();
                    if (count($variant) <= 0) { //if variant don't belong to this product then throw error
                        throw new Error("Product {$findProduct->name} tidak memiliki variant dengan id {$value['variant_id']}");
                    }
                    if (intval($variant[0]->stock) < intval($value['qty'])) { //if variant stock is lesser than quantity then throw error
                        throw new Error("Stock variant {$variant[0]->name} kurang dari {$value['qty']}");
                    }
                    $variant = $variant[0]; //get the variant on first array
                } else {
                    if (intval($findProduct->minimal_stock) < intval($value['qty'])) { //if stock is lesser than quantity then throw error
                        throw new Error("Stock produk {$findProduct->name} kurang dari {$value['qty']}");
                    }
                }
                $productPrice = $dontHaveVariant ? $findProduct->selling_price : $variant->harga;

                array_push($cart_body, array(
                    "item_name" => $findProduct->name,
                    "item_code" => $findProduct->item_code,
                    "note" => $value["note"] ?? "",
                    "selling_price" => $productPrice,
                    "qty" => $value["qty"],
                    "sub_total" => intval($value["qty"]) * intval($productPrice),
                    "item_id" => $value["id"],
                    "variant_id" => $dontHaveVariant ? null : $variant->id,
                    "variant_name" => $dontHaveVariant ? null : $variant->name
                )); //push to cart body
            }
            //validate the cart body first because the header already exist
            $validated_cart_body = [];
            $actions = [];
            foreach ($cart_body as $key => $value) {

                $check = productInOldCart($value, $old_cart_body);
                $isExactMatch = $check["product"] && $check["variant"]; //is product with this variant exist in old cart
                $oldSubTotal = $isExactMatch ? intval($check["old"]["sub_total"]) : 0;
                $oldQty = $isExactMatch ? intval($check["old"]["qty"]) : 0;
                $old_total = $old_total - $oldSubTotal;
                $newQty = intval($value["qty"]) + $oldQty;
                $dontHaveVariant = helper::isEmpty($value["variant_id"]);
                if ($dontHaveVariant) {
                    $findProduct = item::findOrFail($value["item_id"]);
                    $stock = intval($findProduct->minimal_stock);
                } else {
                    $variant = Variant::findOrFail($value["variant_id"]);
                    $stock = $variant->stock;
                }
                if ($newQty > $stock) {
                    $msg = $dontHaveVariant ? "stock product {$findProduct->name} tidak mencukupi" : "stock variant {$variant->name} tidak mencukupi";
                    throw new Error($msg);
                }
                $newSubTotal = intval($value["sub_total"]) + $oldSubTotal;
                $value["qty"] = $newQty;
                $value["sub_total"] = $newSubTotal;
                array_push($validated_cart_body, $value);
                array_push($total, $newSubTotal);
                array_push($actions, ["action" => $isExactMatch ? "update" : "store", "data" => $isExactMatch ? $check["old"] : []]);
            }
            //finally insert/update the new cart body
            // return $actions;
            foreach ($validated_cart_body as $key => $value) {
                switch ($actions[$key]["action"]) {
                    case 'update':
                        $data = $actions[$key]["data"];
                        cart::findOrFail($data["id"])->update($value);
                        break;
                    default:
                        $value["transaction_id"] = $cart_header->id;
                        cart::create($value);
                        break;
                }
            }
            $total_payment = array_sum($total) + $old_total;
            cart_ref::findOrFail($cart_header->id)->update(["total_payment" => $total_payment]);
            return helper::resp(true, "store", "berhasil menambahkan cart", ["cart_header_id" => $cart_header->id]);
        } catch (\Throwable $th) {
            return helper::resp(false, "store", $th->getMessage(), []);
        }
    }
}
function productInOldCart($product)
{
    $variant_id = $product["variant_id"];
    $item_name = $product["item_name"];
    if ($variant_id) {
        $old = cart::where("variant_id", $variant_id)->where("item_name", $item_name)->get();
        if (count($old) <= 0) {
            return array("product" => true, "variant" => false, "old" => []);
        }
        return array("product" => true, "variant" => true, "old" => $old[0]);
    }
    $old = cart::where("variant_id", $variant_id)->where("item_name", $item_name)->get();
    if (count($old) <= 0) {
        return array("product" => true, "variant" => false, "old" => []);
    }
    return array("product" => true, "variant" => true, "old" => $old[0]);
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
