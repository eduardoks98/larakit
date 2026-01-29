<?php

namespace Eduardoks98\PaymentStripe\Services;

use Stripe\StripeClient;
use Stripe\Subscription;
use Stripe\Exception\ApiErrorException;
use Eduardoks98\PaymentStripe\Models\StripeSubscription;
use Eduardoks98\PaymentStripe\Enums\SubscriptionStatus;
use Illuminate\Support\Facades\Log;

/**
 * SubscriptionService - Stripe Subscription API
 *
 * Based on official Stripe Subscription API documentation:
 * https://stripe.com/docs/api/subscriptions
 *
 * Uses stripe/stripe-php v13.x SDK
 */
class SubscriptionService
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
     * Create a Subscription
     *
     * Official docs: https://stripe.com/docs/api/subscriptions/create
     *
     * @param string $customerId Stripe Customer ID
     * @param array $items Subscription items (price_id, quantity)
     * @param array $options Additional options (trial_period_days, metadata, etc.)
     * @return StripeSubscription
     * @throws ApiErrorException
     */
    public function createSubscription(
        string $customerId,
        array $items,
        array $options = []
    ): StripeSubscription {
        $this->ensureConfigured();
        try {
            // Build subscription parameters
            $params = array_merge([
                'customer' => $customerId,
                'items' => $items,
            ], $options);

            // Add default collection method
            if (!isset($params['collection_method'])) {
                $params['collection_method'] = config('stripe.subscription.collection_method', 'charge_automatically');
            }

            // Add trial period if configured
            if (!isset($params['trial_period_days']) && config('stripe.subscription.trial_period_days')) {
                $params['trial_period_days'] = config('stripe.subscription.trial_period_days');
            }

            // Add proration behavior
            if (!isset($params['proration_behavior'])) {
                $params['proration_behavior'] = config('stripe.subscription.proration_behavior', 'create_prorations');
            }

            // Create Subscription via Stripe API
            $subscription = $this->stripe->subscriptions->create($params);

            // Store in database
            $stripeSubscription = $this->syncSubscription($subscription);

            $this->log('Subscription created', ['id' => $subscription->id]);

            return $stripeSubscription;
        } catch (ApiErrorException $e) {
            $this->log('Subscription creation failed', ['error' => $e->getMessage()], 'error');
            throw $e;
        }
    }

    /**
     * Retrieve a Subscription
     *
     * Official docs: https://stripe.com/docs/api/subscriptions/retrieve
     *
     * @param string $subscriptionId
     * @return Subscription
     * @throws ApiErrorException
     */
    public function retrieveSubscription(string $subscriptionId): Subscription
    {
        $this->ensureConfigured();
        return $this->stripe->subscriptions->retrieve($subscriptionId);
    }

    /**
     * Update a Subscription
     *
     * Official docs: https://stripe.com/docs/api/subscriptions/update
     *
     * @param string $subscriptionId
     * @param array $params
     * @return StripeSubscription
     * @throws ApiErrorException
     */
    public function updateSubscription(string $subscriptionId, array $params): StripeSubscription
    {
        $this->ensureConfigured();
        try {
            $subscription = $this->stripe->subscriptions->update($subscriptionId, $params);

            $stripeSubscription = $this->syncSubscription($subscription);

            $this->log('Subscription updated', ['id' => $subscriptionId]);

            return $stripeSubscription;
        } catch (ApiErrorException $e) {
            $this->log('Subscription update failed', [
                'id' => $subscriptionId,
                'error' => $e->getMessage()
            ], 'error');
            throw $e;
        }
    }

    /**
     * Cancel a Subscription
     *
     * Official docs: https://stripe.com/docs/api/subscriptions/cancel
     *
     * @param string $subscriptionId
     * @param bool $cancelAtPeriodEnd If true, cancel at end of billing period
     * @return StripeSubscription
     * @throws ApiErrorException
     */
    public function cancelSubscription(string $subscriptionId, bool $cancelAtPeriodEnd = false): StripeSubscription
    {
        $this->ensureConfigured();
        try {
            if ($cancelAtPeriodEnd) {
                // Cancel at period end (customer retains access until end of billing period)
                $subscription = $this->stripe->subscriptions->update($subscriptionId, [
                    'cancel_at_period_end' => true,
                ]);
            } else {
                // Cancel immediately
                $subscription = $this->stripe->subscriptions->cancel($subscriptionId);
            }

            $stripeSubscription = $this->syncSubscription($subscription);

            $this->log('Subscription canceled', [
                'id' => $subscriptionId,
                'at_period_end' => $cancelAtPeriodEnd
            ]);

            return $stripeSubscription;
        } catch (ApiErrorException $e) {
            $this->log('Subscription cancellation failed', [
                'id' => $subscriptionId,
                'error' => $e->getMessage()
            ], 'error');
            throw $e;
        }
    }

    /**
     * Resume a Subscription (undo cancel_at_period_end)
     *
     * @param string $subscriptionId
     * @return StripeSubscription
     * @throws ApiErrorException
     */
    public function resumeSubscription(string $subscriptionId): StripeSubscription
    {
        $this->ensureConfigured();
        try {
            $subscription = $this->stripe->subscriptions->update($subscriptionId, [
                'cancel_at_period_end' => false,
            ]);

            $stripeSubscription = $this->syncSubscription($subscription);

            $this->log('Subscription resumed', ['id' => $subscriptionId]);

            return $stripeSubscription;
        } catch (ApiErrorException $e) {
            $this->log('Subscription resume failed', [
                'id' => $subscriptionId,
                'error' => $e->getMessage()
            ], 'error');
            throw $e;
        }
    }

    /**
     * Pause a Subscription
     *
     * Official docs: https://stripe.com/docs/billing/subscriptions/pause-payment
     *
     * @param string $subscriptionId
     * @param string $behavior 'keep_as_draft', 'mark_uncollectible', or 'void'
     * @return StripeSubscription
     * @throws ApiErrorException
     */
    public function pauseSubscription(string $subscriptionId, string $behavior = 'mark_uncollectible'): StripeSubscription
    {
        $this->ensureConfigured();
        try {
            $subscription = $this->stripe->subscriptions->update($subscriptionId, [
                'pause_collection' => [
                    'behavior' => $behavior,
                ],
            ]);

            $stripeSubscription = $this->syncSubscription($subscription);

            $this->log('Subscription paused', ['id' => $subscriptionId]);

            return $stripeSubscription;
        } catch (ApiErrorException $e) {
            $this->log('Subscription pause failed', [
                'id' => $subscriptionId,
                'error' => $e->getMessage()
            ], 'error');
            throw $e;
        }
    }

    /**
     * Unpause a Subscription
     *
     * @param string $subscriptionId
     * @return StripeSubscription
     * @throws ApiErrorException
     */
    public function unpauseSubscription(string $subscriptionId): StripeSubscription
    {
        $this->ensureConfigured();
        try {
            $subscription = $this->stripe->subscriptions->update($subscriptionId, [
                'pause_collection' => '',
            ]);

            $stripeSubscription = $this->syncSubscription($subscription);

            $this->log('Subscription unpaused', ['id' => $subscriptionId]);

            return $stripeSubscription;
        } catch (ApiErrorException $e) {
            $this->log('Subscription unpause failed', [
                'id' => $subscriptionId,
                'error' => $e->getMessage()
            ], 'error');
            throw $e;
        }
    }

    /**
     * Change Subscription Price (upgrade/downgrade)
     *
     * @param string $subscriptionId
     * @param string $newPriceId
     * @param array $options Additional options (proration_behavior, etc.)
     * @return StripeSubscription
     * @throws ApiErrorException
     */
    public function changeSubscriptionPrice(
        string $subscriptionId,
        string $newPriceId,
        array $options = []
    ): StripeSubscription {
        $this->ensureConfigured();
        try {
            // Retrieve current subscription to get the subscription item ID
            $subscription = $this->stripe->subscriptions->retrieve($subscriptionId);

            $params = array_merge([
                'items' => [
                    [
                        'id' => $subscription->items->data[0]->id,
                        'price' => $newPriceId,
                    ],
                ],
            ], $options);

            // Add proration behavior if not specified
            if (!isset($params['proration_behavior'])) {
                $params['proration_behavior'] = config('stripe.subscription.proration_behavior', 'create_prorations');
            }

            $subscription = $this->stripe->subscriptions->update($subscriptionId, $params);

            $stripeSubscription = $this->syncSubscription($subscription);

            $this->log('Subscription price changed', [
                'id' => $subscriptionId,
                'new_price' => $newPriceId
            ]);

            return $stripeSubscription;
        } catch (ApiErrorException $e) {
            $this->log('Subscription price change failed', [
                'id' => $subscriptionId,
                'new_price' => $newPriceId,
                'error' => $e->getMessage()
            ], 'error');
            throw $e;
        }
    }

    /**
     * List Customer's Subscriptions
     *
     * Official docs: https://stripe.com/docs/api/subscriptions/list
     *
     * @param string $customerId
     * @param array $params Additional parameters (status, limit, etc.)
     * @return array
     * @throws ApiErrorException
     */
    public function listSubscriptions(string $customerId, array $params = []): array
    {
        $this->ensureConfigured();
        try {
            $params['customer'] = $customerId;

            $subscriptions = $this->stripe->subscriptions->all($params);

            return $subscriptions->data;
        } catch (ApiErrorException $e) {
            $this->log('Subscriptions list failed', [
                'customer' => $customerId,
                'error' => $e->getMessage()
            ], 'error');
            throw $e;
        }
    }

    /**
     * Sync Subscription from Stripe to local database
     *
     * @param Subscription $subscription
     * @return StripeSubscription
     */
    public function syncSubscription(Subscription $subscription): StripeSubscription
    {
        $data = [
            'stripe_customer_id' => $subscription->customer,
            'stripe_price_id' => $subscription->items->data[0]->price->id ?? null,
            'stripe_product_id' => $subscription->items->data[0]->price->product ?? null,
            'status' => SubscriptionStatus::from($subscription->status),
            'current_period_start' => $subscription->current_period_start ? date('Y-m-d H:i:s', $subscription->current_period_start) : null,
            'current_period_end' => $subscription->current_period_end ? date('Y-m-d H:i:s', $subscription->current_period_end) : null,
            'trial_start' => $subscription->trial_start ? date('Y-m-d H:i:s', $subscription->trial_start) : null,
            'trial_end' => $subscription->trial_end ? date('Y-m-d H:i:s', $subscription->trial_end) : null,
            'canceled_at' => $subscription->canceled_at ? date('Y-m-d H:i:s', $subscription->canceled_at) : null,
            'ended_at' => $subscription->ended_at ? date('Y-m-d H:i:s', $subscription->ended_at) : null,
            'metadata' => $subscription->metadata->toArray(),
        ];

        // Try to find user_id from metadata
        if (isset($subscription->metadata['user_id'])) {
            $data['user_id'] = $subscription->metadata['user_id'];
        }

        return StripeSubscription::updateOrCreate(
            ['stripe_subscription_id' => $subscription->id],
            $data
        );
    }

    /**
     * Get Subscription by ID from database
     *
     * @param string $subscriptionId
     * @return StripeSubscription|null
     */
    public function getSubscriptionFromDatabase(string $subscriptionId): ?StripeSubscription
    {
        return StripeSubscription::where('stripe_subscription_id', $subscriptionId)->first();
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
                ->{$level}('[Stripe Subscription] ' . $message, $context);
        }
    }
}
