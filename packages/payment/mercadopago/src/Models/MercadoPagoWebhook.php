<?php

namespace Eduardoks98\PaymentMercadoPago\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Eduardoks98\PaymentMercadoPago\Enums\WebhookTopic;

class MercadoPagoWebhook extends Model
{
    use HasUuids;

    protected $table = 'mercadopago_webhooks';

    protected $fillable = [
        'topic',
        'resource_id',
        'data_id',
        'action',
        'payload',
        'processed',
        'processed_at',
        'error_message',
    ];

    protected $casts = [
        'topic' => WebhookTopic::class,
        'payload' => 'array',
        'processed' => 'boolean',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Mark webhook as processed
     */
    public function markAsProcessed(): void
    {
        $this->update([
            'processed' => true,
            'processed_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Mark webhook as failed
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'processed' => false,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Scope: Filter by topic
     */
    public function scopeTopic($query, WebhookTopic $topic)
    {
        return $query->where('topic', $topic->value);
    }

    /**
     * Scope: Filter by processed status
     */
    public function scopeProcessed($query, bool $processed = true)
    {
        return $query->where('processed', $processed);
    }

    /**
     * Scope: Filter unprocessed webhooks
     */
    public function scopeUnprocessed($query)
    {
        return $query->where('processed', false);
    }
}
