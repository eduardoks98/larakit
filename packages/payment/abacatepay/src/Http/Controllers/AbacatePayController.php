<?php

namespace Eduardoks98\PaymentAbacatePay\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Eduardoks98\PaymentAbacatePay\Services\AbacatePayService;
use Eduardoks98\PaymentAbacatePay\Models\BillingData;
use Eduardoks98\PaymentAbacatePay\Models\ProductData;
use Eduardoks98\PaymentAbacatePay\Models\CustomerData;
use Eduardoks98\PaymentAbacatePay\Enums\Frequency;
use Eduardoks98\PaymentAbacatePay\Enums\PaymentMethod;
use Illuminate\Support\Facades\Validator;

class AbacatePayController extends Controller
{
    public function __construct(
        protected AbacatePayService $abacatePayService
    ) {
    }

    /**
     * Create a new billing
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createBilling(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'frequency' => 'required|string|in:one_time,monthly,yearly',
            'methods' => 'required|array',
            'methods.*' => 'string|in:pix,card',
            'products' => 'required|array|min:1',
            'products.*.name' => 'required|string',
            'products.*.price' => 'required|integer|min:1',
            'products.*.quantity' => 'nullable|integer|min:1',
            'products.*.description' => 'nullable|string',
            'products.*.externalId' => 'nullable|string',
            'customer.email' => 'required|email',
            'customer.name' => 'nullable|string',
            'customer.cellphone' => 'nullable|string',
            'customer.taxId' => 'nullable|string',
            'metadata' => 'nullable|array',
            'returnUrl' => 'nullable|url',
            'completionUrl' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $data = $validator->validated();

            // Build billing data
            $billingData = new BillingData(
                frequency: Frequency::from($data['frequency']),
                methods: array_map(
                    fn($method) => PaymentMethod::from($method),
                    $data['methods']
                ),
                products: array_map(
                    fn($product) => ProductData::fromArray($product),
                    $data['products']
                ),
                customer: CustomerData::fromArray($data['customer']),
                metadata: $data['metadata'] ?? null,
                returnUrl: $data['returnUrl'] ?? config('abacatepay.return_url'),
                completionUrl: $data['completionUrl'] ?? config('abacatepay.completion_url'),
            );

            // Get user ID if authenticated
            $userId = $request->user()?->id;

            // Create billing
            $response = $this->abacatePayService->createBilling($billingData, $userId);

            return response()->json([
                'success' => true,
                'message' => 'Billing created successfully',
                'data' => $response,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create billing',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get billing by ID
     *
     * @param string $billingId
     * @return JsonResponse
     */
    public function getBilling(string $billingId): JsonResponse
    {
        try {
            $billing = $this->abacatePayService->getBilling($billingId);

            return response()->json([
                'success' => true,
                'data' => $billing,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve billing',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * List billings
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listBillings(Request $request): JsonResponse
    {
        try {
            $params = $request->only(['limit', 'offset']);
            $billings = $this->abacatePayService->listBillings($params);

            return response()->json([
                'success' => true,
                'data' => $billings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to list billings',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a customer
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createCustomer(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'name' => 'nullable|string',
            'cellphone' => 'nullable|string',
            'taxId' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $customer = $this->abacatePayService->createCustomer($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Customer created successfully',
                'data' => $customer,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create customer',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get customer by ID
     *
     * @param string $customerId
     * @return JsonResponse
     */
    public function getCustomer(string $customerId): JsonResponse
    {
        try {
            $customer = $this->abacatePayService->getCustomer($customerId);

            return response()->json([
                'success' => true,
                'data' => $customer,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve customer',
                'error' => $e->getMessage(),
            ], 404);
        }
    }
}
