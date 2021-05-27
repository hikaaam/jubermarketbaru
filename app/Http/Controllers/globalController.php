<?php

namespace App\Http\Controllers;

use App\Jobs\juberFoodSync;
use App\Models\item;
use Error;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Response;

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
            // Artisan::call("inspire");
            // return Artisan::output();
            return gettype(true);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public static function syncJuberFood()
    {
        try {
            $items = item::where("service", "jbfood")->where('sync_status', '!=', 1)->get();
            // return $items;
            foreach ($items as $key => $data) {
                if ($data["weight_unit"] == "GR") {
                    $data["weight"] = intval($data["weight"]) / 1000;
                }
                $data["weight"] = $data["weight"] ?? 1;
                $harga = intval($data["selling_price"]);
                $image = $data["picture"];
                $data['sku'] = $data['sku'] ?? self::getSKU($data["name"], $data["store_id"], $data["id"]);
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
                    if ($data["sync_status"] === null) {
                        $id = $lobj['idproduk'];
                        item::findOrFail($data["id"])->update(["juber_id" => $id, "sync_status" => 1]);
                    } else {
                        item::findOrFail($data["id"])->update(["sync_status" => 1]);
                    }
                    // return ["success" => true, "message" => "sync success"];
                } else {
                    // throw new Error($response->msg);
                }
            }
        } catch (\Throwable $th) {
            return ["success" => false, "message" => $th->getMessage()];
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

    private static function getSKU(string $name, $store_id, $id)
    {
        $words = explode(" ", $name);
        $acronym = "";
        foreach ($words as $w) {
            $acronym .= $w[0];
        }
        return "{$store_id}F{$id}_{$acronym}";
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
