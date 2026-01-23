<?php

namespace Eduardoks98\PaymentStripe\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Eduardoks98\PaymentStripe\Services\StripeService;
use Eduardoks98\PaymentStripe\Services\CustomerService;
use Eduardoks98\PaymentStripe\Services\SubscriptionService;

/**
 * WebhookController
 *
 * Handles Stripe webhook events
 * Based on official Stripe webhook events documentation:
 * https://stripe.com/docs/api/events/types
 *
 * IMPORTANT: Webhooks must be verified using VerifyStripeWebhook middleware
 */
class WebhookController extends Controller
{
    public function __construct(
        protected StripeService $stripeService,
        protected CustomerService $customerService,
        protected SubscriptionService $subscriptionService
    ) {}

    /**
     * Handle Stripe webhook
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handle(Request $request): JsonResponse
    {
        // Get the verified event from middleware
        $event = $request->attributes->get('stripe_event');

        if (!$event) {
            return response()->json(['error' => 'No event found'], 400);
        }

        $this->log('Webhook received', [
            'type' => $event->type,
            'id' => $event->id
        ]);

        try {
            // Handle the event based on type
            $handled = match ($event->type) {
                // Payment Intent events
                'payment_intent.succeeded' => $this->handlePaymentIntentSucceeded($event),
                'payment_intent.payment_failed' => $this->handlePaymentIntentFailed($event),
                'payment_intent.canceled' => $this->handlePaymentIntentCanceled($event),
                'payment_intent.created' => $this->handlePaymentIntentCreated($event),
                'payment_intent.processing' => $this->handlePaymentIntentProcessing($event),

                // Subscription events
                'customer.subscription.created' => $this->handleSubscriptionCreated($event),
                'customer.subscription.updated' => $this->handleSubscriptionUpdated($event),
                'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event),
                'customer.subscription.trial_will_end' => $this->handleSubscriptionTrialWillEnd($event),

                // Invoice events
                'invoice.created' => $this->handleInvoiceCreated($event),
                'invoice.finalized' => $this->handleInvoiceFinalized($event),
                'invoice.paid' => $this->handleInvoicePaid($event),
                'invoice.payment_failed' => $this->handleInvoicePaymentFailed($event),

                // Customer events
                'customer.created' => $this->handleCustomerCreated($event),
                'customer.updated' => $this->handleCustomerUpdated($event),
                'customer.deleted' => $this->handleCustomerDeleted($event),

                // Payment Method events
                'payment_method.attached' => $this->handlePaymentMethodAttached($event),
                'payment_method.detached' => $this->handlePaymentMethodDetached($event),

                // Charge events
                'charge.succeeded' => $this->handleChargeSucceeded($event),
                'charge.failed' => $this->handleChargeFailed($event),
                'charge.refunded' => $this->handleChargeRefunded($event),

                // Unknown event type
                default => $this->handleUnknownEvent($event),
            };

            if ($handled) {
                return response()->json(['status' => 'success']);
            }

            return response()->json(['status' => 'ignored']);

        } catch (\Exception $e) {
            $this->log('Webhook handling failed', [
                'type' => $event->type,
                'error' => $e->getMessage()
            ], 'error');

            return response()->json(['error' => 'Webhook handling failed'], 500);
        }
    }

    // ========== Payment Intent Event Handlers ==========

    protected function handlePaymentIntentSucceeded($event): bool
    {
        $paymentIntent = $event->data->object;
        $this->stripeService->syncPaymentIntent($paymentIntent);

        $this->log('PaymentIntent succeeded', ['id' => $paymentIntent->id]);

        // Fire custom event or notification here
        // event(new PaymentSucceeded($payment));

        return true;
    }

    protected function handlePaymentIntentFailed($event): bool
    {
        $paymentIntent = $event->data->object;
        $this->stripeService->syncPaymentIntent($paymentIntent);

        $this->log('PaymentIntent failed', [
            'id' => $paymentIntent->id,
            'error' => $paymentIntent->last_payment_error?->message
        ], 'warning');

        // Fire custom event or notification here
        // event(new PaymentFailed($payment));

        return true;
    }

    protected function handlePaymentIntentCanceled($event): bool
    {
        $paymentIntent = $event->data->object;
        $this->stripeService->syncPaymentIntent($paymentIntent);

        $this->log('PaymentIntent canceled', ['id' => $paymentIntent->id]);

        return true;
    }

    protected function handlePaymentIntentCreated($event): bool
    {
        $paymentIntent = $event->data->object;
        $this->stripeService->syncPaymentIntent($paymentIntent);

        $this->log('PaymentIntent created', ['id' => $paymentIntent->id]);

        return true;
    }

    protected function handlePaymentIntentProcessing($event): bool
    {
        $paymentIntent = $event->data->object;
        $this->stripeService->syncPaymentIntent($paymentIntent);

        $this->log('PaymentIntent processing', ['id' => $paymentIntent->id]);

        return true;
    }

    // ========== Subscription Event Handlers ==========

    protected function handleSubscriptionCreated($event): bool
    {
        $subscription = $event->data->object;
        $this->subscriptionService->syncSubscription($subscription);

        $this->log('Subscription created', ['id' => $subscription->id]);

        // Fire custom event or notification here
        // event(new SubscriptionCreated($stripeSubscription));

        return true;
    }

    protected function handleSubscriptionUpdated($event): bool
    {
        $subscription = $event->data->object;
        $this->subscriptionService->syncSubscription($subscription);

        $this->log('Subscription updated', ['id' => $subscription->id]);

        // Fire custom event or notification here
        // event(new SubscriptionUpdated($stripeSubscription));

        return true;
    }

    protected function handleSubscriptionDeleted($event): bool
    {
        $subscription = $event->data->object;
        $this->subscriptionService->syncSubscription($subscription);

        $this->log('Subscription deleted', ['id' => $subscription->id]);

        // Fire custom event or notification here
        // event(new SubscriptionCanceled($stripeSubscription));

        return true;
    }

    protected function handleSubscriptionTrialWillEnd($event): bool
    {
        $subscription = $event->data->object;

        $this->log('Subscription trial will end', [
            'id' => $subscription->id,
            'trial_end' => $subscription->trial_end
        ]);

        // Send notification to user about trial ending
        // event(new SubscriptionTrialEnding($stripeSubscription));

        return true;
    }

    // ========== Invoice Event Handlers ==========

    protected function handleInvoiceCreated($event): bool
    {
        $invoice = $event->data->object;

        $this->log('Invoice created', ['id' => $invoice->id]);

        return true;
    }

    protected function handleInvoiceFinalized($event): bool
    {
        $invoice = $event->data->object;

        $this->log('Invoice finalized', ['id' => $invoice->id]);

        return true;
    }

    protected function handleInvoicePaid($event): bool
    {
        $invoice = $event->data->object;

        $this->log('Invoice paid', ['id' => $invoice->id]);

        // Fire custom event or notification here
        // event(new InvoicePaid($invoice));

        return true;
    }

    protected function handleInvoicePaymentFailed($event): bool
    {
        $invoice = $event->data->object;

        $this->log('Invoice payment failed', ['id' => $invoice->id], 'warning');

        // Send notification to user about failed payment
        // event(new InvoicePaymentFailed($invoice));

        return true;
    }

    // ========== Customer Event Handlers ==========

    protected function handleCustomerCreated($event): bool
    {
        $customer = $event->data->object;
        $this->customerService->syncCustomer($customer);

        $this->log('Customer created', ['id' => $customer->id]);

        return true;
    }

    protected function handleCustomerUpdated($event): bool
    {
        $customer = $event->data->object;
        $this->customerService->syncCustomer($customer);

        $this->log('Customer updated', ['id' => $customer->id]);

        return true;
    }

    protected function handleCustomerDeleted($event): bool
    {
        $customer = $event->data->object;

        $this->log('Customer deleted', ['id' => $customer->id]);

        // Remove from database
        $this->customerService->getCustomerFromDatabase($customer->id)?->delete();

        return true;
    }

    // ========== Payment Method Event Handlers ==========

    protected function handlePaymentMethodAttached($event): bool
    {
        $paymentMethod = $event->data->object;

        $this->log('PaymentMethod attached', [
            'id' => $paymentMethod->id,
            'customer' => $paymentMethod->customer
        ]);

        return true;
    }

    protected function handlePaymentMethodDetached($event): bool
    {
        $paymentMethod = $event->data->object;

        $this->log('PaymentMethod detached', ['id' => $paymentMethod->id]);

        return true;
    }

    // ========== Charge Event Handlers ==========

    protected function handleChargeSucceeded($event): bool
    {
        $charge = $event->data->object;

        $this->log('Charge succeeded', ['id' => $charge->id]);

        return true;
    }

    protected function handleChargeFailed($event): bool
    {
        $charge = $event->data->object;

        $this->log('Charge failed', ['id' => $charge->id], 'warning');

        return true;
    }

    protected function handleChargeRefunded($event): bool
    {
        $charge = $event->data->object;

        $this->log('Charge refunded', ['id' => $charge->id]);

        // Fire custom event or notification here
        // event(new ChargeRefunded($charge));

        return true;
    }

    // ========== Unknown Event Handler ==========

    protected function handleUnknownEvent($event): bool
    {
        $this->log('Unknown event type', ['type' => $event->type], 'warning');

        return false;
    }

    // ========== Logging ==========

    protected function log(string $message, array $context = [], string $level = 'info'): void
    {
        if (config('stripe.logging.enabled')) {
            Log::channel(config('stripe.logging.channel', 'stack'))
                ->{$level}('[Stripe Webhook] ' . $message, $context);
        }
    }
}
