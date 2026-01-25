<?php

namespace Eduardoks98\AdsAdsense\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AdsenseReportingService
{
    protected $client = null;
    protected $adsenseService = null;

    /**
     * Check if the API is enabled.
     */
    public function isApiEnabled(): bool
    {
        return config('adsense.api.enabled', false);
    }

    /**
     * Check if the API is configured.
     */
    public function isApiConfigured(): bool
    {
        $credentialsPath = config('adsense.api.credentials_path');
        $accountId = config('adsense.api.account_id');

        return $this->isApiEnabled()
            && !empty($credentialsPath)
            && !empty($accountId)
            && file_exists($credentialsPath);
    }

    /**
     * Get the Google Client instance.
     */
    protected function getClient()
    {
        if ($this->client !== null) {
            return $this->client;
        }

        if (!class_exists('\Google\Client')) {
            throw new \RuntimeException('Google API Client is not installed. Run: composer require google/apiclient');
        }

        $this->client = new \Google\Client();
        $this->client->setAuthConfig(config('adsense.api.credentials_path'));
        $this->client->addScope('https://www.googleapis.com/auth/adsense.readonly');

        return $this->client;
    }

    /**
     * Get the AdSense service instance.
     */
    protected function getAdsenseService()
    {
        if ($this->adsenseService !== null) {
            return $this->adsenseService;
        }

        if (!class_exists('\Google\Service\Adsense')) {
            throw new \RuntimeException('Google AdSense API is not available. Run: composer require google/apiclient');
        }

        $this->adsenseService = new \Google\Service\Adsense($this->getClient());

        return $this->adsenseService;
    }

    /**
     * Get the account ID.
     */
    protected function getAccountId(): string
    {
        return config('adsense.api.account_id', '');
    }

    /**
     * Get revenue report for a date range.
     */
    public function getRevenueReport(Carbon $startDate, Carbon $endDate, array $dimensions = []): array
    {
        if (!$this->isApiConfigured()) {
            return $this->getMockRevenueReport();
        }

        $cacheKey = $this->getCacheKey("revenue_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}");

        if ($this->isCacheEnabled()) {
            return Cache::remember($cacheKey, $this->getCacheTtl(), function () use ($startDate, $endDate, $dimensions) {
                return $this->fetchRevenueReport($startDate, $endDate, $dimensions);
            });
        }

        return $this->fetchRevenueReport($startDate, $endDate, $dimensions);
    }

