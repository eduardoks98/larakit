<?php

use Illuminate\Support\Facades\Route;
use Eduardoks98\PaymentStripe\Http\Controllers\PaymentController;
use Eduardoks98\PaymentStripe\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| Stripe API Routes
|--------------------------------------------------------------------------
|
| These routes provide API endpoints for Stripe payments, subscriptions,
| and webhook handling.
|
*/

Route::prefix('api/stripe')->group(function () {

    // Payment Intents
    Route::post('/payment-intents', [PaymentController::class, 'createPaymentIntent'])
        ->name('stripe.payment-intents.create');

    Route::get('/payment-intents/{id}', [PaymentController::class, 'getPaymentIntent'])
        ->name('stripe.payment-intents.show');

    Route::post('/payment-intents/{id}/confirm', [PaymentController::class, 'confirmPaymentIntent'])
        ->name('stripe.payment-intents.confirm');

    Route::post('/payment-intents/{id}/cancel', [PaymentController::class, 'cancelPaymentIntent'])
        ->name('stripe.payment-intents.cancel');

    Route::post('/payment-intents/{id}/capture', [PaymentController::class, 'capturePaymentIntent'])
        ->name('stripe.payment-intents.capture');

    // Customers
    Route::post('/customers', [PaymentController::class, 'createCustomer'])
        ->name('stripe.customers.create');

    // Subscriptions
    Route::post('/subscriptions', [PaymentController::class, 'createSubscription'])
        ->name('stripe.subscriptions.create');

    Route::post('/subscriptions/{id}/cancel', [PaymentController::class, 'cancelSubscription'])
        ->name('stripe.subscriptions.cancel');

    // Webhooks (must use middleware for signature verification)
    Route::post('/webhook', [WebhookController::class, 'handle'])
        ->middleware('stripe.webhook')
        ->name('stripe.webhook');
});
