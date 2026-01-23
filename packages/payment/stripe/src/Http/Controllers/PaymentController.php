<?php

namespace Eduardoks98\PaymentStripe\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Eduardoks98\PaymentStripe\Services\StripeService;
use Eduardoks98\PaymentStripe\Services\CustomerService;
use Eduardoks98\PaymentStripe\Services\SubscriptionService;
use Stripe\Exception\ApiErrorException;

/**
 * PaymentController
 *
 * Handles payment-related API endpoints
 * Uses official Stripe Payment Intents API
 */
class PaymentController extends Controller
{
    public function __construct(
        protected StripeService $stripeService,
        protected CustomerService $customerService,
        protected SubscriptionService $subscriptionService
    ) {}

    /**
     * Create a Payment Intent
     *
     * POST /api/stripe/payment-intents
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createPaymentIntent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|integer|min:50', // Minimum $0.50
            'currency' => 'nullable|string|size:3',
            'customer_id' => 'nullable|string',
            'payment_method' => 'nullable|string',
            'description' => 'nullable|string|max:255',
            'metadata' => 'nullable|array',
        ]);

        try {
            $options = [];

            if (isset($validated['customer_id'])) {
                $options['customer'] = $validated['customer_id'];
            }

            if (isset($validated['payment_method'])) {
                $options['payment_method'] = $validated['payment_method'];
            }

            if (isset($validated['description'])) {
                $options['description'] = $validated['description'];
            }

            if (isset($validated['metadata'])) {
                $options['metadata'] = $validated['metadata'];
            }

            $payment = $this->stripeService->createPaymentIntent(
                $validated['amount'],
                $validated['currency'] ?? null,
                $options
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $payment->stripe_payment_intent_id,
                    'client_secret' => $payment->client_secret,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'status' => $payment->status->value,
                ],
            ], 201);

        } catch (ApiErrorException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Confirm a Payment Intent
     *
     * POST /api/stripe/payment-intents/{id}/confirm
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function confirmPaymentIntent(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'payment_method' => 'nullable|string',
            'return_url' => 'nullable|url',
        ]);

        try {
            $payment = $this->stripeService->confirmPaymentIntent($id, $validated);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $payment->stripe_payment_intent_id,
                    'status' => $payment->status->value,
                ],
            ]);

        } catch (ApiErrorException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Cancel a Payment Intent
     *
     * POST /api/stripe/payment-intents/{id}/cancel
     *
     * @param string $id
     * @return JsonResponse
     */
    public function cancelPaymentIntent(string $id): JsonResponse
    {
        try {
            $payment = $this->stripeService->cancelPaymentIntent($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $payment->stripe_payment_intent_id,
                    'status' => $payment->status->value,
                ],
            ]);

        } catch (ApiErrorException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Capture a Payment Intent (manual capture)
     *
     * POST /api/stripe/payment-intents/{id}/capture
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function capturePaymentIntent(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'amount_to_capture' => 'nullable|integer|min:1',
        ]);

        try {
            $payment = $this->stripeService->capturePaymentIntent(
                $id,
                $validated['amount_to_capture'] ?? null
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $payment->stripe_payment_intent_id,
                    'status' => $payment->status->value,
                    'amount' => $payment->amount,
                ],
            ]);

        } catch (ApiErrorException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get Payment Intent
     *
     * GET /api/stripe/payment-intents/{id}
     *
     * @param string $id
     * @return JsonResponse
     */
    public function getPaymentIntent(string $id): JsonResponse
    {
        try {
            $payment = $this->stripeService->getPaymentFromDatabase($id);

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'error' => 'Payment not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $payment->stripe_payment_intent_id,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'status' => $payment->status->value,
                    'description' => $payment->description,
                    'metadata' => $payment->metadata,
                    'created_at' => $payment->created_at,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Create a Customer
     *
     * POST /api/stripe/customers
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createCustomer(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'metadata' => 'nullable|array',
        ]);

        try {
            $customer = $this->customerService->createCustomer($validated);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $customer->stripe_customer_id,
                    'email' => $customer->email,
                    'name' => $customer->name,
                ],
            ], 201);

        } catch (ApiErrorException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Create a Subscription
     *
     * POST /api/stripe/subscriptions
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createSubscription(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|string',
            'price_id' => 'required|string',
            'quantity' => 'nullable|integer|min:1',
            'trial_period_days' => 'nullable|integer|min:1',
            'metadata' => 'nullable|array',
        ]);

        try {
            $items = [
                [
                    'price' => $validated['price_id'],
                    'quantity' => $validated['quantity'] ?? 1,
                ],
            ];

            $options = [];

            if (isset($validated['trial_period_days'])) {
                $options['trial_period_days'] = $validated['trial_period_days'];
            }

            if (isset($validated['metadata'])) {
                $options['metadata'] = $validated['metadata'];
            }

            $subscription = $this->subscriptionService->createSubscription(
                $validated['customer_id'],
                $items,
                $options
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $subscription->stripe_subscription_id,
                    'status' => $subscription->status->value,
                    'current_period_start' => $subscription->current_period_start,
                    'current_period_end' => $subscription->current_period_end,
                ],
            ], 201);

        } catch (ApiErrorException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Cancel a Subscription
     *
     * POST /api/stripe/subscriptions/{id}/cancel
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function cancelSubscription(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'cancel_at_period_end' => 'nullable|boolean',
        ]);

        try {
            $subscription = $this->subscriptionService->cancelSubscription(
                $id,
                $validated['cancel_at_period_end'] ?? false
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $subscription->stripe_subscription_id,
                    'status' => $subscription->status->value,
                    'canceled_at' => $subscription->canceled_at,
                ],
            ]);

        } catch (ApiErrorException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
