<?php

use Illuminate\Support\Facades\Route;
use Eduardoks98\AdsAdsense\Http\Controllers\Admin\AdUnitController;

Route::middleware(config('adsense.admin_middleware', ['web', 'auth']))
    ->prefix(config('adsense.admin_prefix', 'admin'))
    ->name('admin.')
    ->group(function () {
        // O parametro gerado por padrao seria {ad_unit}, que nao casa com o arg
        // $adUnit do controller — sem match o implicit binding nao ocorre e o
        // Laravel injeta um model VAZIO. O controller tambem nao implementa show().
        Route::resource('ad-units', AdUnitController::class)
            ->except(['show'])
            ->parameters(['ad-units' => 'adUnit']);
        Route::post('ad-units/{adUnit}/toggle', [AdUnitController::class, 'toggle'])->name('ad-units.toggle');
    });
