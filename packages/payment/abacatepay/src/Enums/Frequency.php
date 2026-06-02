<?php

namespace Eduardoks98\PaymentAbacatePay\Enums;

/**
 * Billing frequency options matching AbacatePay SDK
 *
 * Maps to AbacatePay\Frequencies from the official SDK
 */
enum Frequency: string
{
    case ONE_TIME = 'one_time';
    case MONTHLY = 'monthly';
    case YEARLY = 'yearly';

    /**
     * Get the AbacatePay API value
     *
     * API only accepts: ONE_TIME, MULTIPLE_PAYMENTS
     * Monthly/Yearly are treated as MULTIPLE_PAYMENTS (differentiated by price/metadata)
     */
    public function toSdkValue(): string
    {
        return match($this) {
            self::ONE_TIME => 'ONE_TIME',
            self::MONTHLY => 'MULTIPLE_PAYMENTS',
            self::YEARLY => 'MULTIPLE_PAYMENTS',
        };
    }

    /**
     * Get all available frequencies
     */
    public static function all(): array
    {
        return [
            self::ONE_TIME,
            self::MONTHLY,
            self::YEARLY,
        ];
    }

    /**
     * Get frequency label
     */
    public function label(): string
    {
        return match($this) {
            self::ONE_TIME => 'One Time Payment',
            self::MONTHLY => 'Monthly Subscription',
            self::YEARLY => 'Yearly Subscription',
        };
    }
}
