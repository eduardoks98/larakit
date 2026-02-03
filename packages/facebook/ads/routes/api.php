<?php

use Illuminate\Support\Facades\Route;
use Eduardoks98\AdsFacebook\Http\Controllers\FanRewardController;

$callbackPath = config('ads-facebook.rewards.callback_path', '/api/ads/facebook/reward');

// Remove leading /api if present
$path = preg_replace('#^/api#', '', $callbackPath);

$middleware = ['api'];

// Add auth middleware if required
if (config('ads-facebook.rewards.require_auth', true)) {
    $middleware[] = 'auth:sanctum';
}

Route::middleware($middleware)
    ->prefix('api')
    ->group(function () use ($path) {
        Route::post($path, [FanRewardController::class, 'handle'])
            ->name('facebook.reward');

        Route::get($path . '/transaction-id', [FanRewardController::class, 'generateTransactionId'])
            ->name('facebook.transaction-id');
    });
