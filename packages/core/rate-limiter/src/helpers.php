<?php

use Eduardoks98\RateLimiter\Services\IpWhitelistService;
use Eduardoks98\RateLimiter\Models\ApiRequestLog;
use Eduardoks98\RateLimiter\Models\ApiRequestStat;

if (!function_exists('whitelistIp')) {
    /**
     * Add an IP to the rate limiter whitelist.
     *
     * @param string $ip
     * @param string|null $description
     * @return \Eduardoks98\RateLimiter\Models\IpWhitelist
     */
    function whitelistIp(string $ip, ?string $description = null)
    {
        return app(IpWhitelistService::class)->addIp($ip, $description);
    }
}

if (!function_exists('whitelistIpRange')) {
    /**
     * Add an IP range to the rate limiter whitelist.
     *
     * @param string $minRange
     * @param string $maxRange
     * @param string|null $description
     * @return \Eduardoks98\RateLimiter\Models\IpWhitelist
     */
    function whitelistIpRange(string $minRange, string $maxRange, ?string $description = null)
    {
        return app(IpWhitelistService::class)->addRange($minRange, $maxRange, $description);
    }
}

if (!function_exists('whitelistCidr')) {
    /**
     * Add a CIDR range to the rate limiter whitelist.
     *
     * @param string $cidr Example: 192.168.1.0/24
     * @param string|null $description
     * @return \Eduardoks98\RateLimiter\Models\IpWhitelist
     */
    function whitelistCidr(string $cidr, ?string $description = null)
    {
        return app(IpWhitelistService::class)->addCidr($cidr, $description);
    }
}

if (!function_exists('removeWhitelistedIp')) {
    /**
     * Remove an IP from the rate limiter whitelist.
     *
     * @param string $ip
     * @return bool
     */
    function removeWhitelistedIp(string $ip): bool
    {
        return app(IpWhitelistService::class)->removeIp($ip);
    }
}

if (!function_exists('isIpWhitelistedForRateLimit')) {
    /**
     * Check if an IP is whitelisted for rate limiting.
     *
     * @param string $ip
     * @return bool
     */
    function isIpWhitelistedForRateLimit(string $ip): bool
    {
        return app(IpWhitelistService::class)->isWhitelisted($ip);
    }
}

if (!function_exists('getApiRequestLogs')) {
    /**
     * Get API request logs with optional filters.
     *
     * @param array $filters
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getApiRequestLogs(array $filters = [], int $limit = 100)
    {
        $query = ApiRequestLog::query();

        if (isset($filters['ip'])) {
            $query->byIp($filters['ip']);
        }

        if (isset($filters['route'])) {
            $query->byRoute($filters['route']);
        }

        if (isset($filters['failed']) && $filters['failed']) {
            $query->failed();
        }

        if (isset($filters['slow']) && $filters['slow']) {
            $query->slow($filters['slow_threshold'] ?? 1000);
        }

        return $query->latest()->limit($limit)->get();
    }
}

if (!function_exists('getApiRequestStats')) {
    /**
     * Get aggregated API request statistics.
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getApiRequestStats(?string $startDate = null, ?string $endDate = null)
    {
        $query = ApiRequestStat::query();

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        return $query->orderBy('date', 'desc')->get();
    }
}

if (!function_exists('getTopRequestIps')) {
    /**
     * Get the top IPs by request count.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getTopRequestIps(int $limit = 10)
    {
        return ApiRequestStat::topIps($limit)->get();
    }
}

if (!function_exists('getTopRequestRoutes')) {
    /**
     * Get the top routes by request count.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getTopRequestRoutes(int $limit = 10)
    {
        return ApiRequestStat::topRoutes($limit)->get();
    }
}

if (!function_exists('clearRateLimitForIp')) {
    /**
     * Clear all rate limit counters for a specific IP.
     *
     * @param string $ip
     * @return void
     */
    function clearRateLimitForIp(string $ip): void
    {
        $throttleService = app(\Eduardoks98\RateLimiter\Services\ThrottleService::class);

        // Clear tier 3 (global)
        $throttleService->clearRateLimit("rate_limiter:tier3:{$ip}");

        // Note: Tier 1 (route-based) and Tier 2 (IP+route) would need route info to clear
        // This clears the global IP limit which is often the most impactful
    }
}

if (!function_exists('banIpOnSSH')) {
    /**
     * Ban an IP at the system level using fail2ban.
     * Requires fail2ban to be installed and configured.
     *
     * @param string $ip
     * @return bool
     */
    function banIpOnSSH(string $ip): bool
    {
        if (!config('rate-limiter.fail2ban_enabled')) {
            return false;
        }

        $jail = config('rate-limiter.fail2ban_jail', 'laravel-api');

        try {
            $command = "sudo fail2ban-client set {$jail} banip {$ip}";
            exec($command, $output, $returnCode);

            return $returnCode === 0;
        } catch (\Throwable $e) {
            \Log::error("Failed to ban IP via fail2ban: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('unbanIpOnSSH')) {
    /**
     * Unban an IP at the system level using fail2ban.
     *
     * @param string $ip
     * @return bool
     */
    function unbanIpOnSSH(string $ip): bool
    {
        if (!config('rate-limiter.fail2ban_enabled')) {
            return false;
        }

        $jail = config('rate-limiter.fail2ban_jail', 'laravel-api');

        try {
            $command = "sudo fail2ban-client set {$jail} unbanip {$ip}";
            exec($command, $output, $returnCode);

            return $returnCode === 0;
        } catch (\Throwable $e) {
            \Log::error("Failed to unban IP via fail2ban: " . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('getBannedIPsFromSSH')) {
    /**
     * Get all banned IPs from fail2ban.
     *
     * @return array
     */
    function getBannedIPsFromSSH(): array
    {
        if (!config('rate-limiter.fail2ban_enabled')) {
            return [];
        }

        $jail = config('rate-limiter.fail2ban_jail', 'laravel-api');

        try {
            $command = "sudo fail2ban-client status {$jail}";
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                return [];
            }

            // Parse output to extract banned IPs
            $bannedIps = [];
            foreach ($output as $line) {
                if (strpos($line, 'Banned IP list:') !== false) {
                    $ips = trim(substr($line, strpos($line, ':') + 1));
                    $bannedIps = array_filter(explode(' ', $ips));
                    break;
                }
            }

            return $bannedIps;
        } catch (\Throwable $e) {
            \Log::error("Failed to get banned IPs from fail2ban: " . $e->getMessage());
            return [];
        }
    }
}
