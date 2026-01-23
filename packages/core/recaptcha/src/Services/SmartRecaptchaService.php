<?php

namespace Eduardoks98\Recaptcha\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Eduardoks98\Recaptcha\Models\RecaptchaLog;
use Stevebauman\Location\Facades\Location;

class SmartRecaptchaService
{
    protected RecaptchaService $recaptchaService;

    public function __construct(RecaptchaService $recaptchaService)
    {
        $this->recaptchaService = $recaptchaService;
    }

    /**
     * Validate reCAPTCHA with smart context-aware decision.
     *
     * @param string $token
     * @param string $action
     * @param array $context
     * @return array
     */
    public function validateWithContext(string $token, string $action, array $context = []): array
    {
        $ip = $context['ip'] ?? request()->ip();
        $userAgent = $context['user_agent'] ?? request()->userAgent();
        $userId = $context['user_id'] ?? null;
        $loginContext = $context['login_context'] ?? 'unknown';

        // Calculate trust score from historical data
        $trustScore = $this->calculateTrustScore($ip, $userAgent, $userId);

        // Make decision based on trust score
        $decision = $this->makeDecision($trustScore);

        $result = [
            'success' => false,
            'score' => 0.0,
            'trust_score' => $trustScore['overall'],
            'decision' => $decision,
            'reason' => '',
            'factors' => $trustScore['factors'],
            'requires_recaptcha' => false,
        ];

        // Auto-approve high trust
        if ($trustScore['overall'] >= config('recaptcha.auto_approve_threshold', 0.8)) {
            $result['success'] = true;
            $result['score'] = $trustScore['overall'];
            $result['reason'] = 'Auto-approved: High trust score from known source';

            $this->logValidation($result, $ip, $userAgent, $loginContext, null);
            return $result;
        }

        // Auto-reject very low trust
        if ($trustScore['overall'] < config('recaptcha.auto_reject_threshold', 0.2)) {
            $result['success'] = false;
            $result['score'] = $trustScore['overall'];
            $result['reason'] = 'Auto-rejected: Suspicious activity detected';

            $this->logValidation($result, $ip, $userAgent, $loginContext, null);
            return $result;
        }

        // For medium trust, validate with reCAPTCHA
        $result['requires_recaptcha'] = true;
        $recaptchaResult = $this->recaptchaService->verify($token, $action, $ip);

        if (!$recaptchaResult['success']) {
            $result['reason'] = 'reCAPTCHA validation failed: ' . ($recaptchaResult['error'] ?? 'Unknown error');
            $this->logValidation($result, $ip, $userAgent, $loginContext, null);
            return $result;
        }

        // Combine reCAPTCHA score with trust score
        $combinedScore = ($recaptchaResult['score'] * 0.6) + ($trustScore['overall'] * 0.4);
        $result['score'] = $combinedScore;
        $result['success'] = $combinedScore >= config('recaptcha.threshold', 0.5);
        $result['reason'] = $result['success']
            ? "Validation passed (score: {$combinedScore})"
            : "Score too low (score: {$combinedScore})";

        $logId = $this->logValidation($result, $ip, $userAgent, $loginContext, $userId);
        $result['log_id'] = $logId;

        return $result;
    }

    /**
     * Calculate trust score from multiple factors.
     *
     * @param string $ip
     * @param string|null $userAgent
     * @param int|null $userId
     * @return array
     */
    protected function calculateTrustScore(string $ip, ?string $userAgent, ?int $userId): array
    {
        $weights = config('recaptcha.trust_weights', [
            'ip_reputation' => 0.25,
            'user_history' => 0.20,
            'time_pattern' => 0.10,
            'geolocation' => 0.10,
            'user_agent' => 0.05,
        ]);

        $factors = [
            'ip_reputation' => $this->calculateIpReputation($ip),
            'user_history' => $userId ? $this->calculateUserHistory($userId) : 0.5,
            'time_pattern' => $this->calculateTimePattern(),
            'geolocation' => $this->calculateGeolocationRisk($ip),
            'user_agent' => $this->calculateUserAgentScore($userAgent),
        ];

        // Calculate weighted overall score
        $overall = 0.0;
        foreach ($factors as $factor => $score) {
            $overall += $score * ($weights[$factor] ?? 0);
        }

        return [
            'overall' => round($overall, 3),
            'factors' => $factors,
        ];
    }

