<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\Merchant\DashboardController as MerchantDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/health', HealthController::class)->name('health');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', MerchantDashboardController::class)->name('dashboard');
    Route::get('/payments', [MerchantDashboardController::class, 'payments'])->middleware('merchant')->name('merchant.payments');
    Route::get('/payments/{payment}', [MerchantDashboardController::class, 'showPayment'])->middleware('merchant')->name('merchant.payments.show');
    Route::get('/ledger', [MerchantDashboardController::class, 'ledger'])->middleware('merchant')->name('merchant.ledger');

    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/merchants', [AdminDashboardController::class, 'merchants'])->name('admin.merchants');
        Route::post('/merchants', [AdminDashboardController::class, 'storeMerchant'])->name('admin.merchants.store');
        Route::get('/merchants/{merchant}', [AdminDashboardController::class, 'showMerchant'])->name('admin.merchants.show');
        Route::post('/merchants/{merchant}/adjust', [AdminDashboardController::class, 'adjust'])->name('admin.merchants.adjust');
        Route::get('/payments', [AdminDashboardController::class, 'payments'])->name('admin.payments');
        Route::get('/payments/{payment}', [AdminDashboardController::class, 'showPayment'])->name('admin.payments.show');
        Route::get('/transactions', [AdminDashboardController::class, 'transactions'])->name('admin.transactions');
        Route::get('/ledger', [AdminDashboardController::class, 'ledger'])->name('admin.ledger');
        Route::get('/webhooks', [AdminDashboardController::class, 'webhooks'])->name('admin.webhooks');
        Route::post('/webhooks/{delivery}/retry', [AdminDashboardController::class, 'retryWebhook'])->name('admin.webhooks.retry');
        Route::match(['get', 'post'], '/reconciliation', [AdminDashboardController::class, 'reconciliation'])->name('admin.reconciliation');
        Route::get('/reconciliation/{run}', [AdminDashboardController::class, 'showReconciliation'])->name('admin.reconciliation.show');
    });
});
