<?php

namespace Eduardoks98\Monetization\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Eduardoks98\Monetization\Enums\AdProvider;

class VirtualCurrencyTransaction extends Model
{
    public const TYPE_CREDIT = 'credit';
    public const TYPE_DEBIT = 'debit';

    public const SOURCE_AD_REWARD = 'ad_reward';
    public const SOURCE_PURCHASE = 'purchase';
    public const SOURCE_GIFT = 'gift';
    public const SOURCE_ADJUSTMENT = 'adjustment';
    public const SOURCE_SPEND = 'spend';
    public const SOURCE_REFUND = 'refund';

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'source',
        'source_id',
        'provider',
        'description',
        'metadata',
    ];

    protected $casts = [
        'provider' => AdProvider::class,
        'amount' => 'integer',
        'balance_before' => 'integer',
        'balance_after' => 'integer',
        'metadata' => 'array',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('monetization.tables.transactions', 'virtual_currency_transactions');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('monetization.user_model', 'App\\Models\\User'));
    }

    public function reward(): BelongsTo
    {
        return $this->belongsTo(Reward::class, 'source_id')
            ->where('source', self::SOURCE_AD_REWARD);
    }

    public function scopeCredits($query)
    {
        return $query->where('type', self::TYPE_CREDIT);
    }

    public function scopeDebits($query)
    {
        return $query->where('type', self::TYPE_DEBIT);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeBySource($query, string $source)
    {
        return $query->where('source', $source);
    }

    public function scopeFromAds($query)
    {
        return $query->where('source', self::SOURCE_AD_REWARD);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function isCredit(): bool
    {
        return $this->type === self::TYPE_CREDIT;
    }

    public function isDebit(): bool
    {
        return $this->type === self::TYPE_DEBIT;
    }
}
