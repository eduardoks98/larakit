<?php

namespace Eduardoks98\DiscordAuth\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscordUser extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'discord_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'discord_id',
        'email',
        'username',
        'discriminator',
        'global_name',
        'avatar',
        'banner',
        'accent_color',
        'locale',
        'verified',
        'mfa_enabled',
        'premium_type',
        'flags',
        'access_token',
        'refresh_token',
        'expires_in',
        'token_type',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'verified' => 'boolean',
        'mfa_enabled' => 'boolean',
        'accent_color' => 'integer',
        'premium_type' => 'integer',
        'flags' => 'integer',
        'expires_in' => 'integer',
        'last_login_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the Discord account.
     */
    public function user(): BelongsTo
    {
        $userModel = config('discord-auth.user_model', 'App\\Models\\User');

        return $this->belongsTo($userModel);
    }

    /**
     * Check if the access token is expired.
     */
    public function isTokenExpired(): bool
    {
        if (! $this->expires_in || ! $this->updated_at) {
            return true;
        }

        $expiresAt = $this->updated_at->addSeconds($this->expires_in);

        return now()->greaterThan($expiresAt);
    }

    /**
     * Update the access token and related data.
     */
    public function updateToken(string $accessToken, ?string $refreshToken = null, ?int $expiresIn = null): self
    {
        $data = [
            'access_token' => $accessToken,
            'expires_in' => $expiresIn,
            'last_login_at' => now(),
        ];

        if ($refreshToken) {
            $data['refresh_token'] = $refreshToken;
        }

        $this->update($data);

        return $this;
    }

    /**
     * Update user profile data from Discord.
     */
    public function updateProfile(array $userData): self
    {
        $this->update([
            'email' => $userData['email'] ?? $this->email,
            'username' => $userData['username'] ?? $this->username,
            'discriminator' => $userData['discriminator'] ?? $this->discriminator,
            'global_name' => $userData['global_name'] ?? $this->global_name,
            'avatar' => $userData['avatar'] ?? $this->avatar,
            'banner' => $userData['banner'] ?? $this->banner,
            'accent_color' => $userData['accent_color'] ?? $this->accent_color,
            'locale' => $userData['locale'] ?? $this->locale,
            'verified' => $userData['verified'] ?? $this->verified,
            'mfa_enabled' => $userData['mfa_enabled'] ?? $this->mfa_enabled,
            'premium_type' => $userData['premium_type'] ?? $this->premium_type,
            'flags' => $userData['flags'] ?? $this->flags,
        ]);

        return $this;
    }

    /**
     * Get the display name for the Discord user.
     */
    public function getDisplayName(): string
    {
        return $this->global_name ?? $this->username ?? 'Discord User';
    }

    /**
     * Get the full username with discriminator (legacy format).
     */
    public function getFullUsername(): string
    {
        if ($this->discriminator && $this->discriminator !== '0') {
            return "{$this->username}#{$this->discriminator}";
        }

        return $this->username ?? $this->global_name ?? 'Discord User';
    }
}
