<?php

namespace Eduardoks98\SmsComtele\Services;

use Eduardoks98\SmsComtele\Enums\MessageStatus;
use Eduardoks98\SmsComtele\Models\ComteleMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Comtele SMS Service
 *
 * Handles sending SMS messages via Comtele API (Brazilian provider)
 *
 * Based on official Comtele documentation:
 * @see https://docs.comtele.com.br/
 * @see https://comtele.com.br/sms-via-api/
 */
class ComteleService
{
    /**
     * Comtele API key
     *
     * @var string
     */
    protected string $apiKey;

    /**
     * API base URL
     *
     * @var string
     */
    protected string $apiUrl;

    /**
     * Default sender identifier
     *
     * @var string
     */
    protected string $defaultSender;

    /**
     * Whether to track delivery status in database
     *
     * @var bool
     */
    protected bool $trackDelivery;

    /**
     * HTTP client timeout (seconds)
     *
     * @var int
     */
    protected int $timeout;

    /**
     * Constructor
     *
     * @param string|null $apiKey Comtele API key
     */
    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? config('sms-comtele.api_key');

        if (empty($this->apiKey)) {
            throw new \InvalidArgumentException('Comtele API key is required');
        }

        $this->apiUrl = config('sms-comtele.api_url', 'https://sms.comtele.com.br/api/v2');
        $this->defaultSender = config('sms-comtele.default_sender', 'laravel-app');
        $this->trackDelivery = config('sms-comtele.track_delivery', true);
        $this->timeout = config('sms-comtele.http.timeout', 30);
    }

    /**
     * Send SMS message
     *
     * @param string|array $receivers Phone number(s) in DDD+Number format (e.g., "11987654321")
     * @param string $content Message content
     * @param string|null $sender Internal sender identifier (optional)
     * @return ComteleMessage
     * @throws \Exception
     */
    public function send(string|array $receivers, string $content, ?string $sender = null): ComteleMessage
    {
        try {
            // Normalize receivers to array
            $receiversArray = is_array($receivers) ? $receivers : [$receivers];

            // Validate and format phone numbers
            $formattedReceivers = array_map(
                fn($phone) => $this->formatPhoneNumber($phone),
                $receiversArray
            );

            // Validate phone numbers if enabled
            if (config('sms-comtele.validate_phone', true)) {
                foreach ($formattedReceivers as $phone) {
                    $this->validatePhoneNumber($phone);
                }
            }

            // Check bulk limit (max 100 recipients)
            $maxRecipients = config('sms-comtele.bulk.max_recipients', 100);
            if (count($formattedReceivers) > $maxRecipients) {
                throw new \InvalidArgumentException("Maximum {$maxRecipients} recipients per request. Got: " . count($formattedReceivers));
            }

            // Prepare request payload
            $payload = [
                'Sender' => $sender ?? $this->defaultSender,
                'Receivers' => implode(',', $formattedReceivers),
                'Content' => $content,
            ];

            // Send request to Comtele API
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'auth-key' => $this->apiKey,
                ])
                ->post("{$this->apiUrl}/send", $payload);

            // Check response
            if (!$response->successful()) {
                $this->handleErrorResponse($response);
            }

            $data = $response->json();

            // Check if request was successful
            if (!($data['Success'] ?? false)) {
                throw new \Exception($data['Message'] ?? 'Unknown error from Comtele API');
            }

            Log::info('Comtele SMS sent successfully', [
                'request_id' => $data['Object']['requestUniqueId'] ?? 'unknown',
                'receivers' => implode(',', $formattedReceivers),
                'sender' => $payload['Sender'],
            ]);

            // Store message in database if tracking is enabled
            if ($this->trackDelivery) {
                return $this->storeMessage($payload, $data);
            }

            // Return minimal ComteleMessage object
            return $this->createComteleMessage($payload, $data);

        } catch (\Exception $e) {
            Log::error('Comtele SMS send failed', [
                'receivers' => is_array($receivers) ? implode(',', $receivers) : $receivers,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Send SMS with template variables
     *
     * @param string|array $receivers Phone number(s)
     * @param string $template Message template with {variable} placeholders
     * @param array $variables Variables to replace in template
     * @param string|null $sender Sender identifier
     * @return ComteleMessage
     */
    public function sendTemplate(string|array $receivers, string $template, array $variables = [], ?string $sender = null): ComteleMessage
    {
        // Replace template variables
        $content = $template;
        foreach ($variables as $key => $value) {
            $content = str_replace('{' . $key . '}', $value, $content);
        }

        return $this->send($receivers, $content, $sender);
    }

    /**
     * Send bulk SMS to multiple recipients (chunked automatically)
     *
     * @param array $receivers Array of phone numbers
     * @param string $content Message content
     * @param string|null $sender Sender identifier
     * @return array Array of ComteleMessage objects
     */
    public function sendBulk(array $receivers, string $content, ?string $sender = null): array
    {
        $chunkSize = config('sms-comtele.bulk.chunk_size', 50);
        $chunks = array_chunk($receivers, $chunkSize);
        $messages = [];

        foreach ($chunks as $chunk) {
            try {
                $messages[] = $this->send($chunk, $content, $sender);

                // Small delay between chunks to respect rate limits
                if (count($chunks) > 1) {
                    usleep(100000); // 100ms
                }

            } catch (\Exception $e) {
                Log::warning('Bulk SMS chunk failed', [
                    'chunk_size' => count($chunk),
                    'error' => $e->getMessage(),
                ]);

                // Continue sending other chunks
                continue;
            }
        }

        return $messages;
    }

    /**
     * Get detailed reporting for sent messages
     *
     * @param string $startDate Start date (Y-m-d format)
     * @param string $endDate End date (Y-m-d format)
     * @param string|null $sender Filter by sender
     * @return array
     * @throws \Exception
     */
    public function getDetailedReport(string $startDate, string $endDate, ?string $sender = null): array
    {
        try {
            $params = [
                'StartDate' => $startDate,
                'EndDate' => $endDate,
            ];

            if ($sender) {
                $params['Sender'] = $sender;
            }

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'auth-key' => $this->apiKey,
                ])
                ->get("{$this->apiUrl}/detailedreporting", $params);

            if (!$response->successful()) {
                $this->handleErrorResponse($response);
            }

            $data = $response->json();

            if (!($data['Success'] ?? false)) {
                throw new \Exception($data['Message'] ?? 'Failed to fetch detailed report');
            }

            return $data['Object'] ?? [];

        } catch (\Exception $e) {
            Log::error('Comtele detailed report failed', [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Update message status from webhook or polling
     *
     * @param string $requestUniqueId Comtele request UUID
     * @param string $status Status value
     * @param array $data Additional webhook data
     * @return ComteleMessage|null
     */
    public function updateMessageStatus(string $requestUniqueId, string $status, array $data = []): ?ComteleMessage
    {
        if (!$this->trackDelivery) {
            return null;
        }

        $message = ComteleMessage::where('request_unique_id', $requestUniqueId)->first();

        if (!$message) {
            Log::warning('Comtele message not found for status update', [
                'request_unique_id' => $requestUniqueId,
                'status' => $status,
            ]);

            return null;
        }

        // Update status
        try {
            $message->status = MessageStatus::from($status);
        } catch (\ValueError $e) {
            Log::warning('Invalid Comtele status value', [
                'status' => $status,
                'message_id' => $message->id,
            ]);
            return $message;
        }

        // Update timestamps based on status
        if ($message->status === MessageStatus::DELIVERED && !$message->delivered_at) {
            $message->delivered_at = now();
        } elseif ($message->status === MessageStatus::ERROR && !$message->failed_at) {
            $message->failed_at = now();
        }

        // Update status date
        if (isset($data['StatusDate'])) {
            $message->status_date = $data['StatusDate'];
        }

        // Update phone number (for detailed tracking)
        if (isset($data['PhoneNumber'])) {
            $message->phone_number = $data['PhoneNumber'];
        }

        $message->save();

        return $message;
    }

    /**
     * Format phone number to Comtele format (DDD+Number)
     *
     * @param string $phone Phone number
     * @return string Formatted phone number
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Remove country code if present (55 for Brazil)
        if (str_starts_with($phone, '55') && strlen($phone) >= 12) {
            $phone = substr($phone, 2);
        }

        // Remove leading zero if present
        $phone = ltrim($phone, '0');

        return $phone;
    }

    /**
     * Validate Brazilian phone number format
     *
     * @param string $phone Phone number (DDD+Number format)
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validatePhoneNumber(string $phone): void
    {
        // Brazilian phone format: DDD (2 digits) + Number (8 or 9 digits)
        // Examples: 1198765432 (10 digits) or 11987654321 (11 digits)
        if (!preg_match('/^[1-9]{2}9?[0-9]{8}$/', $phone)) {
            throw new \InvalidArgumentException("Invalid Brazilian phone number: {$phone}. Expected format: DDD+Number (e.g., 11987654321)");
        }
    }

    /**
     * Handle error response from API
     *
     * @param \Illuminate\Http\Client\Response $response
     * @return void
     * @throws \Exception
     */
    protected function handleErrorResponse($response): void
    {
        $status = $response->status();
        $body = $response->json();
        $message = $body['Message'] ?? 'Unknown error';

        $errorMessages = [
            400 => "Bad Request: {$message}",
            401 => "Unauthorized: Invalid API key",
            404 => "Not Found: {$message}",
            429 => "Rate Limit Exceeded: Too many requests",
            500 => "Internal Server Error: {$message}",
            503 => "Service Unavailable: {$message}",
        ];

        throw new \Exception(
            $errorMessages[$status] ?? "HTTP {$status}: {$message}",
            $status
        );
    }

    /**
     * Store message in database
     *
     * @param array $payload Request payload
     * @param array $response API response
     * @return ComteleMessage
     */
    protected function storeMessage(array $payload, array $response): ComteleMessage
    {
        return ComteleMessage::create([
            'request_unique_id' => $response['Object']['requestUniqueId'] ?? Str::uuid()->toString(),
            'sender' => $payload['Sender'],
            'receivers' => $payload['Receivers'],
            'content' => $payload['Content'],
            'status' => MessageStatus::PENDING,
            'metadata' => [
                'api_response' => $response,
            ],
        ]);
    }

    /**
     * Create ComteleMessage object from API response
     *
     * @param array $payload
     * @param array $response
     * @return ComteleMessage
     */
    protected function createComteleMessage(array $payload, array $response): ComteleMessage
    {
        $message = new ComteleMessage();
        $message->request_unique_id = $response['Object']['requestUniqueId'] ?? Str::uuid()->toString();
        $message->sender = $payload['Sender'];
        $message->receivers = $payload['Receivers'];
        $message->content = $payload['Content'];
        $message->status = MessageStatus::PENDING;

        return $message;
    }

    /**
     * Create static instance
     *
     * @return static
     */
    public static function make(): static
    {
        return new static();
    }
}
