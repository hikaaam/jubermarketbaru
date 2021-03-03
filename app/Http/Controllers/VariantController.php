<?php

namespace App\Http\Controllers;

use App\Models\Variant;
use Illuminate\Http\Request;



class VariantController extends Controller
{
    public $data = [
        "success"=>"true",
        "message"=>"Berhasil",
        "code"=>200,
        "data"=>[]
    ];
    /**
     * 
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
        $request = json_decode($request->payload);     
     try {
        $nama = $request->variant_name;
        $id = $request->item_id;
        $harga = $request->harga;
        $picture = $request->picture;
        $stock = $request->stock;
        $store = [
            "name"=>$nama,
            "item_id"=>$id,
            "harga"=>$harga,
            "picture"=>$picture,
            "stock"=>$stock
        ];
            Variant::create($store);
            $data["data"] = $store;
            $data["success"] = "true";
            $data["code"] = 201;
            $data["message"] = "Variant ".$nama." berhasil ditambahkan"; 
        } catch (\Throwable $th) {
            $data["data"] = $store;
            $data["success"] = "false";
            $data["code"] = 500;
            $data["message"] = "Error:".$th->getMessage(); 
        } 
     return $data;  
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Variant  $variant
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $show = Variant::where("item_id",$id)->get();
            $data["data"] = $show;
            $data["success"] = "true";
            $data["code"] = 200;
            $data["message"] = "Berhasil"; 
        } catch (\Throwable $th) {
            $data["data"] = [];
            $data["success"] = "false";
            $data["code"] = 500;
            $data["message"] = "Error:".$th->getMessage(); 
        } 
        return $data; 
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Variant  $variant
     * @return \Illuminate\Http\Response
     */
    public function edit(Variant $variant)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Variant  $variant
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        $request = json_decode($request->payload);
        try {
            $nama = $request->variant_name;
            $harga = $request->harga;
            $stock = $request->stock;
            $picture = $request->picture;
            $store = [
                "name"=>$nama,
                "harga"=>$harga,
                "picture"=>$picture,
                "stock"=>$stock
            ];
            Variant::find($id)->update($store);
            $data["data"] = $store;
            $data["success"] = "true";
            $data["code"] = 202;
            $data["message"] = "Variant berhasil diupdate"; 
        } catch (\Throwable $th) {
            $data["data"] = $store;
            $data["success"] = "false";
            $data["code"] = 500;
            $data["message"] = "Error:".$th->getMessage(); 
        } 
        return $data;  
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Variant  $variant
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            Variant::find($id)->delete();
            $data["data"] = [];
            $data["success"] = "true";
            $data["code"] = 202;
            $data["message"] = "Variant berhasil dihapus"; 
        } catch (\Throwable $th) {
            $data["data"] = [];
            $data["success"] = "false";
            $data["code"] = 500;
            $data["message"] = "Error:".$th->getMessage(); 
        } 
        return $data;  
    }
    public function showId($id){
        try {
            $show = Variant::find($id);
            $data["data"] = $show;
            $data["success"] = "true";
            $data["code"] = 200;
            $data["message"] = "Berhasil"; 
        } catch (\Throwable $th) {
            $data["data"] = [];
            $data["success"] = "false";
            $data["code"] = 500;
            $data["message"] = "Error:".$th->getMessage(); 
        } 
        return $data; 
    }
}
