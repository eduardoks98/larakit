<?php

namespace Eduardoks98\PaymentAbacatePay\Models;

use Eduardoks98\PaymentAbacatePay\Enums\Frequency;
use Eduardoks98\PaymentAbacatePay\Enums\PaymentMethod;

/**
 * Billing Data DTO for creating billings with AbacatePay SDK
 *
 * This is a wrapper that will be converted to AbacatePay\Billing
 */
class BillingData
{
    /**
     * @param Frequency $frequency Billing frequency (one_time, monthly, yearly)
     * @param array<PaymentMethod> $methods Payment methods allowed (pix, card)
     * @param array<ProductData> $products Products to be billed
     * @param CustomerData|null $customer Customer information
     * @param array|null $metadata Additional metadata
     * @param string|null $returnUrl URL to redirect after payment
     * @param string|null $completionUrl URL to redirect after successful payment
     */
    public function __construct(
        public Frequency $frequency,
        public array $methods,
        public array $products,
        public ?CustomerData $customer = null,
        public ?array $metadata = null,
        public ?string $returnUrl = null,
        public ?string $completionUrl = null,
    ) {
    }

    /**
     * Convert to AbacatePay SDK Billing object
     */
    public function toSdkBilling(): \AbacatePay\Billing
    {
        $data = [
            'frequency' => $this->frequency->toSdkValue(),
            'methods' => array_map(
                fn(PaymentMethod $method) => $method->toSdkValue(),
                $this->methods
            ),
            'products' => array_map(
                fn(ProductData $product) => $product->toSdkProduct(),
                $this->products
            ),
        ];

        if ($this->customer) {
            $data['customer'] = $this->customer->toSdkCustomer();
        }

        if ($this->metadata) {
            $data['metadata'] = $this->metadata;
        }

        if ($this->returnUrl) {
            $data['returnUrl'] = $this->returnUrl;
        }

        if ($this->completionUrl) {
            $data['completionUrl'] = $this->completionUrl;
        }

        return new \AbacatePay\Billing($data);
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            frequency: Frequency::from($data['frequency']),
            methods: array_map(
                fn($method) => PaymentMethod::from($method),
                $data['methods']
            ),
            products: array_map(
                fn($product) => ProductData::fromArray($product),
                $data['products']
            ),
            customer: isset($data['customer']) ? CustomerData::fromArray($data['customer']) : null,
            metadata: $data['metadata'] ?? null,
            returnUrl: $data['returnUrl'] ?? $data['return_url'] ?? null,
            completionUrl: $data['completionUrl'] ?? $data['completion_url'] ?? null,
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'frequency' => $this->frequency->value,
            'methods' => array_map(
                fn(PaymentMethod $method) => $method->value,
                $this->methods
            ),
            'products' => array_map(
                fn(ProductData $product) => $product->toArray(),
                $this->products
            ),
            'customer' => $this->customer?->toArray(),
            'metadata' => $this->metadata,
            'returnUrl' => $this->returnUrl,
            'completionUrl' => $this->completionUrl,
        ];
    }
}
