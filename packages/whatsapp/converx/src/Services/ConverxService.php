<?php

namespace Eduardoks98\WhatsAppConverx\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Converx WhatsApp Service
 *
 * Handles WhatsApp messaging via Converx API (Chatwoot-based Brazilian provider)
 *
 * Based on Converx API: https://converx.app/api/v1
 */
class ConverxService
{
    /**
     * Converx account ID
     *
     * @var string
     */
    protected string $accountId;

    /**
     * API access token
     *
     * @var string
     */
    protected string $apiToken;

    /**
     * API base URL
     *
     * @var string
     */
    protected string $apiUrl;

    /**
     * Default inbox ID
     *
     * @var string
     */
    protected string $inboxId;

    /**
     * HTTP timeout
     *
     * @var int
     */
    protected int $timeout;

    /**
     * Constructor
     *
     * @param string|null $accountId Converx account ID
     * @param string|null $apiToken API access token
     */
    public function __construct(?string $accountId = null, ?string $apiToken = null)
    {
        $this->accountId = $accountId ?? config('converx.account_id', '8');
        $this->apiToken = $apiToken ?? config('converx.api_token');

        if (empty($this->apiToken)) {
            throw new \InvalidArgumentException('Converx API token is required');
        }

        $this->apiUrl = config('converx.api_url', 'https://converx.app/api/v1');
        $this->inboxId = config('converx.inbox_id', '1');
        $this->timeout = config('converx.http.timeout', 60);
    }

