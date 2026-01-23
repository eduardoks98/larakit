<?php

use Eduardoks98\GoogleAuth\Http\Controllers\GoogleAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Google Auth Routes
|--------------------------------------------------------------------------
|
| These routes handle Google OAuth 2.0 authentication flow.
|
*/

Route::prefix('api/auth/google')->group(function () {
    // Public routes - OAuth flow
    Route::get('/redirect', [GoogleAuthController::class, 'redirect'])
        ->name('google.auth.redirect');

    Route::get('/callback', [GoogleAuthController::class, 'callback'])
        ->name('google.auth.callback');

    // Protected routes - require authentication
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/profile', [GoogleAuthController::class, 'profile'])
            ->name('google.auth.profile');

        Route::post('/refresh', [GoogleAuthController::class, 'refresh'])
            ->name('google.auth.refresh');

        Route::delete('/revoke', [GoogleAuthController::class, 'revoke'])
            ->name('google.auth.revoke');
    });
});
