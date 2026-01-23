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
     * Get the AbacatePay SDK constant value
     */
    public function toSdkValue(): string
    {
        return match($this) {
            self::ONE_TIME => \AbacatePay\Frequencies::ONE_TIME,
            self::MONTHLY => \AbacatePay\Frequencies::MONTHLY,
            self::YEARLY => \AbacatePay\Frequencies::YEARLY,
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
