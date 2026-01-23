<?php

namespace Eduardoks98\MicrosoftAuth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MicrosoftUser extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'microsoft_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'microsoft_id',
        'email',
        'name',
        'given_name',
        'surname',
        'user_principal_name',
        'job_title',
        'office_location',
        'mobile_phone',
        'business_phones',
        'preferred_language',
        'avatar_url',
        'tenant_id',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'last_login_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'business_phones' => 'array',
        'token_expires_at' => 'datetime',
        'last_login_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    /**
     * Get the user that owns the Microsoft account.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('microsoft.user_model'), 'user_id');
    }

    /**
     * Check if the access token is expired.
     */
    public function isTokenExpired(): bool
    {
        if (!$this->token_expires_at) {
            return true;
        }

        return $this->token_expires_at->isPast();
    }

    /**
     * Check if the access token is valid.
     */
    public function hasValidToken(): bool
    {
        return $this->access_token && !$this->isTokenExpired();
    }

    /**
     * Update last login timestamp.
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Store or update Microsoft tokens.
     */
    public function updateTokens(string $accessToken, ?string $refreshToken = null, ?int $expiresIn = null): void
    {
        $data = [
            'access_token' => $accessToken,
        ];

        if ($refreshToken) {
            $data['refresh_token'] = $refreshToken;
        }

        if ($expiresIn) {
            $data['token_expires_at'] = now()->addSeconds($expiresIn);
        }

        $this->update($data);
    }

    /**
     * Clear stored tokens.
     */
    public function clearTokens(): void
    {
        $this->update([
            'access_token' => null,
            'refresh_token' => null,
            'token_expires_at' => null,
        ]);
    }
}
