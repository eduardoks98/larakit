<?php

use Illuminate\Support\Facades\Route;
use Eduardoks98\AdsAdsense\Http\Controllers\AdUnitController;

$prefix = config('adsense.routes.prefix', 'api/ads');
$middleware = config('adsense.routes.middleware', ['api']);

Route::prefix($prefix)
    ->middleware($middleware)
    ->group(function () {
        // Public routes
        Route::get('/units', [AdUnitController::class, 'index']);
        Route::get('/units/grouped', [AdUnitController::class, 'grouped']);
        Route::get('/config', [AdUnitController::class, 'config']);

        // Protected routes (require authentication)
        Route::middleware(['auth:sanctum'])->group(function () {
            Route::get('/revenue', [AdUnitController::class, 'revenue']);
            Route::get('/revenue/summary', [AdUnitController::class, 'revenueSummary']);
        });
    });
