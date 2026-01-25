<?php

namespace Eduardoks98\AdsUnity\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UnityStatsService
{
    protected ?string $apiKey;
    protected ?string $organizationId;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('ads-unity.stats_api.api_key');
        $this->organizationId = config('ads-unity.stats_api.organization_id');
        $this->baseUrl = config('ads-unity.stats_api.base_url');
    }

    /**
     * Check if the Stats API is configured.
     */
    public function isConfigured(): bool
    {
        return $this->apiKey && $this->organizationId;
    }

    /**
     * Get monetization statistics.
     */
    public function getStats(
        Carbon $startDate,
        Carbon $endDate,
        array $fields = ['revenue', 'requests', 'impressions', 'clicks'],
        ?string $groupBy = 'day'
    ): ?array {
        if (!$this->isConfigured()) {
            Log::warning('Unity Stats API not configured');
            return null;
        }

        $url = $this->baseUrl . $this->organizationId;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Token ' . $this->apiKey,
            ])->get($url, [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
                'fields' => implode(',', $fields),
                'groupBy' => $groupBy,
            ]);

            if (!$response->successful()) {
                Log::error('Unity Stats API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            return $response->json();

        } catch (\Exception $e) {
            Log::error('Unity Stats API exception', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get daily revenue statistics.
     */
    public function getDailyRevenue(Carbon $startDate, Carbon $endDate): ?array
    {
        return $this->getStats($startDate, $endDate, ['revenue'], 'day');
    }

    /**
     * Get impression statistics.
     */
    public function getImpressions(Carbon $startDate, Carbon $endDate): ?array
    {
        return $this->getStats($startDate, $endDate, ['impressions', 'requests'], 'day');
    }

    /**
     * Get statistics by country.
     */
    public function getStatsByCountry(Carbon $startDate, Carbon $endDate): ?array
    {
        return $this->getStats($startDate, $endDate, ['revenue', 'impressions'], 'country');
    }

    /**
     * Get statistics by platform.
     */
    public function getStatsByPlatform(Carbon $startDate, Carbon $endDate): ?array
    {
        return $this->getStats($startDate, $endDate, ['revenue', 'impressions'], 'platform');
    }
}
