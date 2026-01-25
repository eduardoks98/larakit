<?php

namespace Eduardoks98\Monetization\Enums;

enum AdProvider: string
{
    case ADMOB = 'admob';
    case UNITY = 'unity';
    case APPLOVIN = 'applovin';
    case FACEBOOK = 'facebook';
    case IRONSOURCE = 'ironsource';
    case VUNGLE = 'vungle';
    case CHARTBOOST = 'chartboost';
    case CUSTOM = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::ADMOB => 'Google AdMob',
            self::UNITY => 'Unity Ads',
            self::APPLOVIN => 'AppLovin MAX',
            self::FACEBOOK => 'Facebook Audience Network',
            self::IRONSOURCE => 'ironSource',
            self::VUNGLE => 'Vungle',
            self::CHARTBOOST => 'Chartboost',
            self::CUSTOM => 'Custom Provider',
        };
    }

    public function supportsServerCallback(): bool
    {
        return match ($this) {
            self::ADMOB, self::UNITY, self::APPLOVIN, self::IRONSOURCE, self::VUNGLE, self::CHARTBOOST => true,
            self::FACEBOOK, self::CUSTOM => false,
        };
    }
}
