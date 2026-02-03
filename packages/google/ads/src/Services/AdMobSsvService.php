<?php

namespace Eduardoks98\AdsGoogle\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class AdMobSsvService
{
    protected array $publicKeys = [];
    protected string $cachePrefix;
    protected int $cacheTtl;

    public function __construct()
    {
        $this->cachePrefix = config('ads-google.ssv.cache_prefix', 'admob_ssv_');
        $this->cacheTtl = config('ads-google.ssv.keys_cache_ttl', 86400);
    }

    /**
     * Verify an AdMob SSV callback.
     */
    public function verifyCallback(Request $request): bool
    {
        if (!config('ads-google.ssv.enabled', true)) {
            return true;
        }

        $signature = $request->query('signature');
        $keyId = $request->query('key_id');

        if (!$signature || !$keyId) {
            $this->logFailure('Missing signature or key_id', $request->all());
            return false;
        }

        // Build the message to verify (all query params except signature)
        $message = $this->buildVerificationMessage($request);

        // Get the public key
        $publicKey = $this->getPublicKey($keyId);
        if (!$publicKey) {
            $this->logFailure('Public key not found', ['key_id' => $keyId]);
            return false;
        }

        // Verify the signature
        $isValid = $this->verifySignature($message, $signature, $publicKey);

        if (!$isValid) {
            $this->logFailure('Signature verification failed', [
                'key_id' => $keyId,
                'message' => $message,
            ]);
            return false;
        }

        // Verify timestamp is within acceptable drift
        $timestamp = $request->query('timestamp');
        if ($timestamp && !$this->verifyTimestamp($timestamp)) {
            $this->logFailure('Timestamp outside acceptable drift', ['timestamp' => $timestamp]);
            return false;
        }

        return true;
    }

    /**
     * Build the message string for signature verification.
     */
    protected function buildVerificationMessage(Request $request): string
    {
        $params = $request->query();

        // Remove signature from params
        unset($params['signature']);

        // Sort parameters alphabetically by key
        ksort($params);

        // Build query string
        $parts = [];
        foreach ($params as $key => $value) {
            $parts[] = $key . '=' . $value;
        }

        return implode('&', $parts);
    }

    /**
     * Get a public key by key ID.
     */
    public function getPublicKey(string $keyId): ?string
    {
        $keys = $this->getPublicKeys();
        return $keys[$keyId] ?? null;
    }

    /**
     * Get all public keys from cache or Google's server.
     */
    public function getPublicKeys(): array
    {
        $cacheKey = $this->cachePrefix . 'public_keys';

        return Cache::remember($cacheKey, $this->cacheTtl, function () {
            return $this->fetchPublicKeys();
        });
    }

    /**
     * Fetch public keys from Google's server.
     */
    protected function fetchPublicKeys(): array
    {
        $url = config('ads-google.ssv.keys_url');

        try {
            $response = Http::timeout(10)->get($url);

            if (!$response->successful()) {
                Log::error('Failed to fetch AdMob public keys', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return [];
            }

            $data = $response->json();

            if (!isset($data['keys']) || !is_array($data['keys'])) {
                Log::error('Invalid AdMob public keys response format');
                return [];
            }

            $keys = [];
            foreach ($data['keys'] as $key) {
                if (isset($key['keyId'], $key['pem'])) {
                    $keys[$key['keyId']] = $key['pem'];
                } elseif (isset($key['keyId'], $key['base64'])) {
                    // Convert base64 to PEM format
                    $keys[$key['keyId']] = $this->base64ToPem($key['base64']);
                }
            }

            Log::info('AdMob public keys fetched', ['count' => count($keys)]);

            return $keys;

        } catch (\Exception $e) {
            Log::error('Exception fetching AdMob public keys', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Convert base64 encoded key to PEM format.
     */
    protected function base64ToPem(string $base64): string
    {
        $der = base64_decode($base64);
        $pem = "-----BEGIN PUBLIC KEY-----\n";
        $pem .= chunk_split(base64_encode($der), 64, "\n");
        $pem .= "-----END PUBLIC KEY-----";
        return $pem;
    }

    /**
     * Verify the ECDSA signature.
     */
    protected function verifySignature(string $message, string $signature, string $publicKey): bool
    {
        try {
            // Decode the base64url signature
            $decodedSignature = $this->base64UrlDecode($signature);

            // Get public key resource
            $keyResource = openssl_pkey_get_public($publicKey);
            if (!$keyResource) {
                Log::error('Failed to parse AdMob public key');
                return false;
            }

            // Verify using ECDSA with SHA256
            $result = openssl_verify(
                $message,
                $decodedSignature,
                $keyResource,
                OPENSSL_ALGO_SHA256
            );

            return $result === 1;

        } catch (\Exception $e) {
            Log::error('Signature verification exception', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Decode base64url encoded string.
     */
    protected function base64UrlDecode(string $input): string
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $input .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($input, '-_', '+/'));
    }

    /**
     * Verify the timestamp is within acceptable drift.
     */
    protected function verifyTimestamp(string $timestamp): bool
    {
        $callbackTime = (int) $timestamp;
        $currentTime = time();
        $drift = config('ads-google.ssv.time_drift', 300);

        return abs($currentTime - $callbackTime) <= $drift;
    }

    /**
     * Parse custom data from callback.
     */
    public function parseCustomData(?string $customData): array
    {
        if (!$customData) {
            return [];
        }

        $format = config('ads-google.custom_data.format', 'plain');

        if ($format === 'json') {
            $decoded = json_decode($customData, true);
            return is_array($decoded) ? $decoded : [];
        }

        // Plain format - custom_data is just the user_id
        return ['user_id' => $customData];
    }

    /**
     * Extract user ID from custom data.
     */
    public function extractUserId(?string $customData): ?int
    {
        $data = $this->parseCustomData($customData);
        $key = config('ads-google.custom_data.user_id_key', 'user_id');

        $userId = $data[$key] ?? $data['user_id'] ?? null;

        return $userId ? (int) $userId : null;
    }

    /**
     * Refresh the public keys cache.
     */
    public function refreshKeys(): array
    {
        $cacheKey = $this->cachePrefix . 'public_keys';
        Cache::forget($cacheKey);
        return $this->getPublicKeys();
    }

    /**
     * Log a failure.
     */
    protected function logFailure(string $message, array $context = []): void
    {
        if (config('ads-google.logging.log_failures', true)) {
            $channel = config('ads-google.logging.channel');
            $logger = $channel ? Log::channel($channel) : Log::getFacadeRoot();
            $logger->warning("AdMob SSV: {$message}", $context);
        }
    }
}
