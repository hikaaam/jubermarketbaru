<?php

namespace App\Http\Controllers\jbfood;

use Illuminate\Http\Request;
use App\Models\jbfood\Dokumen;
use App\Models\jbfood\Merchant;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;

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
            $merchant = Merchant::where('id', $idrs)->update(['status' => $status]);
            return ResponseFormatter::success($merchant, 'Status toko (' . $idrs . ') berhasil diubah : ' . $status);
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
