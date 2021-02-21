<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\item;
use App\Models\Variant;

class ProductController extends Controller
{
    public $data = [
        "success"=>"true",
        "message"=>"Berhasil",
        "code"=>200,
        "data"=>[]
    ];
  
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $result = item::paginate(6);
            $data["success"] = true;
            $data["code"] = 200;
            $data["message"] = "berhasil";
            $data["data"] = $result->setPath(\config('app.url').":8001/api/product");
        
        } catch (\Throwable $th) {
            $data["data"] = [];
            $data["success"] = false;
            $data["code"] = 500;
            $data["message"] = $th->getMessage();
        }
        return $data;
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
           $dataTable = addData("item_type","item_type",$request,$dataTable);
           $dataTable = addData("minimal_stock","minimal_stock",$request,$dataTable);
           $dataTable = addData("category_id","category_id",$request,$dataTable);
           $dataTable = addData("store_id","store_id",$request,$dataTable);
           $dataTable = addData("selling_price","selling_price",$request,$dataTable);
           $dataTable = addData("name","name",$request,$dataTable);
           $dataTable = addData("created_by_id","created_by_id",$request,$dataTable);
           $dataTable = addData("created_by","created_by",$request,$dataTable);
           $dataTable = addData("last_updated_by_id","created_by_id",$request,$dataTable);
           $dataTable = checkifexist("sku","sku",$request,$dataTable);
           $dataTable = checkifexist("description","description",$request,$dataTable);
           $dataTable = checkifexist("item_code","item_code",$request,$dataTable);
           $dataTable = checkifexist("stockable","stockable",$request,$dataTable);
           $dataTable = checkifexist("picture","picture",$request,$dataTable);
           $dataTable = checkifexist("picture_two","picture_two",$request,$dataTable);
           $dataTable = checkifexist("picture_three","picture_three",$request,$dataTable);
           $dataTable = checkifexist("picture_four","picture_four",$request,$dataTable);
           $dataTable = checkifexist("picture_five","picture_five",$request,$dataTable);
           $dataTable = checkifexist("video","video",$request,$dataTable);
           $dataTable = checkifexist("type_of_item","type_of_item",$request,$dataTable);
           $dataTable = checkifexist("item_unit_id","item_unit_id",$request,$dataTable);
           $dataTable = checkifexist("si_active","is_active",$request,$dataTable);
           $dataTable = checkifexist("basic_price","basic_price",$request,$dataTable);
           $dataTable = checkifexist("cost_of_good_sold","cost_of_good_sold",$request,$dataTable);
           $dataTable = checkifexist("item_tax_type","item_tax_type",$request,$dataTable);
           $dataTable = checkifexist("weight","weight",$request,$dataTable);
           $dataTable = checkifexist("condition","condition",$request,$dataTable);
           $dataTable = checkifexist("pre_order","pre_order",$request,$dataTable);
           $dataTable = checkifexist("pre_order_estimation","pre_order_estimation",$request,$dataTable);
           $dataTable = checkifexist("dimension_length","dimension_length",$request,$dataTable);
           $dataTable = checkifexist("dimension_width","dimension_width",$request,$dataTable);
           $dataTable = checkifexist("dimension_height","dimension_height",$request,$dataTable);
           $dataTable = checkifexist("is_shown","is_shown",$request,$dataTable);
           $dataTable = checkifexist("ownership","ownership",$request,$dataTable);
           item::create($dataTable);
            $items = item::orderBy('id','desc')->limit(1)->get();
            $items = $items[0];
            $id = $items->id;
            if(count($request["variant"])>0){
                foreach ($request["variant"] as $key => $value) {
                   $variant = ["name"=>$value['variant_name'],"harga"=>$value['harga'],"item_id"=>$id];
                   Variant::create($variant);
                }
            }
            
            $data["success"] = true;
            $data["code"] = 202;
            $data["message"] = "berhasil";
            $data["data"] = ["request_data"=>$items];
        
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
        try {
            $result = item::find($id);
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        
    }
    public function all(){
        try {
            $result = item::all();
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
    public function productbycat(Request $request,$id){
        try {
            $result = item::where('category_id',$id)->get();
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
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
           $dataTable = addData("item_type","item_type",$request,$dataTable);
           $dataTable = addData("minimal_stock","minimal_stock",$request,$dataTable);
           $dataTable = addData("category_id","category_id",$request,$dataTable);
           $dataTable = addData("store_id","store_id",$request,$dataTable);
           $dataTable = addData("selling_price","selling_price",$request,$dataTable);
           $dataTable = addData("name","name",$request,$dataTable);
           $dataTable = addData("created_by_id","created_by_id",$request,$dataTable);
           $dataTable = addData("created_by","created_by",$request,$dataTable);
           $dataTable = addData("last_updated_by_id","created_by_id",$request,$dataTable);
           $dataTable = checkifexist("sku","sku",$request,$dataTable);
           $dataTable = checkifexist("description","description",$request,$dataTable);
           $dataTable = checkifexist("item_code","item_code",$request,$dataTable);
           $dataTable = checkifexist("stockable","stockable",$request,$dataTable);
           $dataTable = checkifexist("picture","picture",$request,$dataTable);
           $dataTable = checkifexist("picture_two","picture_two",$request,$dataTable);
           $dataTable = checkifexist("picture_three","picture_three",$request,$dataTable);
           $dataTable = checkifexist("picture_four","picture_four",$request,$dataTable);
           $dataTable = checkifexist("picture_five","picture_five",$request,$dataTable);
           $dataTable = checkifexist("video","video",$request,$dataTable);
           $dataTable = checkifexist("type_of_item","type_of_item",$request,$dataTable);
           $dataTable = checkifexist("item_unit_id","item_unit_id",$request,$dataTable);
           $dataTable = checkifexist("si_active","is_active",$request,$dataTable);
           $dataTable = checkifexist("basic_price","basic_price",$request,$dataTable);
           $dataTable = checkifexist("cost_of_good_sold","cost_of_good_sold",$request,$dataTable);
           $dataTable = checkifexist("item_tax_type","item_tax_type",$request,$dataTable);
           $dataTable = checkifexist("weight","weight",$request,$dataTable);
           $dataTable = checkifexist("condition","condition",$request,$dataTable);
           $dataTable = checkifexist("pre_order","pre_order",$request,$dataTable);
           $dataTable = checkifexist("pre_order_estimation","pre_order_estimation",$request,$dataTable);
           $dataTable = checkifexist("dimension_length","dimension_length",$request,$dataTable);
           $dataTable = checkifexist("dimension_width","dimension_width",$request,$dataTable);
           $dataTable = checkifexist("dimension_height","dimension_height",$request,$dataTable);
           $dataTable = checkifexist("is_shown","is_shown",$request,$dataTable);
           $dataTable = checkifexist("ownership","ownership",$request,$dataTable);
            item::findOrFail($id)->update($dataTable);
            Variant::where('item_id',$id)->delete();
            if(count($request["variant"])>0){
                foreach ($request["variant"] as $key => $value) {
                   $variant = ["name"=>$value['variant_name'],"harga"=>$value['harga'],"item_id"=>$id];
                   Variant::create($variant);
                }
            }
            
            $data["success"] = true;
            $data["code"] = 202;
            $data["message"] = "berhasil";
            $data["data"] = ["request_data"=>$request];
        
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $dataTable = [];
        function checkifexist($column,$request_name,$request,$dataTable){
            if($request->has($request_name)){
               $databaru = addData($column,$request_name,$dataTable);
               return $databaru;
            }
        }
        function addData($column,$request_name,$request,$dataTable){
            $dataTable[$column] = $request[$request_name];
            return $dataTable;
        }
      
        try {
            
            item::find($id)->delete();
            Variant::where('item_id',$id)->delete();
            $data["success"] = true;
            $data["code"] = 202;
            $data["message"] = "berhasil di hapus";
            $data["data"] = [];
        
        } catch (\Throwable $th) {
            $data["data"] = [];
            $data["success"] = false;
            $data["code"] = 500;
            $data["message"] = $th->getMessage();
        }
        return $data;
    }
}
