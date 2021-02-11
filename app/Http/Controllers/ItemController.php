<?php

namespace App\Http\Controllers;

use App\Models\item;
use App\Models\Transaction;
use Illuminate\Http\Request;

class ItemController extends Controller
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
            $result = item::selectRaw("item.*,(select SUM(quantity) as item_selled from transaction_detail where
            transaction_detail.item_id = item.id group by transaction_detail.item_id) as item_selled")
            ->where("name","ilike","%kebab%")->orderByRaw("item_selled DESC NULLS LAST")->paginate(6);
            $data["success"] = true;
            $data["code"] = 200;
            $data["message"] = "berhasil";
            $data["data"] = $result->setPath(\config('app.url').":8001/api/search");;
        
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
        try {
            $search = $request->search;
            $result = item::where("name","ILIKE","%".$search."%")->paginate(6);
            $data["data"] = $result->setPath(\config('app.url').":8001/api/search");
            $data["success"] = true;
            $data["code"] = 200;
            $data["message"] = "berhasil";

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
     * @param  \App\Models\item  $item
     * @return \Illuminate\Http\Response
     */
    public function show(item $item)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\item  $item
     * @return \Illuminate\Http\Response
     */
    public function edit(item $item)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\item  $item
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, item $item)
    {
        //
    }

    public function terlaris(Request $request, item $item){
        try {
            $request = json_decode($request->payload);
            $search = $request->search;
            $orderBy = "DESC NULLS LAST";
            if(isset($request->descending)){
                $descending = strtolower($request->descending);
                if($descending == "true"){
                    $orderBy = "DESC NULLS LAST";
                }else{
                    $orderBy = "ASC NULLS LAST";
                }
            }
            $result = item::selectRaw("item.*,(select SUM(quantity) as item_selled from transaction_detail where
            transaction_detail.item_id = item.id group by transaction_detail.item_id) as item_selled")
            ->where("name","ilike","%".$search."%")->orderByRaw("item_selled ".$orderBy)->paginate(6);
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
    public function terbaru(Request $request, item $item){
        $request = json_decode($request->payload);
        $search = $request->search;
        $orderBy = "DESC NULLS LAST";
        if(isset($request->descending)){
            $descending = strtolower($request->descending);
            if($descending == "true"){
                $orderBy = "DESC NULLS LAST";
            }else{
                $orderBy = "ASC NULLS LAST";
            }
        }
        try {
            $result = item::where("name","ILIKE","%".$search."%")->orderByRaw("created ".$orderBy)->paginate(6);
            $data["data"] = $result->setPath(\config('app.url').":8001/api/search");;
            $data["success"] = true;
            $data["code"] = 200;
            $data["message"] = "berhasil";

        } catch (\Throwable $th) {
            $data["data"] = [];
            $data["success"] = false;
            $data["code"] = 500;
            $data["message"] = $th->getMessage();
        }
        return $data;
    }   

    public function harga(Request $request, item $item){
        $request = json_decode($request->payload);
        $search = $request->search;
        $orderBy = "ASC NULLS LAST";
        if(isset($request->descending)){
            $descending = strtolower($request->descending);
            if($descending == "true"){
                $orderBy = "DESC NULLS LAST";
            }else{
                $orderBy = "ASC NULLS LAST";
            }
        }
        try {
            $result = item::where("name","ILIKE","%".$search."%")->orderByRaw("selling_price ".$orderBy)->paginate(6);
            $data["data"] = $result->setPath(\config('app.url').":8001/api/search");;
            $data["success"] = true;
            $data["code"] = 200;
            $data["message"] = "berhasil";

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
     * @param  \App\Models\item  $item
     * @return \Illuminate\Http\Response
     */
    public function destroy(item $item)
    {
        //
    }
}
