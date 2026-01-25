<?php

namespace Eduardoks98\AdsApplovin\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MaxReportingService
{
    protected ?string $reportKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->reportKey = config('ads-applovin.api.report_key');
        $this->baseUrl = config('ads-applovin.reporting.base_url', 'https://r.applovin.com/max/');
    }

    /**
     * Check if the Reporting API is configured.
     */
    public function isConfigured(): bool
    {
        return (bool) $this->reportKey;
    }

    /**
     * Get user-level ad revenue report.
     */
    public function getUserAdRevenueReport(
        Carbon $date,
        ?string $platform = null,
        ?string $packageName = null
    ): ?array {
        if (!$this->isConfigured()) {
            Log::warning('AppLovin Report API not configured');
            return null;
        }

        $platform ??= config('ads-applovin.app.platform', 'android');
        $packageName ??= config('ads-applovin.app.package_name');

        if (!$packageName) {
            Log::warning('AppLovin package name not configured');
            return null;
        }

        $endpoint = $this->baseUrl . config('ads-applovin.reporting.user_revenue_endpoint', 'userAdRevenueReport');

        try {
            $response = Http::timeout(30)->get($endpoint, [
                'api_key' => $this->reportKey,
                'date' => $date->format('Y-m-d'),
                'platform' => $platform,
                'application' => $packageName,
            ]);

            if (!$response->successful()) {
                Log::error('AppLovin Report API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            // The response is typically CSV format
            return $this->parseCsvResponse($response->body());

        } catch (\Exception $e) {
            Log::error('AppLovin Report API exception', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get aggregated revenue for a date range.
     */
    public function getRevenueReport(
        Carbon $startDate,
        Carbon $endDate,
        ?string $platform = null,
        ?string $packageName = null
    ): array {
        $reports = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $report = $this->getUserAdRevenueReport($current, $platform, $packageName);
            if ($report) {
                $reports[$current->format('Y-m-d')] = $report;
            }
            $current->addDay();
        }

        return $reports;
    }

    /**
     * Parse CSV response from AppLovin.
     */
    protected function parseCsvResponse(string $csv): array
    {
        $lines = explode("\n", trim($csv));

        if (count($lines) < 2) {
            return [];
        }

        $headers = str_getcsv(array_shift($lines));
        $data = [];

        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }

            $values = str_getcsv($line);
            $row = [];

            foreach ($headers as $index => $header) {
                $row[$header] = $values[$index] ?? null;
            }

            $data[] = $row;
        }

        return $data;
    }

    /**
     * Calculate total revenue from report data.
     */
    public function calculateTotalRevenue(array $reportData): float
    {
        $total = 0.0;

        foreach ($reportData as $row) {
            if (isset($row['revenue'])) {
                $total += (float) $row['revenue'];
            } elseif (isset($row['estimated_revenue'])) {
                $total += (float) $row['estimated_revenue'];
            }
        }

        return $total;
    }

    /**
     * Get unique users from report data.
     */
    public function getUniqueUsers(array $reportData): int
    {
        $users = [];

        foreach ($reportData as $row) {
            if (isset($row['user_id']) && $row['user_id']) {
                $users[$row['user_id']] = true;
            }
        }

        return count($users);
    }

    /**
     * Group report data by user.
     */
    public function groupByUser(array $reportData): array
    {
        $grouped = [];

        foreach ($reportData as $row) {
            $userId = $row['user_id'] ?? 'unknown';

            if (!isset($grouped[$userId])) {
                $grouped[$userId] = [
                    'impressions' => 0,
                    'revenue' => 0.0,
                    'records' => [],
                ];
            }

            $grouped[$userId]['impressions']++;
            $grouped[$userId]['revenue'] += (float) ($row['revenue'] ?? $row['estimated_revenue'] ?? 0);
            $grouped[$userId]['records'][] = $row;
        }

        return $grouped;
    }
}
