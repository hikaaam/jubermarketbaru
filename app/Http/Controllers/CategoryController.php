<?php

namespace App\Http\Controllers;

use App\Models\category;
use App\Models\ref_cat;
use App\Models\catTokpedChild as child;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public $data = [
        "success" => "true",
        "message" => "Berhasil",
        "code" => 200,
        "data" => []
    ];
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            // DB::enableQueryLog();
            $result = ref_cat::with("child.child")->get();
            // dd(DB::getQueryLog());
            $data["success"] = true;
            $data["code"] = 202;
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
            throw new Error("this api is disabled");
            $dataTable = addData("name", "name", $request, $dataTable);
            $dataTable = addData("store_id", "store_id", $request, $dataTable);
            $dataTable = addData("created_by", "created_by", $request, $dataTable);
            $dataTable = addData("ref_category", "ref_category", $request, $dataTable);

            $dataTable = checkifexist("created_by_id", "created_by_id", $request, $dataTable);
            $dataTable = checkifexist("description", "description", $request, $dataTable);
            $dataTable = checkifexist("parent_id", "parent_id", $request, $dataTable);

            category::create($dataTable);

            $data["success"] = true;
            $data["code"] = 202;
            $data["message"] = "berhasil tambah data";
            $data["data"] = ["request_data" => $dataTable];
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
     * @param  \App\Models\category  $category
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $result = ref_cat::where("id", $id)->with("child.child")->get();

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
     * @param  \App\Models\category  $category
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
     * @param  \App\Models\category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        throw new Error("this api is disabled");
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
            $dataTable = addData("name", "name", $request, $dataTable);
            $dataTable = addData("store_id", "store_id", $request, $dataTable);
            $dataTable = addData("created_by", "created_by", $request, $dataTable);
            $dataTable = addData("ref_category", "ref_category", $request, $dataTable);

            $dataTable = checkifexist("created_by_id", "created_by_id", $request, $dataTable);
            $dataTable = checkifexist("description", "description", $request, $dataTable);
            $dataTable = checkifexist("parent_id", "parent_id", $request, $dataTable);

            category::find($id)->update($dataTable);

            $data["success"] = true;
            $data["code"] = 202;
            $data["message"] = "berhasil update data";
            $data["data"] = ["request_data" => $dataTable];
        } catch (\Throwable $th) {
            $data["data"] = [];
            $data["success"] = false;
            $data["code"] = 500;
            $data["message"] = $th->getMessage();
        }
        return $data;
    }

    public function Getbystoreid($id)
    {
        try {
            throw new Error("this api is disabled");
            $result = category::first();
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\category  $category
     * @return \Illuminate\Http\Response
     */

    public function destroy($id)
    {
        try {
            throw new Error("this api is disabled");
            category::find($id)->delete();

            $data["success"] = true;
            $data["code"] = 202;
            $data["message"] = "berhasil hapus data";
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
