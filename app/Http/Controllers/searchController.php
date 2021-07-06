<?php

namespace App\Http\Controllers;

use App\Models\item;
use App\Models\store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class searchController extends Controller
{
    /**
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
        try {
            $request = json_decode($request->payload, true);
            helper::validateArray($request, ["search"]);
            $src = $request["search"];

            if (strlen($src) < 3) {
                $data = array(
                    "features" => [],
                    "products" => [],
                    "stores" => [],
                );
                return helper::resp(true, "get", "pastikan search minimal 3 karakter", $data);
            }


            // $jsonFile = Storage::get('public/features.json');
            // $jsonFeature = json_decode($jsonFile, true);
            // // return $jsonFeature;
            // $filteredJson = [];
            // foreach ($jsonFeature as $key => $value) {
            //     if (str_contains(strtolower($value["nama"]), strtolower($src))) {
            //         $newArray = array(
            //             "nama" => $value["nama"],
            //             "nexpage" => $value["nextpage"],
            //             "submenu" => $value["submenu"],
            //             "json" => $value["json"]
            //         );
            //         array_push($filteredJson, $newArray);
            //     }
            // }


            $product = item::select("id", "name", "service", "picture")->where('service', '!=', 'etc')->where("name", 'ilike', "%{$src}%")->where("is_shown", 1)->get();
            $store = store::select("id", "store_name", "idrs", "service", "picture", "city")->where("store_name", 'ilike', "%{$src}%")->whereNotNull('idrs')->get();
            $data = array(
                "products" => $product,
                "stores" => $store,
            );
            return helper::resp(true, "get", "search result", $data);
        } catch (\Throwable $th) {
            return helper::resp(false, "get", $th->getMessage(), array(
                "features" => [],
                "products" => [],
                "stores" => [],
            ));
        }
    }

    public function searchProductStore(Request $request)
    {
        try {
            $request = json_decode($request->payload, true);
            helper::validateArray($request, ["search", "store_id"]);
            $src = $request["search"];
            $store = $request["store_id"];
            if (strlen($src) <= 2) {
                $data = item::where("store_id", $store)->where("name", "ilike", "%$src%")->where('service', 'jbmarket')->where('is_shown', 1)->limit(10)->get();
                return helper::resp(true, "get", "berhasil mencari data", $data);
            }
            $data = item::where("store_id", $store)->where("name", "ilike", "%$src%")->where('service', 'jbmarket')->where('is_shown', 1)->get();
            return helper::resp(true, "get", "berhasil mencari data", $data);
        } catch (\Throwable $th) {
            return helper::resp(false, "get", $th->getMessage(), []);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
