<?php

namespace Eduardoks98\PaymentAbacatePay\Models;

/**
 * Product Data DTO for AbacatePay SDK
 *
 * This is a wrapper that will be converted to AbacatePay\Product
 */
class ProductData
{
    /**
     * @param string $name Product name
     * @param int $price Price in cents (e.g., 10000 = R$ 100.00)
     * @param int $quantity Product quantity (default: 1)
     * @param string|null $description Product description
     * @param string|null $externalId External product ID
     */
    public function __construct(
        public string $name,
        public int $price,
        public int $quantity = 1,
        public ?string $description = null,
        public ?string $externalId = null,
    ) {
    }

    /**
     * Convert to AbacatePay SDK Product object
     */
    public function toSdkProduct(): \AbacatePay\Product
    {
        $data = [
            'name' => $this->name,
            'price' => $this->price,
            'quantity' => $this->quantity,
        ];

        if ($this->description) {
            $data['description'] = $this->description;
        }

        if ($this->externalId) {
            $data['externalId'] = $this->externalId;
        }

        return new \AbacatePay\Product($data);
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            price: $data['price'],
            quantity: $data['quantity'] ?? 1,
            description: $data['description'] ?? null,
            externalId: $data['externalId'] ?? $data['external_id'] ?? null,
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'description' => $this->description,
            'externalId' => $this->externalId,
        ];
    }

    /**
     * Get formatted price in BRL
     */
    public function getFormattedPrice(): string
    {
        return 'R$ ' . number_format($this->price / 100, 2, ',', '.');
    }

    /**
     * Get total price (price * quantity) in cents
     */
    public function getTotalPrice(): int
    {
        return $this->price * $this->quantity;
    }

    /**
     * Get formatted total price in BRL
     */
    public function getFormattedTotalPrice(): string
    {
        return 'R$ ' . number_format($this->getTotalPrice() / 100, 2, ',', '.');
    }
}
