<?php

use Eduardoks98\Recaptcha\Services\SmartRecaptchaService;
use Eduardoks98\Recaptcha\Services\RecaptchaService;
use Eduardoks98\Recaptcha\Models\RecaptchaLog;

if (!function_exists('checkRecaptcha')) {
    /**
     * Check reCAPTCHA with smart context-aware validation.
     *
     * @param string $token
     * @param string $action
     * @param array $context
     * @return array
     */
    function checkRecaptcha(string $token, string $action = 'submit', array $context = []): array
    {
        return app(SmartRecaptchaService::class)->validateWithContext($token, $action, $context);
    }
}

if (!function_exists('verifyRecaptchaToken')) {
    /**
     * Verify a reCAPTCHA token (basic validation without smart features).
     *
     * @param string $token
     * @param string $action
     * @param string|null $ip
     * @return array
     */
    function verifyRecaptchaToken(string $token, string $action = 'submit', ?string $ip = null): array
    {
        return app(RecaptchaService::class)->verify($token, $action, $ip);
    }
}

if (!function_exists('updateRecaptchaLoginResult')) {
    /**
     * Update reCAPTCHA log with login result.
     *
     * @param int $logId
     * @param bool $success
     * @param string|null $failureReason
     * @return void
     */
    function updateRecaptchaLoginResult(int $logId, bool $success, ?string $failureReason = null): void
    {
        app(SmartRecaptchaService::class)->updateLoginResult($logId, $success, $failureReason);
    }
}

if (!function_exists('getRecaptchaSiteKey')) {
    /**
     * Get the reCAPTCHA site key for frontend integration.
     *
     * @return string|null
     */
    function getRecaptchaSiteKey(): ?string
    {
        return app(RecaptchaService::class)->getSiteKey();
    }
}

if (!function_exists('isRecaptchaEnabled')) {
    /**
     * Check if reCAPTCHA is enabled.
     *
     * @return bool
     */
    function isRecaptchaEnabled(): bool
    {
        return app(RecaptchaService::class)->isEnabled();
    }
}

if (!function_exists('getRecaptchaLogs')) {
    /**
     * Get reCAPTCHA validation logs with optional filters.
     *
     * @param array $filters
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getRecaptchaLogs(array $filters = [], int $limit = 100)
    {
        $query = RecaptchaLog::query();

        if (isset($filters['ip'])) {
            $query->byIp($filters['ip']);
        }

        if (isset($filters['user_id'])) {
            $query->byUser($filters['user_id']);
        }

        if (isset($filters['success'])) {
            if ($filters['success']) {
                $query->successful();
            } else {
                $query->failed();
            }
        }

        if (isset($filters['login_attempts']) && $filters['login_attempts']) {
            $query->loginAttempts();
        }

        if (isset($filters['suspicious']) && $filters['suspicious']) {
            $query->suspicious();
        }

        return $query->latest()->limit($limit)->get();
    }
}

if (!function_exists('getRecaptchaStats')) {
    /**
     * Get reCAPTCHA statistics.
     *
     * @param int $days Number of days to look back
     * @return array
     */
    function getRecaptchaStats(int $days = 7): array
    {
        $since = now()->subDays($days);

        $total = RecaptchaLog::where('created_at', '>=', $since)->count();
        $successful = RecaptchaLog::where('created_at', '>=', $since)->successful()->count();
        $failed = RecaptchaLog::where('created_at', '>=', $since)->failed()->count();
        $suspicious = RecaptchaLog::where('created_at', '>=', $since)->suspicious()->count();

        $loginAttempts = RecaptchaLog::where('created_at', '>=', $since)->loginAttempts()->count();
        $successfulLogins = RecaptchaLog::where('created_at', '>=', $since)->successfulLogins()->count();
        $failedLogins = RecaptchaLog::where('created_at', '>=', $since)->failedLogins()->count();

        $avgScore = RecaptchaLog::where('created_at', '>=', $since)->avg('score') ?? 0;
        $avgTrustScore = RecaptchaLog::where('created_at', '>=', $since)->avg('trust_score') ?? 0;

        return [
            'period_days' => $days,
            'total_validations' => $total,
            'successful' => $successful,
            'failed' => $failed,
            'suspicious' => $suspicious,
            'success_rate' => $total > 0 ? round(($successful / $total) * 100, 2) : 0,
            'login_attempts' => $loginAttempts,
            'successful_logins' => $successfulLogins,
            'failed_logins' => $failedLogins,
            'login_success_rate' => $loginAttempts > 0 ? round(($successfulLogins / $loginAttempts) * 100, 2) : 0,
            'avg_score' => round($avgScore, 3),
            'avg_trust_score' => round($avgTrustScore, 3),
        ];
    }
}

if (!function_exists('getTopSuspiciousIps')) {
    /**
     * Get the top suspicious IPs from reCAPTCHA logs.
     *
     * @param int $limit
     * @param int $days
     * @return \Illuminate\Support\Collection
     */
    function getTopSuspiciousIps(int $limit = 10, int $days = 7)
    {
        $since = now()->subDays($days);

        return RecaptchaLog::where('created_at', '>=', $since)
            ->where('success', false)
            ->select('ip')
            ->selectRaw('COUNT(*) as failed_count')
            ->selectRaw('AVG(score) as avg_score')
            ->selectRaw('AVG(trust_score) as avg_trust_score')
            ->groupBy('ip')
            ->orderByDesc('failed_count')
            ->limit($limit)
            ->get();
    }
}

if (!function_exists('getRecaptchaDecisionDistribution')) {
    /**
     * Get the distribution of reCAPTCHA decisions.
     *
     * @param int $days
     * @return array
     */
    function getRecaptchaDecisionDistribution(int $days = 7): array
    {
        $since = now()->subDays($days);

        $distribution = RecaptchaLog::where('created_at', '>=', $since)
            ->select('decision')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('decision')
            ->get()
            ->pluck('count', 'decision')
            ->toArray();

        return $distribution;
    }
}
