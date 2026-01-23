<?php

namespace Eduardoks98\PaymentAbacatePay\Enums;

/**
 * Billing status options
 */
enum BillingStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';
    case REFUNDED = 'refunded';

    /**
     * Get all available statuses
     */
    public static function all(): array
    {
        return [
            self::PENDING,
            self::PAID,
            self::CANCELLED,
            self::EXPIRED,
            self::REFUNDED,
        ];
    }

    /**
     * Get status label
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::PAID => 'Paid',
            self::CANCELLED => 'Cancelled',
            self::EXPIRED => 'Expired',
            self::REFUNDED => 'Refunded',
        };
    }

    /**
     * Get status color for UI
     */
    public function color(): string
    {
        return match($this) {
            self::PENDING => 'warning',
            self::PAID => 'success',
            self::CANCELLED => 'danger',
            self::EXPIRED => 'secondary',
            self::REFUNDED => 'info',
        };
    }

    /**
     * Check if status is final (no more changes expected)
     */
    public function isFinal(): bool
    {
        return match($this) {
            self::PAID, self::CANCELLED, self::EXPIRED, self::REFUNDED => true,
            self::PENDING => false,
        };
    }
}
