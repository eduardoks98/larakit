<?php

use Illuminate\Support\Facades\Route;
use Eduardoks98\AdsGoogle\Http\Controllers\AdMobCallbackController;
use Eduardoks98\AdsGoogle\Http\Middleware\VerifyAdMobSignature;

$callbackPath = config('ads-google.ssv.callback_path', '/api/ads/admob/callback');

// Remove leading /api if present (Laravel will add it)
$path = preg_replace('#^/api#', '', $callbackPath);

Route::middleware(['api', VerifyAdMobSignature::class])
    ->prefix('api')
    ->group(function () use ($path) {
        Route::get($path, [AdMobCallbackController::class, 'handle'])
            ->name('admob.callback');
    });
