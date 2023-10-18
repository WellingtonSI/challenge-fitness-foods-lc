<?php

use App\Http\Controllers\ApiInfoController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TesteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::get('teste',[TesteController::class,'testePegarDados']);
Route::get('/',ApiInfoController::class);
Route::group(['prefix' => 'products'], function () {
    Route::get('',[ProductController::class,'list']);
    Route::get('/{code}',[ProductController::class,'show'])->where(['code' =>'[0-9]+']);
    Route::delete('/{code}',[ProductController::class,'destroy'])->where(['code' =>'[0-9]+']);
    Route::put('/{code}',[ProductController::class,'update'])->where(['code' =>'[0-9]+']);
});
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
