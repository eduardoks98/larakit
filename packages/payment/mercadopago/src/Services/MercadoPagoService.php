<?php

namespace Eduardoks98\PaymentMercadoPago\Services;

use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Order\OrderClient;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;
use Eduardoks98\PaymentMercadoPago\Models\MercadoPagoPayment;
use Eduardoks98\PaymentMercadoPago\Enums\PaymentStatus;
use Eduardoks98\PaymentMercadoPago\Enums\PaymentMethod;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MercadoPagoService
{
    protected ?OrderClient $orderClient = null;
    protected ?PaymentClient $paymentClient = null;
    protected bool $loggingEnabled;
    protected bool $configured = false;

    public function __construct()
    {
        $accessToken = config('payment-mercadopago.access_token');

        // Only initialize if access token is configured
        if (!empty($accessToken)) {
            // Configure MercadoPago SDK
            MercadoPagoConfig::setAccessToken($accessToken);

            // Initialize clients
            $this->orderClient = new OrderClient();
            $this->paymentClient = new PaymentClient();
            $this->configured = true;
        }

        $this->loggingEnabled = config('payment-mercadopago.logging.enabled', false);
    }

    /**
     * Check if MercadoPago is configured
     */
    public function isConfigured(): bool
    {
        return $this->configured;
    }

    /**
     * Ensure MercadoPago is configured before making API calls
     */
    protected function ensureConfigured(): void
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('MercadoPago is not configured. Set MERCADOPAGO_ACCESS_TOKEN in .env');
        }
    }

    /**
     * Create PIX payment using Orders API
     *
     * @param array $data Payment data
     * @return MercadoPagoPayment
     * @throws MPApiException
     */
    public function createPixPayment(array $data): MercadoPagoPayment
    {
        $this->ensureConfigured();
        $externalReference = $data['external_reference'] ?? Str::uuid()->toString();
        $amount = (string) $data['amount'];

        $request = [
            'type' => 'online',
            'processing_mode' => config('payment-mercadopago.processing_mode', 'automatic'),
            'total_amount' => $amount,
            'external_reference' => $externalReference,
            'payer' => [
                'email' => $data['payer_email'],
            ],
            'transactions' => [
                'payments' => [
                    [
                        'amount' => $amount,
                        'payment_method' => [
                            'id' => 'pix',
                            'type' => 'bank_transfer',
                        ],
                        'description' => $data['description'] ?? 'PIX Payment',
                    ]
                ]
            ],
        ];

        // Add expiration time if specified
        if (isset($data['expiration_time'])) {
            $request['transactions']['payments'][0]['expiration_time'] = $data['expiration_time'];
        } else {
            $request['transactions']['payments'][0]['expiration_time'] = config('payment-mercadopago.pix.expiration_time', 'PT24H');
        }

        // Add optional payer information
        if (isset($data['payer_name'])) {
            $request['payer']['first_name'] = $data['payer_name'];
        }
        if (isset($data['payer_document'])) {
            $request['payer']['identification'] = [
                'type' => $data['payer_document_type'] ?? 'CPF',
                'number' => $data['payer_document'],
            ];
        }

        // Create idempotency key
        $requestOptions = new RequestOptions();
        $requestOptions->setCustomHeaders([
            'X-Idempotency-Key' => $data['idempotency_key'] ?? Str::uuid()->toString(),
        ]);

        $this->log('Creating PIX payment', $request);

        try {
            $order = $this->orderClient->create($request, $requestOptions);

            $this->log('PIX payment created successfully', [
                'order_id' => $order->id,
                'status' => $order->status,
            ]);

            // Extract payment data from order response
            $payment = $order->transactions->payments[0] ?? null;

            if (!$payment) {
                throw new \Exception('No payment found in order response');
            }

            return $this->storePayment([
                'external_reference' => $externalReference,
                'mercadopago_id' => $payment->id,
                'order_id' => $order->id,
                'payment_method' => PaymentMethod::PIX,
                'payment_type' => 'bank_transfer',
                'status' => $this->mapStatus($payment->status),
                'status_detail' => $payment->status_detail ?? null,
                'amount' => $amount,
                'currency' => $order->currency ?? 'BRL',
                'payer_email' => $data['payer_email'],
                'payer_name' => $data['payer_name'] ?? null,
                'payer_document' => $data['payer_document'] ?? null,
                'description' => $data['description'] ?? null,
                'metadata' => $data['metadata'] ?? null,
                'qr_code' => $payment->payment_method->qr_code ?? null,
                'qr_code_base64' => $payment->payment_method->qr_code_base64 ?? null,
                'ticket_url' => $payment->payment_method->ticket_url ?? null,
            ]);

        } catch (MPApiException $e) {
            $this->log('Error creating PIX payment', [
                'error' => $e->getMessage(),
                'status_code' => $e->getStatusCode(),
                'api_response' => $e->getApiResponse(),
            ], 'error');

            throw $e;
        }
    }

    /**
     * Create credit/debit card payment using Payment API
     *
     * @param array $data Payment data
     * @return MercadoPagoPayment
     * @throws MPApiException
     */
    public function createCardPayment(array $data): MercadoPagoPayment
    {
        $this->ensureConfigured();
        $externalReference = $data['external_reference'] ?? Str::uuid()->toString();

        $request = [
            'transaction_amount' => (float) $data['amount'],
            'token' => $data['token'], // Card token from MercadoPago.js
            'description' => $data['description'] ?? 'Card Payment',
            'installments' => $data['installments'] ?? 1,
            'payment_method_id' => $data['payment_method_id'],
            'payer' => [
                'email' => $data['payer_email'],
            ],
            'external_reference' => $externalReference,
            'statement_descriptor' => config('payment-mercadopago.statement_descriptor', 'YOUR_STORE'),
        ];

        // Add optional payer information
        if (isset($data['payer_name'])) {
            $request['payer']['first_name'] = $data['payer_name'];
        }
        if (isset($data['payer_document'])) {
            $request['payer']['identification'] = [
                'type' => $data['payer_document_type'] ?? 'CPF',
                'number' => $data['payer_document'],
            ];
        }

        // Add metadata if provided
        if (isset($data['metadata'])) {
            $request['metadata'] = $data['metadata'];
        }

        // Create idempotency key
        $requestOptions = new RequestOptions();
        $requestOptions->setCustomHeaders([
            'X-Idempotency-Key' => $data['idempotency_key'] ?? Str::uuid()->toString(),
        ]);

        $this->log('Creating card payment', array_merge($request, ['token' => '***']));

        try {
            $payment = $this->paymentClient->create($request, $requestOptions);

            $this->log('Card payment created successfully', [
                'payment_id' => $payment->id,
                'status' => $payment->status,
            ]);

            return $this->storePayment([
                'external_reference' => $externalReference,
                'mercadopago_id' => $payment->id,
                'payment_method' => $this->detectPaymentMethod($payment->payment_type_id),
                'payment_type' => $payment->payment_type_id,
                'status' => $this->mapStatus($payment->status),
                'status_detail' => $payment->status_detail ?? null,
                'amount' => $payment->transaction_amount,
                'currency' => $payment->currency_id ?? 'BRL',
                'payer_email' => $data['payer_email'],
                'payer_name' => $data['payer_name'] ?? null,
                'payer_document' => $data['payer_document'] ?? null,
                'description' => $data['description'] ?? null,
                'metadata' => $data['metadata'] ?? null,
            ]);

        } catch (MPApiException $e) {
            $this->log('Error creating card payment', [
                'error' => $e->getMessage(),
                'status_code' => $e->getStatusCode(),
                'api_response' => $e->getApiResponse(),
            ], 'error');

            throw $e;
        }
    }

    /**
     * Create Boleto payment using Orders API
     *
     * @param array $data Payment data
     * @return MercadoPagoPayment
     * @throws MPApiException
     */
    public function createBoletoPayment(array $data): MercadoPagoPayment
    {
        $this->ensureConfigured();
        $externalReference = $data['external_reference'] ?? Str::uuid()->toString();
        $amount = (string) $data['amount'];

        // Calculate expiration date
        $expirationDays = $data['expiration_days'] ?? config('payment-mercadopago.boleto.expiration_days', 3);
        $expirationDate = now()->addDays($expirationDays)->format('Y-m-d');

        $request = [
            'type' => 'online',
            'processing_mode' => config('payment-mercadopago.processing_mode', 'automatic'),
            'total_amount' => $amount,
            'external_reference' => $externalReference,
            'payer' => [
                'email' => $data['payer_email'],
            ],
            'transactions' => [
                'payments' => [
                    [
                        'amount' => $amount,
                        'payment_method' => [
                            'id' => 'bolbradesco', // Default Boleto provider
                            'type' => 'ticket',
                        ],
                        'description' => $data['description'] ?? 'Boleto Payment',
                        'date_of_expiration' => $expirationDate,
                    ]
                ]
            ],
        ];

        // Add required payer information for Boleto
        if (isset($data['payer_name'])) {
            $request['payer']['first_name'] = $data['payer_name'];
        }
        if (isset($data['payer_document'])) {
            $request['payer']['identification'] = [
                'type' => $data['payer_document_type'] ?? 'CPF',
                'number' => $data['payer_document'],
            ];
        }

        // Create idempotency key
        $requestOptions = new RequestOptions();
        $requestOptions->setCustomHeaders([
            'X-Idempotency-Key' => $data['idempotency_key'] ?? Str::uuid()->toString(),
        ]);

        $this->log('Creating Boleto payment', $request);

        try {
            $order = $this->orderClient->create($request, $requestOptions);

            $this->log('Boleto payment created successfully', [
                'order_id' => $order->id,
                'status' => $order->status,
            ]);

            // Extract payment data from order response
            $payment = $order->transactions->payments[0] ?? null;

            if (!$payment) {
                throw new \Exception('No payment found in order response');
            }

            return $this->storePayment([
                'external_reference' => $externalReference,
                'mercadopago_id' => $payment->id,
                'order_id' => $order->id,
                'payment_method' => PaymentMethod::BOLETO,
                'payment_type' => 'ticket',
                'status' => $this->mapStatus($payment->status),
                'status_detail' => $payment->status_detail ?? null,
                'amount' => $amount,
                'currency' => $order->currency ?? 'BRL',
                'payer_email' => $data['payer_email'],
                'payer_name' => $data['payer_name'] ?? null,
                'payer_document' => $data['payer_document'] ?? null,
                'description' => $data['description'] ?? null,
                'metadata' => $data['metadata'] ?? null,
                'ticket_url' => $payment->payment_method->ticket_url ?? null,
                'barcode' => $payment->payment_method->barcode ?? null,
                'expiration_date' => $expirationDate,
            ]);

        } catch (MPApiException $e) {
            $this->log('Error creating Boleto payment', [
                'error' => $e->getMessage(),
                'status_code' => $e->getStatusCode(),
                'api_response' => $e->getApiResponse(),
            ], 'error');

            throw $e;
        }
    }

    /**
     * Get payment by ID from MercadoPago API
     *
     * @param string $paymentId
     * @return object
     * @throws MPApiException
     */
    public function getPayment(string $paymentId): object
    {
        $this->ensureConfigured();
        try {
            return $this->paymentClient->get($paymentId);
        } catch (MPApiException $e) {
            $this->log('Error getting payment', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ], 'error');

            throw $e;
        }
    }

    /**
     * Refund payment
     *
     * @param string $paymentId
     * @param float|null $amount Partial refund amount (null for full refund)
     * @return object
     * @throws MPApiException
     */
    public function refundPayment(string $paymentId, ?float $amount = null): object
    {
        $this->ensureConfigured();
        try {
            $request = [];
            if ($amount !== null) {
                $request['amount'] = $amount;
            }

            $refund = $this->paymentClient->refund($paymentId, $request);

            // Update local payment record
            $payment = MercadoPagoPayment::mercadoPagoId($paymentId)->first();
            if ($payment) {
                $payment->update([
                    'status' => PaymentStatus::REFUNDED,
                    'refunded_at' => now(),
                ]);
            }

            return $refund;

        } catch (MPApiException $e) {
            $this->log('Error refunding payment', [
                'payment_id' => $paymentId,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ], 'error');

            throw $e;
        }
    }

    /**
     * Store payment in database
     *
     * @param array $data
     * @return MercadoPagoPayment
     */
    protected function storePayment(array $data): MercadoPagoPayment
    {
        return MercadoPagoPayment::create($data);
    }

    /**
     * Map MercadoPago status to internal status enum
     *
     * @param string $status
     * @return PaymentStatus
     */
    protected function mapStatus(string $status): PaymentStatus
    {
        return match($status) {
            'pending' => PaymentStatus::PENDING,
            'approved' => PaymentStatus::APPROVED,
            'authorized' => PaymentStatus::AUTHORIZED,
            'in_process' => PaymentStatus::IN_PROCESS,
            'in_mediation' => PaymentStatus::IN_MEDIATION,
            'rejected' => PaymentStatus::REJECTED,
            'cancelled' => PaymentStatus::CANCELLED,
            'refunded' => PaymentStatus::REFUNDED,
            'charged_back' => PaymentStatus::CHARGED_BACK,
            'action_required' => PaymentStatus::ACTION_REQUIRED,
            default => PaymentStatus::PENDING,
        };
    }

    /**
     * Detect payment method from payment type
     *
     * @param string $paymentType
     * @return PaymentMethod
     */
    protected function detectPaymentMethod(string $paymentType): PaymentMethod
    {
        return match($paymentType) {
            'credit_card' => PaymentMethod::CREDIT_CARD,
            'debit_card' => PaymentMethod::DEBIT_CARD,
            'ticket' => PaymentMethod::BOLETO,
            'bank_transfer' => PaymentMethod::PIX,
            'account_money' => PaymentMethod::ACCOUNT_MONEY,
            default => PaymentMethod::PIX,
        };
    }

    /**
     * Log message if logging is enabled
     *
     * @param string $message
     * @param array $context
     * @param string $level
     */
    protected function log(string $message, array $context = [], string $level = 'info'): void
    {
        if (!$this->loggingEnabled) {
            return;
        }

        $channel = config('payment-mercadopago.logging.channel', 'stack');

        Log::channel($channel)->$level("[MercadoPago] {$message}", $context);
    }
}
