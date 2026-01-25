<?php

use Eduardoks98\Monetization\Services\CurrencyService;
use Eduardoks98\Monetization\Services\RewardService;
use Eduardoks98\Monetization\Services\AnalyticsService;
use Eduardoks98\Monetization\Enums\AdProvider;
use Eduardoks98\Monetization\Enums\RewardType;

if (!function_exists('monetization_balance')) {
    /**
     * Get the virtual currency balance for a user.
     */
    function monetization_balance(int $userId): int
    {
        return app(CurrencyService::class)->getBalance($userId);
    }
}

if (!function_exists('monetization_credit')) {
    /**
     * Credit virtual currency to a user.
     */
    function monetization_credit(
        int $userId,
        int $amount,
        string $source,
        ?int $sourceId = null,
        ?string $description = null
    ): \Eduardoks98\Monetization\Models\VirtualCurrencyTransaction {
        return app(CurrencyService::class)->credit($userId, $amount, $source, $sourceId, $description);
    }
}

if (!function_exists('monetization_debit')) {
    /**
     * Debit virtual currency from a user.
     */
    function monetization_debit(
        int $userId,
        int $amount,
        string $source,
        ?int $sourceId = null,
        ?string $description = null
    ): ?\Eduardoks98\Monetization\Models\VirtualCurrencyTransaction {
        return app(CurrencyService::class)->debit($userId, $amount, $source, $sourceId, $description);
    }
}

if (!function_exists('monetization_has_balance')) {
    /**
     * Check if a user has sufficient balance.
     */
    function monetization_has_balance(int $userId, int $amount): bool
    {
        return app(CurrencyService::class)->hasSufficientBalance($userId, $amount);
    }
}

if (!function_exists('monetization_reward')) {
    /**
     * Create and dispatch a reward for a user.
     */
    function monetization_reward(
        int $userId,
        AdProvider $provider,
        string $transactionId,
        string $rewardItem,
        int $rewardAmount,
        ?string $adUnitId = null,
        RewardType $rewardType = RewardType::CURRENCY
    ): \Eduardoks98\Monetization\Models\Reward {
        return app(RewardService::class)->createAndDispatch(
            $userId,
            $provider,
            $transactionId,
            $rewardItem,
            $rewardAmount,
            $adUnitId,
            $rewardType
        );
    }
}

if (!function_exists('monetization_stats')) {
    /**
     * Get monetization statistics for a user.
     */
    function monetization_stats(int $userId): array
    {
        return app(AnalyticsService::class)->getUserStats($userId);
    }
}

if (!function_exists('monetization_format_currency')) {
    /**
     * Format a currency amount with the configured symbol.
     */
    function monetization_format_currency(int $amount): string
    {
        $symbol = config('monetization.currency.symbol', '');
        $name = config('monetization.currency.name', 'coins');

        if ($symbol) {
            return $symbol . number_format($amount);
        }

        return number_format($amount) . ' ' . $name;
    }
}
