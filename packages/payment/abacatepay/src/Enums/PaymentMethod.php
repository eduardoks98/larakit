<?php

namespace Eduardoks98\PaymentAbacatePay\Enums;

/**
 * Payment method options matching AbacatePay SDK
 *
 * Maps to AbacatePay\Methods from the official SDK
 */
enum PaymentMethod: string
{
    case PIX = 'pix';
    case CARD = 'card';

    /**
     * Get the AbacatePay SDK constant value
     */
    public function toSdkValue(): string
    {
        return match($this) {
            self::PIX => \AbacatePay\Methods::PIX,
            self::CARD => \AbacatePay\Methods::CARD,
        };
    }

    /**
     * Get all available payment methods
     */
    public static function all(): array
    {
        return [
            self::PIX,
            self::CARD,
        ];
    }

    /**
     * Get payment method label
     */
    public function label(): string
    {
        return match($this) {
            self::PIX => 'PIX',
            self::CARD => 'Credit/Debit Card',
        };
    }

    /**
     * Check if payment method is instant
     */
    public function isInstant(): bool
    {
        return match($this) {
            self::PIX => true,
            self::CARD => false,
        };
    }
}
