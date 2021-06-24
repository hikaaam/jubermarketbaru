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
use App\Jobs\deleteCartItem;
use App\Jobs\deleteTokopedia;
use App\Jobs\insertTokopedia;
use App\Jobs\jbmarketsyncDelete;
use App\Jobs\tokopediaChangeVisible;
use App\Jobs\updateTokopedia;
use App\Models\review;
use App\Models\trans;
use Error;
use Exception;
use Facade\FlareClient\Http\Response;

use function PHPSTORM_META\type;

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
            $result = item::where('is_shown', 1)->where('service', 'jbmarket')->paginate(6);
            $data["success"] = true;
            $data["code"] = 200;
            $data["message"] = "berhasil";
            $data["data"] = $result->setPath("/api/product");
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
            $result = item::where('is_shown', 0)->where('service', 'jbmarket')->paginate(6);
            $data["success"] = true;
            $data["code"] = 200;
            $data["message"] = "berhasil";
            $data["data"] = $result->setPath("/api/product/hidden");
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
            $result = item::where('service', 'jbmarket')->with('store')->paginate(6);
            $data["success"] = true;
            $data["code"] = 200;
            $data["message"] = "berhasil";
            $data["data"] = $result->setPath("/api/product/visible");
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
        // $shopid = helper::shopid();
        $request = json_decode($request->payload, true);
        $dataTable = [];
        $msg = "";
        function checkifexistStore($column, $request_name, $request, $dataTable)
        {
            if (array_key_exists($request_name, $request)) {
                $databaru = addDataStore($column, $request_name, $request, $dataTable);
                return $databaru;
            } else {
                if ($column == "description") {
                    $request[$request_name] = "";
                    $databaru = addDataStore($column, $request_name, $request, $dataTable);
                    return $databaru;
                }
                if ($column == "condition") {
                    $request[$request_name] = "1";
                    $databaru = addDataStore($column, $request_name, $request, $dataTable);
                    return $databaru;
                }
                if ($column == "weight") {
                    // $request[$request_name] = "200";
                    // $databaru = addDataStore($column, $request_name, $request, $dataTable);
                    throw new Exception("Berat wajib di isi!");
                }
                if ($column == "weight_unit") {
                    // $request[$request_name] = "GR";
                    // $databaru = addDataStore($column, $request_name, $request, $dataTable);
                    // return $databaru;
                    throw new Exception("Satuan berat wajib di isi! silahkan pilih GR atau KG");
                }
                if ($column == "sku") {
                    $str_id = $request["store_id"];
                    $store = store::find($str_id);
                    $item_last = item::orderBy('id', 'desc')->limit(1)->first();
                    $item_last_id = $item_last->id + 1;
                    $store_name = $store->store_name;
                    $words = explode(" ", $store_name);
                    $acronym = "";
                    foreach ($words as $w) {
                        if ($w !== "") {
                            $acronym .= $w[0];
                        }
                    }
                    $acronym = $acronym . $str_id . "P" . $item_last_id . chr(rand(65, 90));
                    $request[$request_name] = strtoupper($acronym);
                    $databaru = addDataStore($column, $request_name, $request, $dataTable);
                    return $databaru;
                }
                return $dataTable;
            }
        }
        function addDataStore($column, $request_name, $request, $dataTable)
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
                return  helper::resp(false, 'store', "Masukan minimal 1 foto untuk upload produk", [], 400);
            }
            $dataTable = addDataStore("item_type", "item_type", $request, $dataTable);
            $dataTable = addDataStore("minimal_stock", "minimal_stock", $request, $dataTable);
            $dataTable = addDataStore("category_id", "category_id", $request, $dataTable);
            $dataTable = addDataStore("store_id", "store_id", $request, $dataTable);
            $dataTable = addDataStore("selling_price", "selling_price", $request, $dataTable);
            if (intval($dataTable["selling_price"]) < 100) {
                return  helper::resp(false, 'store', "Harga minimal produk adalah 100", [], 400);
            }
            $dataTable = addDataStore("name", "name", $request, $dataTable);
            $dataTable = addDataStore("created_by_id", "created_by_id", $request, $dataTable);
            $dataTable = addDataStore("created_by", "created_by", $request, $dataTable);
            $dataTable = addDataStore("last_updated_by_id", "created_by_id", $request, $dataTable);
            $dataTable = checkifexistStore("sku", "sku", $request, $dataTable);
            $dataTable = checkifexistStore("description", "description", $request, $dataTable);
            $dataTable = checkifexistStore("item_code", "item_code", $request, $dataTable);
            $dataTable = checkifexistStore("stockable", "stockable", $request, $dataTable);
            $dataTable = checkifexistStore("picture", "picture", $request, $dataTable);
            $dataTable = checkifexistStore("picture_two", "picture_two", $request, $dataTable);
            $dataTable = checkifexistStore("picture_three", "picture_three", $request, $dataTable);
            $dataTable = checkifexistStore("picture_four", "picture_four", $request, $dataTable);
            $dataTable = checkifexistStore("picture_five", "picture_five", $request, $dataTable);
            $dataTable = checkifexistStore("video", "video", $request, $dataTable);
            $dataTable = checkifexistStore("type_of_item", "type_of_item", $request, $dataTable);
            $dataTable = checkifexistStore("item_unit_id", "item_unit_id", $request, $dataTable);
            $dataTable = checkifexistStore("is_active", "is_active", $request, $dataTable);
            $dataTable = checkifexistStore("basic_price", "basic_price", $request, $dataTable);
            $dataTable = checkifexistStore("cost_of_good_sold", "cost_of_good_sold", $request, $dataTable);
            $dataTable = checkifexistStore("item_tax_type", "item_tax_type", $request, $dataTable);
            $dataTable = checkifexistStore("weight", "weight", $request, $dataTable);
            $dataTable = checkifexistStore("weight_unit", "weight_unit", $request, $dataTable);
            $wgUnit = $dataTable["weight_unit"];
            if ($wgUnit !== 'GR' && $wgUnit !== 'KG') {
                throw new Exception("Satuan berat cuma ada GR dan KG");
            }
            $dataTable = checkifexistStore("condition", "condition", $request, $dataTable);
            $dataTable = checkifexistStore("pre_order", "pre_order", $request, $dataTable);
            $dataTable = checkifexistStore("pre_order_estimation", "pre_order_estimation", $request, $dataTable);
            $dataTable = checkifexistStore("dimension_length", "dimension_length", $request, $dataTable);
            $dataTable = checkifexistStore("dimension_width", "dimension_width", $request, $dataTable);
            $dataTable = checkifexistStore("dimension_height", "dimension_height", $request, $dataTable);
            $dataTable = checkifexistStore("is_shown", "is_shown", $request, $dataTable);
            $dataTable = checkifexistStore("ownership", "ownership", $request, $dataTable);
            $dataTable = checkifexistStore("bahan", "bahan", $request, $dataTable);
            $dataTable = checkifexistStore("merk", "merk", $request, $dataTable);
            $dataTable["service"] = "jbmarket";
            $namaExist = item::where("name", $dataTable["name"])->count() > 0;
            if ($namaExist) {
                $msg =  "Barang dengan nama {$request['name']} sudah ada!! silahkan gunakan nama lain";
                return helper::resp(false, 'store', $msg, [], 400);
            }
            $dataTable = checkifexistStore("origin", "origin", $request, $dataTable);
            throw new Exception("the code before this work");
            $items = item::create($dataTable);
            $syncJuber = helper::juberSyncInsert($items);
            if (!$syncJuber["success"]) {
                throw new Error($syncJuber["msg"]);
            }
            $id = $items->id;
            // $id = 324; //for trial purpose
            // $items = []; //for trial purpose
            // $namaExist = false; // for trial purpose
            $dataTable["id"] = $id;

            if (count($request["variant"] ?? 0) > 0) {
                $withVariant = true;
                $variant_ = $request["variant"];
                foreach ($request["variant"] as $key => $value) {
                    $variant = ["name" => $value['variant_name'], "harga" => $value['harga'], "item_id" => $id, "picture" => $value['picture'], "stock" => $value["stock"]];
                    Variant::create($variant);
                }
            }
            $success = true;
            return helper::resp(true, 'store', "berhasil menambahkan product", $items);
        } catch (\Throwable $th) {
            $success = false;
            return helper::resp(false, 'store', $th->getMessage(), []);
        } finally {
            if (!$namaExist && $success) {
                try {
                    $tokopediaData = array(
                        "data" => $dataTable,
                        "id" => $id,
                        "withVariant" => $withVariant,
                        "variant" => $variant_
                    );
                    //TODO: VARIANT TOKOPEDIA
                    insertTokopedia::dispatch($tokopediaData);
                } catch (\Throwable $th) {
                    // return $data;
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
            $id = intval($id);
            if ($id == 0) {
                throw new Exception("ID must be a number and bigger than 0");
            }
            $result = item::findOrFail($id);
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
            $result = item::where('is_shown', 1)->where('service', 'jbmarket')->get();
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
            $result = item::where('is_shown', 1)->where('service', 'jbmarket')->where('category_id', $id)->get();
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
            $result = item::where('is_shown', 1)->where('service', 'jbmarket')->where('store_id', $id)->get();
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
            // $result = DB::table('ref_category')
            //     ->join('category', 'category.ref_category', '=', 'ref_category.id')
            //     ->join('item', 'item.category_id', '=', 'category.id')
            //     ->select('item.*')->where('ref_category.id', $id)
            //     ->paginate(6);
            $result = item::where("category_id", $id)->where('service', 'jbmarket')->paginate(8);
            $data["success"] = true;
            $data["code"] = 200;
            $data["message"] = "berhasil";
            $data["data"] = $result->setPath("/api/productByRef");
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
            $result = item::where('is_shown', 0)->where('service', 'jbmarket')->where('store_id', $id)->get();
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
            $result = item::where('store_id', $id)->where('service', 'jbmarket')->get();
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
        $dontHaveTokopediaId = true;
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

            $table = item::findOrFail($id);
            if (array_key_exists("name", $request)) {
                $check = item::where("name", $request["name"])->count();
                if ($table->name != $request["name"] && $check >= 1) {
                    return getRespond(false, "Nama itu sudah digunakan oleh produk lain", []);
                }
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
            if (array_key_exists("variant", $request)) {
                foreach ($request["variant"] as $key => $value) {
                    $variant = ["name" => $value['variant_name'], "harga" => $value['harga'], "item_id" => $id, "picture" => $value['picture'], "stock" => $value["stock"]];
                    Variant::create($variant);
                }
            }

            $data["success"] = true;
            $data["code"] = 202;
            $data["message"] = "berhasil";
            $data["data"] = ["request_data" => $request];

            $success = true;
            return helper::resp($success, "update", "Berhasil update product", $data["data"]);
        } catch (\Throwable $th) {
            $success = false;
            $data["data"] = [];
            $data["success"] = false;
            $data["code"] = 500;
            $data["message"] = $th->getMessage();
            return helper::resp($success, "update", $th->getMessage(), []);
        } finally {
            if (!$dontHaveTokopediaId) {
                try {
                    //TODO: VARIANT TOKOPEDIA
                    $tokopediaData = array(
                        "data" => $dataTable,
                        "id" => $id,
                        "table" => $table
                    );
                    updateTokopedia::dispatch($tokopediaData);
                    // helper::tokopediaUpdate($dataTable, $table["tokopedia_id"], $table);
                } catch (\Throwable $th) {
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
        $dontHaveTokopediaId = true;
        $request = json_decode($request->payload, true);
        $dataTable = [];
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
                    tokopediaChangeVisible::dispatch($table->tokopedia_id, $isActive);
                } catch (\Throwable $th) {
                    // return $data;
                }
            }
        }
    }

    public function destroy($id)
    {
        $dontHaveTokopediaId = true;
        try {
            $item = item::findOrFail($id);
            $dontHaveTokopediaId = $item["tokopedia_id"] == null;
            $product = $item;
            // return [$item, $haveTrans];
            // $variant = Variant::where('item_id', $id)->get();
            $item->delete();
            deleteCartItem::dispatch($product->id);
            jbmarketsyncDelete::dispatch($product);
            // Variant::where('item_id', $id)->delete();
            $data["success"] = true;
            $data["code"] = 200;
            $data["message"] = "berhasil di hapus";
            $data["data"] =  ["deletedRow" => 1, "product_id" => $id];
            return $data;
        } catch (\Throwable $th) {
            $data["data"] = ["deletedRow" => 0, "product_id" => $id];
            $data["success"] = false;
            $data["code"] = 500;
            $data["message"] = $th->getMessage();
            return $data;
        } finally {
            if (!$dontHaveTokopediaId) {

                try {
                    // helper::deleteTokopedia($item["tokopedia_id"]);
                    deleteTokopedia::dispatch($item["tokopedia_id"]);
                } catch (\Throwable $th) {
                    // return $data;
                }
            }
        }
    }
    public function newDetailProduct($id)
    {
        //frontend says
        //need detail store
        //need product variation
        //related product
        //in one fkn API because he can't do a fucking asynchronous
        try {
            $product = item::where("id", $id)->with("Variant", "Store")->first();
            if (!$product) {
                return helper::resp(false, "get", "Produk tidak ditemukan!", [], 400);
            }
            $related = item::where("category_id", $product->category_id)->where("is_shown", 1)->where('id', '!=', $id)->limit(6)->get();
            $review = review::where('item_id', $id)->with("profile")->orderBy('id', 'desc')->limit(5)->get();
            $product["review_new"] = $review;
            $product["related"] = $related;
            return helper::resp(true, "get", "berhasil mendapatkan detail produk", $product);
        } catch (\Throwable $th) {
            return helper::resp(false, "get", $th->getMessage(), ["id" => $id], 500);
        }
    }

    public function getBestSellingItems()
    {
        try {
            $item = item::where("is_shown", 1)->where('blocked', false)->where("service", "jbmarket")->limit(6)->orderBy("sold", "desc")->get();
            return helper::resp(true, "get", "success", $item);
        } catch (\Throwable $th) {
            return helper::resp(false, "get", $th->getMessage(), [], 500);
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
