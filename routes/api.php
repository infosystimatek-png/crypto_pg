<?php

use App\Http\Controllers\Api\V1\BalanceController;
use App\Http\Controllers\Api\V1\PaymentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['merchant.api', 'throttle:merchant-api'])->group(function () {
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::get('/payments/{paymentId}', [PaymentController::class, 'show']);
    Route::get('/balances', BalanceController::class);
});
