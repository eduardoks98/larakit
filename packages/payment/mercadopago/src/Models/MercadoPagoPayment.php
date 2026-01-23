<?php

namespace Eduardoks98\PaymentMercadoPago\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Eduardoks98\PaymentMercadoPago\Enums\PaymentStatus;
use Eduardoks98\PaymentMercadoPago\Enums\PaymentMethod;

class MercadoPagoPayment extends Model
{
    use HasUuids;

    protected $table = 'mercadopago_payments';

    protected $fillable = [
        'external_reference',
        'mercadopago_id',
        'order_id',
        'payment_method',
        'payment_type',
        'status',
        'status_detail',
        'amount',
        'currency',
        'payer_email',
        'payer_name',
        'payer_document',
        'description',
        'metadata',
        'qr_code',
        'qr_code_base64',
        'ticket_url',
        'barcode',
        'expiration_date',
        'approved_at',
        'rejected_at',
        'refunded_at',
        'cancelled_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'status' => PaymentStatus::class,
        'payment_method' => PaymentMethod::class,
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'refunded_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'expiration_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Check if payment is approved
     */
    public function isApproved(): bool
    {
        return $this->status === PaymentStatus::APPROVED;
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status?->isPending() ?? false;
    }

    /**
     * Check if payment is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === PaymentStatus::REJECTED;
    }

    /**
     * Check if payment requires action (PIX/Boleto)
     */
    public function requiresAction(): bool
    {
        return $this->status === PaymentStatus::ACTION_REQUIRED;
    }

    /**
     * Get PIX QR Code data URI for display
     */
    public function getPixQrCodeDataUri(): ?string
    {
        if (!$this->qr_code_base64) {
            return null;
        }

        return "data:image/jpeg;base64,{$this->qr_code_base64}";
    }

    /**
     * Scope: Filter by status
     */
    public function scopeStatus($query, PaymentStatus $status)
    {
        return $query->where('status', $status->value);
    }

    /**
     * Scope: Filter by payment method
     */
    public function scopePaymentMethod($query, PaymentMethod $method)
    {
        return $query->where('payment_method', $method->value);
    }

    /**
     * Scope: Filter by external reference
     */
    public function scopeExternalReference($query, string $reference)
    {
        return $query->where('external_reference', $reference);
    }

    /**
     * Scope: Filter by MercadoPago ID
     */
    public function scopeMercadoPagoId($query, string $id)
    {
        return $query->where('mercadopago_id', $id);
    }
}
