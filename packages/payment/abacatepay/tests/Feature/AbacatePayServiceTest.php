<?php

namespace Eduardoks98\PaymentAbacatePay\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Eduardoks98\PaymentAbacatePay\Services\AbacatePayService;
use Eduardoks98\PaymentAbacatePay\Models\BillingData;
use Eduardoks98\PaymentAbacatePay\Models\ProductData;
use Eduardoks98\PaymentAbacatePay\Models\CustomerData;
use Eduardoks98\PaymentAbacatePay\Enums\Frequency;
use Eduardoks98\PaymentAbacatePay\Enums\PaymentMethod;

class AbacatePayServiceTest extends TestCase
{
    protected AbacatePayService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Skip tests if token is not configured
        if (!getenv('ABACATEPAY_TOKEN')) {
            $this->markTestSkipped('ABACATEPAY_TOKEN not configured');
        }

        $this->service = new AbacatePayService(getenv('ABACATEPAY_TOKEN'));
    }

    public function test_can_create_billing_data(): void
    {
        $billingData = new BillingData(
            frequency: Frequency::ONE_TIME,
            methods: [PaymentMethod::PIX],
            products: [
                new ProductData(
                    name: 'Test Product',
                    price: 10000, // R$ 100.00
                ),
            ],
            customer: new CustomerData(
                email: 'test@example.com',
                name: 'Test Customer',
            ),
        );

        $this->assertInstanceOf(BillingData::class, $billingData);
        $this->assertEquals(Frequency::ONE_TIME, $billingData->frequency);
        $this->assertCount(1, $billingData->methods);
        $this->assertCount(1, $billingData->products);
    }

    public function test_can_convert_billing_data_to_sdk_billing(): void
    {
        $billingData = new BillingData(
            frequency: Frequency::ONE_TIME,
            methods: [PaymentMethod::PIX],
            products: [
                new ProductData(
                    name: 'Test Product',
                    price: 10000,
                ),
            ],
            customer: new CustomerData(
                email: 'test@example.com',
            ),
        );

        $sdkBilling = $billingData->toSdkBilling();

        $this->assertInstanceOf(\AbacatePay\Billing::class, $sdkBilling);
    }

    public function test_product_data_calculates_total_correctly(): void
    {
        $product = new ProductData(
            name: 'Test Product',
            price: 10000,
            quantity: 3,
        );

        $this->assertEquals(30000, $product->getTotalPrice());
        $this->assertEquals('R$ 100,00', $product->getFormattedPrice());
        $this->assertEquals('R$ 300,00', $product->getFormattedTotalPrice());
    }

    public function test_can_create_customer_data_from_array(): void
    {
        $data = [
            'email' => 'test@example.com',
            'name' => 'Test Customer',
            'cellphone' => '11999999999',
            'taxId' => '12345678900',
        ];

        $customer = CustomerData::fromArray($data);

        $this->assertEquals('test@example.com', $customer->email);
        $this->assertEquals('Test Customer', $customer->name);
        $this->assertEquals('11999999999', $customer->cellphone);
        $this->assertEquals('12345678900', $customer->taxId);
    }
}
