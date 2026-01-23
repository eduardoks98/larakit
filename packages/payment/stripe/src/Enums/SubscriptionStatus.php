<?php

namespace Eduardoks98\PaymentStripe\Enums;

/**
 * Stripe Subscription Status
 *
 * Based on official Stripe API documentation:
 * https://stripe.com/docs/api/subscriptions/object#subscription_object-status
 */
enum SubscriptionStatus: string
{
    /**
     * Subscription is incomplete (first payment attempt failed)
     */
    case INCOMPLETE = 'incomplete';

    /**
     * Subscription is incomplete and will expire
     */
    case INCOMPLETE_EXPIRED = 'incomplete_expired';

    /**
     * Subscription is in trial period
     */
    case TRIALING = 'trialing';

    /**
     * Subscription is active
     */
    case ACTIVE = 'active';

    /**
     * Subscription is past due (payment failed but still active)
     */
    case PAST_DUE = 'past_due';

    /**
     * Subscription was canceled
     */
    case CANCELED = 'canceled';

    /**
     * Subscription is unpaid (final invoice not paid)
     */
    case UNPAID = 'unpaid';

    /**
     * Subscription is paused
     */
    case PAUSED = 'paused';

    /**
     * Returns human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::INCOMPLETE => 'Incomplete',
            self::INCOMPLETE_EXPIRED => 'Incomplete Expired',
            self::TRIALING => 'Trialing',
            self::ACTIVE => 'Active',
            self::PAST_DUE => 'Past Due',
            self::CANCELED => 'Canceled',
            self::UNPAID => 'Unpaid',
            self::PAUSED => 'Paused',
        };
    }

    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        return in_array($this, [
            self::TRIALING,
            self::ACTIVE,
        ]);
    }

    /**
     * Check if subscription has issues
     */
    public function hasIssues(): bool
    {
        return in_array($this, [
            self::INCOMPLETE,
            self::PAST_DUE,
            self::UNPAID,
        ]);
    }

    /**
     * Check if subscription is ended
     */
    public function isEnded(): bool
    {
        return in_array($this, [
            self::INCOMPLETE_EXPIRED,
            self::CANCELED,
        ]);
    }
}
