<?php

namespace Eduardoks98\PaymentAbacatePay\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyAbacatePayWebhook
{
    /**
     * Handle an incoming request.
     *
     * Verify AbacatePay webhook signature
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $secret = config('abacatepay.webhook_secret');

        // If no secret is configured, allow webhook (development mode)
        if (!$secret) {
            Log::warning('AbacatePay webhook secret not configured - skipping verification');
            return $next($request);
        }

        // Get signature from header
        $signature = $request->header('X-AbacatePay-Signature')
                  ?? $request->header('X-Webhook-Signature')
                  ?? $request->header('Signature');

        if (!$signature) {
            Log::warning('AbacatePay webhook missing signature header', [
                'headers' => $request->headers->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Missing webhook signature',
            ], 401);
        }

        // Verify signature
        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        if (!hash_equals($expectedSignature, $signature)) {
            Log::warning('AbacatePay webhook signature verification failed', [
                'expected' => $expectedSignature,
                'received' => $signature,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Invalid webhook signature',
            ], 401);
        }

        return $next($request);
    }
}
