<?php

namespace Eduardoks98\PaymentAbacatePay\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Eduardoks98\PaymentAbacatePay\Enums\Frequency;
use Eduardoks98\PaymentAbacatePay\Enums\PaymentMethod;
use Eduardoks98\PaymentAbacatePay\Enums\BillingStatus;

class EnumTest extends TestCase
{
    public function test_frequency_enum_has_correct_values(): void
    {
        $this->assertEquals('one_time', Frequency::ONE_TIME->value);
        $this->assertEquals('monthly', Frequency::MONTHLY->value);
        $this->assertEquals('yearly', Frequency::YEARLY->value);
    }

    public function test_payment_method_enum_has_correct_values(): void
    {
        $this->assertEquals('pix', PaymentMethod::PIX->value);
        $this->assertEquals('card', PaymentMethod::CARD->value);
    }

    public function test_billing_status_enum_has_correct_values(): void
    {
        $this->assertEquals('pending', BillingStatus::PENDING->value);
        $this->assertEquals('paid', BillingStatus::PAID->value);
        $this->assertEquals('cancelled', BillingStatus::CANCELLED->value);
        $this->assertEquals('expired', BillingStatus::EXPIRED->value);
        $this->assertEquals('refunded', BillingStatus::REFUNDED->value);
    }

    public function test_frequency_returns_correct_labels(): void
    {
        $this->assertEquals('One Time Payment', Frequency::ONE_TIME->label());
        $this->assertEquals('Monthly Subscription', Frequency::MONTHLY->label());
        $this->assertEquals('Yearly Subscription', Frequency::YEARLY->label());
    }

    public function test_payment_method_returns_correct_labels(): void
    {
        $this->assertEquals('PIX', PaymentMethod::PIX->label());
        $this->assertEquals('Credit/Debit Card', PaymentMethod::CARD->label());
    }

    public function test_billing_status_final_states(): void
    {
        $this->assertTrue(BillingStatus::PAID->isFinal());
        $this->assertTrue(BillingStatus::CANCELLED->isFinal());
        $this->assertTrue(BillingStatus::EXPIRED->isFinal());
        $this->assertTrue(BillingStatus::REFUNDED->isFinal());
        $this->assertFalse(BillingStatus::PENDING->isFinal());
    }

    public function test_payment_method_instant_check(): void
    {
        $this->assertTrue(PaymentMethod::PIX->isInstant());
        $this->assertFalse(PaymentMethod::CARD->isInstant());
    }
}