    /**
     * Send text message to WhatsApp contact
     *
     * @param string $phoneNumber Phone number (55XXXXXXXXXXX format)
     * @param string $message Message text
     * @return array API response
     * @throws \Exception
     */
    public function sendMessage(string $phoneNumber, string $message): array
    {
        try {
            // Format phone number
            $phoneNumber = $this->formatPhoneNumber($phoneNumber);

            // Get or create conversation
            $conversationId = $this->getOrCreateConversation($phoneNumber);

            // Send message
            $response = $this->sendMessageToConversation($conversationId, $message);

            Log::info('Converx message sent successfully', [
                'conversation_id' => $conversationId,
                'phone' => $phoneNumber,
            ]);

            return $response;

        } catch (\Exception $e) {
            Log::error('Converx message send failed', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Send template message
     *
     * @param string $phoneNumber Phone number
     * @param string $templateName Template name
     * @param array $parameters Template parameters
     * @param string|null $namespace Template namespace
     * @return array API response
     */
    public function sendTemplate(string $phoneNumber, string $templateName, array $parameters = [], ?string $namespace = null): array
    {
        try {
            $phoneNumber = $this->formatPhoneNumber($phoneNumber);
            $conversationId = $this->getOrCreateConversation($phoneNumber);

            $templateParams = [
                'name' => $templateName,
                'category' => 'UTILITY',
                'language' => 'pt_BR',
                'namespace' => $namespace ?? config('converx.templates.namespace'),
                'processed_params' => $parameters,
            ];

            $response = $this->post(
                $this->buildConversationUrl($conversationId, '/messages'),
                ['template_params' => $templateParams]
            );

            Log::info('Converx template sent successfully', [
                'conversation_id' => $conversationId,
                'template' => $templateName,
            ]);

            return $response;

        } catch (\Exception $e) {
            Log::error('Converx template send failed', [
                'phone' => $phoneNumber,
                'template' => $templateName,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get or create conversation for phone number
     *
     * @param string $phoneNumber Phone number (international format)
     * @return int|string Conversation ID
     * @throws \Exception
     */
    public function getOrCreateConversation(string $phoneNumber): int|string
    {
        try {
            // Search for existing contact
            $contacts = $this->get($this->buildAccountUrl("/contacts/search?q={$phoneNumber}"));

            if (!empty($contacts['payload']) && count($contacts['payload']) > 0) {
                $contactId = $contacts['payload'][0]['id'];

                // Get contact's conversations
                $conversations = $this->get($this->buildAccountUrl("/contacts/{$contactId}/conversations"));

                if (!empty($conversations['payload']) && count($conversations['payload']) > 0) {
                    return $conversations['payload'][0]['id'];
                }
            }

            // Create new conversation
            $newConversation = $this->post($this->buildAccountUrl('/conversations'), [
                'source_id' => $this->inboxId,
                'inbox_id' => $this->inboxId,
                'contact_inbox' => [
                    'source_id' => $phoneNumber,
                ],
            ]);

            return $newConversation['id'] ?? $newConversation['payload']['id'] ?? throw new \Exception('Failed to create conversation');

        } catch (\Exception $e) {
            Log::error('Converx conversation creation failed', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Send message to existing conversation
     *
     * @param int|string $conversationId Conversation ID
     * @param string $message Message text
     * @return array API response
     */
    public function sendMessageToConversation(int|string $conversationId, string $message): array
    {
        $url = $this->buildConversationUrl($conversationId, '/messages');

        return $this->post($url, [
            'content' => $message,
            'message_type' => 'outgoing',
            'private' => false,
        ]);
    }

    /**
     * Format phone number to international format
     *
     * @param string $phoneNumber Phone number
     * @return string Formatted phone number (55XXXXXXXXXXX)
     */
    protected function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove non-numeric characters
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Add country code if missing (Brazil: 55)
        if (strlen($phoneNumber) === 10 || strlen($phoneNumber) === 11) {
            $phoneNumber = '55' . $phoneNumber;
        }

        // Validate length
        if (strlen($phoneNumber) !== 12 && strlen($phoneNumber) !== 13) {
            Log::warning('Converx: Invalid phone number format', [
                'phone' => $phoneNumber,
                'length' => strlen($phoneNumber),
            ]);
        }

        return $phoneNumber;
    }

    /**
     * Build account URL
     *
     * @param string $path Endpoint path
     * @return string Full URL
     */
    protected function buildAccountUrl(string $path): string
    {
        return "/accounts/{$this->accountId}" . $path;
    }

    /**
     * Build conversation URL
     *
     * @param int|string $conversationId Conversation ID
     * @param string $path Endpoint path
     * @return string Full URL
     */
    protected function buildConversationUrl(int|string $conversationId, string $path = ''): string
    {
        return $this->buildAccountUrl("/conversations/{$conversationId}" . $path);
    }

    /**
     * Make GET request to Converx API
     *
     * @param string $endpoint Endpoint path
     * @return array Response data
     * @throws \Exception
     */
    protected function get(string $endpoint): array
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders($this->getHeaders())
            ->get($this->apiUrl . $endpoint);

        if (!$response->successful()) {
            throw new \Exception("Converx API error: {$response->status()} - {$response->body()}");
        }

        return $this->handleResponse($response->json());
    }

    /**
     * Make POST request to Converx API
     *
     * @param string $endpoint Endpoint path
     * @param array $data Request data
     * @return array Response data
     * @throws \Exception
     */
    protected function post(string $endpoint, array $data): array
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders($this->getHeaders())
            ->post($this->apiUrl . $endpoint, $data);

        if (!$response->successful()) {
            throw new \Exception("Converx API error: {$response->status()} - {$response->body()}");
        }

        return $this->handleResponse($response->json());
    }

    /**
     * Get request headers
     *
     * @return array Headers
     */
    protected function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'api_access_token' => $this->apiToken,
        ];
    }

    /**
     * Handle API response
     *
     * @param array $response Response data
     * @return array Processed response
     * @throws \Exception
     */
    protected function handleResponse(array $response): array
    {
        // Converx returns error in format: { "error": "message" }
        if (isset($response['error'])) {
            Log::warning('Converx API error', [
                'error' => $response['error'],
            ]);

            throw new \Exception($response['error']);
        }

        return $response;
    }

    /**
     * Check if service is configured
     *
     * @return bool
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiToken) && !empty($this->accountId);
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
