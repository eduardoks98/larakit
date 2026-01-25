<?php

namespace Eduardoks98\AdsUnity\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Eduardoks98\AdsUnity\Services\UnityCallbackService;
use Eduardoks98\Monetization\Services\RewardService;
use Eduardoks98\Monetization\Models\AdImpression;
use Eduardoks98\Monetization\Enums\AdProvider;
use Eduardoks98\Monetization\Enums\RewardType;

class UnityCallbackController extends Controller
{
    public function __construct(
        protected UnityCallbackService $callbackService,
        protected RewardService $rewardService
    ) {}

    /**
     * Handle Unity Ads S2S callback.
     *
     * Expected query parameters:
     * - sid: Server ID (contains user_id)
     * - oid: Order ID (unique transaction ID)
     * - hmac: HMAC-MD5 signature
     *
     * Additional parameters may include:
     * - product: Product/placement ID
     * - zone: Zone ID
     * - gamer_sid: Alternative user identifier
     */
    public function handle(Request $request): Response
    {
        // Verify HMAC signature
        if (!$this->callbackService->verifyCallback($request)) {
            return response('0', 400);
        }

        $sid = $request->query('sid');
        $oid = $request->query('oid');
        $product = $request->query('product');
        $zone = $request->query('zone');
        $gamerSid = $request->query('gamer_sid');

        // Extract user ID from sid
        $userId = $this->callbackService->extractUserId($sid);

        // Fallback to gamer_sid if sid doesn't contain user_id
        if (!$userId && $gamerSid) {
            $userId = (int) $gamerSid;
        }

        if (!$userId) {
            $this->logCallback('error', 'Missing user_id', [
                'sid' => $sid,
                'gamer_sid' => $gamerSid,
                'oid' => $oid,
            ]);
            return response('1', 200); // Return success to prevent retries
        }

        if (!$oid) {
            $this->logCallback('error', 'Missing oid (order_id)', [
                'user_id' => $userId,
            ]);
            return response('1', 200);
        }

        // Check for duplicate transaction
        $existingReward = $this->rewardService->findByTransaction(AdProvider::UNITY, $oid);
        if ($existingReward) {
            $this->logCallback('info', 'Duplicate transaction detected', [
                'oid' => $oid,
                'user_id' => $userId,
            ]);
            return response('1', 200);
        }

        // Get reward configuration
        $rewardItem = config('ads-unity.rewards.default_item', 'coins');
        $rewardAmount = config('ads-unity.rewards.default_amount', 10);

        try {
            // Track the impression
            if (config('monetization.impressions.enabled', true)) {
                AdImpression::create([
                    'user_id' => $userId,
                    'provider' => AdProvider::UNITY,
                    'ad_unit_id' => $zone,
                    'ad_type' => 'rewarded',
                    'placement' => $product,
                    'transaction_id' => $oid,
                    'ip_address' => config('monetization.impressions.track_ip') ? $request->ip() : null,
                    'user_agent' => config('monetization.impressions.track_user_agent') ? $request->userAgent() : null,
                    'metadata' => [
                        'sid' => $sid,
                        'gamer_sid' => $gamerSid,
                        'product' => $product,
                        'zone' => $zone,
                    ],
                ]);
            }

            // Create and process the reward
            if (config('ads-unity.rewards.sync_processing', false)) {
                $reward = $this->rewardService->createReward(
                    userId: $userId,
                    provider: AdProvider::UNITY,
                    transactionId: $oid,
                    rewardItem: $rewardItem,
                    rewardAmount: $rewardAmount,
                    adUnitId: $zone,
                    rewardType: RewardType::CURRENCY,
                    metadata: [
                        'sid' => $sid,
                        'product' => $product,
                        'callback_params' => $request->query(),
                    ]
                );
                $this->rewardService->processReward($reward);
            } else {
                $this->rewardService->createAndDispatch(
                    userId: $userId,
                    provider: AdProvider::UNITY,
                    transactionId: $oid,
                    rewardItem: $rewardItem,
                    rewardAmount: $rewardAmount,
                    adUnitId: $zone,
                    rewardType: RewardType::CURRENCY,
                    metadata: [
                        'sid' => $sid,
                        'product' => $product,
                        'callback_params' => $request->query(),
                    ]
                );
            }

            $this->logCallback('info', 'Reward created successfully', [
                'user_id' => $userId,
                'oid' => $oid,
                'amount' => $rewardAmount,
            ]);

        } catch (\Exception $e) {
            $this->logCallback('error', 'Failed to process callback', [
                'error' => $e->getMessage(),
                'oid' => $oid,
                'user_id' => $userId,
            ]);
        }

        // Unity expects "1" in the response body for success
        return response('1', 200);
    }

    /**
     * Log callback event.
     */
    protected function logCallback(string $level, string $message, array $context = []): void
    {
        if (!config('ads-unity.logging.enabled', true)) {
            return;
        }

        if ($level === 'info' && !config('ads-unity.logging.log_success', true)) {
            return;
        }

        $channel = config('ads-unity.logging.channel');
        $logger = $channel ? Log::channel($channel) : Log::getFacadeRoot();

        $logger->$level("Unity Ads Callback: {$message}", $context);
    }
}
