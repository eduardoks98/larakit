<?php

namespace Eduardoks98\GoogleAuth\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleUser extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'google_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'google_id',
        'email',
        'name',
        'given_name',
        'family_name',
        'picture',
        'locale',
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
        'expires_in' => 'integer',
        'last_login_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the Google account.
     */
    public function user(): BelongsTo
    {
        $userModel = config('google-auth.user_model', 'App\\Models\\User');

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
     * Update user profile data from Google.
     */
    public function updateProfile(array $userData): self
    {
        $this->update([
            'email' => $userData['email'] ?? $this->email,
            'name' => $userData['name'] ?? $this->name,
            'given_name' => $userData['given_name'] ?? $this->given_name,
            'family_name' => $userData['family_name'] ?? $this->family_name,
            'picture' => $userData['picture'] ?? $this->picture,
            'locale' => $userData['locale'] ?? $this->locale,
        ]);

        return $this;
    }
}
