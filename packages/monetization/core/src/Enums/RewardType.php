<?php

namespace Eduardoks98\Monetization\Enums;

enum RewardType: string
{
    case CURRENCY = 'currency';
    case ITEM = 'item';
    case EXPERIENCE = 'experience';
    case SUBSCRIPTION = 'subscription';
    case UNLOCK = 'unlock';
    case CUSTOM = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::CURRENCY => 'Virtual Currency',
            self::ITEM => 'In-Game Item',
            self::EXPERIENCE => 'Experience Points',
            self::SUBSCRIPTION => 'Subscription Time',
            self::UNLOCK => 'Content Unlock',
            self::CUSTOM => 'Custom Reward',
        };
    }
}
