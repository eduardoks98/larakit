<?php

namespace Eduardoks98\PaymentMercadoPago\Enums;

/**
 * MercadoPago Payment Method Enum
 *
 * Based on official MercadoPago API documentation
 */
enum PaymentMethod: string
{
    /**
     * PIX - Instant payment (Brazil)
     */
    case PIX = 'pix';

    /**
     * Credit Card
     */
    case CREDIT_CARD = 'credit_card';

    /**
     * Debit Card
     */
    case DEBIT_CARD = 'debit_card';

    /**
     * Boleto Bancário (Bank slip - Brazil)
     */
    case BOLETO = 'boleto_bancario';

    /**
     * Bank Transfer
     */
    case BANK_TRANSFER = 'bank_transfer';

    /**
     * Mercado Pago Account Balance
     */
    case ACCOUNT_MONEY = 'account_money';

    /**
     * Get payment method type for API
     */
    public function getType(): string
    {
        return match($this) {
            self::PIX => 'bank_transfer',
            self::CREDIT_CARD => 'credit_card',
            self::DEBIT_CARD => 'debit_card',
            self::BOLETO => 'ticket',
            self::BANK_TRANSFER => 'bank_transfer',
            self::ACCOUNT_MONEY => 'account_money',
        };
    }

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::PIX => 'PIX',
            self::CREDIT_CARD => 'Credit Card',
            self::DEBIT_CARD => 'Debit Card',
            self::BOLETO => 'Boleto Bancário',
            self::BANK_TRANSFER => 'Bank Transfer',
            self::ACCOUNT_MONEY => 'Mercado Pago Balance',
        };
    }

    /**
     * Check if payment method requires immediate action
     */
    public function requiresAction(): bool
    {
        return in_array($this, [
            self::PIX,
            self::BOLETO,
        ]);
    }
}
