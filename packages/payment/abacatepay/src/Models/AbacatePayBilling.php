<?php

namespace Eduardoks98\PaymentAbacatePay\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Eduardoks98\PaymentAbacatePay\Enums\BillingStatus;
use Eduardoks98\PaymentAbacatePay\Enums\Frequency;
use Eduardoks98\PaymentAbacatePay\Enums\PaymentMethod;

class AbacatePayBilling extends Model
{
    protected $table = 'abacatepay_billings';

    protected $fillable = [
        'user_id',
        'abacatepay_id',
        'frequency',
        'methods',
        'amount',
        'status',
        'url',
        'products',
        'customer_data',
        'metadata',
        'return_url',
        'completion_url',
        'expires_at',
        'paid_at',
        'cancelled_at',
        'refunded_at',
    ];

    protected $casts = [
        'methods' => 'array',
        'amount' => 'integer',
        'products' => 'array',
        'customer_data' => 'array',
        'metadata' => 'array',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'refunded_at' => 'datetime',
        'frequency' => Frequency::class,
        'status' => BillingStatus::class,
    ];

    /**
     * Get the user that owns the billing.
     */
    public function user(): BelongsTo
    {
        $userModel = config('auth.providers.users.model', \App\Models\User::class);
        return $this->belongsTo($userModel);
    }

    /**
     * Scope to get billings by status.
     */
    public function scopeByStatus($query, BillingStatus $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get paid billings.
     */
    public function scopePaid($query)
    {
        return $query->where('status', BillingStatus::PAID);
    }

    /**
     * Scope to get pending billings.
     */
    public function scopePending($query)
    {
        return $query->where('status', BillingStatus::PENDING);
    }

    /**
     * Scope to get cancelled billings.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', BillingStatus::CANCELLED);
    }

    /**
     * Scope to get expired billings.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', BillingStatus::EXPIRED);
    }

    /**
     * Scope to get billings by user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get billings by frequency.
     */
    public function scopeByFrequency($query, Frequency $frequency)
    {
        return $query->where('frequency', $frequency);
    }

    /**
     * Check if billing is paid.
     */
    public function isPaid(): bool
    {
        return $this->status === BillingStatus::PAID;
    }

    /**
     * Check if billing is pending.
     */
    public function isPending(): bool
    {
        return $this->status === BillingStatus::PENDING;
    }

    /**
     * Check if billing is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === BillingStatus::CANCELLED;
    }

    /**
     * Check if billing is expired.
     */
    public function isExpired(): bool
    {
        return $this->status === BillingStatus::EXPIRED;
    }

    /**
     * Check if billing is refunded.
     */
    public function isRefunded(): bool
    {
        return $this->status === BillingStatus::REFUNDED;
    }

    /**
     * Get formatted amount in BRL.
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'R$ ' . number_format($this->amount / 100, 2, ',', '.');
    }

    /**
     * Get payment methods as enum array.
     */
    public function getPaymentMethodsAttribute(): array
    {
        if (!$this->methods) {
            return [];
        }

        return array_map(
            fn($method) => PaymentMethod::from($method),
            $this->methods
        );
    }

    /**
     * Mark billing as paid.
     */
    public function markAsPaid(): bool
    {
        $this->status = BillingStatus::PAID;
        $this->paid_at = now();
        return $this->save();
    }

    /**
     * Mark billing as cancelled.
     */
    public function markAsCancelled(): bool
    {
        $this->status = BillingStatus::CANCELLED;
        $this->cancelled_at = now();
        return $this->save();
    }

    /**
     * Mark billing as expired.
     */
    public function markAsExpired(): bool
    {
        $this->status = BillingStatus::EXPIRED;
        return $this->save();
    }

    /**
     * Mark billing as refunded.
     */
    public function markAsRefunded(): bool
    {
        $this->status = BillingStatus::REFUNDED;
        $this->refunded_at = now();
        return $this->save();
    }
}
