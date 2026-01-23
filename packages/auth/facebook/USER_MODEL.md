# User Model Configuration

This guide shows how to configure your User model to work with Facebook authentication.

## Basic Setup

Add the relationship to your User model:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Eduardoks98\FacebookAuth\Models\FacebookUser;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_url',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the user's Facebook profile.
     */
    public function facebookUser()
    {
        return $this->hasOne(FacebookUser::class);
    }
}
```

## Advanced Setup with Trait

Create a trait for better organization:

```php
<?php

namespace App\Models\Concerns;

use Eduardoks98\FacebookAuth\Models\FacebookUser;

trait HasFacebookAccount
{
    /**
     * Get the user's Facebook account.
     */
    public function facebookUser()
    {
        return $this->hasOne(FacebookUser::class);
    }

    /**
     * Check if user has a linked Facebook account.
     */
    public function hasFacebookAccount(): bool
    {
        return $this->facebookUser()->exists();
    }

    /**
     * Get the Facebook ID.
     */
    public function getFacebookId(): ?string
    {
        return $this->facebookUser?->facebook_id;
    }

    /**
     * Get the Facebook avatar URL.
     */
    public function getFacebookAvatar(): ?string
    {
        return $this->facebookUser?->avatar_url;
    }

    /**
     * Get the Facebook email.
     */
    public function getFacebookEmail(): ?string
    {
        return $this->facebookUser?->email;
    }

    /**
     * Get the Facebook name.
     */
    public function getFacebookName(): ?string
    {
        return $this->facebookUser?->name;
    }

    /**
     * Sync avatar from Facebook.
     */
    public function syncFacebookAvatar(): bool
    {
        if (!$this->hasFacebookAccount()) {
            return false;
        }

        $avatarUrl = $this->getFacebookAvatar();

        if (!$avatarUrl) {
            return false;
        }

        $this->update(['avatar_url' => $avatarUrl]);

        return true;
    }

    /**
     * Check if user authenticated via Facebook.
     */
    public function isAuthenticatedViaFacebook(): bool
    {
        return $this->hasFacebookAccount() &&
               str_starts_with($this->email, 'facebook_');
    }

    /**
     * Get Facebook profile data.
     */
    public function getFacebookProfile(): ?array
    {
        if (!$this->hasFacebookAccount()) {
            return null;
        }

        $facebookUser = $this->facebookUser;

        return [
            'facebook_id' => $facebookUser->facebook_id,
            'email' => $facebookUser->email,
            'name' => $facebookUser->name,
            'first_name' => $facebookUser->first_name,
            'last_name' => $facebookUser->last_name,
            'avatar_url' => $facebookUser->avatar_url,
            'linked_at' => $facebookUser->created_at,
            'updated_at' => $facebookUser->updated_at,
        ];
    }
}
```

Use the trait in your User model:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Concerns\HasFacebookAccount;

class User extends Authenticatable
{
    use HasApiTokens, HasFacebookAccount;

    // ... rest of your User model
}
```

## Usage Examples

### Check if user has Facebook account

```php
$user = auth()->user();

if ($user->hasFacebookAccount()) {
    echo "User has linked Facebook account";
}
```

### Get Facebook profile data

```php
$user = auth()->user();

if ($profile = $user->getFacebookProfile()) {
    echo "Facebook ID: " . $profile['facebook_id'];
    echo "Facebook Name: " . $profile['name'];
    echo "Facebook Avatar: " . $profile['avatar_url'];
}
```

### Sync avatar from Facebook

```php
$user = auth()->user();

if ($user->syncFacebookAvatar()) {
    echo "Avatar synced from Facebook";
}
```

### Eager Loading

When querying users, eager load the Facebook relationship:

```php
// Get all users with their Facebook profiles
$users = User::with('facebookUser')->get();

// Get specific user with Facebook profile
$user = User::with('facebookUser')->find($id);

// In controller
public function index()
{
    $users = User::with('facebookUser')->paginate(20);

    return view('users.index', compact('users'));
}
```

### API Resources

Create an API resource for users with Facebook data:

```php
<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar_url' => $this->avatar_url,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,

            // Facebook data
            'facebook' => $this->when($this->hasFacebookAccount(), function () {
                return [
                    'facebook_id' => $this->getFacebookId(),
                    'facebook_name' => $this->getFacebookName(),
                    'facebook_email' => $this->getFacebookEmail(),
                    'facebook_avatar' => $this->getFacebookAvatar(),
                    'linked_at' => $this->facebookUser->created_at,
                ];
            }),

            // Or use the helper method
            // 'facebook_profile' => $this->getFacebookProfile(),
        ];
    }
}
```

### Blade Templates

Display Facebook data in your views:

```blade
@if($user->hasFacebookAccount())
    <div class="facebook-profile">
        <h3>Facebook Profile</h3>

        @if($user->getFacebookAvatar())
            <img src="{{ $user->getFacebookAvatar() }}" alt="Facebook Avatar">
        @endif

        <p>Facebook Name: {{ $user->getFacebookName() }}</p>
        <p>Facebook ID: {{ $user->getFacebookId() }}</p>

        <form action="{{ route('facebook.disconnect') }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">
                Disconnect Facebook
            </button>
        </form>
    </div>
@else
    <a href="{{ route('facebook-auth.redirect') }}" class="btn btn-primary">
        Connect Facebook
    </a>
@endif
```

### Observers

Create an observer to handle Facebook user events:

```php
<?php

namespace App\Observers;

use Eduardoks98\FacebookAuth\Models\FacebookUser;
use App\Models\User;

class FacebookUserObserver
{
    /**
     * Handle the FacebookUser "created" event.
     */
    public function created(FacebookUser $facebookUser): void
    {
        $user = $facebookUser->user;

        // Sync avatar
        if ($facebookUser->avatar_url) {
            $user->update(['avatar_url' => $facebookUser->avatar_url]);
        }

        // Send notification
        // $user->notify(new FacebookAccountLinked($facebookUser));
    }

    /**
     * Handle the FacebookUser "updated" event.
     */
    public function updated(FacebookUser $facebookUser): void
    {
        // Sync updated data
        if ($facebookUser->wasChanged('avatar_url')) {
            $facebookUser->user->update([
                'avatar_url' => $facebookUser->avatar_url
            ]);
        }
    }

    /**
     * Handle the FacebookUser "deleted" event.
     */
    public function deleted(FacebookUser $facebookUser): void
    {
        // Clean up user data
        // Log the disconnection
        \Log::info('Facebook account disconnected', [
            'user_id' => $facebookUser->user_id,
            'facebook_id' => $facebookUser->facebook_id,
        ]);
    }
}
```

Register the observer in `AppServiceProvider`:

```php
use App\Observers\FacebookUserObserver;
use Eduardoks98\FacebookAuth\Models\FacebookUser;

public function boot(): void
{
    FacebookUser::observe(FacebookUserObserver::class);
}
```

### Scopes

Add query scopes for filtering users:

```php
// In User model
public function scopeWithFacebook($query)
{
    return $query->has('facebookUser');
}

public function scopeWithoutFacebook($query)
{
    return $query->doesntHave('facebookUser');
}

// Usage
$usersWithFacebook = User::withFacebook()->get();
$usersWithoutFacebook = User::withoutFacebook()->get();
```

### Accessors

Add accessors for computed properties:

```php
// In User model
use Illuminate\Database\Eloquent\Casts\Attribute;

protected function hasFacebook(): Attribute
{
    return Attribute::make(
        get: fn () => $this->hasFacebookAccount(),
    );
}

protected function facebookId(): Attribute
{
    return Attribute::make(
        get: fn () => $this->getFacebookId(),
    );
}

// Usage
echo $user->has_facebook; // true/false
echo $user->facebook_id; // Facebook ID or null
```

## Database Schema

If you need to add custom fields to your users table:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar_url')->nullable()->after('email');
            $table->timestamp('email_verified_at')->nullable()->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar_url', 'email_verified_at']);
        });
    }
};
```

## Configuration

Update your `config/facebook-auth.php` to use your User model:

```php
'user_model' => App\Models\User::class,
```

Or set it in your `.env`:

```env
FACEBOOK_USER_MODEL=App\Models\User
```