    /**
     * Calculate IP reputation based on historical success rate.
     *
     * @param string $ip
     * @return float
     */
    protected function calculateIpReputation(string $ip): float
    {
        if (!config('recaptcha.ip_history_enabled')) {
            return 0.5;
        }

        $cacheKey = "recaptcha:ip_reputation:{$ip}";

        return Cache::remember($cacheKey, 300, function () use ($ip) {
            $days = config('recaptcha.ip_history_days', 30);
            $since = now()->subDays($days);

            $stats = RecaptchaLog::where('ip', $ip)
                ->where('created_at', '>=', $since)
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) as successful,
                    MIN(created_at) as first_seen,
                    MAX(created_at) as last_seen
                ')
                ->first();

            if (!$stats || $stats->total == 0) {
                return 0.5; // Neutral for unknown IPs
            }

            $successRate = $stats->successful / $stats->total;

            // Bonus for established IPs (seen before)
            $ageBonus = 0.0;
            if ($stats->first_seen) {
                $daysSinceFirstSeen = now()->diffInDays($stats->first_seen);
                $ageBonus = min(0.2, $daysSinceFirstSeen / 100); // Max 0.2 bonus
            }

            return min(1.0, $successRate + $ageBonus);
        });
    }

    /**
     * Calculate user history score for known users.
     *
     * @param int $userId
     * @return float
     */
    protected function calculateUserHistory(int $userId): float
    {
        $cacheKey = "recaptcha:user_history:{$userId}";

        return Cache::remember($cacheKey, 300, function () use ($userId) {
            $stats = RecaptchaLog::where('user_id', $userId)
                ->where('created_at', '>=', now()->subDays(90))
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN login_successful = 1 THEN 1 ELSE 0 END) as successful_logins,
                    SUM(CASE WHEN login_attempted = 1 AND login_successful = 0 THEN 1 ELSE 0 END) as failed_logins
                ')
                ->first();

            if (!$stats || $stats->total == 0) {
                return 0.5; // Neutral for new users
            }

            // High success rate = high trust
            if ($stats->successful_logins > 10 && $stats->failed_logins < 3) {
                return 0.95; // Known good user
            }

            // Many failures = low trust
            if ($stats->failed_logins > 5) {
                return 0.2; // Suspicious user
            }

            return 0.7; // Average known user
        });
    }

    /**
     * Calculate time pattern score (business hours vs off-hours).
     *
     * @return float
     */
    protected function calculateTimePattern(): float
    {
        $config = config('recaptcha.business_hours');
        $now = now($config['timezone']);
        $hour = $now->hour;
        $isWeekend = $now->isWeekend();

        // Business hours on weekdays = high trust
        if (!$isWeekend && $hour >= $config['start'] && $hour < $config['end']) {
            return 0.9;
        }

        // Off-hours but not suspicious
        if (!$isWeekend) {
            return 0.6;
        }

        // Weekends are slightly less trusted
        return 0.5;
    }

    /**
     * Calculate geolocation risk score.
     *
     * @param string $ip
     * @return float
     */
    protected function calculateGeolocationRisk(string $ip): float
    {
        try {
            $location = Location::get($ip);

            if (!$location) {
                return 0.5; // Unknown location = neutral
            }

            $highRiskCountries = config('recaptcha.high_risk_countries', []);

            if (in_array($location->countryCode, $highRiskCountries)) {
                return 0.3; // High-risk country
            }

            return 0.8; // Normal country
        } catch (\Throwable $e) {
            return 0.5; // Neutral if geolocation fails
        }
    }

    /**
     * Calculate user-agent score (bot detection).
     *
     * @param string|null $userAgent
     * @return float
     */
    protected function calculateUserAgentScore(?string $userAgent): float
    {
        if (empty($userAgent)) {
            return 0.3; // No user-agent = suspicious
        }

        $botPatterns = config('recaptcha.bot_patterns', []);

        foreach ($botPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                return 0.1; // Known bot pattern
            }
        }

        return 0.9; // Normal user-agent
    }

    /**
     * Make decision based on trust score.
     *
     * @param array $trustScore
     * @return string
     */
    protected function makeDecision(array $trustScore): string
    {
        $score = $trustScore['overall'];

        if ($score >= config('recaptcha.auto_approve_threshold', 0.8)) {
            return 'auto_approved';
        }

        if ($score < config('recaptcha.auto_reject_threshold', 0.2)) {
            return 'auto_rejected';
        }

        if ($score >= config('recaptcha.high_trust_threshold', 0.7)) {
            return 'high_trust';
        }

        if ($score >= config('recaptcha.medium_trust_threshold', 0.5)) {
            return 'medium_trust';
        }

        if ($score >= config('recaptcha.low_trust_threshold', 0.3)) {
            return 'low_trust';
        }

        return 'suspicious';
    }

    /**
     * Log reCAPTCHA validation for analytics.
     *
     * @param array $result
     * @param string $ip
     * @param string|null $userAgent
     * @param string $context
     * @param int|null $userId
     * @return int|null Log ID
     */
    protected function logValidation(array $result, string $ip, ?string $userAgent, string $context, ?int $userId): ?int
    {
        if (!config('recaptcha.log_enabled')) {
            return null;
        }

        if (config('recaptcha.log_only_failures') && $result['success']) {
            return null;
        }

        try {
            $log = RecaptchaLog::create([
                'ip' => $ip,
                'score' => $result['score'],
                'trust_score' => $result['trust_score'],
                'threshold' => config('recaptcha.threshold'),
                'success' => $result['success'],
                'decision' => $result['decision'],
                'decision_reason' => $result['reason'],
                'context' => $result['factors'] ?? [],
                'user_agent' => $userAgent,
                'user_id' => $userId,
                'login_attempted' => in_array($context, ['login', 'register', 'password_reset']),
            ]);

            return $log->id;
        } catch (\Throwable $e) {
            \Log::error('Failed to log reCAPTCHA validation: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update login result after authentication attempt.
     *
     * @param int $logId
     * @param bool $success
     * @param string|null $failureReason
     * @return void
     */
    public function updateLoginResult(int $logId, bool $success, ?string $failureReason = null): void
    {
        try {
            RecaptchaLog::where('id', $logId)->update([
                'login_successful' => $success,
                'login_failure_reason' => $failureReason,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to update reCAPTCHA login result: ' . $e->getMessage());
        }
    }
}
