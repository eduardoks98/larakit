<?php

use Illuminate\Support\Facades\Route;
use Eduardoks98\AdsApplovin\Http\Controllers\MaxCallbackController;

$callbackPath = config('ads-applovin.s2s.callback_path', '/api/ads/applovin/callback');

// Remove leading /api if present
$path = preg_replace('#^/api#', '', $callbackPath);

Route::middleware(['api'])
    ->prefix('api')
    ->group(function () use ($path) {
        Route::get($path, [MaxCallbackController::class, 'handle'])
            ->name('applovin.callback');
    });
