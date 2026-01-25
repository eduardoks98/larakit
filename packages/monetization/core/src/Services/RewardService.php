<?php

namespace Eduardoks98\Monetization\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Eduardoks98\Monetization\Models\Reward;
use Eduardoks98\Monetization\Models\VirtualCurrencyTransaction;
use Eduardoks98\Monetization\Enums\AdProvider;
use Eduardoks98\Monetization\Enums\RewardStatus;
use Eduardoks98\Monetization\Enums\RewardType;
use Eduardoks98\Monetization\Jobs\FulfillRewardJob;

class RewardService
{
    public function createReward(
        int $userId,
        AdProvider $provider,
        string $transactionId,
        string $rewardItem,
        int $rewardAmount,
        ?string $adUnitId = null,
        RewardType $rewardType = RewardType::CURRENCY,
        ?array $metadata = null
    ): Reward {
        $reward = Reward::create([
            'user_id' => $userId,
            'provider' => $provider,
            'transaction_id' => $transactionId,
            'ad_unit_id' => $adUnitId,
            'reward_type' => $rewardType,
            'reward_item' => $rewardItem,
            'reward_amount' => $rewardAmount,
            'status' => RewardStatus::PENDING,
            'attempts' => 0,
            'metadata' => $metadata,
        ]);

        Log::info('Reward created', [
            'reward_id' => $reward->id,
            'user_id' => $userId,
            'provider' => $provider->value,
            'transaction_id' => $transactionId,
            'amount' => $rewardAmount,
        ]);

        return $reward;
    }

    public function processReward(Reward $reward): bool
    {
        if ($reward->status->isFinal()) {
            Log::warning('Attempted to process reward with final status', [
                'reward_id' => $reward->id,
                'status' => $reward->status->value,
            ]);
            return false;
        }

        if ($reward->isDuplicate()) {
            $reward->markAsDuplicate();
            Log::info('Duplicate reward detected', [
                'reward_id' => $reward->id,
                'transaction_id' => $reward->transaction_id,
            ]);
            return false;
        }

        $reward->markAsProcessing();

        try {
            return DB::transaction(function () use ($reward) {
                $user = $reward->user;

                if (!$user) {
                    throw new \Exception('User not found for reward');
                }

                if ($reward->reward_type === RewardType::CURRENCY) {
                    $user->creditCurrency(
                        amount: $reward->reward_amount,
                        source: VirtualCurrencyTransaction::SOURCE_AD_REWARD,
                        sourceId: $reward->id,
                        provider: $reward->provider,
                        description: "Reward from {$reward->provider->label()}: {$reward->reward_item}",
                        metadata: [
                            'transaction_id' => $reward->transaction_id,
                            'ad_unit_id' => $reward->ad_unit_id,
                        ]
                    );
                }

                $reward->markAsCompleted();

                Log::info('Reward processed successfully', [
                    'reward_id' => $reward->id,
                    'user_id' => $reward->user_id,
                    'amount' => $reward->reward_amount,
                ]);

                return true;
            });
        } catch (\Exception $e) {
            $reward->markAsFailed($e->getMessage());

            Log::error('Reward processing failed', [
                'reward_id' => $reward->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function dispatchReward(Reward $reward): void
    {
        $queue = config('monetization.rewards.queue', 'default');
        FulfillRewardJob::dispatch($reward)->onQueue($queue);
    }

    public function createAndDispatch(
        int $userId,
        AdProvider $provider,
        string $transactionId,
        string $rewardItem,
        int $rewardAmount,
        ?string $adUnitId = null,
        RewardType $rewardType = RewardType::CURRENCY,
        ?array $metadata = null
    ): Reward {
        $reward = $this->createReward(
            $userId,
            $provider,
            $transactionId,
            $rewardItem,
            $rewardAmount,
            $adUnitId,
            $rewardType,
            $metadata
        );

        $this->dispatchReward($reward);

        return $reward;
    }

    public function retryFailed(): int
    {
        $rewards = Reward::retryable()->get();
        $retried = 0;

        foreach ($rewards as $reward) {
            $this->dispatchReward($reward);
            $retried++;
        }

        Log::info('Retrying failed rewards', ['count' => $retried]);

        return $retried;
    }

    public function getPendingRewards(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return Reward::byUser($userId)->pending()->get();
    }

    public function getCompletedRewards(int $userId, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return Reward::byUser($userId)
            ->completed()
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function findByTransaction(AdProvider $provider, string $transactionId): ?Reward
    {
        return Reward::where('provider', $provider)
            ->where('transaction_id', $transactionId)
            ->first();
    }
}
