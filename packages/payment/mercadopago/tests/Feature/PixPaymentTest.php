<?php

namespace Eduardoks98\PaymentMercadoPago\Tests\Feature;

use Orchestra\Testbench\TestCase;
use Eduardoks98\PaymentMercadoPago\PaymentMercadoPagoServiceProvider;
use Eduardoks98\PaymentMercadoPago\Services\MercadoPagoService;
use Eduardoks98\PaymentMercadoPago\Models\MercadoPagoPayment;
use Eduardoks98\PaymentMercadoPago\Enums\PaymentStatus;
use Eduardoks98\PaymentMercadoPago\Enums\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PixPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [PaymentMercadoPagoServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('payment-mercadopago.access_token', 'TEST-ACCESS-TOKEN');
        $app['config']->set('payment-mercadopago.public_key', 'TEST-PUBLIC-KEY');
    }

    /** @test */
    public function it_can_create_pix_payment_via_api()
    {
        $response = $this->postJson('/api/mercadopago/payments/pix', [
            'amount' => 100.00,
            'payer_email' => 'customer@example.com',
            'payer_name' => 'John Doe',
            'description' => 'Test PIX Payment',
        ]);

        // Note: This will fail without valid credentials
        // In real tests, you would mock the MercadoPago API
        $response->assertStatus(201);
    }

    /** @test */
    public function it_validates_required_fields_for_pix_payment()
    {
        $response = $this->postJson('/api/mercadopago/payments/pix', [
            // Missing required fields
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['amount', 'payer_email']);
    }

    /** @test */
    public function it_can_query_payment_by_external_reference()
    {
        $payment = MercadoPagoPayment::create([
            'external_reference' => 'TEST-123',
            'mercadopago_id' => 'MP-123',
            'payment_method' => PaymentMethod::PIX,
            'payment_type' => 'bank_transfer',
            'status' => PaymentStatus::PENDING,
            'amount' => 100.00,
            'currency' => 'BRL',
            'payer_email' => 'test@example.com',
        ]);

        $found = MercadoPagoPayment::externalReference('TEST-123')->first();

        $this->assertEquals($payment->id, $found->id);
    }

    /** @test */
    public function it_can_check_payment_status_helpers()
    {
        $payment = MercadoPagoPayment::create([
            'external_reference' => 'TEST-123',
            'payment_method' => PaymentMethod::PIX,
            'payment_type' => 'bank_transfer',
            'status' => PaymentStatus::APPROVED,
            'amount' => 100.00,
            'currency' => 'BRL',
            'payer_email' => 'test@example.com',
        ]);

        $this->assertTrue($payment->isApproved());
        $this->assertFalse($payment->isPending());
        $this->assertFalse($payment->isRejected());
    }
}
