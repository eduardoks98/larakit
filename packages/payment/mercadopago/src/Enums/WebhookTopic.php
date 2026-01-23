<?php

namespace Eduardoks98\PaymentMercadoPago\Enums;

/**
 * MercadoPago Webhook Topic Enum
 *
 * Based on official MercadoPago IPN/Webhook documentation
 * @see https://www.mercadopago.com.br/developers/en/docs/your-integrations/notifications/webhooks
 */
enum WebhookTopic: string
{
    /**
     * Payment notification
     */
    case PAYMENT = 'payment';

    /**
     * Merchant order notification (creation, closure, or expiration)
     */
    case MERCHANT_ORDER = 'merchant_order';

    /**
     * Chargeback notification (initiation, status changes, fund release)
     */
    case CHARGEBACKS = 'chargebacks';

    /**
     * Get resource API endpoint
     */
    public function getResourceEndpoint(string $id): string
    {
        return match($this) {
            self::PAYMENT => "/v1/payments/{$id}",
            self::MERCHANT_ORDER => "/merchant_orders/{$id}",
            self::CHARGEBACKS => "/v1/chargebacks/{$id}",
        };
    }

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::PAYMENT => 'Payment',
            self::MERCHANT_ORDER => 'Merchant Order',
            self::CHARGEBACKS => 'Chargebacks',
        };
    }
}
