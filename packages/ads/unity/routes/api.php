<?php

use Illuminate\Support\Facades\Route;
use Eduardoks98\AdsUnity\Http\Controllers\UnityCallbackController;

$callbackPath = config('ads-unity.s2s.callback_path', '/api/ads/unity/callback');

// Remove leading /api if present
$path = preg_replace('#^/api#', '', $callbackPath);

Route::middleware(['api'])
    ->prefix('api')
    ->group(function () use ($path) {
        Route::get($path, [UnityCallbackController::class, 'handle'])
            ->name('unity.callback');
    });
