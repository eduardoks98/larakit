<?php

namespace Eduardoks98\AnalyticsGoogle\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool isEnabled()
 * @method static string|null getMeasurementId()
 * @method static bool shouldTrackRoute(?string $route = null)
 * @method static array getConfig()
 * @method static string getScript()
 * @method static string event(string $eventName, array $parameters = [])
 *
 * @see \Eduardoks98\AnalyticsGoogle\Services\GoogleAnalyticsService
 */
class GoogleAnalytics extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'google-analytics';
    }
}
