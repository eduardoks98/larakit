<?php

namespace Eduardoks98\PaymentMercadoPago\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Eduardoks98\PaymentMercadoPago\Services\WebhookService;

class WebhookController extends Controller
{
    protected WebhookService $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    /**
     * Handle webhook notification from MercadoPago
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handle(Request $request): JsonResponse
    {
        // Validate signature if secret is configured
        $isValid = $this->webhookService->validateSignature(
            $request->headers->all(),
            $request->getContent()
        );

        if (!$isValid) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid webhook signature',
            ], 401);
        }

        // Process webhook
        try {
            $webhook = $this->webhookService->processWebhook($request->all());

            return response()->json([
                'success' => true,
                'webhook_id' => $webhook->id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to process webhook',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
