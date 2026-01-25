<?php

namespace Eduardoks98\Monetization\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Eduardoks98\Monetization\Enums\AdProvider;
use Eduardoks98\Monetization\Enums\RewardStatus;
use Eduardoks98\Monetization\Enums\RewardType;

class Reward extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'transaction_id',
        'ad_unit_id',
        'reward_type',
        'reward_item',
        'reward_amount',
        'status',
        'attempts',
        'error_message',
        'processed_at',
        'metadata',
    ];

    protected $casts = [
        'provider' => AdProvider::class,
        'reward_type' => RewardType::class,
        'status' => RewardStatus::class,
        'reward_amount' => 'integer',
        'attempts' => 'integer',
        'metadata' => 'array',
        'processed_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('monetization.tables.rewards', 'rewards');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('monetization.user_model', 'App\\Models\\User'));
    }

    public function scopePending($query)
    {
        return $query->where('status', RewardStatus::PENDING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', RewardStatus::COMPLETED);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', RewardStatus::FAILED);
    }

    public function scopeByProvider($query, AdProvider $provider)
    {
        return $query->where('provider', $provider);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRetryable($query)
    {
        $maxRetries = config('monetization.rewards.max_retries', 3);
        return $query->where('status', RewardStatus::FAILED)
            ->where('attempts', '<', $maxRetries);
    }

    public function markAsProcessing(): bool
    {
        return $this->update([
            'status' => RewardStatus::PROCESSING,
            'attempts' => $this->attempts + 1,
        ]);
    }

    public function markAsCompleted(): bool
    {
        return $this->update([
            'status' => RewardStatus::COMPLETED,
            'processed_at' => now(),
        ]);
    }

    public function markAsFailed(string $errorMessage = null): bool
    {
        return $this->update([
            'status' => RewardStatus::FAILED,
            'error_message' => $errorMessage,
        ]);
    }

    public function markAsDuplicate(): bool
    {
        return $this->update([
            'status' => RewardStatus::DUPLICATE,
            'processed_at' => now(),
        ]);
    }

    public function canRetry(): bool
    {
        $maxRetries = config('monetization.rewards.max_retries', 3);
        return $this->status === RewardStatus::FAILED && $this->attempts < $maxRetries;
    }

    public function isDuplicate(): bool
    {
        if (!config('monetization.rewards.duplicate_detection', true)) {
            return false;
        }

        $window = config('monetization.rewards.duplicate_window', 86400);

        return static::where('transaction_id', $this->transaction_id)
            ->where('provider', $this->provider)
            ->where('id', '!=', $this->id)
            ->where('status', RewardStatus::COMPLETED)
            ->where('created_at', '>=', now()->subSeconds($window))
            ->exists();
    }
}
