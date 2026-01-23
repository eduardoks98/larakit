<?php

namespace Eduardoks98\SmsComtele\Enums;

/**
 * Comtele Message Status Enum
 *
 * Based on Comtele API documentation:
 * @see https://docs.comtele.com.br/
 */
enum MessageStatus: string
{
    /**
     * Message is being processed by Comtele
     */
    case PROCESSED = 'Processed';

    /**
     * Message was successfully delivered to recipient
     */
    case DELIVERED = 'Delivered';

    /**
     * Message failed or encountered an error
     */
    case ERROR = 'Error';

    /**
     * Message is pending/queued (custom status for tracking)
     */
    case PENDING = 'Pending';

    /**
     * Check if status indicates success
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this === self::DELIVERED;
    }

    /**
     * Check if status indicates failure
     *
     * @return bool
     */
    public function isFailure(): bool
    {
        return $this === self::ERROR;
    }

    /**
     * Check if status indicates pending
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return in_array($this, [
            self::PENDING,
            self::PROCESSED,
        ]);
    }

    /**
     * Check if status is final (no more updates expected)
     *
     * @return bool
     */
    public function isFinal(): bool
    {
        return in_array($this, [
            self::DELIVERED,
            self::ERROR,
        ]);
    }
}
