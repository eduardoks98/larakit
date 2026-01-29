<?php

namespace Eduardoks98\PaymentStripe\Services;

use Stripe\StripeClient;
use Stripe\Customer;
use Stripe\PaymentMethod;
use Stripe\Exception\ApiErrorException;
use Eduardoks98\PaymentStripe\Models\StripeCustomer;
use Illuminate\Support\Facades\Log;

/**
 * CustomerService - Stripe Customer API
 *
 * Based on official Stripe Customer API documentation:
 * https://stripe.com/docs/api/customers
 *
 * Uses stripe/stripe-php v13.x SDK
 */
class CustomerService
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
     * Create a Customer
     *
     * Official docs: https://stripe.com/docs/api/customers/create
     *
     * @param array $params Customer parameters (email, name, phone, address, metadata, etc.)
     * @return StripeCustomer
     * @throws ApiErrorException
     */
    public function createCustomer(array $params): StripeCustomer
    {
        $this->ensureConfigured();
        try {
            // Add default metadata
            if (!isset($params['metadata'])) {
                $params['metadata'] = config('stripe.customer.metadata', []);
            }

            // Create Customer via Stripe API
            $customer = $this->stripe->customers->create($params);

            // Store in database
            $stripeCustomer = $this->syncCustomer($customer);

            $this->log('Customer created', ['id' => $customer->id]);

            return $stripeCustomer;
        } catch (ApiErrorException $e) {
            $this->log('Customer creation failed', ['error' => $e->getMessage()], 'error');
            throw $e;
        }
    }

    /**
     * Retrieve a Customer
     *
     * Official docs: https://stripe.com/docs/api/customers/retrieve
     *
     * @param string $customerId
     * @return Customer
     * @throws ApiErrorException
     */
    public function retrieveCustomer(string $customerId): Customer
    {
        $this->ensureConfigured();
        return $this->stripe->customers->retrieve($customerId);
    }

    /**
     * Update a Customer
     *
     * Official docs: https://stripe.com/docs/api/customers/update
     *
     * @param string $customerId
     * @param array $params
     * @return StripeCustomer
     * @throws ApiErrorException
     */
    public function updateCustomer(string $customerId, array $params): StripeCustomer
    {
        $this->ensureConfigured();
        try {
            $customer = $this->stripe->customers->update($customerId, $params);

            $stripeCustomer = $this->syncCustomer($customer);

            $this->log('Customer updated', ['id' => $customerId]);

            return $stripeCustomer;
        } catch (ApiErrorException $e) {
            $this->log('Customer update failed', [
                'id' => $customerId,
                'error' => $e->getMessage()
            ], 'error');
            throw $e;
        }
    }

    /**
     * Delete a Customer
     *
     * Official docs: https://stripe.com/docs/api/customers/delete
     *
     * @param string $customerId
     * @return bool
     * @throws ApiErrorException
     */
    public function deleteCustomer(string $customerId): bool
    {
        $this->ensureConfigured();
        try {
            $this->stripe->customers->delete($customerId);

            // Delete from database
            StripeCustomer::where('stripe_customer_id', $customerId)->delete();

            $this->log('Customer deleted', ['id' => $customerId]);

            return true;
        } catch (ApiErrorException $e) {
            $this->log('Customer deletion failed', [
                'id' => $customerId,
                'error' => $e->getMessage()
            ], 'error');
            throw $e;
        }
    }

    /**
     * Attach a PaymentMethod to a Customer
     *
     * Official docs: https://stripe.com/docs/api/payment_methods/attach
     *
     * @param string $paymentMethodId
     * @param string $customerId
     * @return PaymentMethod
     * @throws ApiErrorException
     */
    public function attachPaymentMethod(string $paymentMethodId, string $customerId): PaymentMethod
    {
        $this->ensureConfigured();
        try {
            $paymentMethod = $this->stripe->paymentMethods->attach($paymentMethodId, [
                'customer' => $customerId,
            ]);

            $this->log('PaymentMethod attached', [
                'payment_method' => $paymentMethodId,
                'customer' => $customerId
            ]);

            return $paymentMethod;
        } catch (ApiErrorException $e) {
            $this->log('PaymentMethod attachment failed', [
                'payment_method' => $paymentMethodId,
                'customer' => $customerId,
                'error' => $e->getMessage()
            ], 'error');
            throw $e;
        }
    }

    /**
     * Detach a PaymentMethod from a Customer
     *
     * Official docs: https://stripe.com/docs/api/payment_methods/detach
     *
     * @param string $paymentMethodId
     * @return PaymentMethod
     * @throws ApiErrorException
     */
    public function detachPaymentMethod(string $paymentMethodId): PaymentMethod
    {
        $this->ensureConfigured();
        try {
            $paymentMethod = $this->stripe->paymentMethods->detach($paymentMethodId);

            $this->log('PaymentMethod detached', ['payment_method' => $paymentMethodId]);

            return $paymentMethod;
        } catch (ApiErrorException $e) {
            $this->log('PaymentMethod detachment failed', [
                'payment_method' => $paymentMethodId,
                'error' => $e->getMessage()
            ], 'error');
            throw $e;
        }
    }

    /**
     * List Customer's PaymentMethods
     *
     * Official docs: https://stripe.com/docs/api/payment_methods/customer_list
     *
     * @param string $customerId
     * @param string $type PaymentMethod type (e.g., 'card')
     * @return array
     * @throws ApiErrorException
     */
    public function listPaymentMethods(string $customerId, string $type = 'card'): array
    {
        $this->ensureConfigured();
        try {
            $paymentMethods = $this->stripe->customers->allPaymentMethods($customerId, [
                'type' => $type,
            ]);

            return $paymentMethods->data;
        } catch (ApiErrorException $e) {
            $this->log('PaymentMethods list failed', [
                'customer' => $customerId,
                'error' => $e->getMessage()
            ], 'error');
            throw $e;
        }
    }

    /**
     * Set default PaymentMethod for a Customer
     *
     * @param string $customerId
     * @param string $paymentMethodId
     * @return StripeCustomer
     * @throws ApiErrorException
     */
    public function setDefaultPaymentMethod(string $customerId, string $paymentMethodId): StripeCustomer
    {
        $this->ensureConfigured();
        try {
            $customer = $this->stripe->customers->update($customerId, [
                'invoice_settings' => [
                    'default_payment_method' => $paymentMethodId,
                ],
            ]);

            $stripeCustomer = $this->syncCustomer($customer);

            $this->log('Default PaymentMethod set', [
                'customer' => $customerId,
                'payment_method' => $paymentMethodId
            ]);

            return $stripeCustomer;
        } catch (ApiErrorException $e) {
            $this->log('Set default PaymentMethod failed', [
                'customer' => $customerId,
                'payment_method' => $paymentMethodId,
                'error' => $e->getMessage()
            ], 'error');
            throw $e;
        }
    }

    /**
     * Find or create Customer by user
     *
     * @param mixed $user
     * @return StripeCustomer
     * @throws ApiErrorException
     */
    public function findOrCreateCustomer($user): StripeCustomer
    {
        // Check if customer exists in database
        $stripeCustomer = StripeCustomer::where('user_id', $user->id)->first();

        if ($stripeCustomer) {
            return $stripeCustomer;
        }

        // Create new customer
        return $this->createCustomer([
            'email' => $user->email,
            'name' => $user->name ?? null,
            'metadata' => [
                'user_id' => $user->id,
            ],
        ]);
    }

    /**
     * Sync Customer from Stripe to local database
     *
     * @param Customer $customer
     * @return StripeCustomer
     */
    public function syncCustomer(Customer $customer): StripeCustomer
    {
        $data = [
            'stripe_customer_id' => $customer->id,
            'email' => $customer->email,
            'name' => $customer->name,
            'phone' => $customer->phone,
            'address' => $customer->address?->toArray(),
            'metadata' => $customer->metadata->toArray(),
            'default_payment_method' => $customer->invoice_settings?->default_payment_method,
        ];

        // Try to find user_id from metadata
        if (isset($customer->metadata['user_id'])) {
            $data['user_id'] = $customer->metadata['user_id'];
        }

        return StripeCustomer::updateOrCreate(
            ['stripe_customer_id' => $customer->id],
            $data
        );
    }

    /**
     * Get Customer by ID from database
     *
     * @param string $customerId
     * @return StripeCustomer|null
     */
    public function getCustomerFromDatabase(string $customerId): ?StripeCustomer
    {
        return StripeCustomer::where('stripe_customer_id', $customerId)->first();
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
                ->{$level}('[Stripe Customer] ' . $message, $context);
        }
    }
}
