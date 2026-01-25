<?php

namespace Eduardoks98\Monetization\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Eduardoks98\Monetization\Models\VirtualCurrencyTransaction;
use Eduardoks98\Monetization\Models\Reward;
use Eduardoks98\Monetization\Models\AdImpression;
use Eduardoks98\Monetization\Enums\AdProvider;

trait HasVirtualCurrency
{
    public function virtualCurrencyTransactions(): HasMany
    {
        return $this->hasMany(VirtualCurrencyTransaction::class);
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(Reward::class);
    }

    public function adImpressions(): HasMany
    {
        return $this->hasMany(AdImpression::class);
    }

    public function getCurrencyBalance(): int
    {
        $lastTransaction = $this->virtualCurrencyTransactions()
            ->latest()
            ->first();

        return $lastTransaction?->balance_after ?? config('monetization.currency.initial_balance', 0);
    }

    public function creditCurrency(
        int $amount,
        string $source,
        ?int $sourceId = null,
        ?AdProvider $provider = null,
        ?string $description = null,
        ?array $metadata = null
    ): VirtualCurrencyTransaction {
        $currentBalance = $this->getCurrencyBalance();
        $maxBalance = config('monetization.currency.max_balance', 999999999);
        $newBalance = min($currentBalance + $amount, $maxBalance);

        return $this->virtualCurrencyTransactions()->create([
            'type' => VirtualCurrencyTransaction::TYPE_CREDIT,
            'amount' => $amount,
            'balance_before' => $currentBalance,
            'balance_after' => $newBalance,
            'source' => $source,
            'source_id' => $sourceId,
            'provider' => $provider,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    public function debitCurrency(
        int $amount,
        string $source,
        ?int $sourceId = null,
        ?string $description = null,
        ?array $metadata = null
    ): ?VirtualCurrencyTransaction {
        $currentBalance = $this->getCurrencyBalance();

        if ($currentBalance < $amount) {
            return null;
        }

        return $this->virtualCurrencyTransactions()->create([
            'type' => VirtualCurrencyTransaction::TYPE_DEBIT,
            'amount' => $amount,
            'balance_before' => $currentBalance,
            'balance_after' => $currentBalance - $amount,
            'source' => $source,
            'source_id' => $sourceId,
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }

    public function hasSufficientBalance(int $amount): bool
    {
        return $this->getCurrencyBalance() >= $amount;
    }

    public function getTotalEarnedFromAds(): int
    {
        return (int) $this->virtualCurrencyTransactions()
            ->where('source', VirtualCurrencyTransaction::SOURCE_AD_REWARD)
            ->where('type', VirtualCurrencyTransaction::TYPE_CREDIT)
            ->sum('amount');
    }

    public function getTotalSpent(): int
    {
        return (int) $this->virtualCurrencyTransactions()
            ->where('type', VirtualCurrencyTransaction::TYPE_DEBIT)
            ->sum('amount');
    }

    public function getRewardCount(): int
    {
        return $this->rewards()->completed()->count();
    }

    public function getImpressionCount(): int
    {
        return $this->adImpressions()->count();
    }

    public function getAdRevenueGenerated(): float
    {
        return (float) $this->adImpressions()->withRevenue()->sum('revenue');
    }
}
