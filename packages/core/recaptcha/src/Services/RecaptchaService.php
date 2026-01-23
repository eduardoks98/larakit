<?php

namespace Eduardoks98\Recaptcha\Services;

use Illuminate\Support\Facades\Http;

class RecaptchaService
{
    /**
     * Verify a reCAPTCHA token.
     *
     * @param string $token
     * @param string $action
     * @param string|null $ip
     * @return array
     */
    public function verify(string $token, string $action, ?string $ip = null): array
    {
        if (!config('recaptcha.enabled')) {
            return [
                'success' => true,
                'score' => 1.0,
                'action' => $action,
                'message' => 'reCAPTCHA disabled',
            ];
        }

        // Use Enterprise API if enabled
        if (config('recaptcha.enterprise_enabled')) {
            return $this->verifyEnterprise($token, $action, $ip);
        }

        return $this->verifyV3($token, $ip);
    }

    /**
     * Verify with reCAPTCHA v3.
     *
     * @param string $token
     * @param string|null $ip
     * @return array
     */
    protected function verifyV3(string $token, ?string $ip = null): array
    {
        $secret = config('recaptcha.v3_secret');

        if (empty($secret)) {
            return [
                'success' => false,
                'score' => 0.0,
                'error' => 'reCAPTCHA secret not configured',
            ];
        }

        $verifyUrl = config('recaptcha.verify_url');
        $timeout = config('recaptcha.timeout', 10);
        $verifySSL = config('recaptcha.verify_ssl', true);

        try {
            $response = Http::timeout($timeout)
                ->withOptions(['verify' => $verifySSL])
                ->asForm()
                ->post($verifyUrl, [
                    'secret' => $secret,
                    'response' => $token,
                    'remoteip' => $ip,
                ]);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'score' => 0.0,
                    'error' => 'reCAPTCHA API request failed',
                ];
            }

            $data = $response->json();

            return [
                'success' => $data['success'] ?? false,
                'score' => $data['score'] ?? 0.0,
                'action' => $data['action'] ?? null,
                'challenge_ts' => $data['challenge_ts'] ?? null,
                'hostname' => $data['hostname'] ?? null,
                'error_codes' => $data['error-codes'] ?? [],
                'error' => $this->getErrorMessage($data['error-codes'] ?? []),
            ];
        } catch (\Throwable $e) {
            \Log::error('reCAPTCHA verification exception: ' . $e->getMessage());

            return [
                'success' => false,
                'score' => 0.0,
                'error' => 'reCAPTCHA verification exception: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify with reCAPTCHA Enterprise.
     *
     * @param string $token
     * @param string $action
     * @param string|null $ip
     * @return array
     */
    protected function verifyEnterprise(string $token, string $action, ?string $ip = null): array
    {
        $apiKey = config('recaptcha.enterprise_api_key');
        $projectId = config('recaptcha.enterprise_project_id');
        $siteKey = config('recaptcha.v3_site_key');

        if (empty($apiKey) || empty($projectId) || empty($siteKey)) {
            return [
                'success' => false,
                'score' => 0.0,
                'error' => 'reCAPTCHA Enterprise not properly configured',
            ];
        }

        $url = str_replace('{projectId}', $projectId, config('recaptcha.enterprise_verify_url'));
        $url .= '?key=' . $apiKey;

        $timeout = config('recaptcha.timeout', 10);
        $verifySSL = config('recaptcha.verify_ssl', true);

        try {
            $response = Http::timeout($timeout)
                ->withOptions(['verify' => $verifySSL])
                ->post($url, [
                    'event' => [
                        'token' => $token,
                        'expectedAction' => $action,
                        'siteKey' => $siteKey,
                        'userIpAddress' => $ip,
                    ],
                ]);

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'score' => 0.0,
                    'error' => 'reCAPTCHA Enterprise API request failed',
                ];
            }

            $data = $response->json();
            $tokenProperties = $data['tokenProperties'] ?? [];
            $riskAnalysis = $data['riskAnalysis'] ?? [];

            $valid = ($tokenProperties['valid'] ?? false) &&
                     ($tokenProperties['action'] ?? '') === $action;

            return [
                'success' => $valid,
                'score' => $riskAnalysis['score'] ?? 0.0,
                'action' => $tokenProperties['action'] ?? null,
                'challenge_ts' => $tokenProperties['createTime'] ?? null,
                'hostname' => $tokenProperties['hostname'] ?? null,
                'reasons' => $riskAnalysis['reasons'] ?? [],
                'error' => $valid ? null : 'Token invalid or action mismatch',
            ];
        } catch (\Throwable $e) {
            \Log::error('reCAPTCHA Enterprise verification exception: ' . $e->getMessage());

            return [
                'success' => false,
                'score' => 0.0,
                'error' => 'reCAPTCHA Enterprise verification exception: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get human-readable error message from error codes.
     *
     * @param array $errorCodes
     * @return string|null
     */
    protected function getErrorMessage(array $errorCodes): ?string
    {
        if (empty($errorCodes)) {
            return null;
        }

        $messages = [
            'missing-input-secret' => 'The secret parameter is missing',
            'invalid-input-secret' => 'The secret parameter is invalid or malformed',
            'missing-input-response' => 'The response parameter is missing',
            'invalid-input-response' => 'The response parameter is invalid or malformed',
            'bad-request' => 'The request is invalid or malformed',
            'timeout-or-duplicate' => 'The response is no longer valid: either is too old or has been used previously',
        ];

        $code = $errorCodes[0];
        return $messages[$code] ?? "Unknown error: {$code}";
    }

    /**
     * Get the reCAPTCHA site key.
     *
     * @return string|null
     */
    public function getSiteKey(): ?string
    {
        return config('recaptcha.v3_site_key');
    }

    /**
     * Check if reCAPTCHA is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return config('recaptcha.enabled', true);
    }
}
