<?php

use Eduardoks98\Security\Services\IpBlockingService;

if (!function_exists('banIp')) {
    /**
     * Ban an IP address.
     *
     * @param string $ip
     * @param string|null $reason
     * @param array $metadata
     * @return \Eduardoks98\Security\Models\BannedIp
     */
    function banIp(string $ip, ?string $reason = null, array $metadata = [])
    {
        $service = app(IpBlockingService::class);
        return $service->banIp($ip, $reason, $metadata);
    }
}

if (!function_exists('unbanIp')) {
    /**
     * Unban an IP address.
     *
     * @param string $ip
     * @return bool
     */
    function unbanIp(string $ip): bool
    {
        $service = app(IpBlockingService::class);
        return $service->unbanIp($ip);
    }
}

if (!function_exists('isIpBanned')) {
    /**
     * Check if an IP is banned.
     *
     * @param string $ip
     * @return bool
     */
    function isIpBanned(string $ip): bool
    {
        $service = app(IpBlockingService::class);
        return $service->isBanned($ip);
    }
}

if (!function_exists('isIpWhitelisted')) {
    /**
     * Check if an IP is whitelisted.
     *
     * @param string $ip
     * @return bool
     */
    function isIpWhitelisted(string $ip): bool
    {
        $service = app(IpBlockingService::class);
        return $service->isWhitelisted($ip);
    }
}
