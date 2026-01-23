<?php

use Illuminate\Support\Facades\Route;
use Eduardoks98\PaymentMercadoPago\Http\Controllers\PaymentController;
use Eduardoks98\PaymentMercadoPago\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| MercadoPago API Routes
|--------------------------------------------------------------------------
|
| These routes are automatically registered by the PaymentMercadoPago package.
| All routes are prefixed with 'api/mercadopago'.
|
*/

Route::prefix('api/mercadopago')->group(function () {

    // Payment routes
    Route::prefix('payments')->name('mercadopago.payments.')->group(function () {
        // Create payments
        Route::post('/pix', [PaymentController::class, 'createPix'])->name('pix.create');
        Route::post('/card', [PaymentController::class, 'createCard'])->name('card.create');
        Route::post('/boleto', [PaymentController::class, 'createBoleto'])->name('boleto.create');

        // Get payment details
        Route::get('/{identifier}', [PaymentController::class, 'show'])->name('show');

        // Refund payment
        Route::post('/{identifier}/refund', [PaymentController::class, 'refund'])->name('refund');
    });

    // Webhook routes
    Route::post('/webhook', [WebhookController::class, 'handle'])
        ->middleware('mercadopago.webhook')
        ->name('mercadopago.webhook');
});
