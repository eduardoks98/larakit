<?php

namespace Eduardoks98\AdsGoogle\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Eduardoks98\AdsGoogle\Services\AdMobSsvService;
use Eduardoks98\Monetization\Services\RewardService;
use Eduardoks98\Monetization\Models\AdImpression;
use Eduardoks98\Monetization\Enums\AdProvider;
use Eduardoks98\Monetization\Enums\RewardType;

class AdMobCallbackController extends Controller
{
    public function __construct(
        protected AdMobSsvService $ssvService,
        protected RewardService $rewardService
    ) {}

    /**
     * Handle AdMob SSV callback.
     *
     * Expected query parameters:
     * - ad_network: The ad network
     * - ad_unit: The ad unit ID
     * - custom_data: User identifier (user_id)
     * - key_id: Key ID for signature verification
     * - reward_amount: Amount of reward
     * - reward_item: Reward item name
     * - signature: ECDSA signature
     * - timestamp: Unix timestamp
     * - transaction_id: Unique transaction ID
     */
    public function handle(Request $request): Response
    {
        $transactionId = $request->query('transaction_id');
        $customData = $request->query('custom_data');
        $rewardAmount = (int) $request->query('reward_amount', config('ads-google.rewards.default_amount', 10));
        $rewardItem = $request->query('reward_item', config('ads-google.rewards.default_item', 'coins'));
        $adUnit = $request->query('ad_unit');
        $adNetwork = $request->query('ad_network');

        // Extract user ID from custom_data
        $userId = $this->ssvService->extractUserId($customData);

        if (!$userId) {
            $this->logCallback('error', 'Missing user_id in custom_data', [
                'custom_data' => $customData,
                'transaction_id' => $transactionId,
            ]);
            return response('', 200); // Return 200 to prevent retries
        }

        if (!$transactionId) {
            $this->logCallback('error', 'Missing transaction_id', [
                'user_id' => $userId,
            ]);
            return response('', 200);
        }

        // Check for duplicate transaction
        $existingReward = $this->rewardService->findByTransaction(AdProvider::ADMOB, $transactionId);
        if ($existingReward) {
            $this->logCallback('info', 'Duplicate transaction detected', [
                'transaction_id' => $transactionId,
                'user_id' => $userId,
            ]);
            return response('', 200);
        }

        // Validate ad unit if configured
        $allowedUnits = config('ads-google.ad_units', []);
        if (!empty($allowedUnits) && $adUnit && !in_array($adUnit, $allowedUnits)) {
            $this->logCallback('warning', 'Invalid ad unit', [
                'ad_unit' => $adUnit,
                'transaction_id' => $transactionId,
            ]);
            return response('', 200);
        }

        try {
            // Track the impression
            if (config('monetization.impressions.enabled', true)) {
                AdImpression::create([
                    'user_id' => $userId,
                    'provider' => AdProvider::ADMOB,
                    'ad_unit_id' => $adUnit,
                    'ad_network' => $adNetwork,
                    'ad_type' => 'rewarded',
                    'transaction_id' => $transactionId,
                    'platform' => $this->detectPlatform($request),
                    'ip_address' => config('monetization.impressions.track_ip') ? $request->ip() : null,
                    'user_agent' => config('monetization.impressions.track_user_agent') ? $request->userAgent() : null,
                    'metadata' => [
                        'reward_item' => $rewardItem,
                        'reward_amount' => $rewardAmount,
                    ],
                ]);
            }

            // Create and process the reward
            if (config('ads-google.rewards.sync_processing', false)) {
                $reward = $this->rewardService->createReward(
                    userId: $userId,
                    provider: AdProvider::ADMOB,
                    transactionId: $transactionId,
                    rewardItem: $rewardItem,
                    rewardAmount: $rewardAmount,
                    adUnitId: $adUnit,
                    rewardType: RewardType::CURRENCY,
                    metadata: [
                        'ad_network' => $adNetwork,
                        'callback_params' => $request->query(),
                    ]
                );
                $this->rewardService->processReward($reward);
            } else {
                $this->rewardService->createAndDispatch(
                    userId: $userId,
                    provider: AdProvider::ADMOB,
                    transactionId: $transactionId,
                    rewardItem: $rewardItem,
                    rewardAmount: $rewardAmount,
                    adUnitId: $adUnit,
                    rewardType: RewardType::CURRENCY,
                    metadata: [
                        'ad_network' => $adNetwork,
                        'callback_params' => $request->query(),
                    ]
                );
            }

            $this->logCallback('info', 'Reward created successfully', [
                'user_id' => $userId,
                'transaction_id' => $transactionId,
                'amount' => $rewardAmount,
                'item' => $rewardItem,
            ]);

        } catch (\Exception $e) {
            $this->logCallback('error', 'Failed to process callback', [
                'error' => $e->getMessage(),
                'transaction_id' => $transactionId,
                'user_id' => $userId,
            ]);
        }

        // Always return 200 OK to prevent Google retries
        return response('', 200);
    }

    /**
     * Detect platform from user agent.
     */
    protected function detectPlatform(Request $request): ?string
    {
        $userAgent = strtolower($request->userAgent() ?? '');

        if (str_contains($userAgent, 'android')) {
            return 'android';
        }

        if (str_contains($userAgent, 'iphone') || str_contains($userAgent, 'ipad')) {
            return 'ios';
        }

        return null;
    }

    /**
     * Log callback event.
     */
    protected function logCallback(string $level, string $message, array $context = []): void
    {
        if (!config('ads-google.logging.enabled', true)) {
            return;
        }

        if ($level === 'info' && !config('ads-google.logging.log_success', true)) {
            return;
        }

        $channel = config('ads-google.logging.channel');
        $logger = $channel ? Log::channel($channel) : Log::getFacadeRoot();

        $logger->$level("AdMob Callback: {$message}", $context);
    }
}
