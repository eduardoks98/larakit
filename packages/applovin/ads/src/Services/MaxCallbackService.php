<?php

namespace Eduardoks98\AdsApplovin\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MaxCallbackService
{
    protected ?string $eventKey;

    public function __construct()
    {
        $this->eventKey = config('ads-applovin.s2s.event_key');
    }

    /**
     * Verify an AppLovin MAX S2S callback.
     *
     * AppLovin uses the event_token parameter for verification.
     * The token should match the event_key configured in the dashboard.
     */
    public function verifyCallback(Request $request): bool
    {
        if (!config('ads-applovin.s2s.enabled', true)) {
            return true;
        }

        if (!$this->eventKey) {
            $this->logFailure('Event key not configured');
            return false;
        }

        $eventToken = $request->query('event_token');

        if (!$eventToken) {
            $this->logFailure('Missing event_token parameter', $request->all());
            return false;
        }

        // Verify the event token matches our event key
        if (!hash_equals($this->eventKey, $eventToken)) {
            $this->logFailure('Event token mismatch', [
                'received' => substr($eventToken, 0, 8) . '...',
            ]);
            return false;
        }

        return true;
    }

    /**
     * Parse callback parameters into a structured format.
     */
    public function parseCallback(Request $request): array
    {
        return [
            'event_id' => $request->query('event_id'),
            'event_token' => $request->query('event_token'),
            'user_id' => $request->query('user_id'),
            'amount' => $request->query('amount'),
            'currency' => $request->query('currency'),
            'ad_unit_id' => $request->query('ad_unit_id'),
            'placement' => $request->query('placement'),
            'network' => $request->query('network'),
            'country' => $request->query('country'),
            'idfa' => $request->query('idfa'),
            'gaid' => $request->query('gaid'),
            'idfv' => $request->query('idfv'),
            'android_id' => $request->query('android_id'),
            'custom_data' => $request->query('custom_data'),
        ];
    }

    /**
     * Extract user ID from callback.
     *
     * Tries multiple sources: user_id, custom_data
     */
    public function extractUserId(Request $request): ?int
    {
        // First, try the user_id parameter
        $userId = $request->query('user_id');
        if ($userId && is_numeric($userId)) {
            return (int) $userId;
        }

        // Try custom_data (might be JSON or plain user_id)
        $customData = $request->query('custom_data');
        if ($customData) {
            // Try to decode as JSON
            $decoded = json_decode($customData, true);
            if (is_array($decoded) && isset($decoded['user_id'])) {
                return (int) $decoded['user_id'];
            }

            // Try as plain number
            if (is_numeric($customData)) {
                return (int) $customData;
            }
        }

        return null;
    }

    /**
     * Extract transaction/event ID.
     */
    public function extractEventId(Request $request): ?string
    {
        return $request->query('event_id');
    }

    /**
     * Extract reward amount from callback.
     */
    public function extractRewardAmount(Request $request): int
    {
        $amount = $request->query('amount');

        if ($amount && is_numeric($amount)) {
            return (int) $amount;
        }

        return config('ads-applovin.rewards.default_amount', 10);
    }

    /**
     * Extract currency type from callback.
     */
    public function extractCurrencyType(Request $request): string
    {
        $currency = $request->query('currency');

        return $currency ?: config('ads-applovin.rewards.default_item', 'coins');
    }

    /**
     * Log a failure.
     */
    protected function logFailure(string $message, array $context = []): void
    {
        if (config('ads-applovin.logging.log_failures', true)) {
            $channel = config('ads-applovin.logging.channel');
            $logger = $channel ? Log::channel($channel) : Log::getFacadeRoot();
            $logger->warning("AppLovin MAX S2S: {$message}", $context);
        }
    }
}
