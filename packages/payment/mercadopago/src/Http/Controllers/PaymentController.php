<?php

namespace Eduardoks98\PaymentMercadoPago\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Eduardoks98\PaymentMercadoPago\Services\MercadoPagoService;
use Eduardoks98\PaymentMercadoPago\Models\MercadoPagoPayment;
use MercadoPago\Exceptions\MPApiException;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    protected MercadoPagoService $mercadoPagoService;

    public function __construct(MercadoPagoService $mercadoPagoService)
    {
        $this->mercadoPagoService = $mercadoPagoService;
    }

    /**
     * Create PIX payment
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createPix(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'payer_email' => 'required|email',
            'payer_name' => 'nullable|string',
            'payer_document' => 'nullable|string',
            'payer_document_type' => 'nullable|string|in:CPF,CNPJ',
            'description' => 'nullable|string',
            'external_reference' => 'nullable|string|unique:mercadopago_payments,external_reference',
            'metadata' => 'nullable|array',
            'expiration_time' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $payment = $this->mercadoPagoService->createPixPayment($request->all());

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $payment->id,
                    'external_reference' => $payment->external_reference,
                    'mercadopago_id' => $payment->mercadopago_id,
                    'status' => $payment->status->value,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'qr_code' => $payment->qr_code,
                    'qr_code_base64' => $payment->qr_code_base64,
                    'qr_code_data_uri' => $payment->getPixQrCodeDataUri(),
                    'ticket_url' => $payment->ticket_url,
                    'expiration_date' => $payment->expiration_date,
                    'created_at' => $payment->created_at,
                ],
            ], 201);

        } catch (MPApiException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to create PIX payment',
                'message' => $e->getMessage(),
            ], $e->getStatusCode() ?? 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create card payment
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createCard(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'token' => 'required|string',
            'payment_method_id' => 'required|string',
            'payer_email' => 'required|email',
            'payer_name' => 'nullable|string',
            'payer_document' => 'nullable|string',
            'payer_document_type' => 'nullable|string|in:CPF,CNPJ',
            'installments' => 'nullable|integer|min:1|max:12',
            'description' => 'nullable|string',
            'external_reference' => 'nullable|string|unique:mercadopago_payments,external_reference',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $payment = $this->mercadoPagoService->createCardPayment($request->all());

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $payment->id,
                    'external_reference' => $payment->external_reference,
                    'mercadopago_id' => $payment->mercadopago_id,
                    'status' => $payment->status->value,
                    'status_detail' => $payment->status_detail,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'created_at' => $payment->created_at,
                ],
            ], 201);

        } catch (MPApiException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to create card payment',
                'message' => $e->getMessage(),
            ], $e->getStatusCode() ?? 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create Boleto payment
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createBoleto(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'payer_email' => 'required|email',
            'payer_name' => 'required|string',
            'payer_document' => 'required|string',
            'payer_document_type' => 'nullable|string|in:CPF,CNPJ',
            'description' => 'nullable|string',
            'external_reference' => 'nullable|string|unique:mercadopago_payments,external_reference',
            'metadata' => 'nullable|array',
            'expiration_days' => 'nullable|integer|min:1|max:30',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $payment = $this->mercadoPagoService->createBoletoPayment($request->all());

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $payment->id,
                    'external_reference' => $payment->external_reference,
                    'mercadopago_id' => $payment->mercadopago_id,
                    'status' => $payment->status->value,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'ticket_url' => $payment->ticket_url,
                    'barcode' => $payment->barcode,
                    'expiration_date' => $payment->expiration_date,
                    'created_at' => $payment->created_at,
                ],
            ], 201);

        } catch (MPApiException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to create Boleto payment',
                'message' => $e->getMessage(),
            ], $e->getStatusCode() ?? 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get payment by ID or external reference
     *
     * @param Request $request
     * @param string $identifier
     * @return JsonResponse
     */
    public function show(Request $request, string $identifier): JsonResponse
    {
        $payment = MercadoPagoPayment::where('id', $identifier)
            ->orWhere('external_reference', $identifier)
            ->orWhere('mercadopago_id', $identifier)
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'error' => 'Payment not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $payment->id,
                'external_reference' => $payment->external_reference,
                'mercadopago_id' => $payment->mercadopago_id,
                'order_id' => $payment->order_id,
                'payment_method' => $payment->payment_method->value,
                'status' => $payment->status->value,
                'status_detail' => $payment->status_detail,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'payer_email' => $payment->payer_email,
                'description' => $payment->description,
                'qr_code' => $payment->qr_code,
                'qr_code_base64' => $payment->qr_code_base64,
                'qr_code_data_uri' => $payment->getPixQrCodeDataUri(),
                'ticket_url' => $payment->ticket_url,
                'barcode' => $payment->barcode,
                'expiration_date' => $payment->expiration_date,
                'metadata' => $payment->metadata,
                'approved_at' => $payment->approved_at,
                'rejected_at' => $payment->rejected_at,
                'created_at' => $payment->created_at,
                'updated_at' => $payment->updated_at,
            ],
        ]);
    }

    /**
     * Refund payment
     *
     * @param Request $request
     * @param string $identifier
     * @return JsonResponse
     */
    public function refund(Request $request, string $identifier): JsonResponse
    {
        $payment = MercadoPagoPayment::where('id', $identifier)
            ->orWhere('external_reference', $identifier)
            ->orWhere('mercadopago_id', $identifier)
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'error' => 'Payment not found',
            ], 404);
        }

        if (!$payment->isApproved()) {
            return response()->json([
                'success' => false,
                'error' => 'Only approved payments can be refunded',
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'nullable|numeric|min:0.01|max:' . $payment->amount,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $refund = $this->mercadoPagoService->refundPayment(
                $payment->mercadopago_id,
                $request->input('amount')
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment refunded successfully',
                'data' => [
                    'refund_id' => $refund->id,
                    'amount' => $refund->amount,
                    'status' => $refund->status,
                ],
            ]);

        } catch (MPApiException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to refund payment',
                'message' => $e->getMessage(),
            ], $e->getStatusCode() ?? 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
