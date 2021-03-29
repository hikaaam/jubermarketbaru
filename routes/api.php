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
use App\Http\Controllers\StoreController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\DummyController;
use App\Http\Controllers\AlamatController;
use App\Http\Controllers\RefRekeningController;
use App\Http\Controllers\RekeningController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ReturnProblemController;
use App\Http\Controllers\TokopediaController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\TransHeadController;
use App\Http\Controllers\TransReturnController;
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
Route::get('search', [ItemController::class, 'index']);
Route::get('search/{id}', [ItemController::class, 'show']);
Route::post('search', [ItemController::class, 'store']);
Route::post('search/terlaris', [ItemController::class, 'terlaris']);
Route::post('search/harga', [ItemController::class, 'harga']);
Route::post('search/terbaru', [ItemController::class, 'terbaru']);
Route::put('search/{id}', [ItemController::class, 'update']);
Route::delete('search/{id}', [ItemController::class, 'destroy']);

Route::get('variant/{id}', [VariantController::class, 'show']);
Route::get('variant/show/{id}', [VariantController::class, 'showId']);
Route::post('variant', [VariantController::class, 'store']);
Route::put('variant/{id}', [VariantController::class, 'update']);
Route::delete('variant/{id}', [VariantController::class, 'destroy']);


Route::get('ref_cat', [RefCatController::class, 'index']);
Route::get('ref_cat/{id}', [RefCatController::class, 'show']);
Route::post('ref_cat', [RefCatController::class, 'store']);
Route::put('ref_cat/{id}', [RefCatController::class, 'update']);
Route::delete('ref_cat/{id}', [RefCatController::class, 'destroy']);

Route::get('product', [ProductController::class, 'index']);
Route::get('product/hidden', [ProductController::class, 'isNotShown']);
Route::get('product/visible', [ProductController::class, 'visible']);
Route::get('product/all', [ProductController::class, 'all']);
Route::get('product/{id}', [ProductController::class, 'show']);
Route::get('product/category/{id}', [ProductController::class, 'productbycat']);
Route::get('product/ref_cat/{id}', [ProductController::class, 'productByRefId']);
Route::get('product/store/{id}', [ProductController::class, 'productByStId']);
Route::get('product/store/hidden/{id}', [ProductController::class, 'productByStId_']);
Route::get('product/store/visible/{id}', [ProductController::class, 'productByStIdVisible']);
Route::post('product', [ProductController::class, 'store']);
Route::put('product/{id}', [ProductController::class, 'update']);
Route::put('product/is_shown/{id}', [ProductController::class, 'updateIsShown']);
Route::delete('product/{id}', [ProductController::class, 'destroy']);
Route::put('product/related/{id}', [ProductController::class, 'getRelatedProduct']);

Route::get('category', [CategoryController::class, 'index']);
// Route::get('product/all',[CategoryController::class, 'all']);
Route::get('category/{id}', [CategoryController::class, 'show']);
Route::get('category/store_id/{id}', [CategoryController::class, 'getbystoreid']);
Route::post('category', [CategoryController::class, 'store']);
Route::put('category/{id}', [CategoryController::class, 'update']);
Route::delete('category/{id}', [CategoryController::class, 'destroy']);

Route::post('upload', [UploadController::class, 'store']);
Route::post('upload_video', [UploadController::class, 'video']);
Route::post('delete_image', [UploadController::class, 'deleteImg']);

Route::post('bridge_', [bridgeController::class, 'store']);
Route::post('bridge', [bridgeController::class, 'bridge']);
Route::get('bridge', [bridgeController::class, 'index']);
Route::get('storage/{filename}', function ($filename) {
    return Image::make(storage_path() . '/app/images/' . $filename)->response();
});


//store
Route::get('store', [StoreController::class, 'index']);
Route::get('store/{id}', [StoreController::class, 'show']);
Route::get('store/owner/{id}', [StoreController::class, 'getByOwner']);
Route::get('store/idrs/{id}', [StoreController::class, 'getByIdrs']);
Route::post('store', [StoreController::class, 'store']);
Route::put('store/{id}', [StoreController::class, 'update']);
Route::delete('store/{id}', [StoreController::class, 'destroy']);
Route::get('store/last_active/{id}', [StoreController::class, 'lastActive']);

//area
Route::get('area', [AreaController::class, 'index']);
Route::get('area/{id}', [AreaController::class, 'show']);
Route::get('area/city', [AreaController::class, 'getCity']);
Route::get('area/district', [AreaController::class, 'getDistrict']);
Route::get('area/city/{id}', [AreaController::class, 'getCityById']);
Route::get('area/district/{id}', [AreaController::class, 'getDistrictById']);

Route::post('dummy_payment', [DummyController::class, 'store']);

Route::get('alamat', [AlamatController::class, 'index']);
Route::get('alamat/{id}', [AlamatController::class, 'show']);
Route::get('alamat/idrs/{id}', [AlamatController::class, 'getByIdrs']);
Route::post('alamat', [AlamatController::class, 'store']);
Route::put('alamat/{id}', [AlamatController::class, 'update']);
Route::delete('alamat/{id}', [AlamatController::class, 'destroy']);

