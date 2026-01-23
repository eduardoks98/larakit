<?php

namespace Eduardoks98\PaymentAbacatePay\Services;

use AbacatePay\Client;
use AbacatePay\BillingClient;
use AbacatePay\CustomerClient;
use Eduardoks98\PaymentAbacatePay\Models\BillingData;
use Eduardoks98\PaymentAbacatePay\Models\AbacatePayBilling;
use Eduardoks98\PaymentAbacatePay\Enums\BillingStatus;
use Illuminate\Support\Facades\Log;

/**
 * AbacatePay Service - Wrapper for official AbacatePay PHP SDK
 *
 * This service wraps the official AbacatePay SDK clients and provides
 * a Laravel-friendly interface with optional database persistence.
 */
class AbacatePayService
{
    protected BillingClient $billingClient;
    protected CustomerClient $customerClient;

    /**
     * Initialize AbacatePay service with API token
     */
    public function __construct(?string $token = null)
    {
        $token = $token ?? config('abacatepay.token');

        if (!$token) {
            throw new \InvalidArgumentException('AbacatePay token is required. Set ABACATEPAY_TOKEN in .env');
        }

        // Set token using official SDK method
        Client::setToken($token);

        // Initialize official SDK clients
        $this->billingClient = new BillingClient();
        $this->customerClient = new CustomerClient();
    }

    /**
     * Create a new billing using the official SDK
     *
     * @param BillingData $billingData Billing data
     * @param int|null $userId User ID to associate with billing (optional)
     * @return array Response from SDK with billing details
     */
    public function createBilling(BillingData $billingData, ?int $userId = null): array
    {
        try {
            // Convert to SDK Billing object
            $sdkBilling = $billingData->toSdkBilling();

            // Create billing using official SDK
            $response = $this->billingClient->create($sdkBilling);

            // Store in database if enabled
            if (config('abacatepay.store_billings', true)) {
                $this->storeBilling($response, $billingData, $userId);
            }

            return $response;
        } catch (\Exception $e) {
            Log::error('AbacatePay billing creation failed', [
                'error' => $e->getMessage(),
                'billing_data' => $billingData->toArray(),
            ]);

            throw $e;
        }
    }

    /**
     * Get billing by ID using official SDK
     *
     * @param string $billingId AbacatePay billing ID
     * @return array Billing details
     */
    public function getBilling(string $billingId): array
    {
        try {
            return $this->billingClient->retrieve($billingId);
        } catch (\Exception $e) {
            Log::error('AbacatePay billing retrieval failed', [
                'error' => $e->getMessage(),
                'billing_id' => $billingId,
            ]);

            throw $e;
        }
    }

    /**
     * List all billings using official SDK
     *
     * @param array $params Query parameters (limit, offset, etc.)
     * @return array List of billings
     */
    public function listBillings(array $params = []): array
    {
        try {
            return $this->billingClient->list($params);
        } catch (\Exception $e) {
            Log::error('AbacatePay billing list failed', [
                'error' => $e->getMessage(),
                'params' => $params,
            ]);

            throw $e;
        }
    }

    /**
     * Create a customer using official SDK
     *
     * @param array $customerData Customer data
     * @return array Created customer
     */
    public function createCustomer(array $customerData): array
    {
        try {
            return $this->customerClient->create($customerData);
        } catch (\Exception $e) {
            Log::error('AbacatePay customer creation failed', [
                'error' => $e->getMessage(),
                'customer_data' => $customerData,
            ]);

            throw $e;
        }
    }

    /**
     * Get customer by ID using official SDK
     *
     * @param string $customerId AbacatePay customer ID
     * @return array Customer details
     */
    public function getCustomer(string $customerId): array
    {
        try {
            return $this->customerClient->retrieve($customerId);
        } catch (\Exception $e) {
            Log::error('AbacatePay customer retrieval failed', [
                'error' => $e->getMessage(),
                'customer_id' => $customerId,
            ]);

            throw $e;
        }
    }

    /**
     * Get the BillingClient instance for direct SDK access
     *
     * @return BillingClient
     */
    public function getBillingClient(): BillingClient
    {
        return $this->billingClient;
    }

    /**
     * Get the CustomerClient instance for direct SDK access
     *
     * @return CustomerClient
     */
    public function getCustomerClient(): CustomerClient
    {
        return $this->customerClient;
    }

    /**
     * Store billing in database
     */
    protected function storeBilling(array $response, BillingData $billingData, ?int $userId): AbacatePayBilling
    {
        // Calculate total amount from products
        $totalAmount = 0;
        foreach ($billingData->products as $product) {
            $totalAmount += $product->getTotalPrice();
        }

        return AbacatePayBilling::create([
            'user_id' => $userId,
            'abacatepay_id' => $response['id'] ?? null,
            'frequency' => $billingData->frequency,
            'methods' => array_map(
                fn($method) => $method->value,
                $billingData->methods
            ),
            'amount' => $totalAmount,
            'status' => BillingStatus::PENDING,
            'url' => $response['url'] ?? null,
            'products' => array_map(
                fn($product) => $product->toArray(),
                $billingData->products
            ),
            'customer_data' => $billingData->customer?->toArray(),
            'metadata' => $billingData->metadata,
            'return_url' => $billingData->returnUrl,
            'completion_url' => $billingData->completionUrl,
        ]);
    }

    /**
     * Update billing status from webhook
     */
    public function updateBillingStatus(string $abacatePayId, string $status, ?array $data = null): ?AbacatePayBilling
    {
        $billing = AbacatePayBilling::where('abacatepay_id', $abacatePayId)->first();

        if (!$billing) {
            return null;
        }

        $statusEnum = BillingStatus::tryFrom($status);

        if (!$statusEnum) {
            Log::warning('Unknown billing status from webhook', [
                'status' => $status,
                'billing_id' => $abacatePayId,
            ]);
            return $billing;
        }

        // Update billing based on status
        match($statusEnum) {
            BillingStatus::PAID => $billing->markAsPaid(),
            BillingStatus::CANCELLED => $billing->markAsCancelled(),
            BillingStatus::EXPIRED => $billing->markAsExpired(),
            BillingStatus::REFUNDED => $billing->markAsRefunded(),
            default => $billing->update(['status' => $statusEnum]),
        };

        return $billing;
    }
}
