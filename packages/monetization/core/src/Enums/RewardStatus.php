<?php

namespace Eduardoks98\Monetization\Enums;

enum RewardStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case DUPLICATE = 'duplicate';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::COMPLETED => 'Completed',
            self::FAILED => 'Failed',
            self::CANCELLED => 'Cancelled',
            self::DUPLICATE => 'Duplicate',
        };
    }

    public function isFinal(): bool
    {
        return match ($this) {
            self::COMPLETED, self::FAILED, self::CANCELLED, self::DUPLICATE => true,
            self::PENDING, self::PROCESSING => false,
        };
    }

    public function canRetry(): bool
    {
        return $this === self::FAILED;
    }
}