    /**
     * Fetch revenue report from API.
     */
    protected function fetchRevenueReport(Carbon $startDate, Carbon $endDate, array $dimensions = []): array
    {
        try {
            $service = $this->getAdsenseService();
            $accountId = $this->getAccountId();

            $report = $service->accounts_reports->generate($accountId, [
                'dateRange' => 'CUSTOM',
                'startDate.year' => $startDate->year,
                'startDate.month' => $startDate->month,
                'startDate.day' => $startDate->day,
                'endDate.year' => $endDate->year,
                'endDate.month' => $endDate->month,
                'endDate.day' => $endDate->day,
                'dimensions' => array_merge(['DATE'], $dimensions),
                'metrics' => [
                    'ESTIMATED_EARNINGS',
                    'PAGE_VIEWS',
                    'AD_REQUESTS',
                    'AD_REQUESTS_COVERAGE',
                    'CLICKS',
                    'COST_PER_CLICK',
                    'PAGE_VIEWS_RPM',
                ],
            ]);

            return $this->parseReportResponse($report);
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * Parse the API report response.
     */
    protected function parseReportResponse($report): array
    {
        $rows = $report->getRows() ?? [];
        $data = [];

        foreach ($rows as $row) {
            $cells = $row->getCells();
            $data[] = [
                'date' => $cells[0]->getValue() ?? null,
                'earnings' => (float) ($cells[1]->getValue() ?? 0),
                'page_views' => (int) ($cells[2]->getValue() ?? 0),
                'ad_requests' => (int) ($cells[3]->getValue() ?? 0),
                'coverage' => (float) ($cells[4]->getValue() ?? 0),
                'clicks' => (int) ($cells[5]->getValue() ?? 0),
                'cpc' => (float) ($cells[6]->getValue() ?? 0),
                'rpm' => (float) ($cells[7]->getValue() ?? 0),
            ];
        }

        return [
            'data' => $data,
            'totals' => $this->calculateTotals($data),
        ];
    }

    /**
     * Calculate totals from report data.
     */
    protected function calculateTotals(array $data): array
    {
        return [
            'earnings' => array_sum(array_column($data, 'earnings')),
            'page_views' => array_sum(array_column($data, 'page_views')),
            'ad_requests' => array_sum(array_column($data, 'ad_requests')),
            'clicks' => array_sum(array_column($data, 'clicks')),
            'avg_rpm' => count($data) > 0
                ? array_sum(array_column($data, 'rpm')) / count($data)
                : 0,
            'avg_cpc' => count($data) > 0
                ? array_sum(array_column($data, 'cpc')) / count($data)
                : 0,
        ];
    }

    /**
     * Get today's revenue summary.
     */
    public function getTodayRevenue(): array
    {
        $today = Carbon::today();
        return $this->getRevenueReport($today, $today);
    }

    /**
     * Get this week's revenue summary.
     */
    public function getWeekRevenue(): array
    {
        $start = Carbon::now()->startOfWeek();
        $end = Carbon::now();
        return $this->getRevenueReport($start, $end);
    }

    /**
     * Get this month's revenue summary.
     */
    public function getMonthRevenue(): array
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now();
        return $this->getRevenueReport($start, $end);
    }

    /**
     * Get revenue comparison with previous period.
     */
    public function getRevenueComparison(Carbon $startDate, Carbon $endDate): array
    {
        $currentPeriod = $this->getRevenueReport($startDate, $endDate);

        $periodLength = $startDate->diffInDays($endDate) + 1;
        $previousStart = $startDate->copy()->subDays($periodLength);
        $previousEnd = $endDate->copy()->subDays($periodLength);

        $previousPeriod = $this->getRevenueReport($previousStart, $previousEnd);

        $currentEarnings = $currentPeriod['totals']['earnings'] ?? 0;
        $previousEarnings = $previousPeriod['totals']['earnings'] ?? 0;

        $change = $previousEarnings > 0
            ? (($currentEarnings - $previousEarnings) / $previousEarnings) * 100
            : 0;

        return [
            'current' => $currentPeriod,
            'previous' => $previousPeriod,
            'change_percentage' => round($change, 2),
            'change_amount' => $currentEarnings - $previousEarnings,
        ];
    }

    /**
     * Get mock revenue report for testing or when API is not configured.
     */
    protected function getMockRevenueReport(): array
    {
        return [
            'data' => [],
            'totals' => [
                'earnings' => 0,
                'page_views' => 0,
                'ad_requests' => 0,
                'clicks' => 0,
                'avg_rpm' => 0,
                'avg_cpc' => 0,
            ],
            'note' => 'API not configured. Configure ADSENSE_API_ENABLED and credentials to see real data.',
        ];
    }

    /**
     * Check if cache is enabled.
     */
    protected function isCacheEnabled(): bool
    {
        return config('adsense.cache.enabled', true);
    }

    /**
     * Get cache TTL.
     */
    protected function getCacheTtl(): int
    {
        return config('adsense.cache.ttl', 300);
    }

    /**
     * Get cache key with prefix.
     */
    protected function getCacheKey(string $key): string
    {
        $prefix = config('adsense.cache.prefix', 'adsense_');
        return $prefix . 'reporting_' . $key;
    }

    /**
     * Clear reporting cache.
     */
    public function clearCache(): void
    {
        // Since we don't know all cache keys, this is a best-effort approach
        // In production, consider using cache tags
    }
}
