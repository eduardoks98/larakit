<?php

namespace Eduardoks98\PaymentStripe\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * StripeCustomer Model
 *
 * Stores Stripe Customer data locally
 * Synced with Stripe Customer object
 *
 * @property string $id
 * @property string $stripe_customer_id
 * @property int|null $user_id
 * @property string|null $email
 * @property string|null $name
 * @property string|null $phone
 * @property array|null $address
 * @property array|null $metadata
 * @property string|null $default_payment_method
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class StripeCustomer extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'stripe_customers';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'stripe_customer_id',
        'user_id',
        'email',
        'name',
        'phone',
        'address',
        'metadata',
        'default_payment_method',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'address' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the customer.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    /**
     * Get the payments for the customer.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(StripePayment::class, 'stripe_customer_id', 'stripe_customer_id');
    }

    /**
     * Get the subscriptions for the customer.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(StripeSubscription::class, 'stripe_customer_id', 'stripe_customer_id');
    }

    /**
     * Get active subscriptions
     */
    public function activeSubscriptions(): HasMany
    {
        return $this->subscriptions()->active();
    }
}
