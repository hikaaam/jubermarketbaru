<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\item;
use App\Models\Variant;
use App\Models\ref_cat;
use App\Models\store;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Http;
use Config;
use App\Http\Controllers\helper;
use Facade\FlareClient\Http\Response;


class ProductController extends Controller
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
            $result = item::where('is_shown', 1)->paginate(6);
            $data["success"] = true;
            $data["code"] = 200;
            $data["message"] = "berhasil";
            $data["data"] = $result->setPath(\config('app.url') . ":8001/api/product");
        } catch (\Throwable $th) {
            $data["data"] = [];
            $data["success"] = false;
            $data["code"] = 500;
            $data["message"] = $th->getMessage();
        }
        return $data;
    }
    public function isNotShown()
    {
        try {
            $result = item::where('is_shown', 0)->paginate(6);
            $data["success"] = true;
            $data["code"] = 200;
            $data["message"] = "berhasil";
            $data["data"] = $result->setPath(\config('app.url') . ":8001/api/product/hidden");
        } catch (\Throwable $th) {
            $data["data"] = [];
            $data["success"] = false;
            $data["code"] = 500;
            $data["message"] = $th->getMessage();
        }
        return $data;
    }
    public function visible()
    {
        try {
            $result = item::paginate(6);
            $data["success"] = true;
            $data["code"] = 200;
            $data["message"] = "berhasil";
            $data["data"] = $result->setPath(\config('app.url') . ":8001/api/product/visible");
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
        $shopid = helper::shopid();
        $request = json_decode($request->payload, true);
        $dataTable = [];
        function checkifexist($column, $request_name, $request, $dataTable)
        {
            if (array_key_exists($request_name, $request)) {
                $databaru = addData($column, $request_name, $request, $dataTable);
                return $databaru;
            } else {
                if ($column == "description") {
                    $request[$request_name] = "";
                    $databaru = addData($column, $request_name, $request, $dataTable);
                    return $databaru;
                }
                if ($column == "condition") {
                    $request[$request_name] = "1";
                    $databaru = addData($column, $request_name, $request, $dataTable);
                    return $databaru;
                }
                if ($column == "weight") {
                    $request[$request_name] = "200";
                    $databaru = addData($column, $request_name, $request, $dataTable);
                    return $databaru;
                }
                if ($column == "weight_unit") {
                    $request[$request_name] = "GR";
                    $databaru = addData($column, $request_name, $request, $dataTable);
                    return $databaru;
                }
                if ($column == "sku") {
                    $str_id = $request["store_id"];
                    $store = store::find($str_id);
                    $item_last = item::orderBy('id', 'desc')->first();
                    $item_last_id = $item_last->id + 1;
                    $store_name = $store->store_name;
                    $words = explode(" ", $store_name);
                    $acronym = "";
                    foreach ($words as $w) {
                        $acronym .= $w[0];
                    }
                    $acronym = $acronym . $str_id . "P" . $item_last_id;
                    $request[$request_name] = strtoupper($acronym);
                    $databaru = addData($column, $request_name, $request, $dataTable);
                    return $databaru;
                }
                return $dataTable;
            }
        }
        function addData($column, $request_name, $request, $dataTable)
        {
            $dataTable[$column] = $request[$request_name];
            return $dataTable;
        }
        try {
            $namaExist = false;
            $success = false;
            $withVariant = false;
            $variant_ = null;
            if (!helper::isPicture($request["picture"])) {
                return getRespond(false, "Masukan minimal 1 foto untuk upload produk", []);
            }
            $dataTable = addData("item_type", "item_type", $request, $dataTable);
            $dataTable = addData("minimal_stock", "minimal_stock", $request, $dataTable);
            $dataTable = addData("category_id", "category_id", $request, $dataTable);
            $dataTable = addData("store_id", "store_id", $request, $dataTable);
            $dataTable = addData("selling_price", "selling_price", $request, $dataTable);
            $dataTable = addData("name", "name", $request, $dataTable);
            $dataTable = addData("created_by_id", "created_by_id", $request, $dataTable);
            $dataTable = addData("created_by", "created_by", $request, $dataTable);
            $dataTable = addData("last_updated_by_id", "created_by_id", $request, $dataTable);
            $dataTable = checkifexist("sku", "sku", $request, $dataTable);
            $dataTable = checkifexist("description", "description", $request, $dataTable);
            $dataTable = checkifexist("item_code", "item_code", $request, $dataTable);
            $dataTable = checkifexist("stockable", "stockable", $request, $dataTable);
            $dataTable = checkifexist("picture", "picture", $request, $dataTable);
            $dataTable = checkifexist("picture_two", "picture_two", $request, $dataTable);
            $dataTable = checkifexist("picture_three", "picture_three", $request, $dataTable);
            $dataTable = checkifexist("picture_four", "picture_four", $request, $dataTable);
            $dataTable = checkifexist("picture_five", "picture_five", $request, $dataTable);
            $dataTable = checkifexist("video", "video", $request, $dataTable);
            $dataTable = checkifexist("type_of_item", "type_of_item", $request, $dataTable);
            $dataTable = checkifexist("item_unit_id", "item_unit_id", $request, $dataTable);
            $dataTable = checkifexist("is_active", "is_active", $request, $dataTable);
            $dataTable = checkifexist("basic_price", "basic_price", $request, $dataTable);
            $dataTable = checkifexist("cost_of_good_sold", "cost_of_good_sold", $request, $dataTable);
            $dataTable = checkifexist("item_tax_type", "item_tax_type", $request, $dataTable);
            $dataTable = checkifexist("weight", "weight", $request, $dataTable);
            $dataTable = checkifexist("weight_unit", "weight_unit", $request, $dataTable);
            $dataTable = checkifexist("condition", "condition", $request, $dataTable);
            $dataTable = checkifexist("pre_order", "pre_order", $request, $dataTable);
            $dataTable = checkifexist("pre_order_estimation", "pre_order_estimation", $request, $dataTable);
            $dataTable = checkifexist("dimension_length", "dimension_length", $request, $dataTable);
            $dataTable = checkifexist("dimension_width", "dimension_width", $request, $dataTable);
            $dataTable = checkifexist("dimension_height", "dimension_height", $request, $dataTable);
            $dataTable = checkifexist("is_shown", "is_shown", $request, $dataTable);
            $dataTable = checkifexist("ownership", "ownership", $request, $dataTable);
            $dataTable = checkifexist("bahan", "bahan", $request, $dataTable);
            $dataTable = checkifexist("merk", "merk", $request, $dataTable);
            $namaExist = item::where("name", $dataTable["name"])->count() > 0;
            if ($namaExist) {
                $data["success"] = false;
                $data["code"] = 402;
                $data["message"] = "Barang dengan nama {$request['name']} sudah ada!! silahkan gunakan nama lain";
                $data["data"] = [];
                return $data;
            }
            $dataTable = checkifexist("origin", "origin", $request, $dataTable);
            $items = item::create($dataTable);
            $id = $items->id;


            // $id = 240; //for trial purpose
            // $items = []; //for trial purpose

            if (count($request["variant"]) > 0) {
                $withVariant = true;
                $variant_ = $request["variant"];
                foreach ($request["variant"] as $key => $value) {
                    $variant = ["name" => $value['variant_name'], "harga" => $value['harga'], "item_id" => $id, "picture" => $value['picture'], "stock" => $value["stock"]];
                    Variant::create($variant);
                }
            }

            $data["success"] = true;
            $data["code"] = 202;
            $data["message"] = "berhasil";
            $data["data"] = ["request_data" => $items];
            $success = true;
            return $data;
        } catch (\Throwable $th) {
            $data["data"] = [];
            $data["success"] = false;
            $data["code"] = 500;
            $data["message"] = $th->getMessage();
            $success = false;
            return $data;
        } finally {
            if (!$namaExist && $success) {
                try {
                    helper::tokopediaUpload($dataTable, $id, $withVariant, $variant_);
                } catch (\Throwable $th) {
                    return $data;
                }
            }
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
        try {
            $result = item::find($id);
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }
    public function all()
    {
        try {
            $result = item::where('is_shown', 1)->get();
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
    public function productbycat(Request $request, $id)
    {
        try {
            $result = item::where('is_shown', 1)->where('category_id', $id)->get();
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
    public function productByStId(Request $request, $id)
    {
        try {
            $result = item::where('is_shown', 1)->where('store_id', $id)->get();
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
    public function productByRefId(Request $request, $id)
    {
        try {
            $result = DB::table('ref_category')
                ->join('category', 'category.ref_category', '=', 'ref_category.id')
                ->join('item', 'item.category_id', '=', 'category.id')
                ->select('item.*')->where('ref_category.id', $id)
                ->paginate(6);
            $data["success"] = true;
            $data["code"] = 200;
            $data["message"] = "berhasil";
            $data["data"] = $result->setPath(\config('app.url') . ":8001/api/productByRef");
        } catch (\Throwable $th) {
            $data["data"] = [];
            $data["success"] = false;
            $data["code"] = 500;
            $data["message"] = $th->getMessage();
        }
        return $data;
    }
    public function productByStId_(Request $request, $id)
    {
        try {
            $result = item::where('is_shown', 0)->where('store_id', $id)->get();
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
    public function productByStIdVisible(Request $request, $id)
    {
        try {
            $result = item::where('store_id', $id)->get();
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
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
            $dontHaveTokopediaId = false;
            $table = item::findOrFail($id);
            $check = item::where("name", $request["name"])->count();
            if ($table->name != $request["name"] && $check >= 1) {
                return getRespond(false, "Nama itu sudah digunakan oleh produk lain", []);
            }
            $dataTable = checkifexist("item_type", "item_type", $request, $dataTable);
            $dataTable = checkifexist("minimal_stock", "minimal_stock", $request, $dataTable);
            $dataTable = checkifexist("category_id", "category_id", $request, $dataTable);
            $dataTable = checkifexist("origin", "origin", $request, $dataTable);
            $dataTable = checkifexist("store_id", "store_id", $request, $dataTable);
            $dataTable = checkifexist("selling_price", "selling_price", $request, $dataTable);
            $dataTable = checkifexist("name", "name", $request, $dataTable);
            $dataTable = checkifexist("created_by_id", "created_by_id", $request, $dataTable);
            $dataTable = checkifexist("created_by", "created_by", $request, $dataTable);
            $dataTable = checkifexist("last_updated_by_id", "created_by_id", $request, $dataTable);
            $dataTable = checkifexist("sku", "sku", $request, $dataTable);
            $dataTable = checkifexist("description", "description", $request, $dataTable);
            $dataTable = checkifexist("item_code", "item_code", $request, $dataTable);
            $dataTable = checkifexist("stockable", "stockable", $request, $dataTable);
            $dataTable = checkifexist("picture", "picture", $request, $dataTable);
            $dataTable = checkifexist("picture_two", "picture_two", $request, $dataTable);
            $dataTable = checkifexist("picture_three", "picture_three", $request, $dataTable);
            $dataTable = checkifexist("picture_four", "picture_four", $request, $dataTable);
            $dataTable = checkifexist("picture_five", "picture_five", $request, $dataTable);
            $dataTable = checkifexist("video", "video", $request, $dataTable);
            $dataTable = checkifexist("type_of_item", "type_of_item", $request, $dataTable);
            $dataTable = checkifexist("item_unit_id", "item_unit_id", $request, $dataTable);
            $dataTable = checkifexist("is_active", "is_active", $request, $dataTable);
            $dataTable = checkifexist("basic_price", "basic_price", $request, $dataTable);
            $dataTable = checkifexist("cost_of_good_sold", "cost_of_good_sold", $request, $dataTable);
            $dataTable = checkifexist("item_tax_type", "item_tax_type", $request, $dataTable);
            $dataTable = checkifexist("weight", "weight", $request, $dataTable);
            $dataTable = checkifexist("weight_unit", "weight_unit", $request, $dataTable);
            $dataTable = checkifexist("condition", "condition", $request, $dataTable);
            $dataTable = checkifexist("pre_order", "pre_order", $request, $dataTable);
            $dataTable = checkifexist("pre_order_estimation", "pre_order_estimation", $request, $dataTable);
            $dataTable = checkifexist("dimension_length", "dimension_length", $request, $dataTable);
            $dataTable = checkifexist("dimension_width", "dimension_width", $request, $dataTable);
            $dataTable = checkifexist("dimension_height", "dimension_height", $request, $dataTable);
            $dataTable = checkifexist("is_shown", "is_shown", $request, $dataTable);
            $dataTable = checkifexist("ownership", "ownership", $request, $dataTable);
            $dataTable = checkifexist("bahan", "bahan", $request, $dataTable);
            $dataTable = checkifexist("merk", "merk", $request, $dataTable);
            $table->update($dataTable);
            $dontHaveTokopediaId = $table->tokopedia_id == null;
            Variant::where('item_id', $id)->delete();
            if (count($request["variant"]) > 0) {
                foreach ($request["variant"] as $key => $value) {
                    $variant = ["name" => $value['variant_name'], "harga" => $value['harga'], "item_id" => $id, "picture" => $value['picture'], "stock" => $value["stock"]];
                    Variant::create($variant);
                }
            }

            $data["success"] = true;
            $data["code"] = 202;
            $data["message"] = "berhasil";
            $data["data"] = ["request_data" => $request];
            return $data;
        } catch (\Throwable $th) {
            $data["data"] = [];
            $data["success"] = false;
            $data["code"] = 500;
            $data["message"] = $th->getMessage();
            return $data;
        } finally {
            if (!$dontHaveTokopediaId) {
                try {
                    helper::tokopediaUpdate($dataTable, $table["tokopedia_id"], $table);
                } catch (\Throwable $th) {
                    return $data;
                }
            }
        }
        // return $data
    }

    public function getRelatedProduct(Request $request, $id)
    {
        try {
            $request = json_decode($request->payload, true);
            $limit = 3;
            if (array_key_exists("limit", $request)) {
                $limit = $request["limit"];
            }
            $item = item::findOrFail($id);
            $cat_id = $item->category_id;
            if (checkNull($cat_id)) {
                return getRespond(false, "Tidak ada kategori yang berelasi dengan product ini", []);
            } else {
                $data = item::where("category_id", $cat_id)->where("is_shown", 1)->limit($limit)->get();
                return getRespond(true, "Berhasil fetching data", $data);
            }
        } catch (\Throwable $th) {
            return getRespond(false, $th->getMessage(), []);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateIsShown(Request $request, $id)
    {
        $request = json_decode($request->payload, true);
        $dataTable = [];
        $dontHaveTokopediaId = true;
        try {
            $dataTable = helper::addData("is_shown", "is_shown", $request, $dataTable);
            $isActive = intval($dataTable["is_shown"]) == 1;
            $table = item::findOrFail($id);
            $table->update($dataTable);
            $dontHaveTokopediaId = $table->tokopedia_id == null;
            $msg = $isActive ? 'aktif' : 'tidak aktif';
            $data = getRespond(true, "berhasil mengubah produk menjadi {$msg}", ["updatedField" => 1, "status" => $msg]);
            return $data;
        } catch (\Throwable $th) {
            $data = getRespond(true, "gagal mengubah produk menjadi {$msg}", ["updatedField" => 0, "status" => $msg, "Reason" => $th->getMessage()]);
            return $data;
        } finally {
            if (!$dontHaveTokopediaId) {
                try {
                    helper::tokopediaChangeVisibility($table->tokopedia_id, $isActive);
                } catch (\Throwable $th) {
                    return $data;
                }
            }
        }
    }

    public function destroy($id)
    {
        try {
            $item = item::find($id);
            $dontHaveTokopediaId = $item["tokopedia_id"] == null;
            $variant = Variant::where('item_id', $id)->get();
            $item->delete();
            Variant::where('item_id', $id)->delete();
            $data["success"] = true;
            $data["code"] = 202;
            $data["message"] = "berhasil di hapus";
            $data["data"] = [];
            return $data;
        } catch (\Throwable $th) {
            $data["data"] = [];
            $data["success"] = false;
            $data["code"] = 500;
            $data["message"] = $th->getMessage();
            return $data;
        } finally {
            if (!$dontHaveTokopediaId) {
                try {
                    helper::deleteTokopedia($item["tokopedia_id"]);
                } catch (\Throwable $th) {
                    return $data;
                }
            }
        }
    }
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
function checkNull($var)
{
    if ($var == null) {
        return true;
    } else {
        return false;
    }
}
