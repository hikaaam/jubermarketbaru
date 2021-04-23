<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\ScheduleController as schedule;
use App\Models\item;
use App\Models\tokopedia_token;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class helper extends Controller
{
    public static function getAuth($token)
    {
        return [
            'Authorization' => "Bearer {$token}",
            'Content-Type' => 'application/json'
        ];
    }

    public static function getToken()
    {
        $app_id = 14523;
        $now = Carbon::now()->timestamp;
        $partner = tokopedia_token::find(1);
        $token = $partner->access_token;
        $updated_at = $partner->updated_at;
        $updated_at_second  = Carbon::parse($updated_at)->timestamp;
        if ($updated_at == null) {
            $is_expired = true;
        } else {
            $is_expired = $updated_at_second <= $now;
        }
        if ($is_expired) {
            $response =  Http::withHeaders([
                'Authorization' => 'Basic YzY0MDYyNjNmYmY1NDMxZWE3OTNiOWFkYzUxNTg3NDk6ZTcyOTk4YWRlMDYwNDNkYjk4ZTllYmJjOTBlOWM1NmM=',
                'Content-Length' => '0',
                'User-Agent' => 'PostmanRuntime/7.17.1'
            ])->post('https://accounts.tokopedia.com/token?grant_type=client_credentials');
            $res_data = $response->json();
            // return $res_data;
            $token = $res_data["access_token"];
            $expired_at = $res_data["expires_in"];
            $last_login_type = $res_data["last_login_type"];
            $refresh = true;
            $updated_at = Carbon::now()->toDateTimeString();
            tokopedia_token::find(1)->update(
                [
                    "access_token" => $token,
                    "expires_in" => $expired_at,
                    "updated_at" => $updated_at,
                    "last_login_type" => $last_login_type
                ]
            );
        } else {
            $refresh = false;
        }
        return ["token" => $token, "fs_id" => $app_id, "refresh" => $refresh, "last_updated_at" => $updated_at];
    }

    public static function isPicture($pict)
    {
        return $pict !== "null" && $pict !== "" && $pict !== null;
    }

    public static function shopid()
    {
        return 10408203;
    }

    public static function tokopediaUpload($dataTable, $id, $withVariant, $variant)
    {
        Log::info("Trying to upload to tokopedia");
        $shopid = self::shopid();
        $tokopedia_data = self::getToken();
        $token = $tokopedia_data["token"];
        $fs_id = $tokopedia_data["fs_id"];
        $pictures = [];
        if (self::isPicture($dataTable["picture"])) {
            array_push($pictures, ["file_path" => self::imageTokopediaFormat($dataTable["picture"])]);
        }
        if (self::isPicture($dataTable["picture_two"])) {
            array_push($pictures, ["file_path" => self::imageTokopediaFormat($dataTable["picture_two"])]);
        }
        if (self::isPicture($dataTable["picture_three"])) {
            array_push($pictures, ["file_path" => self::imageTokopediaFormat($dataTable["picture_three"])]);
        }
        if (self::isPicture($dataTable["picture_four"])) {
            array_push($pictures, ["file_path" => self::imageTokopediaFormat($dataTable["picture_four"])]);
        }
        if (self::isPicture($dataTable["picture_five"])) {
            array_push($pictures, ["file_path" => self::imageTokopediaFormat($dataTable["picture_five"])]);
        }
        $products = [];
        $products["name"] = $dataTable["name"];
        $products["condition"] = ($dataTable["condition"] == 1) ? "NEW" : "USED";
        $products["description"] = $dataTable["description"];
        $products["price"] = intval($dataTable["selling_price"]);
        $products["status"] = "LIMITED";
        $products["price_currency"] = "IDR";
        $products["weight"] = intval($dataTable["weight"]);
        $products["weight_unit"] = $dataTable["weight_unit"];
        $products["category_id"] = intval($dataTable["category_id"]);
        $products["sku"] = $dataTable["sku"];
        $products["is_free_return"] = false;
        $products["is_must_insurance"] = false;
        $products["stock"] = intval($dataTable["minimal_stock"]);
        $products["min_order"] = 1;
        $products["pictures"] = $pictures;
        $url = 'https://fs.tokopedia.net/v2/products/fs/' . $fs_id . '/create?shop_id=' . $shopid;
        $variants = [];
        $selections = [];
        if ($withVariant && $variant !== null && false) { //disabled this function on purpose by using && false (Reason infrastructure not supported)
            foreach ($variant as $key => $value) {
                $pv = []; //product variants
                $so = []; //selection options
                $pv["is_primary"] = $key == 0;
                $pv["status"] = "LIMITED";
                $pv["price"] = $value['harga'];
                $pv["stock"] = $value['stock'];
                $pv["sku"] = $dataTable["sku"];
                $pv["combination"] = [$key];
                $pv["pictures"] = self::imageTokopediaFormat($value['picture']);
                array_push($variants, $pv);
                //not done yet (Reason infrastructure not supported)
            }
            $products = ["products" => [$products], "variants" => ["products" => $variants]];
        } else {
            $products = ["products" => [$products]];
        }
        $response =  http::withHeaders(self::getAuth($token))->post($url, $products);
        $response = $response->json();
        try {
            $uploadId = $response["data"]["upload_id"];
            $response = http::withHeaders(self::getAuth($token))->get("https://fs.tokopedia.net/v2/products/fs/{$fs_id}/status/{$uploadId}?shop_id={$shopid}");
            $resdata = $response->json();
            $resdata = $resdata["data"];
            if ($resdata["success_rows"] >= 1) {
                $productid = $resdata["success_rows_data"][0]["product_id"];
                item::findOrFail($id)->update(["tokopedia_id" => $productid, "tokopedia_is_upload" => 1]);
                // return Response($response, 200);
                Log::info("data with id {$id} is succesfully updated to tokopedia with product id of {$productid}");
            }
            if ($resdata["unprocessed_rows"] >= 1) {
                item::findOrFail($id)->update(["tokopedia_upload_id" => $uploadId, "tokopedia_is_upload" => 0]);
                // return Response($response, 200);
                return self::tokopediaUploadCheck($uploadId, $token, $fs_id, $id);
            }
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public static function tokopediaUploadCheck($uploadId, $token, $fs_id, $id)
    {
        Log::info("Looping this function until get product id from tokopedia");
        $shopid = self::shopid();
        sleep(1);
        try {
            $response = http::withHeaders(self::getAuth($token))->get("https://fs.tokopedia.net/v2/products/fs/{$fs_id}/status/{$uploadId}?shop_id={$shopid}");
            $resdata = $response->json();
            $resdata = $resdata["data"];
            if ($resdata["success_rows"] >= 1) {
                $productid = $resdata["success_rows_data"][0]["product_id"];
                item::findOrFail($id)->update(["tokopedia_id" => $productid, "tokopedia_is_upload" => 1]);
                // return Response($response, 200);
                Log::info("data with id {$id} is succesfully updated to tokopedia with product id of {$productid}");
            }
            if ($resdata["unprocessed_rows"] >= 1) {
                item::findOrFail($id)->update(["tokopedia_upload_id" => $uploadId, "tokopedia_is_upload" => 0]);
                // return Response($response, 200);
                self::tokopediaUploadCheck($uploadId, $token, $fs_id, $id);
            }
        } catch (\Throwable $th) {
            //throw $th;
            Log::info("data with id {$id} is failed to upload tokopedia");
            return $th->getMessage();
        }
    }

    public static function imageTokopediaFormat($img)
    {
        if (self::strStartWith($img, "/")) {
            if (self::strContains(config('app.url'), 800)) {
                return config('app.url') . "{$img}";
            }
            return config('app.url') . ":/8001/{$img}";
        }
        if (self::strContains($img, "http")) {
            return $img;
        }
        return "https://ecs7.tokopedia.net/img/cache/700/product-1/2017/9/27/5510391/5510391_9968635e-a6f4-446a-84d0-ff3a98a5d4a2.jpg";
    }

    public static function strStartWith($var, $str)
    {
        $len = strlen($str);
        return (substr($var, 0, $len) === $str);
    }

    public static function strContains($var, $str)
    {
        return strpos($var, $str);
    }
}
