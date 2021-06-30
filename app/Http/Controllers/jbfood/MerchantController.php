<?php

namespace App\Http\Controllers\jbfood;

use Illuminate\Http\Request;
use App\Models\jbfood\Dokumen;
use App\Helpers\RequestChecker;
use App\Models\jbfood\Merchant;
use App\Models\jbfood\Transaksi;
use App\Models\jbfood\Appsjbfood;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\jbfood\RestoReview;

class MerchantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $merchant = Merchant::all();
            return ResponseFormatter::success($merchant, 'Data Berhasil Diambil');
        } catch (\Throwable $th) {
            return ResponseFormatter::error([], $th->getMessage(), 500);
        }
    }

    public function byidrs($idrs)
    {
        try {
            $merchant = Merchant::where('kode_agen', $idrs)->get();
            return ResponseFormatter::success($merchant, 'Data Berhasil Diambil');
        } catch (\Throwable $th) {
            return ResponseFormatter::error([], $th->getMessage(), 500);
        }
    }

    public function updatestatus(Request $request)
    {
        try {
            if ($request->has('payload')) {
                $payload = json_decode(html_entity_decode($request->payload), true);
            } else {
                return ResponseFormatter::error([], 'Payload kosong!', 500);
            }
            $idrs = $payload['merchantid'];
            $status = $payload['status'];
            $merchant = Merchant::where('id', $idrs);
            $merchant->update(['status' => $status]);
            $merchant = $merchant->get()->first();
            return ResponseFormatter::success(["status" => $merchant->status], 'Status toko ' . $merchant->nama . ' berhasil diubah : ' . $merchant->status);
        } catch (\Throwable $th) {
            return ResponseFormatter::error([], $th->getMessage(), 500);
        }
    }

    public function updatepajak(Request $request)
    {
        try {
            if ($request->has('payload')) {
                $payload = json_decode(html_entity_decode($request->payload), true);
            } else {
                return ResponseFormatter::error([], 'Payload kosong!', 500);
            }
            $mcid = $payload['merchantid'];
            $pajak = $payload['pajak'];
            $merchant = Merchant::where('id', $mcid)->update(['pajak' => $pajak]);
            return ResponseFormatter::success($merchant, 'Pajak toko (' . $mcid . ') diubah : ' . $pajak . '%');
        } catch (\Throwable $th) {
            return ResponseFormatter::error([], $th->getMessage(), 500);
        }
    }

    public function npwpbykodeagen($id)
    {
        try {
            $merchant = Dokumen::where('jenisdok', '08')->where('kodeagen', $id)->get();
            return ResponseFormatter::success($merchant, 'Data Berhasil Diambil');
        } catch (\Throwable $th) {
            return ResponseFormatter::error([], $th->getMessage(), 500);
        }
    }

    public function getfeaturedmerchant()
    {
        try {
            $featuredRule = Appsjbfood::where('idapps', 'featuredmclimit')->get()->first();
            $limitData =  intval($featuredRule->value);

            $superPartner = Merchant::whereNotNull('super_partner')->get()->sortBy('super_partner')->toArray();
            $jmlSuperPartner = count($superPartner);

            $data = [];
            $data["limit"] = $limitData;
            $data["super_partner"] = $jmlSuperPartner;

            $featured = $superPartner;
            if ($jmlSuperPartner < $limitData) {
                $sisaData = [];
                $jmlTopTrx = 0;
                $jmlFiveStar = 0;

                $mcFiveStar = Merchant::where('star', '5')->whereNull('super_partner')->get();
                $topFiveArr = $mcFiveStar->toArray();

                if (count($topFiveArr) > 0) {
                    $jmlKurang = $limitData - $jmlSuperPartner;
                    if ($jmlKurang > count($topFiveArr)) {
                        $jmlKurang = count($topFiveArr);
                    }

                    $minTrx  = Appsjbfood::where('idapps', 'trxtopfive')->get()->first();
                    $minTrx = $minTrx->value;

                    for ($i = 0; $i < $jmlKurang; $i++) {
                        $id = $mcFiveStar[$i]->id;
                        $trx = Transaksi::where('merchant', $id)->get();

                        $jmlTrx = count($trx);
                        if ($jmlTrx >= $minTrx) {
                            array_push($featured, $topFiveArr[$i]);
                            $jmlTopTrx += 1;
                        } else {
                            array_push($sisaData, $topFiveArr[$i]);
                            $jmlFiveStar += 1;
                        }
                    }
                    $data["regular_top_trx"] = $jmlTopTrx;
                    $data["regular_five_star"] = $jmlFiveStar;
                }

                if (count($featured) < $limitData && count($sisaData) > 0) {
                    for ($i = 0; $i < count($sisaData); $i++) {
                        array_push($featured, $sisaData[$i]);
                    }
                }

                if (count($featured) < $limitData) {
                    $excludeList = [];
                    $temp = array_values($featured);
                    for ($i = 0; $i < count($featured); $i++) {
                        $idex = $temp[$i]["id"];
                        array_push($excludeList, $idex);
                    }

                    $sisaKurang = $limitData - count($featured);
                    $regular = Merchant::inRandomOrder()->whereNotIn('id', $excludeList)->limit($sisaKurang)->get();
                    $regularArr = $regular->toArray();
                    if (count($regularArr) > 0) {
                        $jmlRegular = 0;
                        for ($i = 0; $i < count($regularArr); $i++) {
                            array_push($featured, $regularArr[$i]);
                            $jmlRegular += 1;
                        }
                        $data["random_regular"] = $jmlRegular;
                    }
                }
            } else {
                $featured = array_slice($featured, 0, 5);
            }

            if (count($featured) > 0) {
                $dataTopFive = array_values($featured);
            }

            $data["top_five"] = count($featured);
            $data["data_topfive"] = $dataTopFive;
            return ResponseFormatter::success($data, 'Data Berhasil Diambil');
        } catch (\Throwable $th) {
            return ResponseFormatter::error([], $th->getMessage(), 500);
        }
    }

    public function counttrx($id)
    {
        try {
            $datamc = Merchant::where('id', $id)->orWhere('nama', 'like', '%' . $id . '%')->get(['id', 'nama', 'star', 'super_partner'])->first();
            $jmlTrx = Transaksi::where('merchant', $datamc->id)->get();
            $jmlTrx = count($jmlTrx);

            return ResponseFormatter::success(["jmlTrx" => $jmlTrx, "merchant" => $datamc], 'Sukses');
        } catch (\Throwable $th) {
            return ResponseFormatter::error([], $th->getMessage(), 500);
        }
    }

    public function superpartner()
    {
        try {
            $data = Merchant::whereNotNull('super_partner')->get()->sortBy('super_partner');
            return ResponseFormatter::success(["superpartner" => count($data), "data" => $data], 'Sukses');
        } catch (\Throwable $th) {
            return ResponseFormatter::error([], $th->getMessage(), 500);
        }
    }

    public function fivestar()
    {
        try {
            $data = Merchant::where('star', '5')->get()->sortBy('star');
            return ResponseFormatter::success(["fivestar" => count($data), "data" => $data], 'Sukses');
        } catch (\Throwable $th) {
            return ResponseFormatter::error([], $th->getMessage(), 500);
        }
    }

    public function searchbyname(Request $request)
    {
        try {
            if ($request->has('payload')) {
                $payload = json_decode(html_entity_decode($request->payload), true);
            } else {
                return ResponseFormatter::error([], 'Payload kosong!', 200);
            }
            $query = $payload["query"];
            $data = Merchant::where('nama', 'like', '%' . $query . '%')->get()->sortBy('nama', SORT_NATURAL)->toArray();
            $data = array_values($data);
            return ResponseFormatter::success(["found" => count($data), "data" => $data], 'Sukses');
        } catch (\Throwable $th) {
            return ResponseFormatter::error([], $th->getMessage(), 500);
        }
    }

    public function gettoptrxmc(Request $request)
    {
        try {
            if ($request->has('payload')) {
                $payload = json_decode(html_entity_decode($request->payload), true);
            } else {
                return ResponseFormatter::error([], 'Payload kosong!', 200);
            }
            $limit = $payload["limit"];
            $query =    'select DISTINCT t1.merchant as id, t2.nama as nama, COUNT(t1.merchant) as jml_trx from transaksi as 
                        t1 inner join merchant as t2 on t1.merchant = t2.id group by nama, t1.merchant order by jml_trx desc';
            if ($limit > 0) {
                $query = $query . ' limit ' . $limit;
            }
            $topTrx = DB::connection('mysql')->select($query);
            return ResponseFormatter::success($topTrx, 'Sukses');
        } catch (\Throwable $th) {
            return ResponseFormatter::error([], $th->getMessage(), 500);
        }
    }

    public function gettoptrxmcwithlimit(Request $request)
    {
        try {
            if ($request->has('payload')) {
                $payload = json_decode(html_entity_decode($request->payload), true);
            } else {
                return ResponseFormatter::error([], 'Payload kosong!', 200);
            }
            $limit = $payload["limit"];
            $minTrx  = Appsjbfood::where('idapps', 'trxtopfive')->get()->first();
            $minTrx = $minTrx->value;
            $query =    'select DISTINCT t1.merchant as id, t2.nama as nama, COUNT(t1.merchant) as jml_trx from transaksi as 
                        t1 inner join merchant as t2 on t1.merchant = t2.id group by nama, t1.merchant having jml_trx > ' . $minTrx . ' order by jml_trx desc';
            if ($limit > 0) {
                $query = $query . ' limit ' . $limit;
            }
            $topTrx = DB::connection('mysql')->select($query);
            return ResponseFormatter::success($topTrx, 'Sukses');
        } catch (\Throwable $th) {
            return ResponseFormatter::error([], $th->getMessage(), 500);
        }
    }

    public function getreview($id)
    {
        try {
            $data = RestoReview::where('idmerchant', $id)->get();

            $query = 'select AVG(star) as jml from restoreview where idmerchant = ' . $id;
            $rating = DB::connection('mysql')->select($query);
            $rating = number_format($rating[0]->jml, 1);
            return ResponseFormatter::success(["rating" => $rating, "data" => $data], 'Sukses');
        } catch (\Throwable $th) {
            return ResponseFormatter::error([], $th->getMessage(), 500);
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
    public function update(Request $request)
    {
        try {
            if ($request->has('payload')) {
                $payload = json_decode(html_entity_decode($request->payload), true);
            } else {
                return ResponseFormatter::error([], 'Payload kosong!', 200);
            }
            $array = $payload;
            $dataTable = [];
            if (array_key_exists("koordinat", $array)) {
                $koordinat = $array["koordinat"];
                $koordinat = explode("#", $koordinat);
                if (count($koordinat) == 2) {
                    $dataTable["lat"] = $koordinat[0];
                    $dataTable["lon"] = $koordinat[1];
                }
            }
            $dataTable = RequestChecker::checkArrayifexist('id', 'id', $array, $dataTable);
            $dataTable = RequestChecker::checkArrayifexist('telp', 'telp', $array, $dataTable);
            $dataTable = RequestChecker::checkArrayifexist('nama', 'nama', $array, $dataTable);
            $dataTable = RequestChecker::checkArrayifexist('alamat', 'alamat', $array, $dataTable);
            $dataTable = RequestChecker::checkArrayifexist('jambuka', 'jambuka', $array, $dataTable);
            $dataTable = RequestChecker::checkArrayifexist('jamtutup', 'jamtutup', $array, $dataTable);
            $dataTable = RequestChecker::checkArrayifexist('prov', 'prov', $array, $dataTable);
            $dataTable = RequestChecker::checkArrayifexist('kota', 'kota', $array, $dataTable);
            $dataTable = RequestChecker::checkArrayifexist('kec', 'kec', $array, $dataTable);
            $dataTable = RequestChecker::checkArrayifexist('kodepos', 'kodepos', $array, $dataTable);
            $dataTable = RequestChecker::checkArrayifexist('rincian', 'rincian', $array, $dataTable);
            $dataTable = RequestChecker::checkArrayifexist('gambar', 'img', $array, $dataTable);
            $merchant = Merchant::findOrFail($dataTable["id"]);
            $merchant->update($dataTable);
            return ResponseFormatter::success($merchant, "Perubahan berhasil disimpan");
        } catch (\Throwable $th) {
            return ResponseFormatter::error([], $th->getMessage(), 200);
        }
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
