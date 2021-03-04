<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use App\Models\category;
use App\Models\ref_cat;
use DateTime;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $app_id = 14523;
        $url = "https://fs.tokopedia.net/inventory/v1/fs/$app_id/product/category";
        $token = "c:vYRluIJoR0a0I1cnlItwyg";
        $start_time =  new \DateTime('NOW');
           try {
                ref_cat::where('id','>',0)->delete();
                $response = http::withToken($token)->get($url);
                $res_data = $response->json();
                $data = $res_data['data']['categories'];
                foreach ($data as $key => $value) {
                    $ref_name = $value['name'];
                    $ref_id = $value['id'];
                    if(array_key_exists("child",$value)){
                        $child = $value['child'];
                        $ref_cat = ["name"=>$ref_name,"id"=>$ref_id];
                        ref_cat::create($ref_cat);
                        foreach ($child as $child_key => $child_value) {
                            $child_name = $child_value['name'];
                            $child_id = $child_value['id'];
                            $cat = ["name"=>$child_name,"id"=>$child_id,"ref_category"=>$ref_id];
                            category::create($cat);
                        }
                    }
                }
               
                $end_time = new \DateTime('NOW');
                return ["run time"=>($end_time-$start_time),"response"=>$response];
            } catch (\Throwable $th) {
                return ["start"=>$start_time,"end"=>$end_time,"msg"=>$th->getMessage()];
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
