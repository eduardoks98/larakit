<?php

namespace Eduardoks98\AnalyticsGoogle\Services;

class GoogleAnalyticsService
{
    /**
     * Check if tracking is enabled.
     */
    public function isEnabled(): bool
    {
        if (!config('google-analytics.enabled')) {
            return false;
        }

        if (!config('google-analytics.measurement_id')) {
            return false;
        }

        $trackInEnvironments = config('google-analytics.track_in_environments', ['production']);

        return in_array(app()->environment(), $trackInEnvironments);
    }

    /**
     * Get the Measurement ID.
     */
    public function getMeasurementId(): ?string
    {
        return config('google-analytics.measurement_id');
    }

    /**
     * Check if current route should be tracked.
     */
    public function shouldTrackRoute(?string $route = null): bool
    {
        $route = $route ?? request()->path();
        $excludedRoutes = config('google-analytics.excluded_routes', []);

        foreach ($excludedRoutes as $pattern) {
            if (fnmatch($pattern, $route)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the gtag configuration.
     */
    public function getConfig(): array
    {
        $config = [];

        if (config('google-analytics.debug')) {
            $config['debug_mode'] = true;
        }

        if (config('google-analytics.anonymize_ip')) {
            $config['anonymize_ip'] = true;
        }

        $cookieDomain = config('google-analytics.cookie.domain');
        if ($cookieDomain && $cookieDomain !== 'auto') {
            $config['cookie_domain'] = $cookieDomain;
        }

        $cookieExpires = config('google-analytics.cookie.expires');
        if ($cookieExpires) {
            $config['cookie_expires'] = $cookieExpires;
        }

        return $config;
    }

    /**
     * Generate the gtag script.
     */
    public function getScript(): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        $measurementId = $this->getMeasurementId();
        $config = $this->getConfig();
        $configJson = !empty($config) ? ', ' . json_encode($config) : '';

        return <<<HTML
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id={$measurementId}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{$measurementId}'{$configJson});
</script>
HTML;
    }

    /**
     * Generate a custom event script.
     */
    public function event(string $eventName, array $parameters = []): string
    {
        if (!$this->isEnabled()) {
            return '';
        }

        $paramsJson = !empty($parameters) ? json_encode($parameters) : '{}';

        return "<script>gtag('event', '{$eventName}', {$paramsJson});</script>";
    }
}
