<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\OrderController;

Route::post('login', [AuthController::class, 'login']);
Route::middleware('auth:api')->group(function () {
    Route::get('/validate', [AuthController::class, 'validateToken']);
    Route::get('/refresh', [AuthController::class, 'refresh']);
    Route::get('user', function (Request $request) {
        return $request->user();
    });
});

Route::middleware('auth:api')->group(function () {
    Route::post('/wallet', [WalletController::class, 'store']);
    Route::get('/wallet/balance', [WalletController::class, 'balance']);
    Route::post('/wallet/transfer', [WalletController::class, 'transfer']);
});

Route::middleware('auth:api')->group(function () {
    Route::post('/order/initiate', [OrderController::class, 'initiate']);
    Route::get('/order/status/{order}', [OrderController::class, 'status']);
});


