<?php

namespace Eduardoks98\AdsApplovin\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Eduardoks98\AdsApplovin\Services\MaxCallbackService;
use Eduardoks98\Monetization\Services\RewardService;
use Eduardoks98\Monetization\Models\AdImpression;
use Eduardoks98\Monetization\Enums\AdProvider;
use Eduardoks98\Monetization\Enums\RewardType;

class MaxCallbackController extends Controller
{
    public function __construct(
        protected MaxCallbackService $callbackService,
        protected RewardService $rewardService
    ) {}

    /**
     * Handle AppLovin MAX S2S callback.
     *
     * Common callback parameters:
     * - event_id: Unique event/transaction ID
     * - event_token: Token for verification
     * - user_id: User identifier
     * - amount: Reward amount
     * - currency: Reward currency/type
     * - ad_unit_id: Ad unit ID
     * - placement: Placement name
     * - network: Ad network that served the ad
     * - country: User's country
     * - idfa/gaid: Device advertising IDs
     * - custom_data: Custom data passed from the app
     */
    public function handle(Request $request): Response
    {
        // Verify the callback
        if (!$this->callbackService->verifyCallback($request)) {
            return response('Invalid callback', 400);
        }

        $eventId = $this->callbackService->extractEventId($request);
        $userId = $this->callbackService->extractUserId($request);
        $rewardAmount = $this->callbackService->extractRewardAmount($request);
        $rewardItem = $this->callbackService->extractCurrencyType($request);
        $adUnitId = $request->query('ad_unit_id');
        $placement = $request->query('placement');
        $network = $request->query('network');
        $country = $request->query('country');

        if (!$userId) {
            $this->logCallback('error', 'Missing user_id', [
                'event_id' => $eventId,
                'custom_data' => $request->query('custom_data'),
            ]);
            return response('OK', 200); // Return OK to prevent retries
        }

        if (!$eventId) {
            $this->logCallback('error', 'Missing event_id', [
                'user_id' => $userId,
            ]);
            return response('OK', 200);
        }

        // Check for duplicate transaction
        $existingReward = $this->rewardService->findByTransaction(AdProvider::APPLOVIN, $eventId);
        if ($existingReward) {
            $this->logCallback('info', 'Duplicate transaction detected', [
                'event_id' => $eventId,
                'user_id' => $userId,
            ]);
            return response('OK', 200);
        }

        try {
            // Track the impression
            if (config('monetization.impressions.enabled', true)) {
                AdImpression::create([
                    'user_id' => $userId,
                    'provider' => AdProvider::APPLOVIN,
                    'ad_unit_id' => $adUnitId,
                    'ad_network' => $network,
                    'ad_type' => 'rewarded',
                    'placement' => $placement,
                    'transaction_id' => $eventId,
                    'country' => $country,
                    'device_id' => $request->query('gaid') ?? $request->query('idfa'),
                    'ip_address' => config('monetization.impressions.track_ip') ? $request->ip() : null,
                    'user_agent' => config('monetization.impressions.track_user_agent') ? $request->userAgent() : null,
                    'metadata' => [
                        'network' => $network,
                        'placement' => $placement,
                        'idfa' => $request->query('idfa'),
                        'gaid' => $request->query('gaid'),
                        'idfv' => $request->query('idfv'),
                        'android_id' => $request->query('android_id'),
                    ],
                ]);
            }

            // Create and process the reward
            if (config('ads-applovin.rewards.sync_processing', false)) {
                $reward = $this->rewardService->createReward(
                    userId: $userId,
                    provider: AdProvider::APPLOVIN,
                    transactionId: $eventId,
                    rewardItem: $rewardItem,
                    rewardAmount: $rewardAmount,
                    adUnitId: $adUnitId,
                    rewardType: RewardType::CURRENCY,
                    metadata: [
                        'network' => $network,
                        'placement' => $placement,
                        'country' => $country,
                        'callback_params' => $request->query(),
                    ]
                );
                $this->rewardService->processReward($reward);
            } else {
                $this->rewardService->createAndDispatch(
                    userId: $userId,
                    provider: AdProvider::APPLOVIN,
                    transactionId: $eventId,
                    rewardItem: $rewardItem,
                    rewardAmount: $rewardAmount,
                    adUnitId: $adUnitId,
                    rewardType: RewardType::CURRENCY,
                    metadata: [
                        'network' => $network,
                        'placement' => $placement,
                        'country' => $country,
                        'callback_params' => $request->query(),
                    ]
                );
            }

            $this->logCallback('info', 'Reward created successfully', [
                'user_id' => $userId,
                'event_id' => $eventId,
                'amount' => $rewardAmount,
                'network' => $network,
            ]);

        } catch (\Exception $e) {
            $this->logCallback('error', 'Failed to process callback', [
                'error' => $e->getMessage(),
                'event_id' => $eventId,
                'user_id' => $userId,
            ]);
        }

        return response('OK', 200);
    }

    /**
     * Log callback event.
     */
    protected function logCallback(string $level, string $message, array $context = []): void
    {
        if (!config('ads-applovin.logging.enabled', true)) {
            return;
        }

        if ($level === 'info' && !config('ads-applovin.logging.log_success', true)) {
            return;
        }

        $channel = config('ads-applovin.logging.channel');
        $logger = $channel ? Log::channel($channel) : Log::getFacadeRoot();

        $logger->$level("AppLovin MAX Callback: {$message}", $context);
    }
}
