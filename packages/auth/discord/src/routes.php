<?php

use Eduardoks98\DiscordAuth\Http\Controllers\DiscordAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Discord Auth Routes
|--------------------------------------------------------------------------
|
| These routes handle Discord OAuth 2.0 authentication flow.
|
*/

Route::prefix('api/auth/discord')->group(function () {
    // Public routes - OAuth flow
    Route::get('/redirect', [DiscordAuthController::class, 'redirect'])
        ->name('discord.auth.redirect');

    Route::get('/callback', [DiscordAuthController::class, 'callback'])
        ->name('discord.auth.callback');

    // Protected routes - require authentication
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/profile', [DiscordAuthController::class, 'profile'])
            ->name('discord.auth.profile');

        Route::post('/refresh', [DiscordAuthController::class, 'refresh'])
            ->name('discord.auth.refresh');

        Route::delete('/disconnect', [DiscordAuthController::class, 'disconnect'])
            ->name('discord.auth.disconnect');
    });
});
