<?php

namespace Eduardoks98\EmailValidator\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Eduardoks98\EmailValidator\Exceptions\EmailValidationException;

/**
 * Email Validator Service
 *
 * Comprehensive email validation with multiple checks:
 * - Syntax validation (RFC 5322)
 * - DNS verification
 * - MX records check
 * - Disposable email detection
 * - Role-based email detection
 * - Quality scoring
 */
class EmailValidatorService
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('email-validator');
    }

    /**
     * Validate an email address with all configured checks.
     *
     * @param string $email Email address to validate
     * @param array $options Override default checks
     * @return array Validation result with details
     */
    public function validate(string $email, array $options = []): array
    {
        $email = strtolower(trim($email));

        // Check cache first
        $cacheKey = $this->getCacheKey($email);
        if ($this->isCacheEnabled() && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $result = [
            'email' => $email,
            'valid' => false,
            'checks' => [],
            'score' => 0,
            'quality' => 'unknown',
            'suggestions' => [],
        ];

        // Parse email
        $parts = $this->parseEmail($email);
        if (!$parts) {
            $result['checks']['syntax'] = false;
            $result['error'] = 'Invalid email format';
            return $result;
        }

        $result['local_part'] = $parts['local'];
        $result['domain'] = $parts['domain'];

        // Run checks
        $checks = array_merge($this->config['checks'] ?? [], $options);

        // 1. Syntax check
        if ($checks['syntax'] ?? true) {
            $result['checks']['syntax'] = $this->checkSyntax($email);
        }

        // 2. DNS check
        if ($checks['dns'] ?? true) {
            $result['checks']['dns'] = $this->checkDns($parts['domain']);
        }

        // 3. MX records check
        if ($checks['mx'] ?? true) {
            $result['checks']['mx'] = $this->checkMx($parts['domain']);
            $result['mx_records'] = $this->getMxRecords($parts['domain']);
        }

        // 4. Disposable email check
        if ($checks['disposable'] ?? true) {
            $result['checks']['disposable'] = $this->isDisposable($parts['domain']);
            $result['is_disposable'] = $result['checks']['disposable'];
        }

        // 5. Role-based email check
        if ($checks['role'] ?? true) {
            $result['checks']['role'] = $this->isRoleBased($parts['local']);
            $result['is_role'] = $result['checks']['role'];
        }

        // 6. Check if trusted domain
        $result['is_trusted'] = $this->isTrustedDomain($parts['domain']);

        // 7. SMTP verification (if enabled)
        if ($checks['smtp'] ?? false) {
            $result['checks']['smtp'] = $this->checkSmtp($email, $parts['domain']);
        }

        // Calculate score and quality
        $result['score'] = $this->calculateScore($result['checks'], $result['is_trusted']);
        $result['quality'] = $this->getQualityLabel($result['score']);

        // Determine if email is valid
        $result['valid'] = $this->isValid($result);

        // Add suggestions
        $result['suggestions'] = $this->getSuggestions($result);

        // Cache result
        if ($this->isCacheEnabled()) {
            Cache::put($cacheKey, $result, $this->config['cache']['ttl'] ?? 3600);
        }

        return $result;
    }

    /**
     * Quick validation (syntax only).
     *
     * @param string $email
     * @return bool
     */
    public function isValidSyntax(string $email): bool
    {
        return $this->checkSyntax(strtolower(trim($email)));
    }

    /**
     * Check if email is from a disposable domain.
     *
     * @param string $email
     * @return bool
     */
    public function isEmailDisposable(string $email): bool
    {
        $parts = $this->parseEmail($email);
        return $parts ? $this->isDisposable($parts['domain']) : false;
    }

    /**
     * Check if email is role-based.
     *
     * @param string $email
     * @return bool
     */
    public function isEmailRoleBased(string $email): bool
    {
        $parts = $this->parseEmail($email);
        return $parts ? $this->isRoleBased($parts['local']) : false;
    }

    /**
     * Get domain from email.
     *
     * @param string $email
     * @return string|null
     */
    public function getDomain(string $email): ?string
    {
        $parts = $this->parseEmail($email);
        return $parts['domain'] ?? null;
    }

    /**
     * Check if domain has valid MX records.
     *
     * @param string $domain
     * @return bool
     */
    public function hasMxRecords(string $domain): bool
    {
        return $this->checkMx($domain);
    }

    /**
     * Validate multiple emails.
     *
     * @param array $emails
     * @param array $options
     * @return array
     */
    public function validateBatch(array $emails, array $options = []): array
    {
        $results = [];

        foreach ($emails as $email) {
            $results[$email] = $this->validate($email, $options);
        }

        return $results;
    }

    /**
     * Parse email into local part and domain.
     */
    protected function parseEmail(string $email): ?array
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        $parts = explode('@', $email);

        if (count($parts) !== 2) {
            return null;
        }

        return [
            'local' => $parts[0],
            'domain' => $parts[1],
        ];
    }

    /**
     * Check email syntax.
     */
    protected function checkSyntax(string $email): bool
    {
        // Basic filter validation
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // Additional RFC 5322 checks
        $pattern = '/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD';

        return preg_match($pattern, $email) === 1;
    }

    /**
     * Check DNS records for domain.
     */
    protected function checkDns(string $domain): bool
    {
        return checkdnsrr($domain, 'ANY');
    }

    /**
     * Check MX records for domain.
     */
    protected function checkMx(string $domain): bool
    {
        return checkdnsrr($domain, 'MX');
    }

    /**
     * Get MX records for domain.
     */
    protected function getMxRecords(string $domain): array
    {
        $hosts = [];
        $weights = [];

        if (getmxrr($domain, $hosts, $weights)) {
            $records = [];
            foreach ($hosts as $i => $host) {
                $records[] = [
                    'host' => $host,
                    'priority' => $weights[$i] ?? 0,
                ];
            }
            // Sort by priority
            usort($records, fn($a, $b) => $a['priority'] <=> $b['priority']);
            return $records;
        }

        return [];
    }

    /**
     * Check if domain is disposable.
     */
    protected function isDisposable(string $domain): bool
    {
        $disposableDomains = $this->config['disposable_domains'] ?? [];
        return in_array($domain, $disposableDomains);
    }

    /**
     * Check if email is role-based.
     */
    protected function isRoleBased(string $localPart): bool
    {
        $rolePrefixes = $this->config['role_prefixes'] ?? [];

        foreach ($rolePrefixes as $prefix) {
            if ($localPart === $prefix || str_starts_with($localPart, $prefix . '.') || str_starts_with($localPart, $prefix . '_') || str_starts_with($localPart, $prefix . '-')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if domain is trusted.
     */
    protected function isTrustedDomain(string $domain): bool
    {
        $trustedDomains = $this->config['trusted_domains'] ?? [];
        return in_array($domain, $trustedDomains);
    }

    /**
     * Check SMTP deliverability.
     */
    protected function checkSmtp(string $email, string $domain): bool
    {
        $mxRecords = $this->getMxRecords($domain);

        if (empty($mxRecords)) {
            return false;
        }

        $smtpConfig = $this->config['smtp'] ?? [];
        $timeout = $smtpConfig['timeout'] ?? 10;
        $fromEmail = $smtpConfig['from_email'] ?? 'verify@example.com';

        foreach ($mxRecords as $mx) {
            try {
                $socket = @fsockopen($mx['host'], 25, $errno, $errstr, $timeout);

                if (!$socket) {
                    continue;
                }

                // Read greeting
                $response = fgets($socket, 1024);
                if (strpos($response, '220') !== 0) {
                    fclose($socket);
                    continue;
                }

                // HELO
                fwrite($socket, "HELO verify.local\r\n");
                $response = fgets($socket, 1024);

                // MAIL FROM
                fwrite($socket, "MAIL FROM:<{$fromEmail}>\r\n");
                $response = fgets($socket, 1024);

                // RCPT TO
                fwrite($socket, "RCPT TO:<{$email}>\r\n");
                $response = fgets($socket, 1024);

                // QUIT
                fwrite($socket, "QUIT\r\n");
                fclose($socket);

                // Check if RCPT TO was accepted
                return strpos($response, '250') === 0 || strpos($response, '251') === 0;
            } catch (\Exception $e) {
                Log::warning('SMTP verification failed', [
                    'email' => $email,
                    'mx' => $mx['host'],
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return false;
    }

    /**
     * Calculate quality score.
     */
    protected function calculateScore(array $checks, bool $isTrusted): int
    {
        $weights = $this->config['scoring']['weights'] ?? [];
        $score = 0;

        if ($checks['syntax'] ?? false) {
            $score += $weights['syntax'] ?? 20;
        }

        if ($checks['dns'] ?? false) {
            $score += $weights['dns'] ?? 20;
        }

        if ($checks['mx'] ?? false) {
            $score += $weights['mx'] ?? 25;
        }

        if (!($checks['disposable'] ?? false)) {
            $score += $weights['not_disposable'] ?? 20;
        }

        if (!($checks['role'] ?? false)) {
            $score += $weights['not_role'] ?? 10;
        }

        if ($isTrusted) {
            $score += $weights['trusted_domain'] ?? 5;
        }

        return min(100, $score);
    }

    /**
     * Get quality label from score.
     */
    protected function getQualityLabel(int $score): string
    {
        $thresholds = $this->config['scoring']['thresholds'] ?? [];

        if ($score >= ($thresholds['excellent'] ?? 90)) {
            return 'excellent';
        }
        if ($score >= ($thresholds['good'] ?? 70)) {
            return 'good';
        }
        if ($score >= ($thresholds['acceptable'] ?? 50)) {
            return 'acceptable';
        }
        if ($score >= ($thresholds['poor'] ?? 30)) {
            return 'poor';
        }

        return 'invalid';
    }

    /**
     * Determine if email is valid based on checks.
     */
    protected function isValid(array $result): bool
    {
        // Must pass syntax
        if (!($result['checks']['syntax'] ?? false)) {
            return false;
        }

        // Must have MX records (if checked)
        if (isset($result['checks']['mx']) && !$result['checks']['mx']) {
            return false;
        }

        // Cannot be disposable
        if ($result['is_disposable'] ?? false) {
            return false;
        }

        return true;
    }

    /**
     * Get suggestions based on validation result.
     */
    protected function getSuggestions(array $result): array
    {
        $suggestions = [];

        if ($result['is_disposable'] ?? false) {
            $suggestions[] = 'This is a disposable email address. Consider using a permanent email.';
        }

        if ($result['is_role'] ?? false) {
            $suggestions[] = 'This appears to be a role-based email. Personal emails may have better deliverability.';
        }

        if (!($result['checks']['mx'] ?? true)) {
            $suggestions[] = 'Domain has no mail servers configured. Email delivery is unlikely.';
        }

        if (!($result['checks']['dns'] ?? true)) {
            $suggestions[] = 'Domain does not exist or has DNS issues.';
        }

        return $suggestions;
    }

    /**
     * Check if cache is enabled.
     */
    protected function isCacheEnabled(): bool
    {
        return $this->config['cache']['enabled'] ?? true;
    }

    /**
     * Get cache key with prefix.
     */
    protected function getCacheKey(string $email): string
    {
        $prefix = $this->config['cache']['prefix'] ?? 'email_validator';
        return "{$prefix}:" . md5($email);
    }
}
