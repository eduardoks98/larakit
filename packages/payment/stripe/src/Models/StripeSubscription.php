<?php

namespace Eduardoks98\PaymentStripe\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Eduardoks98\PaymentStripe\Enums\SubscriptionStatus;

/**
 * StripeSubscription Model
 *
 * Stores Stripe Subscription data locally
 * Synced with Stripe Subscription object
 *
 * @property string $id
 * @property string $stripe_subscription_id
 * @property int|null $user_id
 * @property string|null $stripe_customer_id
 * @property string $stripe_price_id
 * @property string|null $stripe_product_id
 * @property SubscriptionStatus $status
 * @property \Illuminate\Support\Carbon|null $current_period_start
 * @property \Illuminate\Support\Carbon|null $current_period_end
 * @property \Illuminate\Support\Carbon|null $trial_start
 * @property \Illuminate\Support\Carbon|null $trial_end
 * @property \Illuminate\Support\Carbon|null $canceled_at
 * @property \Illuminate\Support\Carbon|null $ended_at
 * @property array|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class StripeSubscription extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'stripe_subscriptions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'stripe_subscription_id',
        'user_id',
        'stripe_customer_id',
        'stripe_price_id',
        'stripe_product_id',
        'status',
        'current_period_start',
        'current_period_end',
        'trial_start',
        'trial_end',
        'canceled_at',
        'ended_at',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'status' => SubscriptionStatus::class,
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'trial_start' => 'datetime',
        'trial_end' => 'datetime',
        'canceled_at' => 'datetime',
        'ended_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    /**
     * Get the Stripe customer associated with the subscription.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(StripeCustomer::class, 'stripe_customer_id', 'stripe_customer_id');
    }

    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    /**
     * Check if subscription is on trial
     */
    public function isOnTrial(): bool
    {
        return $this->status === SubscriptionStatus::TRIALING;
    }

    /**
     * Check if subscription is canceled
     */
    public function isCanceled(): bool
    {
        return $this->status === SubscriptionStatus::CANCELED;
    }

    /**
     * Check if subscription has ended
     */
    public function hasEnded(): bool
    {
        return $this->status->isEnded();
    }

    /**
     * Scope to filter by status
     */
    public function scopeStatus($query, SubscriptionStatus $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            SubscriptionStatus::TRIALING,
            SubscriptionStatus::ACTIVE,
        ]);
    }

    /**
     * Scope to get canceled subscriptions
     */
    public function scopeCanceled($query)
    {
        return $query->where('status', SubscriptionStatus::CANCELED);
    }

    /**
     * Scope to get subscriptions on trial
     */
    public function scopeOnTrial($query)
    {
        return $query->where('status', SubscriptionStatus::TRIALING);
    }
}
