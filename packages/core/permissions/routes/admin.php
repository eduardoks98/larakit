<?php

use Illuminate\Support\Facades\Route;
use Eduardoks98\Permissions\Http\Controllers\ProfileController;

Route::middleware(config('permissions.admin_middleware', ['web', 'auth']))
    ->prefix(config('permissions.admin_prefix', 'admin'))
    ->name('admin.')
    ->group(function () {
        // O controller nao implementa show() — sem o except, GET profiles/{id}
        // gera rota que morre em "Call to undefined method ...::show()".
        Route::resource('profiles', ProfileController::class)->except(['show']);
    });
