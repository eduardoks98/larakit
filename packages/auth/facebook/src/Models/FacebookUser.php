<?php

namespace Eduardoks98\FacebookAuth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Facebook User Model
 *
 * Stores Facebook user data and links to the main User model
 *
 * @property int $id
 * @property int $user_id
 * @property string $facebook_id
 * @property string|null $email
 * @property string|null $name
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $avatar_url
 * @property string|null $access_token
 * @property array|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class FacebookUser extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'facebook_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'facebook_id',
        'email',
        'name',
        'first_name',
        'last_name',
        'avatar_url',
        'access_token',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'access_token',
    ];

    /**
     * Get the table name from config.
     *
     * @return string
     */
    public function getTable()
    {
        return config('facebook-auth.tables.facebook_users', parent::getTable());
    }

    /**
     * Get the user that owns the Facebook account.
     */
    public function user(): BelongsTo
    {
        $userModel = config('facebook-auth.user_model', 'App\\Models\\User');
        return $this->belongsTo($userModel);
    }

    /**
     * Find a Facebook user by Facebook ID.
     *
     * @param string $facebookId
     * @return self|null
     */
    public static function findByFacebookId(string $facebookId): ?self
    {
        return static::where('facebook_id', $facebookId)->first();
    }

    /**
     * Create or update a Facebook user.
     *
     * @param array $data
     * @return self
     */
    public static function createOrUpdate(array $data): self
    {
        return static::updateOrCreate(
            ['facebook_id' => $data['facebook_id']],
            $data
        );
    }
}
