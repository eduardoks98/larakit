<?php

namespace Eduardoks98\WhatsAppOfficial\Enums;

/**
 * WhatsApp Message Status Enum
 *
 * Based on official WhatsApp Cloud API documentation
 * @see https://developers.facebook.com/docs/whatsapp/cloud-api/webhooks/components#statuses-object
 */
enum MessageStatus: string
{
    /**
     * Message is queued to be sent
     */
    case QUEUED = 'queued';

    /**
     * Message has been sent
     */
    case SENT = 'sent';

    /**
     * Message has been delivered to recipient
     */
    case DELIVERED = 'delivered';

    /**
     * Message has been read by recipient
     */
    case READ = 'read';

    /**
     * Message failed to send
     */
    case FAILED = 'failed';

    /**
     * Check if status indicates success
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return in_array($this, [
            self::SENT,
            self::DELIVERED,
            self::READ,
        ]);
    }

    /**
     * Check if status indicates failure
     *
     * @return bool
     */
    public function isFailure(): bool
    {
        return $this === self::FAILED;
    }

    /**
     * Check if status indicates pending
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this === self::QUEUED;
    }

    /**
     * Check if status is final (no more updates expected)
     *
     * @return bool
     */
    public function isFinal(): bool
    {
        return in_array($this, [
            self::READ,
            self::FAILED,
        ]);
    }
}
