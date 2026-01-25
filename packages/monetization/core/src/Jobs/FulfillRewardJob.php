<?php

namespace Eduardoks98\Monetization\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Eduardoks98\Monetization\Models\Reward;
use Eduardoks98\Monetization\Services\RewardService;

class FulfillRewardJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries;
    public int $backoff;

    public function __construct(
        public Reward $reward
    ) {
        $this->tries = config('monetization.rewards.max_retries', 3);
        $this->backoff = config('monetization.rewards.retry_delay', 60);
    }

    public function handle(RewardService $rewardService): void
    {
        Log::info('Processing reward job', [
            'reward_id' => $this->reward->id,
            'attempt' => $this->attempts(),
        ]);

        $success = $rewardService->processReward($this->reward);

        if (!$success && $this->reward->canRetry()) {
            $this->release($this->backoff);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Reward job failed permanently', [
            'reward_id' => $this->reward->id,
            'error' => $exception->getMessage(),
        ]);

        $this->reward->markAsFailed($exception->getMessage());
    }

    public function tags(): array
    {
        return [
            'monetization',
            'reward',
            'reward:' . $this->reward->id,
            'user:' . $this->reward->user_id,
            'provider:' . $this->reward->provider->value,
        ];
    }
}
