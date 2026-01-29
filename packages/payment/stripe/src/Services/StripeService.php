<?php

namespace Eduardoks98\PaymentStripe\Services;

use Stripe\StripeClient;
use Stripe\PaymentIntent;
use Stripe\Exception\ApiErrorException;
use Eduardoks98\PaymentStripe\Models\StripePayment;
use Eduardoks98\PaymentStripe\Enums\PaymentStatus;
use Illuminate\Support\Facades\Log;

/**
 * StripeService - Payment Intents API
 *
 * Based on official Stripe Payment Intents API documentation:
 * https://stripe.com/docs/api/payment_intents
 *
 * Uses stripe/stripe-php v13.x SDK
 */
class StripeService
{
    protected ?StripeClient $stripe = null;

    public function __construct()
    {
        $apiKey = config('stripe.secret_key');

        // Only initialize Stripe client if API key is configured
        if (!empty($apiKey)) {
            $this->stripe = new StripeClient([
                'api_key' => $apiKey,
                'stripe_version' => config('stripe.api_version'),
            ]);
        }
    }

    /**
     * Check if Stripe is configured
     */
    public function isConfigured(): bool
    {
        return $this->stripe !== null;
    }

    /**
     * Ensure Stripe is configured before making API calls
     */
    protected function ensureConfigured(): void
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('Stripe is not configured. Set STRIPE_SECRET in .env');
        }
    }

    /**
     * Create a PaymentIntent
     *
     * Official docs: https://stripe.com/docs/api/payment_intents/create
     *
     * @param int $amount Amount in cents (e.g., 2000 = $20.00)
     * @param string $currency Three-letter ISO currency code (e.g., 'usd')
     * @param array $options Additional options (customer, payment_method, metadata, etc.)
     * @return StripePayment
     * @throws ApiErrorException
     */
    public function createPaymentIntent(
        int $amount,
        string $currency = null,
        array $options = []
    ): StripePayment {
        $this->ensureConfigured();
        $currency = $currency ?? config('stripe.currency', 'usd');

        // Build PaymentIntent parameters
        $params = array_merge([
            'amount' => $amount,
            'currency' => $currency,
        ], $options);

        // Add automatic payment methods if enabled
        if (config('stripe.payment.automatic_payment_methods')) {
            $params['automatic_payment_methods'] = ['enabled' => true];
        } elseif (!isset($params['payment_method_types'])) {
            $params['payment_method_types'] = config('stripe.payment.payment_method_types', ['card']);
        }

        // Add capture method
        if (!isset($params['capture_method'])) {
            $params['capture_method'] = config('stripe.payment.capture_method', 'automatic');
        }

        try {
            // Create PaymentIntent via Stripe API
            $paymentIntent = $this->stripe->paymentIntents->create($params);

            // Store in database
            $payment = $this->syncPaymentIntent($paymentIntent);

            $this->log('PaymentIntent created', ['id' => $paymentIntent->id]);

            return $payment;
        } catch (ApiErrorException $e) {
            $this->log('PaymentIntent creation failed', ['error' => $e->getMessage()], 'error');
            throw $e;
        }
    }

    /**
     * Retrieve a PaymentIntent
     *
     * Official docs: https://stripe.com/docs/api/payment_intents/retrieve
     *
     * @param string $paymentIntentId
     * @return PaymentIntent
     * @throws ApiErrorException
     */
    public function retrievePaymentIntent(string $paymentIntentId): PaymentIntent
    {
        $this->ensureConfigured();
        return $this->stripe->paymentIntents->retrieve($paymentIntentId);
    }

    /**
     * Confirm a PaymentIntent
     *
     * Official docs: https://stripe.com/docs/api/payment_intents/confirm
     *
     * @param string $paymentIntentId
     * @param array $options
     * @return StripePayment
     * @throws ApiErrorException
     */
    public function confirmPaymentIntent(string $paymentIntentId, array $options = []): StripePayment
    {
        $this->ensureConfigured();
        try {
            $paymentIntent = $this->stripe->paymentIntents->confirm($paymentIntentId, $options);

            $payment = $this->syncPaymentIntent($paymentIntent);

            $this->log('PaymentIntent confirmed', ['id' => $paymentIntentId]);

            return $payment;
        } catch (ApiErrorException $e) {
            $this->log('PaymentIntent confirmation failed', [
                'id' => $paymentIntentId,
                'error' => $e->getMessage()
            ], 'error');
            throw $e;
        }
    }

    /**
     * Cancel a PaymentIntent
     *
     * Official docs: https://stripe.com/docs/api/payment_intents/cancel
     *
     * @param string $paymentIntentId
     * @param array $options
     * @return StripePayment
     * @throws ApiErrorException
     */
    public function cancelPaymentIntent(string $paymentIntentId, array $options = []): StripePayment
    {
        $this->ensureConfigured();
        try {
            $paymentIntent = $this->stripe->paymentIntents->cancel($paymentIntentId, $options);

            $payment = $this->syncPaymentIntent($paymentIntent);

            $this->log('PaymentIntent canceled', ['id' => $paymentIntentId]);

            return $payment;
        } catch (ApiErrorException $e) {
            $this->log('PaymentIntent cancellation failed', [
                'id' => $paymentIntentId,
                'error' => $e->getMessage()
            ], 'error');
            throw $e;
        }
    }

    /**
     * Capture a PaymentIntent (manual capture mode)
     *
     * Official docs: https://stripe.com/docs/api/payment_intents/capture
     *
     * @param string $paymentIntentId
     * @param int|null $amountToCapture Amount to capture in cents (null = full amount)
     * @return StripePayment
     * @throws ApiErrorException
     */
    public function capturePaymentIntent(string $paymentIntentId, ?int $amountToCapture = null): StripePayment
    {
        $this->ensureConfigured();
        try {
            $params = [];
            if ($amountToCapture !== null) {
                $params['amount_to_capture'] = $amountToCapture;
            }

            $paymentIntent = $this->stripe->paymentIntents->capture($paymentIntentId, $params);

            $payment = $this->syncPaymentIntent($paymentIntent);

            $this->log('PaymentIntent captured', ['id' => $paymentIntentId]);

            return $payment;
        } catch (ApiErrorException $e) {
            $this->log('PaymentIntent capture failed', [
                'id' => $paymentIntentId,
                'error' => $e->getMessage()
            ], 'error');
            throw $e;
        }
    }

    /**
     * Update a PaymentIntent
     *
     * Official docs: https://stripe.com/docs/api/payment_intents/update
     *
     * @param string $paymentIntentId
     * @param array $params
     * @return StripePayment
     * @throws ApiErrorException
     */
    public function updatePaymentIntent(string $paymentIntentId, array $params): StripePayment
    {
        $this->ensureConfigured();
        try {
            $paymentIntent = $this->stripe->paymentIntents->update($paymentIntentId, $params);

            $payment = $this->syncPaymentIntent($paymentIntent);

            $this->log('PaymentIntent updated', ['id' => $paymentIntentId]);

            return $payment;
        } catch (ApiErrorException $e) {
            $this->log('PaymentIntent update failed', [
                'id' => $paymentIntentId,
                'error' => $e->getMessage()
            ], 'error');
            throw $e;
        }
    }

    /**
     * Sync PaymentIntent from Stripe to local database
     *
     * @param PaymentIntent $paymentIntent
     * @return StripePayment
     */
    public function syncPaymentIntent(PaymentIntent $paymentIntent): StripePayment
    {
        return StripePayment::updateOrCreate(
            ['stripe_payment_intent_id' => $paymentIntent->id],
            [
                'stripe_customer_id' => $paymentIntent->customer,
                'amount' => $paymentIntent->amount,
                'currency' => $paymentIntent->currency,
                'status' => PaymentStatus::from($paymentIntent->status),
                'payment_method' => $paymentIntent->payment_method,
                'client_secret' => $paymentIntent->client_secret,
                'description' => $paymentIntent->description,
                'metadata' => $paymentIntent->metadata->toArray(),
                'last_payment_error' => $paymentIntent->last_payment_error?->message,
            ]
        );
    }

    /**
     * Get PaymentIntent by ID from database
     *
     * @param string $paymentIntentId
     * @return StripePayment|null
     */
    public function getPaymentFromDatabase(string $paymentIntentId): ?StripePayment
    {
        return StripePayment::where('stripe_payment_intent_id', $paymentIntentId)->first();
    }

    /**
     * Log Stripe operations
     *
     * @param string $message
     * @param array $context
     * @param string $level
     * @return void
     */
    protected function log(string $message, array $context = [], string $level = 'info'): void
    {
        if (config('stripe.logging.enabled')) {
            Log::channel(config('stripe.logging.channel', 'stack'))
                ->{$level}('[Stripe Payment] ' . $message, $context);
        }
    }
}
