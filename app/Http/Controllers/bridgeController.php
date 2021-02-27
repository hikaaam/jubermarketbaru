<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class bridgeController extends Controller
{
    public $data = [
        "success"=>"true",
        "message"=>"Berhasil",
        "code"=>200,
        "data"=>[]
    ];
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $host = "http://127.0.0.1:8000/api/";
      if(isset($_GET['product'])){
        $url = $host.'product?page='.$_GET['product'];
        $request = Request::create($url, 'POST',[]);
        $response = Route::dispatch($request);
        return $response;
      }
      if(isset($_GET['category'])){

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
        return 'gak dipake';
        // try {
        //     $host = "http://127.0.0.1:8000/api/";
        //     if($request->has("payload")){
        //         $bounds = html_entity_decode($request->payload);
        //         $payload = json_decode($bounds,true);
        //     }
        //     if($request->method == "POST"){
        //         $url = $host.$request->key;
        //         $ch = curl_init();
        //         curl_setopt($ch, CURLOPT_URL, $url);
        //         curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        //         curl_setopt($ch, CURLOPT_POST, 1);
        //         curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($payload));
        //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //         $response  = curl_exec($ch);
        //         curl_close($ch);
        //         return $response;
        //     }else if($request->method == "PUT"){
        //         $url = $host.$request->key.'/'.$payload['id'];
        //         // return $url;
        //         $ch = curl_init();
        //         curl_setopt($ch, CURLOPT_URL, $url);
        //         curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen(json_encode($payload))));
        //         curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        //         curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($payload));
        //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //         $response  = curl_exec($ch);
        //         curl_close($ch);
        //         return $response;
        //     }else if($request->method == "DELETE"){
        //         $url = $host.$request->key.'/'.$payload['id'];
        //         // return $url;
        //         $ch = curl_init();
        //         curl_setopt($ch, CURLOPT_URL, $url);
        //         curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        //         curl_setopt($ch, CURLOPT_POSTFIELDS,json_encode($payload));
        //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //         $response  = curl_exec($ch);
        //         curl_close($ch);
        //         return $response;
        //     }else{
        //         $url = $host.$request->key;
        //         // return $url;
        //         if($request->has("payload")){
        //            $url = $host.$request->key."/".$payload['id'];
        //         }
        //         // return $url;
        //         $ch = curl_init();
        //         curl_setopt($ch, CURLOPT_URL, $url);
        //         curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        //         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //         $response = curl_exec($ch);
        //         curl_close($ch);
        //         return $response;
        //     }
        //     // CallAPI("POST","http://localhost/JuberMarketPlace/api/".$request->key,$data);
        //     // return $payload;
        // } catch (\Throwable $th) {
        //     $data["data"] = [];
        //     $data["success"] = false;
        //     $data["code"] = 500;
        //     $data["message"] = $th->getMessage();
        //     return $data;
        // }
    }

    public function bridge(Request $request){
        

            $host = "http://127.0.0.1:8000/api/";
            if($request->has("payload")){
                $bounds = html_entity_decode($request->payload);
                $payload = json_decode($bounds,true);
            }
            if(strtoupper($request->method) == "POST"){
                try{
                $url = $host.$request->key;
                $request = Request::create($url, 'POST',[]);
                $response = Route::dispatch($request);
                return $response;
            }  
            catch (\Throwable $th) {
                   $data["data"] = [];
                   $data["success"] = false;
                   $data["code"] = 500;
                   $data["message"] = $th->getMessage();
                   return $data;
               }
            }else if(strtoupper($request->method) == "PUT"){
                try{
                $url = $host.$request->key.'/'.$payload['id'];
                $request = Request::create($url, 'PUT', []);   
                $response = Route::dispatch($request);
                return $response;
            }  
            catch (\Throwable $th) {
                   $data["data"] = [];
                   $data["success"] = false;
                   $data["code"] = 500;
                   $data["message"] = $th->getMessage();
                   return $data;
               }
            }else if(strtoupper($request->method) == "DELETE"){
                try{
                $url = $host.$request->key.'/'.$payload['id'];
                $request = Request::create($url, 'DELETE', []);
                $response = Route::dispatch($request);
                return $response;
            }  
            catch (\Throwable $th) {
                   $data["data"] = [];
                   $data["success"] = false;
                   $data["code"] = 500;
                   $data["message"] = $th->getMessage();
                   return $data;
               }
            }else{
                $url = $host.$request->key;
                // return $url;
                try{
                if($request->has("payload")){
                   $url = $host.$request->key."/".$payload['id'];
                }
                // return $url;
                $request = Request::create($url, 'GET');
                $response = Route::dispatch($request);
                return $response;
            }  
             catch (\Throwable $th) {
                    $data["data"] = [];
                    $data["success"] = false;
                    $data["code"] = 500;
                    $data["message"] = $th->getMessage();
                    return $data;
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
