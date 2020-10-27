<?php

use App\Http\Controllers\APIController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::post('/login', [AuthController::class, 'login']);

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/users', function (Request $request) {
        return $request->user();
    });
    Route::get('/list_client_lowest_total', [APIController::class, 'listClientsLowestTotal']);
    Route::get('/list_clients_biggest_buy', [APIController::class, 'listClientsBiggestBuy']);
    Route::get('/list_clients_most_buys', [APIController::class, 'listClientsMostBuys']);
    Route::post('/recommend_clothes', [APIController::class, 'recommendClothes']);
});
