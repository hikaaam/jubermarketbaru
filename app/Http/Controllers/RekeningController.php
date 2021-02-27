<?php

namespace App\Http\Controllers;

use App\Models\rekening;
use App\Models\ref_rekening;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RekeningController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $result = rekening::all();
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
                if($column == "status"){
                    $dataTable[$column] = 1;
                }
                return $dataTable;
            }
        }
        function addData($column,$request_name,$request,$dataTable){
            $dataTable[$column] = $request[$request_name];
            return $dataTable;
        }
        $dataTable = addData("name","name",$request,$dataTable);
        $dataTable = addData("no_rek","no_rek",$request,$dataTable);
        $dataTable = addData("idrs","idrs",$request,$dataTable);
        $dataTable = addData("ref_bank","ref_bank",$request,$dataTable);
        $dataTable = checkifexist("branch_name","branch_name",$request,$dataTable);
        $dataTable = checkifexist("city","city",$request,$dataTable);
        try {
           
            $result = rekening::create($dataTable);
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
     * Display the specified resource.
     *
     * @param  \App\Models\rekening  $rekening
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
           
            $result = DB::table('rekening')
            ->join('ref_rekening', 'ref_rekening.id', '=', 'rekening.ref_bank')
            ->select('rekening.*', 'ref_rekening.name', 'ref_rekening.detail_name')
            ->where('rekening.id','=',$id)->get();
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

    public function getByIdrs($id)
    {
        try {
           
            $result = DB::table('rekening')
            ->join('ref_rekening', 'ref_rekening.id', '=', 'rekening.ref_bank')
            ->select('rekening.*', 'ref_rekening.name', 'ref_rekening.detail_name')
            ->where('rekening.idrs','=',$id)->get();
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
     * @param  \App\Models\rekening  $rekening
     * @return \Illuminate\Http\Response
     */
    public function edit(rekening $rekening)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\rekening  $rekening
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
                if($column == "status"){
                    $dataTable[$column] = 1;
                }
                return $dataTable;
            }
        }
        function addData($column,$request_name,$request,$dataTable){
            $dataTable[$column] = $request[$request_name];
            return $dataTable;
        }
        $dataTable = checkifexist("name","name",$request,$dataTable);
        $dataTable = checkifexist("no_rek","no_rek",$request,$dataTable);
        $dataTable = checkifexist("ref_bank","ref_bank",$request,$dataTable);
        $dataTable = checkifexist("branch_name","branch_name",$request,$dataTable);
        $dataTable = checkifexist("city","city",$request,$dataTable);
        try {
           
            rekening::findOrFail($id)->up($dataTable);
            $data["success"] = true;
            $data["code"] = 200;
            $data["message"] = "berhasil";
            $data["data"] = $dataTable;
        
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
     * @param  \App\Models\rekening  $rekening
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
           
            rekening::findOrFail($id)->delete();
            $data["success"] = true;
            $data["code"] = 200;
            $data["message"] = "berhasil";
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
