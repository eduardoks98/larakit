<?php

namespace Eduardoks98\PaymentAbacatePay\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Eduardoks98\PaymentAbacatePay\Services\AbacatePayService;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        protected AbacatePayService $abacatePayService
    ) {
    }

    /**
     * Handle AbacatePay webhook
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handle(Request $request): JsonResponse
    {
        try {
            // Log webhook payload
            Log::info('AbacatePay webhook received', [
                'payload' => $request->all(),
            ]);

            $payload = $request->all();

            // Extract billing ID and status
            $billingId = $payload['id'] ?? $payload['billing_id'] ?? null;
            $status = $payload['status'] ?? null;

            if (!$billingId || !$status) {
                Log::warning('AbacatePay webhook missing required fields', [
                    'payload' => $payload,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Missing required fields',
                ], 400);
            }

            // Update billing status
            $billing = $this->abacatePayService->updateBillingStatus(
                $billingId,
                $status,
                $payload
            );

            if (!$billing) {
                Log::warning('AbacatePay webhook billing not found in database', [
                    'billing_id' => $billingId,
                ]);
            }

            // Dispatch events based on status
            $this->dispatchWebhookEvents($billingId, $status, $payload, $billing);

            return response()->json([
                'success' => true,
                'message' => 'Webhook processed successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('AbacatePay webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Dispatch webhook events
     */
    protected function dispatchWebhookEvents(string $billingId, string $status, array $payload, $billing): void
    {
        // You can dispatch Laravel events here for different billing statuses
        // Example:
        // match($status) {
        //     'paid' => event(new BillingPaid($billing, $payload)),
        //     'cancelled' => event(new BillingCancelled($billing, $payload)),
        //     'expired' => event(new BillingExpired($billing, $payload)),
        //     'refunded' => event(new BillingRefunded($billing, $payload)),
        //     default => null,
        // };

        Log::info('AbacatePay webhook event', [
            'billing_id' => $billingId,
            'status' => $status,
            'has_local_billing' => $billing !== null,
        ]);
    }
}
