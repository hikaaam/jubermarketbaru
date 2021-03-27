<?php

namespace App\Http\Controllers;

use App\Models\return_problem;
use Illuminate\Http\Request;

class ReturnProblemController extends Controller
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
        try {
            $result = return_problem::all();
            return getRespond(true, "berhasil fetching data", $result);
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
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
        try {
            $dataTable = addData("name", "name", $request, $dataTable);
            $items = return_problem::create($dataTable);
            return getRespond(true, "berhasil", $items);
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
        // return $data;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\return_problem  $return_problem
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $result = return_problem::findORFail($id);
            return getRespond(true, "berhasil", $result);
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
        // return $data;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\return_problem  $return_problem
     * @return \Illuminate\Http\Response
     */
    public function edit(return_problem $return_problem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\return_problem  $return_problem
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request = json_decode($request->payload, true);
        $dataTable = [];
        try {
            $dataTable = addData("name", "name", $request, $dataTable);
            return_problem::findOrFail($id)->update($dataTable);
            return getRespond(true, "berhasil", ["updatedField" => "1"]);
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
        // return $data;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\return_problem  $return_problem
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            return_problem::findOrFail($id)->delete();
            return getRespond(true, "berhasil", ["deletedField" => "1"]);
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
        // return $data;
    }
}
function addData($column, $request_name, $request, $dataTable)
{
    $dataTable[$column] = $request[$request_name];
    return $dataTable;
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
