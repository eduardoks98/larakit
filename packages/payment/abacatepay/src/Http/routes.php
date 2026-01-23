<?php

use Illuminate\Support\Facades\Route;
use Eduardoks98\PaymentAbacatePay\Http\Controllers\AbacatePayController;
use Eduardoks98\PaymentAbacatePay\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| AbacatePay Routes
|--------------------------------------------------------------------------
*/

Route::prefix('api/abacatepay')
    ->middleware(['api'])
    ->group(function () {
        // Billing routes
        Route::post('/billings', [AbacatePayController::class, 'createBilling'])
            ->name('abacatepay.billings.create');

        Route::get('/billings', [AbacatePayController::class, 'listBillings'])
            ->name('abacatepay.billings.list');

        Route::get('/billings/{billingId}', [AbacatePayController::class, 'getBilling'])
            ->name('abacatepay.billings.show');

        // Customer routes
        Route::post('/customers', [AbacatePayController::class, 'createCustomer'])
            ->name('abacatepay.customers.create');

        Route::get('/customers/{customerId}', [AbacatePayController::class, 'getCustomer'])
            ->name('abacatepay.customers.show');
    });

// Webhook route (with signature verification middleware)
Route::post('/webhooks/abacatepay', [WebhookController::class, 'handle'])
    ->middleware(['abacatepay.webhook'])
    ->name('abacatepay.webhook');
