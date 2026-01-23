<?php

namespace Eduardoks98\WhatsAppOfficial\Models;

use Eduardoks98\WhatsAppOfficial\Enums\MessageStatus;
use Eduardoks98\WhatsAppOfficial\Enums\MessageType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * WhatsApp Message Model
 *
 * Stores sent and received WhatsApp messages
 *
 * @property int $id
 * @property string $message_id WhatsApp message ID (wamid.*)
 * @property string $phone_number Recipient phone number (E.164)
 * @property MessageType $type Message type
 * @property MessageStatus $status Current message status
 * @property string|null $text_content Text content (for text messages)
 * @property string|null $media_url Media URL (for media messages)
 * @property string|null $media_id WhatsApp media ID
 * @property string|null $template_name Template name (for template messages)
 * @property array|null $template_params Template parameters
 * @property array|null $metadata Additional custom data
 * @property string|null $error_code WhatsApp error code
 * @property string|null $error_message Error description
 * @property \Carbon\Carbon|null $sent_at When message was sent
 * @property \Carbon\Carbon|null $delivered_at When message was delivered
 * @property \Carbon\Carbon|null $read_at When message was read
 * @property \Carbon\Carbon|null $failed_at When message failed
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class WhatsAppMessage extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'whatsapp_messages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'message_id',
        'phone_number',
        'type',
        'status',
        'text_content',
        'media_url',
        'media_id',
        'template_name',
        'template_params',
        'metadata',
        'error_code',
        'error_message',
        'sent_at',
        'delivered_at',
        'read_at',
        'failed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => MessageType::class,
        'status' => MessageStatus::class,
        'template_params' => 'array',
        'metadata' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    /**
     * Scope for sent messages
     */
    public function scopeSent($query)
    {
        return $query->whereIn('status', [
            MessageStatus::SENT->value,
            MessageStatus::DELIVERED->value,
            MessageStatus::READ->value,
        ]);
    }

    /**
     * Scope for delivered messages
     */
    public function scopeDelivered($query)
    {
        return $query->whereIn('status', [
            MessageStatus::DELIVERED->value,
            MessageStatus::READ->value,
        ]);
    }

    /**
     * Scope for read messages
     */
    public function scopeRead($query)
    {
        return $query->where('status', MessageStatus::READ->value);
    }

    /**
     * Scope for failed messages
     */
    public function scopeFailed($query)
    {
        return $query->where('status', MessageStatus::FAILED->value);
    }

    /**
     * Scope for messages by type
     */
    public function scopeByType($query, MessageType $type)
    {
        return $query->where('type', $type->value);
    }

    /**
     * Scope for text messages
     */
    public function scopeTextMessages($query)
    {
        return $query->where('type', MessageType::TEXT->value);
    }

    /**
     * Scope for media messages
     */
    public function scopeMediaMessages($query)
    {
        return $query->whereIn('type', [
            MessageType::IMAGE->value,
            MessageType::VIDEO->value,
            MessageType::AUDIO->value,
            MessageType::DOCUMENT->value,
        ]);
    }

    /**
     * Scope for template messages
     */
    public function scopeTemplateMessages($query)
    {
        return $query->where('type', MessageType::TEMPLATE->value);
    }

    /**
     * Check if message was delivered
     *
     * @return bool
     */
    public function isDelivered(): bool
    {
        return in_array($this->status, [
            MessageStatus::DELIVERED,
            MessageStatus::READ,
        ]);
    }

    /**
     * Check if message was read
     *
     * @return bool
     */
    public function isRead(): bool
    {
        return $this->status === MessageStatus::READ;
    }

    /**
     * Check if message failed
     *
     * @return bool
     */
    public function hasFailed(): bool
    {
        return $this->status === MessageStatus::FAILED;
    }

    /**
     * Check if message is pending
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === MessageStatus::QUEUED;
    }

    /**
     * Check if message contains media
     *
     * @return bool
     */
    public function hasMedia(): bool
    {
        return $this->type?->supportsMedia() ?? false;
    }

    /**
     * Check if message is a template
     *
     * @return bool
     */
    public function isTemplate(): bool
    {
        return $this->type === MessageType::TEMPLATE;
    }
}
