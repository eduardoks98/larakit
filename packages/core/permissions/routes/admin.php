<?php

use Illuminate\Support\Facades\Route;
use Eduardoks98\Permissions\Http\Controllers\ProfileController;

Route::middleware(config('permissions.admin_middleware', ['web', 'auth']))
    ->prefix(config('permissions.admin_prefix', 'admin'))
    ->name('admin.')
    ->group(function () {
        Route::resource('profiles', ProfileController::class);
    });
