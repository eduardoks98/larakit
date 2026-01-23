<?php

namespace Eduardoks98\RateLimiter\Services;

use Illuminate\Support\Facades\Cache;
use Eduardoks98\RateLimiter\Models\IpWhitelist;

class IpWhitelistService
{
    protected const CACHE_KEY = 'rate_limiter:whitelist';
    protected const CACHE_TTL = 600; // 10 minutes

    /**
     * Check if an IP is whitelisted.
     *
     * @param string $ip
     * @return bool
     */
    public function isWhitelisted(string $ip): bool
    {
        if (!config('rate-limiter.whitelist_enabled')) {
            return false;
        }

        $whitelist = $this->getWhitelist();

        // Check exact IP match
        if (in_array($ip, $whitelist['exact'])) {
            return true;
        }

        // Check IP ranges
        $ipLong = ip2long($ip);
        if ($ipLong === false) {
            return false;
        }

        foreach ($whitelist['ranges'] as $range) {
            if ($ipLong >= $range['min'] && $ipLong <= $range['max']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all whitelisted IPs and ranges from cache or database.
     *
     * @return array
     */
    protected function getWhitelist(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            $records = IpWhitelist::all();

            $exact = [];
            $ranges = [];

            foreach ($records as $record) {
                if ($record->unique_ip) {
                    $exact[] = $record->unique_ip;
                } else {
                    $ranges[] = [
                        'min' => ip2long($record->min_range),
                        'max' => ip2long($record->max_range),
                    ];
                }
            }

            return [
                'exact' => $exact,
                'ranges' => $ranges,
            ];
        });
    }

    /**
     * Add an IP to the whitelist.
     *
     * @param string $ip
     * @param string|null $description
     * @return IpWhitelist
     */
    public function addIp(string $ip, ?string $description = null): IpWhitelist
    {
        $whitelist = IpWhitelist::create([
            'unique_ip' => $ip,
            'description' => $description,
        ]);

        $this->clearCache();

        return $whitelist;
    }

    /**
     * Add an IP range to the whitelist.
     *
     * @param string $minRange
     * @param string $maxRange
     * @param string|null $description
     * @return IpWhitelist
     */
    public function addRange(string $minRange, string $maxRange, ?string $description = null): IpWhitelist
    {
        $whitelist = IpWhitelist::create([
            'min_range' => $minRange,
            'max_range' => $maxRange,
            'description' => $description,
        ]);

        $this->clearCache();

        return $whitelist;
    }

    /**
     * Add a CIDR range to the whitelist.
     *
     * @param string $cidr Example: 192.168.1.0/24
     * @param string|null $description
     * @return IpWhitelist
     */
    public function addCidr(string $cidr, ?string $description = null): IpWhitelist
    {
        [$ip, $mask] = explode('/', $cidr);

        $ipLong = ip2long($ip);
        $maskLong = ~((1 << (32 - $mask)) - 1);

        $minRange = long2ip($ipLong & $maskLong);
        $maxRange = long2ip($ipLong | (~$maskLong & 0xFFFFFFFF));

        return $this->addRange($minRange, $maxRange, $description ?? "CIDR: {$cidr}");
    }

    /**
     * Remove an IP from the whitelist.
     *
     * @param string $ip
     * @return bool
     */
    public function removeIp(string $ip): bool
    {
        $deleted = IpWhitelist::where('unique_ip', $ip)->delete();

        if ($deleted) {
            $this->clearCache();
        }

        return $deleted > 0;
    }

    /**
     * Remove an IP range from the whitelist.
     *
     * @param string $minRange
     * @param string $maxRange
     * @return bool
     */
    public function removeRange(string $minRange, string $maxRange): bool
    {
        $deleted = IpWhitelist::where('min_range', $minRange)
            ->where('max_range', $maxRange)
            ->delete();

        if ($deleted) {
            $this->clearCache();
        }

        return $deleted > 0;
    }

    /**
     * Get all whitelist entries.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll()
    {
        return IpWhitelist::all();
    }

    /**
     * Clear the whitelist cache.
     *
     * @return void
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
