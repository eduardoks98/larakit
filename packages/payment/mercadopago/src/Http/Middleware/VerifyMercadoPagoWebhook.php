<?php

namespace Eduardoks98\PaymentMercadoPago\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class VerifyMercadoPagoWebhook
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('payment-mercadopago.webhook_secret');

        // If no secret is configured, allow the request (optional security)
        if (!$secret) {
            Log::warning('[MercadoPago Webhook] No webhook secret configured - signature validation skipped');
            return $next($request);
        }

        // MercadoPago webhook signature validation
        // According to MercadoPago documentation, webhooks include:
        // - x-signature header with signature
        // - x-request-id header with unique ID

        $signature = $request->header('x-signature');
        $requestId = $request->header('x-request-id');

        if (!$signature || !$requestId) {
            Log::warning('[MercadoPago Webhook] Missing signature or request ID headers');

            return response()->json([
                'success' => false,
                'error' => 'Missing required webhook headers',
            ], 401);
        }

        // Validate signature
        if (!$this->validateSignature($signature, $requestId, $request->getContent(), $secret)) {
            Log::warning('[MercadoPago Webhook] Invalid webhook signature', [
                'signature' => $signature,
                'request_id' => $requestId,
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Invalid webhook signature',
            ], 401);
        }

        return $next($request);
    }

    /**
     * Validate MercadoPago webhook signature
     *
     * @param string $signature
     * @param string $requestId
     * @param string $payload
     * @param string $secret
     * @return bool
     */
    protected function validateSignature(string $signature, string $requestId, string $payload, string $secret): bool
    {
        // MercadoPago signature format (may vary - check official docs):
        // HMAC-SHA256 of: request_id + payload + secret

        // Parse signature (format: "ts=timestamp,v1=hash")
        $parts = [];
        foreach (explode(',', $signature) as $part) {
            [$key, $value] = explode('=', $part, 2);
            $parts[$key] = $value;
        }

        if (!isset($parts['v1'])) {
            return false;
        }

        $expectedHash = $parts['v1'];

        // Calculate expected signature
        $signatureData = $requestId . $payload;
        $calculatedHash = hash_hmac('sha256', $signatureData, $secret);

        // Compare hashes
        return hash_equals($calculatedHash, $expectedHash);
    }
}
