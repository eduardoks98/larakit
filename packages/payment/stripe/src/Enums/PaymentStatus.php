<?php

namespace Eduardoks98\PaymentStripe\Enums;

/**
 * Stripe Payment Intent Status
 *
 * Based on official Stripe API documentation:
 * https://stripe.com/docs/api/payment_intents/object#payment_intent_object-status
 */
enum PaymentStatus: string
{
    /**
     * Payment requires payment method
     */
    case REQUIRES_PAYMENT_METHOD = 'requires_payment_method';

    /**
     * Payment requires confirmation
     */
    case REQUIRES_CONFIRMATION = 'requires_confirmation';

    /**
     * Payment requires action (3D Secure, etc.)
     */
    case REQUIRES_ACTION = 'requires_action';

    /**
     * Payment is processing
     */
    case PROCESSING = 'processing';

    /**
     * Payment requires capture (manual capture mode)
     */
    case REQUIRES_CAPTURE = 'requires_capture';

    /**
     * Payment was canceled
     */
    case CANCELED = 'canceled';

    /**
     * Payment succeeded
     */
    case SUCCEEDED = 'succeeded';

    /**
     * Returns human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::REQUIRES_PAYMENT_METHOD => 'Requires Payment Method',
            self::REQUIRES_CONFIRMATION => 'Requires Confirmation',
            self::REQUIRES_ACTION => 'Requires Action',
            self::PROCESSING => 'Processing',
            self::REQUIRES_CAPTURE => 'Requires Capture',
            self::CANCELED => 'Canceled',
            self::SUCCEEDED => 'Succeeded',
        };
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return in_array($this, [
            self::REQUIRES_PAYMENT_METHOD,
            self::REQUIRES_CONFIRMATION,
            self::REQUIRES_ACTION,
            self::PROCESSING,
            self::REQUIRES_CAPTURE,
        ]);
    }

    /**
     * Check if payment is completed
     */
    public function isCompleted(): bool
    {
        return $this === self::SUCCEEDED;
    }

    /**
     * Check if payment failed or was canceled
     */
    public function isFailed(): bool
    {
        return $this === self::CANCELED;
    }
}
