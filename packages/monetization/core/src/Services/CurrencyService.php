<?php

namespace Eduardoks98\Monetization\Services;

use Illuminate\Support\Facades\Log;
use Eduardoks98\Monetization\Models\VirtualCurrencyTransaction;

class CurrencyService
{
    public function getBalance(int $userId): int
    {
        $lastTransaction = VirtualCurrencyTransaction::byUser($userId)
            ->latest()
            ->first();

        return $lastTransaction?->balance_after ?? config('monetization.currency.initial_balance', 0);
    }

    public function credit(
        int $userId,
        int $amount,
        string $source,
        ?int $sourceId = null,
        ?string $description = null,
        ?array $metadata = null
    ): VirtualCurrencyTransaction {
        $currentBalance = $this->getBalance($userId);
        $maxBalance = config('monetization.currency.max_balance', 999999999);
        $newBalance = min($currentBalance + $amount, $maxBalance);

        $transaction = VirtualCurrencyTransaction::create([
            'user_id' => $userId,
            'type' => VirtualCurrencyTransaction::TYPE_CREDIT,
            'amount' => $amount,
            'balance_before' => $currentBalance,
            'balance_after' => $newBalance,
            'source' => $source,
            'source_id' => $sourceId,
            'description' => $description,
            'metadata' => $metadata,
        ]);

        Log::info('Currency credited', [
            'user_id' => $userId,
            'amount' => $amount,
            'balance' => $newBalance,
            'source' => $source,
        ]);

        return $transaction;
    }

    public function debit(
        int $userId,
        int $amount,
        string $source,
        ?int $sourceId = null,
        ?string $description = null,
        ?array $metadata = null
    ): ?VirtualCurrencyTransaction {
        $currentBalance = $this->getBalance($userId);

        if ($currentBalance < $amount) {
            Log::warning('Insufficient balance for debit', [
                'user_id' => $userId,
                'amount' => $amount,
                'balance' => $currentBalance,
            ]);
            return null;
        }

        $transaction = VirtualCurrencyTransaction::create([
            'user_id' => $userId,
            'type' => VirtualCurrencyTransaction::TYPE_DEBIT,
            'amount' => $amount,
            'balance_before' => $currentBalance,
            'balance_after' => $currentBalance - $amount,
            'source' => $source,
            'source_id' => $sourceId,
            'description' => $description,
            'metadata' => $metadata,
        ]);

        Log::info('Currency debited', [
            'user_id' => $userId,
            'amount' => $amount,
            'balance' => $currentBalance - $amount,
            'source' => $source,
        ]);

        return $transaction;
    }

    public function transfer(
        int $fromUserId,
        int $toUserId,
        int $amount,
        ?string $description = null
    ): ?array {
        $fromBalance = $this->getBalance($fromUserId);

        if ($fromBalance < $amount) {
            return null;
        }

        $debitTx = $this->debit(
            $fromUserId,
            $amount,
            VirtualCurrencyTransaction::SOURCE_GIFT,
            null,
            $description ?? "Transfer to user #{$toUserId}",
            ['recipient_id' => $toUserId]
        );

        $creditTx = $this->credit(
            $toUserId,
            $amount,
            VirtualCurrencyTransaction::SOURCE_GIFT,
            null,
            $description ?? "Transfer from user #{$fromUserId}",
            ['sender_id' => $fromUserId]
        );

        return [
            'debit' => $debitTx,
            'credit' => $creditTx,
        ];
    }

    public function adjust(
        int $userId,
        int $newBalance,
        string $reason = 'Manual adjustment'
    ): VirtualCurrencyTransaction {
        $currentBalance = $this->getBalance($userId);
        $difference = $newBalance - $currentBalance;

        $type = $difference >= 0 ? VirtualCurrencyTransaction::TYPE_CREDIT : VirtualCurrencyTransaction::TYPE_DEBIT;

        $transaction = VirtualCurrencyTransaction::create([
            'user_id' => $userId,
            'type' => $type,
            'amount' => abs($difference),
            'balance_before' => $currentBalance,
            'balance_after' => $newBalance,
            'source' => VirtualCurrencyTransaction::SOURCE_ADJUSTMENT,
            'description' => $reason,
        ]);

        Log::info('Currency adjusted', [
            'user_id' => $userId,
            'from' => $currentBalance,
            'to' => $newBalance,
            'reason' => $reason,
        ]);

        return $transaction;
    }

    public function hasSufficientBalance(int $userId, int $amount): bool
    {
        return $this->getBalance($userId) >= $amount;
    }

    public function getTransactionHistory(int $userId, int $limit = 50, int $offset = 0): \Illuminate\Database\Eloquent\Collection
    {
        return VirtualCurrencyTransaction::byUser($userId)
            ->latest()
            ->offset($offset)
            ->limit($limit)
            ->get();
    }

    public function getTotalEarned(int $userId): int
    {
        return (int) VirtualCurrencyTransaction::byUser($userId)
            ->credits()
            ->sum('amount');
    }

    public function getTotalSpent(int $userId): int
    {
        return (int) VirtualCurrencyTransaction::byUser($userId)
            ->debits()
            ->sum('amount');
    }

    public function getTotalEarnedFromAds(int $userId): int
    {
        return (int) VirtualCurrencyTransaction::byUser($userId)
            ->credits()
            ->fromAds()
            ->sum('amount');
    }
}
