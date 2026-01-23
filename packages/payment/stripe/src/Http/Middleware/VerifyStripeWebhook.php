<?php

namespace Eduardoks98\PaymentStripe\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * VerifyStripeWebhook Middleware
 *
 * Verifies Stripe webhook signatures to ensure authenticity
 * Based on official Stripe documentation:
 * https://stripe.com/docs/webhooks/signatures
 */
class VerifyStripeWebhook
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        $webhookSecret = config('stripe.webhook_secret');

        if (empty($webhookSecret)) {
            abort(500, 'Stripe webhook secret not configured');
        }

        try {
            // Get the webhook signature from the request headers
            $signature = $request->header('Stripe-Signature');

            if (empty($signature)) {
                abort(400, 'Missing Stripe-Signature header');
            }

            // Verify the webhook signature
            // This will throw an exception if the signature is invalid
            $event = Webhook::constructEvent(
                $request->getContent(),
                $signature,
                $webhookSecret,
                config('stripe.webhook_tolerance', 300)
            );

            // Add the verified event to the request
            $request->attributes->add(['stripe_event' => $event]);

            return $next($request);

        } catch (SignatureVerificationException $e) {
            // Invalid signature
            abort(400, 'Invalid webhook signature: ' . $e->getMessage());
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            abort(400, 'Invalid webhook payload: ' . $e->getMessage());
        } catch (\Exception $e) {
            // Other errors
            abort(400, 'Webhook verification failed: ' . $e->getMessage());
        }
    }
}
