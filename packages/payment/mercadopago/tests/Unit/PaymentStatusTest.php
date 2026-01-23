<?php

namespace Eduardoks98\PaymentMercadoPago\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Eduardoks98\PaymentMercadoPago\Enums\PaymentStatus;

class PaymentStatusTest extends TestCase
{
    /** @test */
    public function it_has_correct_status_values()
    {
        $this->assertEquals('pending', PaymentStatus::PENDING->value);
        $this->assertEquals('approved', PaymentStatus::APPROVED->value);
        $this->assertEquals('rejected', PaymentStatus::REJECTED->value);
    }

    /** @test */
    public function it_returns_correct_labels()
    {
        $this->assertEquals('Approved', PaymentStatus::APPROVED->label());
        $this->assertEquals('Pending', PaymentStatus::PENDING->label());
        $this->assertEquals('Rejected', PaymentStatus::REJECTED->label());
    }

    /** @test */
    public function it_detects_final_states()
    {
        $this->assertTrue(PaymentStatus::APPROVED->isFinal());
        $this->assertTrue(PaymentStatus::REJECTED->isFinal());
        $this->assertTrue(PaymentStatus::CANCELLED->isFinal());
        $this->assertFalse(PaymentStatus::PENDING->isFinal());
        $this->assertFalse(PaymentStatus::IN_PROCESS->isFinal());
    }

    /** @test */
    public function it_detects_successful_payments()
    {
        $this->assertTrue(PaymentStatus::APPROVED->isSuccessful());
        $this->assertFalse(PaymentStatus::PENDING->isSuccessful());
        $this->assertFalse(PaymentStatus::REJECTED->isSuccessful());
    }

    /** @test */
    public function it_detects_pending_payments()
    {
        $this->assertTrue(PaymentStatus::PENDING->isPending());
        $this->assertTrue(PaymentStatus::IN_PROCESS->isPending());
        $this->assertTrue(PaymentStatus::ACTION_REQUIRED->isPending());
        $this->assertFalse(PaymentStatus::APPROVED->isPending());
    }
}
