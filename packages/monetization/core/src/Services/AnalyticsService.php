<?php

namespace Eduardoks98\Monetization\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Eduardoks98\Monetization\Models\AdImpression;
use Eduardoks98\Monetization\Models\Reward;
use Eduardoks98\Monetization\Models\VirtualCurrencyTransaction;
use Eduardoks98\Monetization\Enums\AdProvider;
use Eduardoks98\Monetization\Enums\RewardStatus;
use Carbon\Carbon;

class AnalyticsService
{
    protected int $cacheDuration;

    public function __construct()
    {
        $this->cacheDuration = config('monetization.analytics.cache_duration', 60);
    }

    public function getDashboardStats(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate ??= now()->subDays(30);
        $endDate ??= now();

        $cacheKey = "monetization_dashboard_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}";

        return Cache::remember($cacheKey, $this->cacheDuration * 60, function () use ($startDate, $endDate) {
            return [
                'impressions' => $this->getImpressionStats($startDate, $endDate),
                'rewards' => $this->getRewardStats($startDate, $endDate),
                'currency' => $this->getCurrencyStats($startDate, $endDate),
                'revenue' => $this->getRevenueStats($startDate, $endDate),
            ];
        });
    }

    public function getImpressionStats(Carbon $startDate, Carbon $endDate): array
    {
        $impressions = AdImpression::inDateRange($startDate, $endDate);

        return [
            'total' => $impressions->count(),
            'by_provider' => $this->getImpressionsByProvider($startDate, $endDate),
            'by_day' => $this->getImpressionsByDay($startDate, $endDate),
            'unique_users' => $impressions->distinct('user_id')->count('user_id'),
        ];
    }

    public function getRewardStats(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'total' => Reward::whereBetween('created_at', [$startDate, $endDate])->count(),
            'completed' => Reward::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', RewardStatus::COMPLETED)->count(),
            'failed' => Reward::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', RewardStatus::FAILED)->count(),
            'pending' => Reward::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', RewardStatus::PENDING)->count(),
            'duplicates' => Reward::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', RewardStatus::DUPLICATE)->count(),
            'by_provider' => $this->getRewardsByProvider($startDate, $endDate),
            'total_amount' => Reward::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', RewardStatus::COMPLETED)
                ->sum('reward_amount'),
        ];
    }

    public function getCurrencyStats(Carbon $startDate, Carbon $endDate): array
    {
        $transactions = VirtualCurrencyTransaction::inDateRange($startDate, $endDate);

        return [
            'total_credited' => (int) $transactions->clone()->credits()->sum('amount'),
            'total_debited' => (int) $transactions->clone()->debits()->sum('amount'),
            'from_ads' => (int) $transactions->clone()->fromAds()->sum('amount'),
            'transaction_count' => $transactions->count(),
            'unique_users' => $transactions->distinct('user_id')->count('user_id'),
        ];
    }

    public function getRevenueStats(Carbon $startDate, Carbon $endDate): array
    {
        $impressions = AdImpression::inDateRange($startDate, $endDate)->withRevenue();

        return [
            'total' => (float) $impressions->sum('revenue'),
            'by_provider' => $this->getRevenueByProvider($startDate, $endDate),
            'by_day' => $this->getRevenueByDay($startDate, $endDate),
            'average_per_impression' => $impressions->count() > 0
                ? (float) $impressions->sum('revenue') / $impressions->count()
                : 0,
        ];
    }

    public function getImpressionsByProvider(Carbon $startDate, Carbon $endDate): array
    {
        return AdImpression::inDateRange($startDate, $endDate)
            ->select('provider', DB::raw('COUNT(*) as count'))
            ->groupBy('provider')
            ->pluck('count', 'provider')
            ->toArray();
    }

    public function getImpressionsByDay(Carbon $startDate, Carbon $endDate): array
    {
        return AdImpression::inDateRange($startDate, $endDate)
            ->select(DB::raw('DATE(impression_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();
    }

    public function getRewardsByProvider(Carbon $startDate, Carbon $endDate): array
    {
        return Reward::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', RewardStatus::COMPLETED)
            ->select('provider', DB::raw('COUNT(*) as count'), DB::raw('SUM(reward_amount) as total_amount'))
            ->groupBy('provider')
            ->get()
            ->keyBy('provider')
            ->map(fn ($item) => [
                'count' => $item->count,
                'total_amount' => $item->total_amount,
            ])
            ->toArray();
    }

    public function getRevenueByProvider(Carbon $startDate, Carbon $endDate): array
    {
        return AdImpression::inDateRange($startDate, $endDate)
            ->withRevenue()
            ->select('provider', DB::raw('SUM(revenue) as total'))
            ->groupBy('provider')
            ->pluck('total', 'provider')
            ->toArray();
    }

    public function getRevenueByDay(Carbon $startDate, Carbon $endDate): array
    {
        return AdImpression::inDateRange($startDate, $endDate)
            ->withRevenue()
            ->select(DB::raw('DATE(impression_at) as date'), DB::raw('SUM(revenue) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();
    }

    public function getUserStats(int $userId): array
    {
        return [
            'impressions' => AdImpression::byUser($userId)->count(),
            'rewards_completed' => Reward::byUser($userId)->completed()->count(),
            'total_earned' => (int) Reward::byUser($userId)->completed()->sum('reward_amount'),
            'current_balance' => app(CurrencyService::class)->getBalance($userId),
            'total_spent' => app(CurrencyService::class)->getTotalSpent($userId),
            'revenue_generated' => (float) AdImpression::byUser($userId)->withRevenue()->sum('revenue'),
        ];
    }

    public function getTopUsers(int $limit = 10, string $metric = 'rewards'): \Illuminate\Support\Collection
    {
        return match ($metric) {
            'rewards' => Reward::completed()
                ->select('user_id', DB::raw('SUM(reward_amount) as total'))
                ->groupBy('user_id')
                ->orderByDesc('total')
                ->limit($limit)
                ->get(),
            'impressions' => AdImpression::select('user_id', DB::raw('COUNT(*) as total'))
                ->groupBy('user_id')
                ->orderByDesc('total')
                ->limit($limit)
                ->get(),
            'revenue' => AdImpression::withRevenue()
                ->select('user_id', DB::raw('SUM(revenue) as total'))
                ->groupBy('user_id')
                ->orderByDesc('total')
                ->limit($limit)
                ->get(),
            default => collect(),
        };
    }

    public function clearCache(): void
    {
        Cache::forget('monetization_dashboard_*');
    }
}
