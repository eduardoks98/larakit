<?php

namespace Eduardoks98\DiscordAuth\Traits;

use Eduardoks98\DiscordAuth\Models\DiscordUser;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait HasDiscordAuth
{
    /**
     * Get the user's Discord account.
     */
    public function discordUser(): HasOne
    {
        return $this->hasOne(DiscordUser::class);
    }

    /**
     * Check if the user has linked a Discord account.
     */
    public function hasDiscordAccount(): bool
    {
        return $this->discordUser()->exists();
    }

    /**
     * Get the user's Discord avatar URL.
     */
    public function getDiscordAvatar(): ?string
    {
        return $this->discordUser?->avatar;
    }

    /**
     * Get the user's Discord display name.
     */
    public function getDiscordDisplayName(): ?string
    {
        return $this->discordUser?->getDisplayName();
    }

    /**
     * Get the user's Discord username.
     */
    public function getDiscordUsername(): ?string
    {
        return $this->discordUser?->username;
    }

    /**
     * Check if the user's Discord access token is expired.
     */
    public function isDiscordTokenExpired(): bool
    {
        if (! $this->discordUser) {
            return true;
        }

        return $this->discordUser->isTokenExpired();
    }
}
