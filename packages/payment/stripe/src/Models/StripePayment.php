<?php

namespace Eduardoks98\PaymentStripe\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Eduardoks98\PaymentStripe\Enums\PaymentStatus;

/**
 * StripePayment Model
 *
 * Stores Stripe Payment Intent data locally
 * Synced with Stripe PaymentIntent object
 *
 * @property string $id
 * @property string $stripe_payment_intent_id
 * @property int|null $user_id
 * @property string|null $stripe_customer_id
 * @property int $amount
 * @property string $currency
 * @property PaymentStatus $status
 * @property string|null $payment_method
 * @property string|null $client_secret
 * @property string|null $description
 * @property array|null $metadata
 * @property string|null $last_payment_error
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class StripePayment extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'stripe_payments';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'stripe_payment_intent_id',
        'user_id',
        'stripe_customer_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'client_secret',
        'description',
        'metadata',
        'last_payment_error',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'amount' => 'integer',
        'status' => PaymentStatus::class,
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the payment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    /**
     * Get the Stripe customer associated with the payment.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(StripeCustomer::class, 'stripe_customer_id', 'stripe_customer_id');
    }

    /**
     * Get amount in dollars/main currency unit
     */
    public function getAmountInDollarsAttribute(): float
    {
        return $this->amount / 100;
    }

    /**
     * Check if payment is successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === PaymentStatus::SUCCEEDED;
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status->isPending();
    }

    /**
     * Check if payment failed
     */
    public function isFailed(): bool
    {
        return $this->status->isFailed();
    }

    /**
     * Scope to filter by status
     */
    public function scopeStatus($query, PaymentStatus $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get successful payments
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', PaymentStatus::SUCCEEDED);
    }

    /**
     * Scope to get pending payments
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', [
            PaymentStatus::REQUIRES_PAYMENT_METHOD,
            PaymentStatus::REQUIRES_CONFIRMATION,
            PaymentStatus::REQUIRES_ACTION,
            PaymentStatus::PROCESSING,
            PaymentStatus::REQUIRES_CAPTURE,
        ]);
    }
}
