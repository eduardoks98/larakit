<?php

namespace Eduardoks98\PaymentAbacatePay\Models;

/**
 * Customer Data DTO for AbacatePay SDK
 *
 * This is a wrapper that will be converted to AbacatePay\Customer
 */
class CustomerData
{
    /**
     * @param string $email Customer email
     * @param string|null $name Customer name
     * @param string|null $cellphone Customer cellphone
     * @param string|null $taxId Customer tax ID (CPF/CNPJ)
     */
    public function __construct(
        public string $email,
        public ?string $name = null,
        public ?string $cellphone = null,
        public ?string $taxId = null,
    ) {
    }

    /**
     * Convert to AbacatePay SDK Customer object
     */
    public function toSdkCustomer(): \AbacatePay\Customer
    {
        $data = [
            'email' => $this->email,
        ];

        if ($this->name) {
            $data['name'] = $this->name;
        }

        if ($this->cellphone) {
            $data['cellphone'] = $this->cellphone;
        }

        if ($this->taxId) {
            $data['taxId'] = $this->taxId;
        }

        return new \AbacatePay\Customer($data);
    }

    /**
     * Create from array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'],
            name: $data['name'] ?? null,
            cellphone: $data['cellphone'] ?? null,
            taxId: $data['taxId'] ?? $data['tax_id'] ?? null,
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'name' => $this->name,
            'cellphone' => $this->cellphone,
            'taxId' => $this->taxId,
        ];
    }

    /**
     * Create from Laravel User model
     */
    public static function fromUser($user): self
    {
        return new self(
            email: $user->email,
            name: $user->name ?? null,
            cellphone: $user->phone ?? $user->cellphone ?? null,
            taxId: $user->cpf ?? $user->cnpj ?? $user->tax_id ?? null,
        );
    }
}
