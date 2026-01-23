<?php

namespace Eduardoks98\Security\Services;

use Eduardoks98\Security\Models\BannedIp;
use Illuminate\Support\Facades\Cache;

class IpBlockingService
{
    /**
     * Check if an IP is banned.
     *
     * @param string $ip
     * @return bool
     */
    public function isBanned(string $ip): bool
    {
        // Check cache first for performance
        $cacheKey = "banned_ip:{$ip}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($ip) {
            return BannedIp::where('ip_address', $ip)->exists();
        });
    }

    /**
     * Ban an IP address.
     *
     * @param string $ip
     * @param string|null $reason
     * @param array $metadata
     * @return BannedIp
     */
    public function banIp(string $ip, ?string $reason = null, array $metadata = []): BannedIp
    {
        $bannedIp = BannedIp::create([
            'ip_address' => $ip,
            'reason' => $reason,
            'user_agent' => $metadata['user_agent'] ?? null,
            'country' => $metadata['country'] ?? null,
            'city' => $metadata['city'] ?? null,
            'latitude' => $metadata['latitude'] ?? null,
            'longitude' => $metadata['longitude'] ?? null,
        ]);

        // Clear cache
        Cache::forget("banned_ip:{$ip}");
        Cache::forget("whitelisted_ip:{$ip}");

        return $bannedIp;
    }

    /**
     * Unban an IP address.
     *
     * @param string $ip
     * @return bool
     */
    public function unbanIp(string $ip): bool
    {
        $deleted = BannedIp::where('ip_address', $ip)->delete();

        // Clear cache
        Cache::forget("banned_ip:{$ip}");

        return $deleted > 0;
    }

    /**
     * Check if an IP is whitelisted.
     *
     * @param string $ip
     * @return bool
     */
    public function isWhitelisted(string $ip): bool
    {
        if (!config('security.ip_blocking.whitelist_enabled', true)) {
            return false;
        }

        // Check cache first
        $cacheKey = "whitelisted_ip:{$ip}";

        return Cache::remember($cacheKey, now()->addHour(), function () use ($ip) {
            // Check exact match or IP ranges
            // This is a simplified version - you can implement IP range checking
            return in_array($ip, config('security.ip_whitelist', []));
        });
    }

    /**
     * Get all banned IPs.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllBanned()
    {
        return BannedIp::orderBy('created_at', 'desc')->get();
    }
}
