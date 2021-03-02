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
        "success"=>"true",
        "message"=>"Berhasil",
        "code"=>200,
        "data"=>[]
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
        $request = json_decode($request->payload,true);
        $dataTable = [];
        function checkifexist($column,$request_name,$request,$dataTable){
            if( array_key_exists($request_name,$request)){
               $databaru = addData($column,$request_name,$request,$dataTable);
               return $databaru;
            }
            else{
                return $dataTable;
            }
        }
        function addData($column,$request_name,$request,$dataTable){
            $dataTable[$column] = $request[$request_name];
            return $dataTable;
        }
        try {
           $user_id = $request['user_id'];
           $store_id = $request['store_id'];
           $product = $request['product'];
           $note = $request['note'];
           $uuid = $request['uuid'];
           $profile = profile::findOrFail($user_id);
           $store = store::findOrFail($store_id);
           $uniqueId= time().mt_rand(1,9000);
           $cartHeader = ["currency"=>'IDR','note'=>$note,'device_id'=>$uuid,'store_id'=>$store_id,'user_id'=>$user_id,'idrs'=>$profile->idrs,'total_payment'=>0,'transaction_number'=>$uniqueId];
           $dataCartHeader  = cart_ref::create($cartHeader);
           $trxId = $dataCartHeader->id;
    
           $total = [];
           
           foreach ($product as $key => $value) {
               $id_p = $value['id'];
               $qty = $value['qty'];
               $note_ = $value['note'];
               $variant_id = $value['variant_id'];
               $dataproduct = item::findOrFail($id_p);
               if (variant::where('item_id',$id_p)->count() > 0 && $variant_id>0) {
                   # code...
                   $variant = variant::findOrFail($variant_id);
                   $harga = $variant->harga;
               } else {
                   $harga = $dataproduct->selling_price;
                   $variant = new stdClass();
                   $variant->name = 'null';
               }   
               $subtotal = $harga*$qty;
               $data_ = ['item_id'=>$id_p,'item_name'=>$dataproduct->name,'item_code'=>$dataproduct->item_code,'selling_price'=>$harga,
               'note'=>$note_,'sub_total'=>$subtotal,'transaction_id'=>$trxId,'variant_id'=>$variant_id,'variant_name'=>$variant->name,'qty'=>$qty];
               cart::create($data_);
               array_push($total,$subtotal);
           }
           $total = array_sum($total);
           $data_ = ['total_payment'=>$total];
           cart_ref::findOrFail($trxId)->update($data_);
           $data["success"] = true;
           $data["code"] = 202;
           $data["message"] = "berhasil";
           $data["data"] = ["cart_header_id"=>$trxId];
       
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
     * @param  \App\Models\alamat  $alamat
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            
            $header = cart_ref::where('user_id',$id)->get();
            $data_cart = [];
            foreach ($header as $key => $value) {
                $cart = cart::where('transaction_id',$value->id)->get();
                array_push($data_cart,$cart);
            }
            $result = [];
            $i =0;
            foreach ($header as $key => $value) {
                $data_ = ['cart_header'=>$value,'cart_item'=>$data_cart[$i]];
                array_push($result,$data_);
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
        $request = json_decode($request->payload,true);
        $dataTable = [];
        function checkifexist($column,$request_name,$request,$dataTable){
            if( array_key_exists($request_name,$request)){
               $databaru = addData($column,$request_name,$request,$dataTable);
               return $databaru;
            }
            else{
                return $dataTable;
            }
        }
        function addData($column,$request_name,$request,$dataTable){
            $dataTable[$column] = $request[$request_name];
            return $dataTable;
        }
        try {
           $product = $request['product'];
           $note = $request['note'];
           $trxId = $id;
    
           $total = [];
           cart::where('transaction_id',$trxId)->delete();
           foreach ($product as $key => $value) {
               $id_p = $value['id'];
               $qty = $value['qty'];
               $note_ = $value['note'];
               $variant_id = $value['variant_id'];
               $dataproduct = item::findOrFail($id_p);
               if (variant::where('item_id',$id_p)->count() > 0 && $variant_id>0) {
                   # code...
                   $variant = variant::findOrFail($variant_id);
                   $harga = $variant->harga;
               } else {
                   $harga = $dataproduct->selling_price;
                   $variant = new stdClass();
                   $variant->name = 'null';
               }   
               $subtotal = $harga*$qty;
               $data_ = ['item_id'=>$id_p,'item_name'=>$dataproduct->name,'item_code'=>$dataproduct->item_code,'selling_price'=>$harga,
               'note'=>$note_,'sub_total'=>$subtotal,'transaction_id'=>$trxId,'variant_id'=>$variant_id,'variant_name'=>$variant->name,'qty'=>$qty];
               cart::create($data_);
               array_push($total,$subtotal);
           }
           $total = array_sum($total);
           $data_ = ['total_payment'=>$total,'note'=>$note];
           cart_ref::findOrFail($trxId)->update($data_);
           $data["success"] = true;
           $data["code"] = 202;
           $data["message"] = "berhasil update data";
           $data["data"] = ["cart_header_id"=>$trxId];
       
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
            $data["data"] = $result.' data';
        
        } catch (\Throwable $th) {
            $data["data"] = [];
            $data["success"] = false;
            $data["code"] = 500;
            $data["message"] = $th->getMessage();
        }
        return $data;
    }
}
