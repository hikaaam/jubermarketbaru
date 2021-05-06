<?php

namespace App\Http\Controllers;

use App\Models\chat;
use App\Models\profile;
use App\Models\store;
use Error;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {

            // DB::enableQueryLog();
            // $chat = chat::with('store', 'user', 'store_user')->paginate(10);
            // perfomance = 1800ms one record test & online DB : postgresql

            $chat = chat::select(
                'store.store_name',
                'profile.name as user_name',
                'user.name as store_user_name',
                'user.token as store_token',
                'profile.token as user_token',
                'chat.*'
            )->join("store", 'store.id', '=', 'chat.store_id')
                ->join("profile", 'profile.id', '=', 'chat.user_id')
                ->join("profile as user", 'user.id', '=', 'chat.store_user_id')->paginate(10);
            // perfomance = 1800ms one record test & online DB : postgresql
            // return DB::getQueryLog();

            return helper::resp(true, "get", "berhasil mendapatkan chat", $chat);
        } catch (\Throwable $th) {
            return helper::resp(false, "get", $th->getMessage(), []);
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
        try {
            $request = json_decode($request->payload, true);
            $dataTable = [];
            $dataTable = helper::addData("user_id", "user_id", $request, $dataTable);
            $dataTable = helper::addData("store_id", "store_id", $request, $dataTable);

            $getOldChat = chat::where("user_id", $dataTable["user_id"])->where("store_id", $dataTable["store_id"])->get();
            if (count($getOldChat) >= 1) {
                $msg = "Anda sudah pernah membuat chat history";
                return helper::resp(false, "store", $msg, $getOldChat);
            }
            $user = profile::find($dataTable["user_id"]);
            if (!$user) {
                throw new Error("User tidak ditemukan");
            }
            $store = store::find($dataTable["store_id"]);
            if (!$store) {
                throw new Error("Toko tidak ditemukan");
            }
            $store_user = profile::where("idrs", $store->idrs)->first();
            if ($store_user) {
                $dataTable["store_user_id"] = $store_user->id;
            }
            $dataTable["user_idrs"] = $user->idrs;

            $chat = chat::create($dataTable);
            return helper::resp(true, "store", "berhasil mendapatkan chat", $chat);
        } catch (\Throwable $th) {
            return helper::resp(false, "store", $th->getMessage(), []);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\chat  $chat
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            return response("i am speed", 202);
            // DB::enableQueryLog();
            $chat = chat::select(
                'store.store_name',
                'profile.name as user_name',
                'user.name as store_user_name',
                'user.token as store_token',
                'profile.token as user_token',
                'chat.*'
            )->join("store", 'store.id', '=', 'chat.store_id')
                ->join("profile", 'profile.id', '=', 'chat.user_id')
                ->join("profile as user", 'user.id', '=', 'chat.store_user_id')->where("chat.id", $id)->first(); //performance == 700ms one record test
            // return DB::getQueryLog();
            if (!$chat) {
                throw new Error("Chat tidak ditemukan");
            }
            return helper::resp(true, "get", "berhasil mendapatkan chat", $chat);
        } catch (\Throwable $th) {
            return helper::resp(false, "get", $th->getMessage(), []);
        } finally {
            $i = 1;
            $limit = 111111111;
            while ($i <= $limit) {
                $i++;
                if ($i == $limit) {
                    // return "it does take long idiot";
                }
            }
        }
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\chat  $chat
     * @return \Illuminate\Http\Response
     */
    public function edit(chat $chat)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\chat  $chat
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, chat $chat)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\chat  $chat
     * @return \Illuminate\Http\Response
     */
    public function destroy(chat $chat)
    {
        //
    }
}
