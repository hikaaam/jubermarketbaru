<?php

namespace App\Http\Controllers;

use App\Models\profile;
use Illuminate\Http\Request;

class ProfileController extends Controller
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
        try {
            $result = profile::all();
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
           $dataTable = addData("idrs","idrs",$request,$dataTable);
           $dataTable = addData("gender","gender",$request,$dataTable);
           $dataTable = checkifexist("profile_picture","profile_picture",$request,$dataTable);
           $dataTable = checkifexist("cover_picture","cover_picture",$request,$dataTable);
           $dataTable = checkifexist("social_media","social_media",$request,$dataTable);
           
           $items = profile::create($dataTable);
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
     * @param  \App\Models\alamat  $alamat
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $result = profile::where('idrs',$id)->get();
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
            $dataTable = checkifexist("gender","gender",$request,$dataTable);
            $dataTable = checkifexist("profile_picture","profile_picture",$request,$dataTable);
            $dataTable = checkifexist("cover_picture","cover_picture",$request,$dataTable);
            $dataTable = checkifexist("social_media","social_media",$request,$dataTable);

           $items = profile::findOrFail($id)->update($dataTable);
           $data["success"] = true;
           $data["code"] = 202;
           $data["message"] = "berhasil";
           $data["data"] = ["request_data"=>$dataTable];
       
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
            $result = profile::findOrFail($id)->delete();
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
}
