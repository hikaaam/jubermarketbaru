<?php

namespace App\Http\Controllers;

use App\Jobs\juberFoodSync;
use App\Models\item;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

class globalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            return helper::getToken();
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public static function apiLocalTest()
    {
        try {
            // return helper::juberCoreSyncStatusTrx("JBM0500005", 5);
            // juberFoodSync::dispatch(["id" => "test"]);
            Artisan::call("inspire");
            // return "running the job";
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public static function syncJuberFood()
    {
        try {
            $items = item::where("service", "jbfood")->get();
            foreach ($items as $key => $data) {
                if ($data["weight_unit"] == "GR") {
                    $data["weight"] = intval($data["weight"]) / 1000;
                }
                $harga = intval($data["selling_price"]);
                $image = $data["picture"];
                $payload = "{\"kdprodukgoota\":\"{$data['id']}\",\"nmproduk\":\"{$data['name']}\",\"singkatan\":\"{$data['sku']}\",\"isstokkosong\":\"0\"," .
                    "\"jamstart\":\"09:00\",\"jamend\":\"16:30\",\"keterangan\":\"{$data['description']}\"," .
                    "\"imgurl\":\"{$image}\",\"berat\":\"{$data['weight']}\",\"harga\":{$harga}," .
                    "\"hargapromo\":{$harga},\"kdMercant\":\"{$data['store_id']}\",\"kategori\":\"{$data['category_id']}\",\"type\":\"{$data['service']}\"}";
                $url = "http://192.168.2.45:9888/jbmiddleware";
                $key = "createproduk";
                $body = ["key" => $key, "payload" => $payload];
                $response =  http::withHeaders(helper::getJuberHeaders())->post($url, $body);
                if ($response["code"] == 200) {
                    $lobj = $response["lobj"][0];
                    $id = $lobj['idproduk'];
                    item::findOrFail($data["id"])->update(["juber_id" => $id]);
                    return response(["success" => true, "message" => "sync success"], 200);
                } else {
                    throw new Error($response->msg);
                }
            }
        } catch (\Throwable $th) {
            return response(["success" => false, "message" => $th->getMessage()], 500);
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
        //
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
