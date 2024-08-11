<?php

use App\Http\Controllers\Api\AccountController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommonController;
use App\Http\Controllers\Api\TransactionController;

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
});
Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::prefix('/common')->group(function () {
        Route::get('account-groups', [CommonController::class, 'getAccountGroups']);
        Route::get('transaction-types', [CommonController::class, 'getTransactionTypes']);
    });
    Route::resource('accounts', AccountController::class);
    Route::resource('transactions', TransactionController::class);
});
