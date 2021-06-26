<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\ScheduleController as schedule;
use App\Models\item;
use App\Models\tokopedia_token;
use App\Models\Variant;
use Carbon\Carbon;
use Error;
use GrahamCampbell\ResultType\Success;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class helper extends Controller
{
    //hey if you are the new programmer and reading this. goodluck lmao XD

    public static function getAuth($token)
    {
        return [
            'Authorization' => "Bearer {$token}",
            'Content-Type' => 'application/json'
        ];
    }
    public static function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    public static function getToken()
    {
        $url = "http://192.168.2.61:8000/api/token";
        $response = http::get($url);
        return $response;
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
        try {
            $tokopedia_data = self::getToken();
            $token = $tokopedia_data["token"];
            $fs_id = $tokopedia_data["fs_id"];
            $shopid = self::shopid();
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
            self::isForbidden($response->headers(), $response->body());
            $response = $response->json();
            $uploadId = $response["data"]["upload_id"];
            $response = http::withHeaders(self::getAuth($token))->get("https://fs.tokopedia.net/v2/products/fs/{$fs_id}/status/{$uploadId}?shop_id={$shopid}");
            $resdata = $response->json();
            $resdata = $resdata["data"];
            if ($resdata['processed_rows'] >= 1) {
                if ($resdata["success_rows"] >= 1) {
                    $productid = $resdata["success_rows_data"][0]["product_id"];
                    // return $resdata; //for dev only 
                    item::findOrFail($id)->update(["tokopedia_id" => $productid, "tokopedia_is_upload" => 1]);
                    self::Logger("data with id {$id} is succesfully updated to tokopedia with product id of {$productid}");
                }
                if ($resdata["failed_rows"] >= 1) {
                    // return $resdata; //for dev only 
                    $error = $resdata["failed_rows_data"][0]['error'];
                    item::findOrFail($id)->update(["tokopedia_upload_id" => $uploadId, "tokopedia_is_upload" => 0]);
                    throw new Error(implode("", $error));
                }
            }

            if ($resdata["unprocessed_rows"] >= 1) {
                // return $resdata;
                item::findOrFail($id)->update(["tokopedia_upload_id" => $uploadId, "tokopedia_is_upload" => 0]);
                self::tokopediaUploadCheck($uploadId, $token, $fs_id, $id);
            }
        } catch (\Throwable $th) {
            // return $th->getMessage();
            self::Logger("data with id {$id} is failed to upload tokopedia", "err");
            self::Logger("Reason ~> {$th->getMessage()}", "err");
        }
    }

    public static function deleteTokopedia($id)
    {
        try {
            self::Logger("Trying to delete produk with id {$id} from tokopedia");
            $tokopediaData = self::getToken();
            $token = $tokopediaData["token"];
            $fs_id = $tokopediaData["fs_id"];
            $shop_id = self::shopid();
            $url = "https://fs.tokopedia.net/v3/products/fs/{$fs_id}/delete?shop_id={$shop_id}";
            $req = [
                "product_id" => [
                    $id
                ]
            ];
            $response  = http::withHeaders(self::getAuth($token))->post($url, $req);
            self::Logger("product with id {$id} is successfully deleted from tokopedia");
        } catch (\Throwable $th) {
            self::Logger("product with id {$id} is failed to deleted from tokopedia", "err");
            self::Logger("Reason ~> {$th->getMessage()}", "err");
        }
    }

    public static function tokopediaChangeVisibility($id, $isActive)
    {
        try {
            $shop_id = self::shopid();
            $tokopedia_data = self::getToken();
            $token = $tokopedia_data["token"];
            $fs_id = $tokopedia_data["fs_id"];
            $auth = self::getAuth($token);
            if ($isActive) {
                $modeMsg = "set active product";
                $mode = "active";
            } else {
                $modeMsg = "set unactive product";
                $mode = "inactive";
            }
            self::Logger("Trying to {$modeMsg} with id {$id} from tokopedia");
            $url = "https://fs.tokopedia.net/v1/products/fs/{$fs_id}/{$mode}?shop_id={$shop_id}";
            $req = [
                "product_id" => [
                    $id
                ]
            ];
            $response  = http::withHeaders($auth)->post($url, $req);
            self::Logger("{$modeMsg} with id {$id} from tokopedia is success");
        } catch (\Throwable $th) {
            self::Logger("{$modeMsg} with id {$id} from tokopedia is failed", "err");
            self::Logger("Reason ~> {$th->getMessage()}", "err");
        }
    }

    public static function tokopediaUploadCheck($uploadId, $token, $fs_id, $id)
    {
        self::Logger("Looping this function until get product id from tokopedia");
        $shopid = self::shopid();
        sleep(1);
        try {
            $response = http::withHeaders(self::getAuth($token))->get("https://fs.tokopedia.net/v2/products/fs/{$fs_id}/status/{$uploadId}?shop_id={$shopid}");
            $resdata = $response->json();
            $resdata = $resdata["data"];
            if ($resdata["success_rows"] >= 1) {
                $productid = $resdata["success_rows_data"][0]["product_id"];
                item::findOrFail($id)->update(["tokopedia_id" => $productid, "tokopedia_is_upload" => 1]);
                self::Logger("data with id {$id} is succesfully processed to tokopedia with product id of {$productid}");
            }
            if ($resdata["failed_rows"] >= 1) {
                $error = $resdata["failed_rows_data"][0]['error'];
                item::findOrFail($id)->update(["tokopedia_upload_id" => $uploadId, "tokopedia_is_upload" => 0]);
                throw new Error(implode(" ", $error));
            }
            if ($resdata["unprocessed_rows"] >= 1) {
                item::findOrFail($id)->update(["tokopedia_upload_id" => $uploadId, "tokopedia_is_upload" => 0]);
                self::tokopediaUploadCheck($uploadId, $token, $fs_id, $id);
            }
        } catch (\Throwable $th) {
            //throw $th;
            self::Logger("data with id {$id} is failed to upload tokopedia", "err");
            self::Logger("Reason ~> {$th->getMessage()}", "err");
        }
    }


    public static function isEmpty($var)
    {
        return $var == 0 || $var == "null" || $var == false || $var == null;
    }


    public static function imageTokopediaFormat($img)
    {
        if (self::strStartWith($img, "/")) {
            if (self::strContains(config('app.url'), 800)) {
                return config('app.url') . "{$img}";
            }
            return config('app.url') . ":/8001{$img}";
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
        return str_contains($var, $str);
    }

    public static function Logger($msg, $type = "default")
    {
        switch (strtolower($type)) {
            case 'err':
                Log::alert("[Tokopedia Error]: {$msg}");
                break;
            case 'jbr':
                Log::info("[Juber]: {$msg}");
                break;
            case 'jbrerr':
                Log::alert("[Juber Error]: {$msg}");
                break;
            default:
                Log::info("[Tokopedia]: {$msg}");
                break;
        }
    }
    public static function validateArray(array $array, array $rules, string $msg = "")
    {
        foreach ($rules as $key => $value) {
            $type = "any";
            $msg = $msg ?? "";
            if (str_contains($value, ":")) {
                $explode = explode(":", $value);

                if (count($explode) > 2) {
                    throw new Error("{$msg} Error ~> {$value} is not a valid JSON key!!");
                }

                $value = $explode[0];
                $type = $explode[1];
            }
            $isExist = array_key_exists($value, $array);
            if (!$isExist) {
                throw new Error("{$msg} {$value} is Required !!");
            }
            if ($type !== "any" && $type !== "string" && $type !== "integer" && $type !== "array" && $type !== "boolean" && $type !== "object" && $type !== "null") {
                throw new Error("type {$type} is not valid!!");
            }
            if ($type !== "any" && strtolower(gettype($array[$value])) !== $type) {
                throw new Error("{$msg} {$value} must be {$type}");
            }
        }
    }

    public static function typeLower($data)
    {
        return strtolower(gettype($data));
    }
    public static function juberSyncDelete($data)
    {
        $url = "http://192.168.2.45:9888/delprodjbcore";
        $headers = array("Content-Type" => "application/json");
        $data = ["idbarang" => $data["id"], "idbarangjbcore" => $data["juber_id"]];
        $formatedData = ["json" => json_encode($data)];
        http::withHeaders($headers)->post($url, $formatedData);
    }
    public static function juberSyncInsert($data)
    {
        // {
        //     id,
        //     name,
        //     sku,
        //     description,
        //     picture,
        //     weight,
        //     selling_price,
        //     store_id,
        //     category_id,
        //     service,
        //     is_variant,
        //     pid,
        // }

        try {

            if ($data["weight_unit"] == "GR") {
                $data["weight"] = intval($data["weight"]) / 1000;
            }
            $harga = intval($data["selling_price"]);
            $hargaPromo = $data['discount_price'] ?? 0;
            $image = self::imageTokopediaFormat($data['picture']);
            $payload = "{\"kdprodukgoota\":\"{$data['id']}\",\"nmproduk\":\"{$data['name']}\",\"singkatan\":\"{$data['sku']}\",\"isstokkosong\":\"0\"," .
                "\"jamstart\":\"09:00\",\"jamend\":\"16:30\",\"keterangan\":\"{$data['description']}\"," .
                "\"imgurl\":\"{$image}\",\"berat\":\"{$data['weight']}\",\"harga\":{$harga}," .
                "\"hargapromo\":{$hargaPromo},\"kdMercant\":\"{$data['store_id']}\",\"kategori\":\"{$data['category_id']}\",\"layanan\":\"{$data['service']}\"}";
            $url = "http://192.168.2.45:9888/jbmiddleware";
            $key = "createproduk";
            $body = ["key" => $key, "payload" => $payload];
            $response =  http::withHeaders(self::getJuberHeaders())->post($url, $body);
            if ($response["code"] == 200) {
                $lobj = $response["lobj"][0];
                $id = $lobj['idproduk'];
                $haveVariant = $data["is_variant"] ?? false;
                if ($haveVariant) {
                    Variant::findOrFail($data["id"])->update(["juber_id" => $id]);
                } else {
                    item::findOrFail($data["id"])->update(["juber_id" => $id]);
                }
                self::Logger("sync upload produk with id {$data['id']} on juber {$id}", "jbr");
                return ["success" => true];
            } else {
                self::Logger(strval($response), "jbrerr");
                $msg = $response->msg ?? "server juber error";
                $msg = $response["msg"] ?? "server juber error";
                throw new Error($msg);
            }
        } catch (\Throwable $th) {
            $id = $data['id'] ?? '';
            $id = $data["is_variant"] ? $data["pid"] ?? '' : $id;
            if ($id !== '') {
                item::findOrFail($id)->delete();
            }
            self::Logger("Gagal sync data product dengan id => {$id} ke juber database", "jbrerr");
            self::Logger("Reason: {$th->getMessage()}", "jbrerr");
            return ["success" => false, "msg" => $th->getMessage()];
        }
    }
    public static function getJuberHeaders()
    {
        return ["Cookie" => "JSESSIONID=FDCDF7969FB1F9F89EB1E0AA4B3C4359; PHPSESSID=dacd3c46c86606a8d51bec99bcf858b9; XSRF-TOKEN=N587398437849043239", "Content-Type" => "application/json"];
    }
    public static function getJsonHeader()
    {
        return ["Content-type" => "application/json"];
    }
    public static function checkifexist($column, $request_name, $request, $dataTable)
    {
        if (array_key_exists($request_name, $request)) {
            $databaru = self::addData($column, $request_name, $request, $dataTable);
            return $databaru;
        } else {
            return $dataTable;
        }
    }
    public static function addData($column, $request_name, $request, $dataTable)
    {
        if (array_key_exists($request_name, $request)) {
            $dataTable[$column] = $request[$request_name];
            return $dataTable;
        } else {
            throw new Error("{$request_name} is required");
        }
    }
    public static function isForbidden($headers, $body)
    {
        if ($headers["Content-Type"] != "application/json") {
            if (is_string($body)) {
                if (str_contains($body, "Forbidden")) {
                    throw new Error("Forbidden Request");
                }
            }
        }
    }
    public static function tokopediaUpdate($dataTable, $id, $table)
    {

        try {
            self::Logger("Trying to update product with id {$id}");
            $shopid = self::shopid();
            $tokopedia_data = self::getToken();
            $token = $tokopedia_data["token"];
            $fs_id = $tokopedia_data["fs_id"];
            $oldPictures = [];
            if (self::isPicture($table->picture)) {
                array_push($oldPictures, ["file_path" => self::imageTokopediaFormat($table->picture)]);
            }
            if (self::isPicture($table->picture_two)) {
                array_push($oldPictures, ["file_path" => self::imageTokopediaFormat($table->picture_two)]);
            }
            if (self::isPicture($table->picture_three)) {
                array_push($oldPictures, ["file_path" => self::imageTokopediaFormat($table->picture_three)]);
            }
            if (self::isPicture($table->picture_four)) {
                array_push($oldPictures, ["file_path" => self::imageTokopediaFormat($table->picture_two)]);
            }
            if (self::isPicture($table->picture_five)) {
                array_push($oldPictures, ["file_path" => self::imageTokopediaFormat($table->picture_five)]);
            }

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
            $products["id"] = $id;
            array_key_exists("name", $dataTable) ? $products["name"] = $dataTable["name"] :
                $products["name"] = $table->name;
            array_key_exists("condition", $dataTable) ? $products["condition"] = ($dataTable["condition"] == 1) ? "NEW" : "USED" :
                $products["condition"] = ($table->condition == 1) ? "NEW" : "USED";
            array_key_exists("description", $dataTable) ? $products["description"] = $dataTable["description"] :
                $products["description"] = $table->description;
            array_key_exists("selling_price", $dataTable) ?  $products["price"] = intval($dataTable["selling_price"]) :
                $products["price"] = intval($table->selling_price);
            array_key_exists("weight", $dataTable) ? $products["weight"] = intval($dataTable["weight"]) :
                $products["weight"] = intval($table->weight);
            array_key_exists("weight_unit", $dataTable) ? $products["weight_unit"] = $dataTable["weight_unit"] :
                $products["weight_unit"] = $table->weight_unit;
            array_key_exists("category_id", $dataTable) ? $products["category_id"] = intval($dataTable["category_id"]) :
                $products["category_id"] = intval($table->category_id);
            array_key_exists("minimal_stock", $dataTable) ?  $products["stock"] = intval($dataTable["minimal_stock"]) :
                $products["minimal_stock"] = intval($table->minimal_stock);
            count($pictures) >= 1 ? $products["pictures"] = $pictures :
                $products["pictures"] = $oldPictures;
            $products["status"] = "LIMITED";
            $products["is_free_return"] = false;
            $products["is_must_insurance"] = false;
            $products["price_currency"] = "IDR";
            $products["min_order"] = 1;
            $url = "https://fs.tokopedia.net/v2/products/fs/{$fs_id}/edit?shop_id={$shopid}";  //the patch request need to put all data
            $products = ["products" => [$products]]; //even if you just want to change the product name you still need to put the old data
            $response =  http::withHeaders(self::getAuth($token))->patch($url, $products); //i hate you tokopedia :) 
            self::isForbidden($response->headers(), $response->body());
            $response = $response->json();
            $uploadId = $response["data"]["upload_id"];
            $response = http::withHeaders(self::getAuth($token))->get("https://fs.tokopedia.net/v2/products/fs/{$fs_id}/status/{$uploadId}?shop_id={$shopid}");
            $resdata = $response->json();
            $resdata = $resdata["data"];
            if ($resdata['processed_rows'] >= 1) {
                if ($resdata["success_rows"] >= 1) {
                    $productid = $resdata["success_rows_data"][0]["product_id"];
                    item::findOrFail($table->id)->update(["tokopedia_id" => $productid, "tokopedia_is_upload" => 1]);
                    self::Logger("data with id {$table->id} is succesfully updated to tokopedia with product id of {$productid}");
                }
                if ($resdata["failed_rows"] >= 1) {
                    // return $resdata["failed_rows_data"];
                    $error = $resdata["failed_rows_data"][0]['error'];
                    item::findOrFail($id)->update(["tokopedia_upload_id" => $uploadId, "tokopedia_is_upload" => 1]);
                    throw new Error(implode("", $error));
                }
            }
            if ($resdata["unprocessed_rows"] >= 1) {
                item::findOrFail($table->id)->update(["tokopedia_upload_id" => $uploadId, "tokopedia_is_upload" => 1]);
                // return Response($response, 200);
                self::tokopediaUploadCheck($uploadId, $token, $fs_id, $table->id);
            }
        } catch (\Throwable $th) {
            // return $th->getMessage();
            self::Logger("data with id {$table->id} is failed to update tokopedia", "err");
            self::Logger("Reason ~> {$th->getMessage()}", "err");
        }
    }
    public static function resp($success, $type, $msg, $datas, $code = 200)
    {
        if ($success) {
            $data["code"] = 200;
        } else {
            if ($code != 200) {
                $data["code"] = $code;
            } else {
                $data["code"] = 500;
            }
        }
        $data["success"] = $success;
        $data["message"] = $msg;
        switch (strtolower($type)) {
            case 'destroy':
                $data["data"] = ["deleted_rows" => $success ? 1 : 0, "data" => $datas];
                return $data;
            case 'store':
                $data["data"] = ["created_rows" => $success ? 1 : 0, "data" => $datas];
                return $data;
            case 'update':
                $data["data"] = ["updated_rows" => $success ? 1 : 0, "data" => $datas];
                return $data;
            default:
                $data["data"] = $datas;
                return $data;
        }
    }

    public static function getLocationCode($district)
    {
        try {
            if ($district == null && $district == "") {
                throw new Error("district not found !!");
            }
            $url = "http://192.168.2.45:9888/cariwilayah";
            $data = ["key" => $district, "code" => "3"]; //code province=1;city=2;district=3;
            $response = http::post($url, $data);
            $location = $response->json();
            if ($location["code"] != "200") {
                throw new Error($location["msg"]);
            }
            if (count($location["lobj"]) <= 0) {
                throw new Error("Lokasi {$district} tidak ditemukan");
            }
            $juber_place_code = $location["lobj"][0]["kode"];
            $sapkode  = $location["lobj"][0]["kodeSAP"];
            return  ["success" => true, "data" => $juber_place_code, "sap" => $sapkode];
        } catch (\Throwable $th) {
            return ["success" => false, "msg" => $th->getMessage()];
        }
    }
    public static function getJsonError($json_msg)
    {
        switch ($json_msg) {
            case JSON_ERROR_NONE:
                return 'false';
            case JSON_ERROR_DEPTH:
                return ' Maximum stack depth exceeded';
            case JSON_ERROR_STATE_MISMATCH:
                return 'Underflow or the modes mismatch';
            case JSON_ERROR_CTRL_CHAR:
                return 'Unexpected control character found';
            case JSON_ERROR_SYNTAX:
                return 'Syntax error, malformed JSON Request';
            case JSON_ERROR_UTF8:
                return 'Malformed UTF-8 characters, possibly incorrectly encoded';
            default:
                return 'Unknown error';
        }
    }
    public static function sendNotification(string $token, string $msg, string $type, string $title, string $image = null, string $markup = null)
    {
        try {
            $markup  = $markup ?? $msg;
            $url = "https://delivery.juber.co.id:8002/notif";
            $data = [
                "tokens" => [$token],
                "judul" => $title,
                "msg" => $msg,
                "data" => [
                    "service" => "jbmarket",
                    "type" => $type, //user or seller or admin
                    "image" => $image,
                    "msgMarkup" => $markup
                ]
            ];
            http::post($url, $data);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    public static function juberCoreSyncStatusTrx(string $id, int $status)
    {
        $url = "http://192.168.2.45:9888/updatejbmarket";
        $headers = ["Content-Type" => "application/json"];
        $payload = ["trxid" => $id, "stsjbcore" => $status];
        $stringifyPayload = json_encode($payload, true);
        $body = ["json" => $stringifyPayload];
        $response = http::withHeaders($headers)->post($url, $body);
    }
    public static function juberCoreGetCourrier(string $device_id, string $transaction_id)
    {
        try {
            $url = "http://192.168.2.45:9888/orderkurir";
            $headers = self::getJsonHeader();
            $payload = ["uuid" => $device_id, "trxid" => $transaction_id];
            $data = ["json" => json_encode($payload)];
            $response = http::withHeaders($headers)->post($url, $data);
            $resJson = $response->json();
            if ($resJson["code"] == "200") {
                return ["success" => true, "data" => $resJson["lobj"][0]];
            }
            throw new Error($resJson["msg"]);
        } catch (\Throwable $th) {
            return ["success" => false, "data" => $th->getMessage()];
        }
    }
}
