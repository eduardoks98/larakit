<?php

use Illuminate\Support\Facades\Route;
use Eduardoks98\MicrosoftAuth\Http\Controllers\MicrosoftAuthController;

/*
|--------------------------------------------------------------------------
| Microsoft Auth API Routes
|--------------------------------------------------------------------------
|
| OAuth 2.0 routes for Microsoft/Azure AD authentication.
|
*/

Route::prefix('api/auth/microsoft')->group(function () {
    // OAuth flow
    Route::get('redirect', [MicrosoftAuthController::class, 'redirect'])
        ->name('microsoft.redirect');

    Route::get('callback', [MicrosoftAuthController::class, 'callback'])
        ->name('microsoft.callback');

    // Protected routes (require Sanctum authentication)
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('me', [MicrosoftAuthController::class, 'me'])
            ->name('microsoft.me');

        Route::post('refresh', [MicrosoftAuthController::class, 'refresh'])
            ->name('microsoft.refresh');

        Route::post('unlink', [MicrosoftAuthController::class, 'unlink'])
            ->name('microsoft.unlink');
    });
});
