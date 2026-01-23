<?php

namespace Eduardoks98\SmsComtele\Models;

use Eduardoks98\SmsComtele\Enums\MessageStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Comtele Message Model
 *
 * Stores sent SMS messages with delivery tracking
 *
 * @property int $id
 * @property string $request_unique_id Comtele request UUID
 * @property string $sender Internal sender identifier
 * @property string $receivers Comma-separated phone numbers
 * @property string $content Message text content
 * @property MessageStatus $status Current message status
 * @property string|null $phone_number Individual phone number (for detailed reports)
 * @property string|null $status_date When status was updated
 * @property string|null $error_message Error description if failed
 * @property array|null $metadata Additional custom data
 * @property \Carbon\Carbon|null $delivered_at When message was delivered
 * @property \Carbon\Carbon|null $failed_at When message failed
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ComteleMessage extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'comtele_messages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'request_unique_id',
        'sender',
        'receivers',
        'content',
        'status',
        'phone_number',
        'status_date',
        'error_message',
        'metadata',
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
        'metadata' => 'array',
        'delivered_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<string>
     */
    protected $hidden = [];

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
        return $query->where('status', MessageStatus::ERROR->value);
    }

    /**
     * Scope for pending messages
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', [
            MessageStatus::PENDING->value,
            MessageStatus::PROCESSED->value,
        ]);
    }

    /**
     * Scope for messages by sender
     */
    public function scopeBySender($query, string $sender)
    {
        return $query->where('sender', $sender);
    }

    /**
     * Scope for messages by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
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
        return $this->status === MessageStatus::ERROR;
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
     * Get array of receiver phone numbers
     *
     * @return array
     */
    public function getReceiversArray(): array
    {
        if (empty($this->receivers)) {
            return [];
        }

        return array_map('trim', explode(',', $this->receivers));
    }

    /**
     * Get number of recipients
     *
     * @return int
     */
    public function getRecipientCount(): int
    {
        return count($this->getReceiversArray());
    }

    /**
     * Get message length
     *
     * @return int
     */
    public function getMessageLength(): int
    {
        return mb_strlen($this->content);
    }

    /**
     * Calculate estimated number of SMS segments
     *
     * @return int
     */
    public function getEstimatedSegments(): int
    {
        $length = $this->getMessageLength();

        if ($length <= 160) {
            return 1;
        }

        return (int) ceil($length / 153);
    }
}