Route::get('rekening', [RekeningController::class, 'index']);
Route::get('rekening/{id}', [RekeningController::class, 'show']);
Route::get('rekening/idrs/{id}', [RekeningController::class, 'getByIdrs']);
Route::post('rekening', [RekeningController::class, 'store']);
Route::put('rekening/{id}', [RekeningController::class, 'update']);
Route::delete('rekening/{id}', [RekeningController::class, 'destroy']);

Route::get('ref_rekening', [RefRekeningController::class, 'index']);
Route::get('ref_rekening/{id}', [RefRekeningController::class, 'show']);
Route::post('ref_rekening', [RefRekeningController::class, 'store']);
Route::put('ref_rekening/{id}', [RefRekeningController::class, 'update']);
Route::delete('ref_rekening/{id}', [RefRekeningController::class, 'destroy']);

Route::get('profile', [ProfileController::class, 'index']);
Route::get('profile/{id}', [ProfileController::class, 'show']);
Route::post('profile', [ProfileController::class, 'store']);
Route::put('profile/{id}', [ProfileController::class, 'update']);
Route::put('profile/update_token/idrs/{id}', [ProfileController::class, 'updateToken']);
Route::delete('profile/{id}', [ProfileController::class, 'destroy']);

Route::get('review', [ReviewController::class, 'index']);
Route::get('review/{id}', [ReviewController::class, 'show']);
Route::get('review/user_id/{id}', [ReviewController::class, 'getByUser']);
Route::get('review/item_id/{id}', [ReviewController::class, 'getByIdBarang']);
Route::get('review/store_id/{id}', [ReviewController::class, 'getByStore']);
Route::post('review', [ReviewController::class, 'store']);
Route::put('review/{id}', [ReviewController::class, 'update']);
Route::delete('review/{id}', [ReviewController::class, 'destroy']);

Route::get('cart', [CartController::class, 'index']);
Route::get('cart/{id}', [CartController::class, 'show']);
Route::post('cart', [CartController::class, 'store']);
Route::put('cart/{id}', [CartController::class, 'update']);
Route::delete('cart/{id}', [CartController::class, 'destroy']);

Route::get('tokopedia/sync', [TokopediaController::class, 'index']);
Route::get('schedule', [ScheduleController::class, 'index']);

//order or transactions
Route::get('order/{id}', [TransHeadController::class, 'show']);
Route::get('order/store/{id}', [TransHeadController::class, 'byStoreRecent']);
Route::get('order/store/past/{id}', [TransHeadController::class, 'byStorePast']);
Route::get('order/store/unreview/{id}', [TransHeadController::class, 'byStoreUnreviewed']);
Route::get('order/user/idrs/{id}', [TransHeadController::class, 'byUserRecent']);
Route::get('order/user/past/idrs/{id}', [TransHeadController::class, 'byUserPast']);
Route::get('order/user/unreview/idrs/{id}', [TransHeadController::class, 'byUserUnreviewed']);
Route::get('order/all/status', [TransHeadController::class, 'allOrderStatus']);
Route::get('seller/token/{id}', [TransHeadController::class, 'sellerToken']);
Route::get('order/update/packing/{id}', [TransHeadController::class, 'updatePacking']);
Route::put('order/update/sending/{id}', [TransHeadController::class, 'updateSending']);
Route::put('order/update/cancel/{id}', [TransHeadController::class, 'updateCancel']);
Route::get('order/update/accept/{id}', [TransHeadController::class, 'updateAccept']);
Route::put('order/return/{id}', [TransHeadController::class, 'returnOrder']);

// Route::post('ref_rekening', [RefRekeningController::class, 'store']);
// Route::put('ref_rekening/{id}', [RefRekeningController::class, 'update']);
// Route::delete('ref_rekening/{id}', [RefRekeningController::class, 'destroy']);


Route::get('problem', [ReturnProblemController::class, 'index']);
Route::get('problem/{id}', [ReturnProblemController::class, 'show']);
Route::post('problem', [ReturnProblemController::class, 'store']);
Route::put('problem/{id}', [ReturnProblemController::class, 'update']);
Route::delete('problem/{id}', [ReturnProblemController::class, 'destroy']);


//return order
Route::get('return', [TransReturnController::class, 'index']);
Route::get('return/{id}', [TransReturnController::class, 'show']);
Route::get('return/all/status', [TransReturnController::class, 'getAllStatus']);
Route::get('return/update/packing/{id}', [TransReturnController::class, 'updatePacking']);
Route::put('return/update/sending/{id}', [TransReturnController::class, 'updateSending']);
Route::put('return/update/cancel/{id}', [TransReturnController::class, 'updateCancel']);
Route::get('return/update/accept/{id}', [TransReturnController::class, 'updateAccept']);
Route::get('return/update/lapor_juber/{id}', [TransReturnController::class, 'updateJuber']);
Route::get('return/update/done/{id}', [TransReturnController::class, 'updatedone']);
