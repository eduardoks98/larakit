<?php

namespace Eduardoks98\GoogleAuth\Traits;

use Eduardoks98\GoogleAuth\Models\GoogleUser;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait HasGoogleAuth
{
    /**
     * Get the user's Google account.
     */
    public function googleUser(): HasOne
    {
        return $this->hasOne(GoogleUser::class);
    }

    /**
     * Check if the user has linked a Google account.
     */
    public function hasGoogleAccount(): bool
    {
        return $this->googleUser()->exists();
    }

    /**
     * Get the user's Google profile picture.
     */
    public function getGooglePicture(): ?string
    {
        return $this->googleUser?->picture;
    }

    /**
     * Check if the user's Google access token is expired.
     */
    public function isGoogleTokenExpired(): bool
    {
        if (! $this->googleUser) {
            return true;
        }

        return $this->googleUser->isTokenExpired();
    }
}
