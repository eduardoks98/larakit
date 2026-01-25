<?php

namespace Eduardoks98\Monetization\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Eduardoks98\Monetization\Enums\AdProvider;

class AdImpression extends Model
{
    protected $fillable = [
        'user_id',
        'provider',
        'ad_unit_id',
        'ad_network',
        'ad_type',
        'placement',
        'transaction_id',
        'revenue',
        'currency',
        'country',
        'platform',
        'device_id',
        'ip_address',
        'user_agent',
        'metadata',
        'impression_at',
    ];

    protected $casts = [
        'provider' => AdProvider::class,
        'revenue' => 'decimal:6',
        'metadata' => 'array',
        'impression_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('monetization.tables.impressions', 'ad_impressions');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('monetization.user_model', 'App\\Models\\User'));
    }

    public function scopeByProvider($query, AdProvider $provider)
    {
        return $query->where('provider', $provider);
    }

    public function scopeByAdUnit($query, string $adUnitId)
    {
        return $query->where('ad_unit_id', $adUnitId);
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('impression_at', [$startDate, $endDate]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('impression_at', today());
    }

    public function scopeWithRevenue($query)
    {
        return $query->whereNotNull('revenue')->where('revenue', '>', 0);
    }
}
