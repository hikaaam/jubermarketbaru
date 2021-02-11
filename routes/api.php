<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\VariantController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\bridgeController;
use App\Http\Controllers\RefCatController;
use Intervention\Image\ImageManagerStatic as Image;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('search',[ItemController::class, 'index']);
Route::get('search/{id}',[ItemController::class, 'show']);
Route::post('search',[ItemController::class, 'store']);
Route::post('search/terlaris',[ItemController::class, 'terlaris']);
Route::post('search/harga',[ItemController::class, 'harga']);
Route::post('search/terbaru',[ItemController::class, 'terbaru']);
Route::put('search/{id}',[ItemController::class, 'update']);
Route::delete('search/{id}',[ItemController::class, 'destroy']);

Route::get('variant/{id}',[VariantController::class, 'show']);
Route::get('variant/show/{id}',[VariantController::class, 'showId']);
Route::post('variant',[VariantController::class, 'store']);
Route::put('variant/{id}',[VariantController::class, 'update']);
Route::delete('variant/{id}',[VariantController::class, 'destroy']);


Route::get('ref_cat',[RefCatController::class, 'index']);
Route::get('ref_cat/{id}',[RefCatController::class, 'show']);
Route::post('ref_cat',[RefCatController::class, 'store']);
Route::put('ref_cat/{id}',[RefCatController::class, 'update']);
Route::delete('ref_cat/{id}',[RefCatController::class, 'destroy']);

Route::get('product',[ProductController::class, 'index']);
Route::get('product/all',[ProductController::class, 'all']);
Route::get('product/{id}',[ProductController::class, 'show']);
Route::post('product',[ProductController::class, 'store']);
Route::put('product/{id}',[ProductController::class, 'update']);
Route::delete('product/{id}',[ProductController::class, 'destroy']);

Route::get('category',[CategoryController::class, 'index']);
// Route::get('product/all',[CategoryController::class, 'all']);
Route::get('category/{id}',[CategoryController::class, 'show']);
Route::get('category/store_id/{id}',[CategoryController::class, 'getbystoreid']);
Route::post('category',[CategoryController::class, 'store']);
Route::put('category/{id}',[CategoryController::class, 'update']);
Route::delete('category/{id}',[CategoryController::class, 'destroy']);

Route::post('upload',[UploadController::class, 'store']);
Route::post('upload_video',[UploadController::class, 'video']);

Route::post('bridge_',[bridgeController::class, 'store']);
Route::post('bridge',[bridgeController::class, 'bridge']);
Route::get('bridge',[bridgeController::class, 'index']);
Route::get('storage/{filename}', function ($filename) {
    return Image::make(storage_path() . '/app/images/' . $filename)->response();
});