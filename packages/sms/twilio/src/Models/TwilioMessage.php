<?php

namespace Eduardoks98\SmsTwilio\Models;

use Eduardoks98\SmsTwilio\Enums\MessageDirection;
use Eduardoks98\SmsTwilio\Enums\MessageStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Twilio Message Model
 *
 * Stores sent and received SMS messages with delivery tracking
 *
 * @property int $id
 * @property string $message_sid Twilio message SID
 * @property string $from Sender phone number (E.164 format)
 * @property string $to Recipient phone number (E.164 format)
 * @property string $body Message content
 * @property MessageStatus $status Current message status
 * @property MessageDirection $direction Message direction
 * @property int|null $num_segments Number of SMS segments
 * @property string|null $price Message price
 * @property string|null $price_unit Price currency (USD, BRL, etc)
 * @property int|null $error_code Twilio error code
 * @property string|null $error_message Error description
 * @property array|null $metadata Additional custom data
 * @property \Carbon\Carbon|null $sent_at When message was sent
 * @property \Carbon\Carbon|null $delivered_at When message was delivered
 * @property \Carbon\Carbon|null $failed_at When message failed
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class TwilioMessage extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'twilio_messages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'message_sid',
        'from',
        'to',
        'body',
        'status',
        'direction',
        'num_segments',
        'price',
        'price_unit',
        'error_code',
        'error_message',
        'metadata',
        'sent_at',
        'delivered_at',
        'failed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => MessageStatus::class,
        'direction' => MessageDirection::class,
        'metadata' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'failed_at' => 'datetime',
        'num_segments' => 'integer',
        'error_code' => 'integer',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    protected $hidden = [];

    /**
     * Scope for outbound messages
     */
    public function scopeOutbound($query)
    {
        return $query->whereIn('direction', [
            MessageDirection::OUTBOUND_API->value,
            MessageDirection::OUTBOUND_CALL->value,
            MessageDirection::OUTBOUND_REPLY->value,
        ]);
    }

    /**
     * Scope for inbound messages
     */
    public function scopeInbound($query)
    {
        return $query->where('direction', MessageDirection::INBOUND->value);
    }

    /**
     * Scope for delivered messages
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', MessageStatus::DELIVERED->value);
    }

    /**
     * Scope for failed messages
     */
    public function scopeFailed($query)
    {
        return $query->whereIn('status', [
            MessageStatus::FAILED->value,
            MessageStatus::UNDELIVERED->value,
        ]);
    }

    /**
     * Scope for pending messages
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', [
            MessageStatus::QUEUED->value,
            MessageStatus::SENDING->value,
            MessageStatus::SENT->value,
        ]);
    }

    /**
     * Check if message was successfully delivered
     *
     * @return bool
     */
    public function isDelivered(): bool
    {
        return $this->status === MessageStatus::DELIVERED;
    }

    /**
     * Check if message failed
     *
     * @return bool
     */
    public function hasFailed(): bool
    {
        return $this->status?->isFailure() ?? false;
    }

    /**
     * Check if message is still pending
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status?->isPending() ?? false;
    }

    /**
     * Get total cost
     *
     * @return float|null
     */
    public function getTotalCost(): ?float
    {
        if (!$this->price) {
            return null;
        }

        // Remove currency symbols and convert to float
        return (float) abs($this->price);
    }
}
