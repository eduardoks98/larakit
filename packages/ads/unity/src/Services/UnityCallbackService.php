<?php

namespace Eduardoks98\AdsUnity\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UnityCallbackService
{
    protected ?string $secretKey;

    public function __construct()
    {
        $this->secretKey = config('ads-unity.s2s.secret_key');
    }

    /**
     * Verify a Unity Ads S2S callback.
     *
     * Unity uses HMAC-MD5 for signature verification.
     * The signature is computed from all parameters (except hmac) sorted alphabetically.
     */
    public function verifyCallback(Request $request): bool
    {
        if (!config('ads-unity.s2s.enabled', true)) {
            return true;
        }

        if (!$this->secretKey) {
            $this->logFailure('Secret key not configured');
            return false;
        }

        $hmac = $request->query('hmac');

        if (!$hmac) {
            $this->logFailure('Missing hmac parameter', $request->all());
            return false;
        }

        // Build the message to verify
        $message = $this->buildVerificationMessage($request);

        // Compute expected HMAC
        $expectedHmac = $this->computeHmac($message);

        // Compare signatures
        if (!hash_equals($expectedHmac, $hmac)) {
            $this->logFailure('HMAC verification failed', [
                'expected' => $expectedHmac,
                'received' => $hmac,
            ]);
            return false;
        }

        return true;
    }

    /**
     * Build the message string for HMAC verification.
     *
     * According to Unity docs:
     * 1. Sort parameters alphabetically by key (excluding hmac)
     * 2. Create comma-separated key=value string
     */
    protected function buildVerificationMessage(Request $request): string
    {
        $params = $request->query();

        // Remove hmac from params
        unset($params['hmac']);

        // Sort parameters alphabetically by key
        ksort($params);

        // Build comma-separated key=value string
        $parts = [];
        foreach ($params as $key => $value) {
            $parts[] = $key . '=' . $value;
        }

        return implode(',', $parts);
    }

    /**
     * Compute HMAC-MD5 signature.
     */
    protected function computeHmac(string $message): string
    {
        return hash_hmac('md5', $message, $this->secretKey);
    }

    /**
     * Parse the server ID (sid) to extract user information.
     *
     * The sid typically contains the user_id.
     */
    public function parseServerId(?string $sid): array
    {
        if (!$sid) {
            return [];
        }

        // Try to decode as JSON first
        $decoded = json_decode($sid, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // Otherwise, treat as plain user_id
        return ['user_id' => $sid];
    }

    /**
     * Extract user ID from server ID.
     */
    public function extractUserId(?string $sid): ?int
    {
        $data = $this->parseServerId($sid);
        $userId = $data['user_id'] ?? null;

        return $userId ? (int) $userId : null;
    }

    /**
     * Generate a signature for testing purposes.
     */
    public function generateSignature(array $params): string
    {
        // Remove hmac if present
        unset($params['hmac']);

        // Sort alphabetically
        ksort($params);

        // Build message
        $parts = [];
        foreach ($params as $key => $value) {
            $parts[] = $key . '=' . $value;
        }

        $message = implode(',', $parts);

        return $this->computeHmac($message);
    }

    /**
     * Log a failure.
     */
    protected function logFailure(string $message, array $context = []): void
    {
        if (config('ads-unity.logging.log_failures', true)) {
            $channel = config('ads-unity.logging.channel');
            $logger = $channel ? Log::channel($channel) : Log::getFacadeRoot();
            $logger->warning("Unity Ads S2S: {$message}", $context);
        }
    }
}
