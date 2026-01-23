<?php

namespace Eduardoks98\PaymentMercadoPago\Enums;

/**
 * MercadoPago Payment Status Enum
 *
 * Based on official MercadoPago API documentation
 * @see https://www.mercadopago.com.br/developers/en/docs/checkout-api/payment-status
 */
enum PaymentStatus: string
{
    /**
     * Payment is pending (awaiting payment)
     */
    case PENDING = 'pending';

    /**
     * Payment approved and credited
     */
    case APPROVED = 'approved';

    /**
     * Payment authorized (awaiting capture)
     */
    case AUTHORIZED = 'authorized';

    /**
     * Payment is being processed (e.g., bank transfer in progress)
     */
    case IN_PROCESS = 'in_process';

    /**
     * Payment is being mediated (dispute/chargeback)
     */
    case IN_MEDIATION = 'in_mediation';

    /**
     * Payment was rejected (insufficient funds, invalid data, etc.)
     */
    case REJECTED = 'rejected';

    /**
     * Payment was cancelled
     */
    case CANCELLED = 'cancelled';

    /**
     * Payment was refunded to the customer
     */
    case REFUNDED = 'refunded';

    /**
     * Payment was charged back
     */
    case CHARGED_BACK = 'charged_back';

    /**
     * Payment requires action (e.g., PIX awaiting QR code scan)
     */
    case ACTION_REQUIRED = 'action_required';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::AUTHORIZED => 'Authorized',
            self::IN_PROCESS => 'In Process',
            self::IN_MEDIATION => 'In Mediation',
            self::REJECTED => 'Rejected',
            self::CANCELLED => 'Cancelled',
            self::REFUNDED => 'Refunded',
            self::CHARGED_BACK => 'Charged Back',
            self::ACTION_REQUIRED => 'Action Required',
        };
    }

    /**
     * Check if payment is in a final state
     */
    public function isFinal(): bool
    {
        return in_array($this, [
            self::APPROVED,
            self::REJECTED,
            self::CANCELLED,
            self::REFUNDED,
            self::CHARGED_BACK,
        ]);
    }

    /**
     * Check if payment is successful
     */
    public function isSuccessful(): bool
    {
        return $this === self::APPROVED;
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return in_array($this, [
            self::PENDING,
            self::IN_PROCESS,
            self::ACTION_REQUIRED,
        ]);
    }
}
