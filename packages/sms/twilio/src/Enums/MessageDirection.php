<?php

namespace Eduardoks98\SmsTwilio\Enums;

/**
 * Twilio Message Direction Enum
 *
 * @see https://www.twilio.com/docs/messaging/api/message-resource#message-properties
 */
enum MessageDirection: string
{
    /**
     * Inbound message (received)
     */
    case INBOUND = 'inbound';

    /**
     * Outbound message sent via API
     */
    case OUTBOUND_API = 'outbound-api';

    /**
     * Outbound message sent during call
     */
    case OUTBOUND_CALL = 'outbound-call';

    /**
     * Outbound reply to inbound message
     */
    case OUTBOUND_REPLY = 'outbound-reply';

    /**
     * Check if direction is outbound
     *
     * @return bool
     */
    public function isOutbound(): bool
    {
        return in_array($this, [
            self::OUTBOUND_API,
            self::OUTBOUND_CALL,
            self::OUTBOUND_REPLY,
        ]);
    }

    /**
     * Check if direction is inbound
     *
     * @return bool
     */
    public function isInbound(): bool
    {
        return $this === self::INBOUND;
    }
}
