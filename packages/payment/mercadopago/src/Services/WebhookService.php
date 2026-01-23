<?php

namespace Eduardoks98\PaymentMercadoPago\Services;

use Eduardoks98\PaymentMercadoPago\Models\MercadoPagoPayment;
use Eduardoks98\PaymentMercadoPago\Models\MercadoPagoWebhook;
use Eduardoks98\PaymentMercadoPago\Enums\WebhookTopic;
use Eduardoks98\PaymentMercadoPago\Enums\PaymentStatus;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    protected MercadoPagoService $mercadoPagoService;

    public function __construct(MercadoPagoService $mercadoPagoService)
    {
        $this->mercadoPagoService = $mercadoPagoService;
    }

    /**
     * Process incoming webhook notification
     *
     * @param array $payload
     * @return MercadoPagoWebhook
     */
    public function processWebhook(array $payload): MercadoPagoWebhook
    {
        // Extract webhook data
        $topic = $payload['type'] ?? $payload['topic'] ?? null;
        $dataId = $payload['data']['id'] ?? $payload['id'] ?? null;
        $action = $payload['action'] ?? null;

        // Store webhook for audit trail
        $webhook = MercadoPagoWebhook::create([
            'topic' => $topic,
            'resource_id' => $dataId,
            'data_id' => $dataId,
            'action' => $action,
            'payload' => $payload,
            'processed' => false,
        ]);

        try {
            // Process based on topic
            $topicEnum = WebhookTopic::tryFrom($topic);

            if (!$topicEnum) {
                throw new \Exception("Unknown webhook topic: {$topic}");
            }

            match($topicEnum) {
                WebhookTopic::PAYMENT => $this->processPaymentNotification($dataId),
                WebhookTopic::MERCHANT_ORDER => $this->processMerchantOrderNotification($dataId),
                WebhookTopic::CHARGEBACKS => $this->processChargebackNotification($dataId),
            };

            $webhook->markAsProcessed();

        } catch (\Exception $e) {
            $webhook->markAsFailed($e->getMessage());

            Log::error('[MercadoPago Webhook] Processing failed', [
                'webhook_id' => $webhook->id,
                'topic' => $topic,
                'data_id' => $dataId,
                'error' => $e->getMessage(),
            ]);
        }

        return $webhook;
    }

    /**
     * Process payment notification
     *
     * @param string $paymentId
     */
    protected function processPaymentNotification(string $paymentId): void
    {
        // Fetch payment from MercadoPago API
        $apiPayment = $this->mercadoPagoService->getPayment($paymentId);

        // Find local payment record
        $payment = MercadoPagoPayment::mercadoPagoId($paymentId)->first();

        if (!$payment) {
            Log::warning('[MercadoPago Webhook] Payment not found in database', [
                'payment_id' => $paymentId,
            ]);
            return;
        }

        // Update payment status
        $oldStatus = $payment->status;
        $newStatus = $this->mapStatus($apiPayment->status);

        $updateData = [
            'status' => $newStatus,
            'status_detail' => $apiPayment->status_detail ?? null,
        ];

        // Set status-specific timestamps
        match($newStatus) {
            PaymentStatus::APPROVED => $updateData['approved_at'] = now(),
            PaymentStatus::REJECTED => $updateData['rejected_at'] = now(),
            PaymentStatus::REFUNDED => $updateData['refunded_at'] = now(),
            PaymentStatus::CANCELLED => $updateData['cancelled_at'] = now(),
            default => null,
        };

        $payment->update($updateData);

        Log::info('[MercadoPago Webhook] Payment status updated', [
            'payment_id' => $paymentId,
            'external_reference' => $payment->external_reference,
            'old_status' => $oldStatus->value ?? null,
            'new_status' => $newStatus->value,
        ]);
    }

    /**
     * Process merchant order notification
     *
     * @param string $orderId
     */
    protected function processMerchantOrderNotification(string $orderId): void
    {
        // This can be extended based on your needs
        // For now, we just log the notification
        Log::info('[MercadoPago Webhook] Merchant order notification received', [
            'order_id' => $orderId,
        ]);

        // You can implement order status updates here if needed
    }

    /**
     * Process chargeback notification
     *
     * @param string $chargebackId
     */
    protected function processChargebackNotification(string $chargebackId): void
    {
        // This can be extended based on your needs
        Log::info('[MercadoPago Webhook] Chargeback notification received', [
            'chargeback_id' => $chargebackId,
        ]);

        // You can implement chargeback handling here if needed
    }

    /**
     * Validate webhook signature (if secret is configured)
     *
     * @param array $headers
     * @param string $payload
     * @return bool
     */
    public function validateSignature(array $headers, string $payload): bool
    {
        $secret = config('payment-mercadopago.webhook_secret');

        if (!$secret) {
            // If no secret is configured, skip validation
            return true;
        }

        // MercadoPago signature validation
        // This should be implemented according to MercadoPago's documentation
        // For now, we return true as a placeholder
        // TODO: Implement actual signature validation based on MercadoPago docs

        return true;
    }

    /**
     * Map MercadoPago status to internal status enum
     *
     * @param string $status
     * @return PaymentStatus
     */
    protected function mapStatus(string $status): PaymentStatus
    {
        return match($status) {
            'pending' => PaymentStatus::PENDING,
            'approved' => PaymentStatus::APPROVED,
            'authorized' => PaymentStatus::AUTHORIZED,
            'in_process' => PaymentStatus::IN_PROCESS,
            'in_mediation' => PaymentStatus::IN_MEDIATION,
            'rejected' => PaymentStatus::REJECTED,
            'cancelled' => PaymentStatus::CANCELLED,
            'refunded' => PaymentStatus::REFUNDED,
            'charged_back' => PaymentStatus::CHARGED_BACK,
            'action_required' => PaymentStatus::ACTION_REQUIRED,
            default => PaymentStatus::PENDING,
        };
    }
}
