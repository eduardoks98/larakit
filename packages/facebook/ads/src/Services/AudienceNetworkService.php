<?php

namespace Eduardoks98\AdsFacebook\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AudienceNetworkService
{
    protected ?string $appId;
    protected ?string $accessToken;
    protected string $baseUrl;
    protected string $apiVersion;

    public function __construct()
    {
        $this->appId = config('ads-facebook.app.app_id');
        $this->accessToken = config('ads-facebook.app.access_token');
        $this->baseUrl = config('ads-facebook.graph_api.base_url', 'https://graph.facebook.com/');
        $this->apiVersion = config('ads-facebook.graph_api.version', 'v21.0');
    }

    /**
     * Check if the service is configured.
     */
    public function isConfigured(): bool
    {
        return $this->appId && $this->accessToken;
    }

    /**
     * Get Audience Network insights/statistics.
     */
    public function getInsights(
        Carbon $startDate,
        Carbon $endDate,
        array $metrics = null,
        ?string $breakdowns = null
    ): ?array {
        if (!$this->isConfigured()) {
            Log::warning('Facebook Ads not configured');
            return null;
        }

        $metrics ??= config('ads-facebook.reporting.default_metrics');
        $cacheKey = "fb_ads_insights_{$this->appId}_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}";

        $cacheDuration = config('ads-facebook.reporting.cache_duration', 60);

        return Cache::remember($cacheKey, $cacheDuration * 60, function () use ($startDate, $endDate, $metrics, $breakdowns) {
            return $this->fetchInsights($startDate, $endDate, $metrics, $breakdowns);
        });
    }

    /**
     * Fetch insights from Graph API.
     */
    protected function fetchInsights(
        Carbon $startDate,
        Carbon $endDate,
        array $metrics,
        ?string $breakdowns
    ): ?array {
        $url = $this->baseUrl . $this->apiVersion . '/' . $this->appId . '/app_insights/app_event/';

        $params = [
            'access_token' => $this->accessToken,
            'since' => $startDate->format('Y-m-d'),
            'until' => $endDate->format('Y-m-d'),
            'event_name' => implode(',', array_map(fn($m) => $m . '|COUNT', $metrics)),
        ];

        if ($breakdowns) {
            $params['breakdowns'] = $breakdowns;
        }

        try {
            $response = Http::timeout(30)->get($url, $params);

            if (!$response->successful()) {
                Log::error('Facebook Ads API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            return $response->json();

        } catch (\Exception $e) {
            Log::error('Facebook Ads API exception', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get revenue statistics.
     */
    public function getRevenueStats(Carbon $startDate, Carbon $endDate): ?array
    {
        return $this->getInsights(
            $startDate,
            $endDate,
            ['fb_ad_network_revenue', 'fb_ad_network_imp', 'fb_ad_network_cpm']
        );
    }

    /**
     * Get impression statistics.
     */
    public function getImpressionStats(Carbon $startDate, Carbon $endDate): ?array
    {
        return $this->getInsights(
            $startDate,
            $endDate,
            ['fb_ad_network_imp', 'fb_ad_network_request', 'fb_ad_network_filled_request', 'fb_ad_network_fill_rate']
        );
    }

    /**
     * Get stats by country.
     */
    public function getStatsByCountry(Carbon $startDate, Carbon $endDate): ?array
    {
        return $this->getInsights(
            $startDate,
            $endDate,
            ['fb_ad_network_revenue', 'fb_ad_network_imp'],
            'country'
        );
    }

    /**
     * Get stats by placement.
     */
    public function getStatsByPlacement(Carbon $startDate, Carbon $endDate): ?array
    {
        return $this->getInsights(
            $startDate,
            $endDate,
            ['fb_ad_network_revenue', 'fb_ad_network_imp'],
            'placement'
        );
    }

    /**
     * Parse insights response to extract metrics.
     */
    public function parseInsights(array $response): array
    {
        $parsed = [
            'impressions' => 0,
            'clicks' => 0,
            'revenue' => 0.0,
            'requests' => 0,
            'filled_requests' => 0,
            'fill_rate' => 0.0,
            'cpm' => 0.0,
        ];

        if (!isset($response['data'])) {
            return $parsed;
        }

        foreach ($response['data'] as $item) {
            $eventName = $item['event'] ?? '';
            $value = $item['value'] ?? 0;

            if (str_contains($eventName, 'fb_ad_network_imp')) {
                $parsed['impressions'] += (int) $value;
            } elseif (str_contains($eventName, 'fb_ad_network_click')) {
                $parsed['clicks'] += (int) $value;
            } elseif (str_contains($eventName, 'fb_ad_network_revenue')) {
                $parsed['revenue'] += (float) $value;
            } elseif (str_contains($eventName, 'fb_ad_network_request')) {
                $parsed['requests'] += (int) $value;
            } elseif (str_contains($eventName, 'fb_ad_network_filled_request')) {
                $parsed['filled_requests'] += (int) $value;
            } elseif (str_contains($eventName, 'fb_ad_network_fill_rate')) {
                $parsed['fill_rate'] = (float) $value;
            } elseif (str_contains($eventName, 'fb_ad_network_cpm')) {
                $parsed['cpm'] = (float) $value;
            }
        }

        return $parsed;
    }

    /**
     * Clear cached insights.
     */
    public function clearCache(): void
    {
        Cache::forget("fb_ads_insights_{$this->appId}_*");
    }
}
