<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

class bridgeController extends Controller
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
        // $host = "http://127.0.0.1:8000/api/";
        // if (isset($_GET['product'])) {
        //     $url = $host . 'product?page=' . $_GET['product'];
        //     $request = Request::create($url, 'POST', []);
        //     $response = Route::dispatch($request);
        //     return $response;
        // }
        // if (isset($_GET['category'])) {
        // }
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
    }
    public function bridge(Request $request)
    {
        try {
            $host = "http://127.0.0.1:8001/api/";
            $url = $host . $request->key;
            if ($request->has("payload")) {
                $payload = json_decode(html_entity_decode($request->payload), true);
            }
            switch (strtoupper($request->method)) {
                case "POST":
                    return Route::dispatch(Request::create($url, 'POST', []));
                case "PUT":
                    $url .= '/' . $payload['id'];
                    return Route::dispatch(Request::create($url, 'PUT', []));
                case "DELETE":
                    $url .= '/' . $payload['id'];
                    return Route::dispatch(Request::create($url, 'DELETE', []));
                default:
                    if ($request->has("payload")) {
                        if (array_key_exists("id", $payload)) {
                            if ($payload["id"] == null) {
                                throw new Exception("Can't get ID with null");
                            }
                            $url .= "/" . $payload['id'];
                        } else if (array_key_exists("page", $payload)) {
                            if ($payload["page"] == null) {
                                throw new Exception("Can't get ID with null");
                            }
                            $url .= "?page=" . $payload['page'];
                            try {
                                $response = Request::create($url, 'GET');
                                return app()->handle($response);
                            } catch (\Throwable $th) {
                                return $th->getMessage();
                            }
                        } else {
                            throw new Exception("Route not found");
                        }
                    }
                    return Route::dispatch(Request::create($url, 'GET'));
            }
        } catch (\Throwable $th) {
            return [
                "data" => ["payload" => $payload ?? [], "key" => $request->key, "url" => $url],
                "success" => false,
                "code" => 404,
                "message" => $th->getMessage() == "" ? "Route not found" : $th->getMessage(),
            ];
        }
    }
    // public function bridge(Request $request)
    // {


    //     $host = "http://127.0.0.1:8000/api/";
    //     if ($request->has("payload")) {
    //         $bounds = html_entity_decode($request->payload);
    //         $payload = json_decode($bounds, true);
    //     }
    //     if (strtoupper($request->method) == "POST") {
    //         try {
    //             $url = $host . $request->key;
    //             $request = Request::create($url, 'POST', []);
    //             $response = Route::dispatch($request);
    //             return $response;
    //         } catch (\Throwable $th) {
    //             $data["data"] = [];
    //             $data["success"] = false;
    //             $data["code"] = 500;
    //             $data["message"] = $th->getMessage();
    //             return $data;
    //         }
    //     } else if (strtoupper($request->method) == "PUT") {
    //         try {
    //             // return $payload;
    //             $url = $host . $request->key . '/' . $payload['id'];
    //             $request = Request::create($url, 'PUT', []);
    //             $response = Route::dispatch($request);
    //             return $response;
    //         } catch (\Throwable $th) {
    //             $data["data"] = [];
    //             $data["success"] = false;
    //             $data["code"] = 500;
    //             $data["message"] = $th->getMessage();
    //             return $data;
    //         }
    //     } else if (strtoupper($request->method) == "DELETE") {
    //         try {
    //             $url = $host . $request->key . '/' . $payload['id'];
    //             $request = Request::create($url, 'DELETE', []);
    //             $response = Route::dispatch($request);
    //             return $response;
    //         } catch (\Throwable $th) {
    //             $data["data"] = [];
    //             $data["success"] = false;
    //             $data["code"] = 500;
    //             $data["message"] = $th->getMessage();
    //             return $data;
    //         }
    //     } else {
    //         $url = $host . $request->key;
    //         // return $url;
    //         try {
    //             if ($request->has("payload")) {
    //                 $url = $host . $request->key . "/" . $payload['id'];
    //             }
    //             // return $url;
    //             $request = Request::create($url, 'GET');
    //             $response = Route::dispatch($request);
    //             return $response;
    //         } catch (\Throwable $th) {
    //             $data["data"] = [];
    //             $data["success"] = false;
    //             $data["code"] = 500;
    //             $data["message"] = $th->getMessage();
    //             return $data;
    //         }
    //     }
    // }

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
