<?php

use Illuminate\Support\Facades\Route;
use Eduardoks98\FacebookAuth\Http\Controllers\FacebookAuthController;

/*
|--------------------------------------------------------------------------
| Facebook Auth API Routes
|--------------------------------------------------------------------------
|
| These routes provide API endpoints for Facebook OAuth2 authentication.
|
*/

Route::prefix('api/facebook-auth')->group(function () {

    // Redirect to Facebook for authentication
    Route::get('/redirect', [FacebookAuthController::class, 'redirect'])
        ->name('facebook-auth.redirect');

    // Handle Facebook OAuth callback
    Route::get('/callback', [FacebookAuthController::class, 'callback'])
        ->name('facebook-auth.callback');

    // Protected routes (require authentication)
    Route::middleware(['auth:sanctum'])->group(function () {

        // Get authenticated user's Facebook profile
        Route::get('/profile', [FacebookAuthController::class, 'profile'])
            ->name('facebook-auth.profile');

        // Disconnect Facebook account
        Route::delete('/disconnect', [FacebookAuthController::class, 'disconnect'])
            ->name('facebook-auth.disconnect');
    });
});
