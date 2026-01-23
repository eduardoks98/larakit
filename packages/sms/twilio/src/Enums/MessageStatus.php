<?php

namespace Eduardoks98\SmsTwilio\Enums;

/**
 * Twilio Message Status Enum
 *
 * Based on official Twilio documentation:
 * @see https://www.twilio.com/docs/messaging/api/message-resource#message-status-values
 */
enum MessageStatus: string
{
    /**
     * Message is queued and waiting to be sent
     */
    case QUEUED = 'queued';

    /**
     * Message is currently being sent to carrier
     */
    case SENDING = 'sending';

    /**
     * Message has been sent to carrier
     */
    case SENT = 'sent';

    /**
     * Message was successfully delivered to recipient
     */
    case DELIVERED = 'delivered';

    /**
     * Message failed to send
     */
    case FAILED = 'failed';

    /**
     * Message was undelivered (carrier reported failure)
     */
    case UNDELIVERED = 'undelivered';

    /**
     * Message was accepted (Messaging Service only)
     */
    case ACCEPTED = 'accepted';

    /**
     * Message is scheduled for future delivery
     */
    case SCHEDULED = 'scheduled';

    /**
     * Scheduled message was canceled
     */
    case CANCELED = 'canceled';

    /**
     * Message is being received (inbound only)
     */
    case RECEIVING = 'receiving';

    /**
     * Message was received (inbound only)
     */
    case RECEIVED = 'received';

    /**
     * Message was read (WhatsApp/RCS only)
     */
    case READ = 'read';

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
            self::ACCEPTED,
            self::RECEIVED,
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
        return in_array($this, [
            self::FAILED,
            self::UNDELIVERED,
            self::CANCELED,
        ]);
    }

    /**
     * Check if status indicates pending
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return in_array($this, [
            self::QUEUED,
            self::SENDING,
            self::SCHEDULED,
            self::RECEIVING,
        ]);
    }

    /**
     * Check if status is final (no more updates expected)
     *
     * @return bool
     */
    public function isFinal(): bool
    {
        return $this->isSuccess() || $this->isFailure();
    }
}
