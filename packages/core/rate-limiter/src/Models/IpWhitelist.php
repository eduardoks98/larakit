<?php

namespace Eduardoks98\RateLimiter\Models;

use Illuminate\Database\Eloquent\Model;

class IpWhitelist extends Model
{
    protected $table = 'ip_whitelist';

    protected $fillable = [
        'min_range',
        'max_range',
        'unique_ip',
        'description',
    ];

    /**
     * Scope to get exact IP entries.
     */
    public function scopeExactIp($query)
    {
        return $query->whereNotNull('unique_ip');
    }

    /**
     * Scope to get IP range entries.
     */
    public function scopeIpRange($query)
    {
        return $query->whereNull('unique_ip')
            ->whereNotNull('min_range')
            ->whereNotNull('max_range');
    }

    /**
     * Check if this is a single IP entry.
     */
    public function isSingleIp(): bool
    {
        return !empty($this->unique_ip);
    }

    /**
     * Check if this is an IP range entry.
     */
    public function isRange(): bool
    {
        return !empty($this->min_range) && !empty($this->max_range);
    }

    /**
     * Get a human-readable representation of the whitelist entry.
     */
    public function getDisplayAttribute(): string
    {
        if ($this->isSingleIp()) {
            return $this->unique_ip;
        }

        if ($this->isRange()) {
            return "{$this->min_range} - {$this->max_range}";
        }

        return 'Invalid entry';
    }
}
