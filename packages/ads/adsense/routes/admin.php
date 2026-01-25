<?php

use Illuminate\Support\Facades\Route;
use Eduardoks98\AdsAdsense\Http\Controllers\Admin\AdUnitController;

Route::middleware(config('adsense.admin_middleware', ['web', 'auth']))
    ->prefix(config('adsense.admin_prefix', 'admin'))
    ->name('admin.')
    ->group(function () {
        Route::resource('ad-units', AdUnitController::class);
        Route::post('ad-units/{adUnit}/toggle', [AdUnitController::class, 'toggle'])->name('ad-units.toggle');
    });
