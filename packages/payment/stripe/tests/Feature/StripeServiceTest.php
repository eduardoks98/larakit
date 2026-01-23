<?php

namespace Eduardoks98\PaymentStripe\Tests\Feature;

use Eduardoks98\PaymentStripe\Services\StripeService;
use Eduardoks98\PaymentStripe\Enums\PaymentStatus;
use Orchestra\Testbench\TestCase;
use Eduardoks98\PaymentStripe\StripeServiceProvider;

class StripeServiceTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [StripeServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup test configuration
        $app['config']->set('stripe.secret_key', 'sk_test_fake_key_for_testing');
        $app['config']->set('stripe.currency', 'usd');
    }

    /** @test */
    public function it_can_create_payment_intent()
    {
        // This is a basic structure for testing
        // In real tests, you would mock Stripe API responses

        $this->markTestSkipped('Requires Stripe API mocking or real test keys');

        $service = app(StripeService::class);

        $payment = $service->createPaymentIntent(
            amount: 2000,
            currency: 'usd',
            options: [
                'description' => 'Test payment',
            ]
        );

        $this->assertNotNull($payment);
        $this->assertEquals(2000, $payment->amount);
        $this->assertEquals('usd', $payment->currency);
    }

    /** @test */
    public function it_can_sync_payment_intent()
    {
        // This test would verify that PaymentIntent data is correctly synced to database
        $this->markTestSkipped('Requires database setup and Stripe API mocking');
    }

    /** @test */
    public function payment_status_enum_has_correct_values()
    {
        $statuses = [
            'requires_payment_method',
            'requires_confirmation',
            'requires_action',
            'processing',
            'requires_capture',
            'canceled',
            'succeeded',
        ];

        foreach ($statuses as $status) {
            $enum = PaymentStatus::from($status);
            $this->assertEquals($status, $enum->value);
        }
    }

    /** @test */
    public function payment_status_helper_methods_work()
    {
        $succeeded = PaymentStatus::SUCCEEDED;
        $this->assertTrue($succeeded->isCompleted());
        $this->assertFalse($succeeded->isPending());
        $this->assertFalse($succeeded->isFailed());

        $processing = PaymentStatus::PROCESSING;
        $this->assertTrue($processing->isPending());
        $this->assertFalse($processing->isCompleted());

        $canceled = PaymentStatus::CANCELED;
        $this->assertTrue($canceled->isFailed());
        $this->assertFalse($canceled->isCompleted());
    }
}
