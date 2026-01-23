<?php

namespace Eduardoks98\WhatsAppOfficial\Services;

use Eduardoks98\WhatsAppOfficial\Enums\MessageStatus;
use Eduardoks98\WhatsAppOfficial\Enums\MessageType;
use Eduardoks98\WhatsAppOfficial\Models\WhatsAppMessage;
use Illuminate\Support\Facades\Log;
use Netflie\WhatsAppCloudApi\WhatsAppCloudApi;
use Netflie\WhatsAppCloudApi\Message\TextMessage;
use Netflie\WhatsAppCloudApi\Message\Media\LinkID;
use Netflie\WhatsAppCloudApi\Message\Media\MediaObjectID;
use Netflie\WhatsAppCloudApi\Message\Template\Component;

/**
 * WhatsApp Business Cloud API Service
 *
 * Handles sending WhatsApp messages via official Meta Cloud API
 *
 * Based on official WhatsApp documentation:
 * @see https://developers.facebook.com/docs/whatsapp/cloud-api
 * @see https://github.com/netflie/whatsapp-cloud-api
 */
class WhatsAppService
{
    /**
     * WhatsApp Cloud API client
     *
     * @var WhatsAppCloudApi
     */
    protected WhatsAppCloudApi $client;

    /**
     * From phone number ID
     *
     * @var string
     */
    protected string $fromPhoneNumberId;

    /**
     * Whether to track messages in database
     *
     * @var bool
     */
    protected bool $trackMessages;

    /**
     * Constructor
     *
     * @param string|null $fromPhoneNumberId WhatsApp phone number ID
     * @param string|null $accessToken Meta access token
     */
    public function __construct(?string $fromPhoneNumberId = null, ?string $accessToken = null)
    {
        $this->fromPhoneNumberId = $fromPhoneNumberId ?? config('whatsapp.from_phone_number_id');
        $token = $accessToken ?? config('whatsapp.access_token');

        if (empty($this->fromPhoneNumberId) || empty($token)) {
            throw new \InvalidArgumentException('WhatsApp phone number ID and access token are required');
        }

        $this->client = new WhatsAppCloudApi([
            'from_phone_number_id' => $this->fromPhoneNumberId,
            'access_token' => $token,
        ]);

        $this->trackMessages = config('whatsapp.track_messages', true);
    }

    /**
     * Send text message
     *
     * @param string $to Recipient phone number (E.164 format)
     * @param string $text Message text
     * @param bool $previewUrl Enable URL preview
     * @return WhatsAppMessage
     */
    public function sendText(string $to, string $text, bool $previewUrl = false): WhatsAppMessage
    {
        try {
            $to = $this->formatPhoneNumber($to);

            $response = $this->client->sendTextMessage(
                $to,
                $text,
                $previewUrl
            );

            Log::info('WhatsApp text message sent', [
                'message_id' => $response->decodedBody()['messages'][0]['id'] ?? 'unknown',
                'to' => $to,
            ]);

            if ($this->trackMessages) {
                return $this->storeMessage([
                    'message_id' => $response->decodedBody()['messages'][0]['id'] ?? 'unknown',
                    'phone_number' => $to,
                    'type' => MessageType::TEXT,
                    'text_content' => $text,
                    'status' => MessageStatus::QUEUED,
                ]);
            }

            return $this->createWhatsAppMessage([
                'message_id' => $response->decodedBody()['messages'][0]['id'] ?? 'unknown',
                'phone_number' => $to,
                'type' => MessageType::TEXT,
                'text_content' => $text,
            ]);

        } catch (\Exception $e) {
            Log::error('WhatsApp text message failed', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Send image message
     *
     * @param string $to Recipient phone number
     * @param string $imageUrl Image URL or media ID
     * @param string|null $caption Optional caption
     * @return WhatsAppMessage
     */
    public function sendImage(string $to, string $imageUrl, ?string $caption = null): WhatsAppMessage
    {
        try {
            $to = $this->formatPhoneNumber($to);

            $link = new LinkID($imageUrl);
            $response = $this->client->sendImage($to, $link, $caption);

            Log::info('WhatsApp image sent', [
                'message_id' => $response->decodedBody()['messages'][0]['id'] ?? 'unknown',
                'to' => $to,
            ]);

            if ($this->trackMessages) {
                return $this->storeMessage([
                    'message_id' => $response->decodedBody()['messages'][0]['id'] ?? 'unknown',
                    'phone_number' => $to,
                    'type' => MessageType::IMAGE,
                    'media_url' => $imageUrl,
                    'text_content' => $caption,
                    'status' => MessageStatus::QUEUED,
                ]);
            }

            return $this->createWhatsAppMessage([
                'message_id' => $response->decodedBody()['messages'][0]['id'] ?? 'unknown',
                'phone_number' => $to,
                'type' => MessageType::IMAGE,
                'media_url' => $imageUrl,
            ]);

        } catch (\Exception $e) {
            Log::error('WhatsApp image failed', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Send document message
     *
     * @param string $to Recipient phone number
     * @param string $documentUrl Document URL
     * @param string|null $caption Optional caption
     * @param string|null $filename Optional filename
     * @return WhatsAppMessage
     */
    public function sendDocument(string $to, string $documentUrl, ?string $caption = null, ?string $filename = null): WhatsAppMessage
    {
        try {
            $to = $this->formatPhoneNumber($to);

            $link = new LinkID($documentUrl);
            $response = $this->client->sendDocument($to, $link, $filename, $caption);

            Log::info('WhatsApp document sent', [
                'message_id' => $response->decodedBody()['messages'][0]['id'] ?? 'unknown',
                'to' => $to,
            ]);

            if ($this->trackMessages) {
                return $this->storeMessage([
                    'message_id' => $response->decodedBody()['messages'][0]['id'] ?? 'unknown',
                    'phone_number' => $to,
                    'type' => MessageType::DOCUMENT,
                    'media_url' => $documentUrl,
                    'text_content' => $caption,
                    'status' => MessageStatus::QUEUED,
                ]);
            }

            return $this->createWhatsAppMessage([
                'message_id' => $response->decodedBody()['messages'][0]['id'] ?? 'unknown',
                'phone_number' => $to,
                'type' => MessageType::DOCUMENT,
                'media_url' => $documentUrl,
            ]);

        } catch (\Exception $e) {
            Log::error('WhatsApp document failed', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Send template message
     *
     * @param string $to Recipient phone number
     * @param string $templateName Template name (must be approved)
     * @param string $language Language code (e.g., 'pt_BR', 'en_US')
     * @param array $parameters Template parameters
     * @return WhatsAppMessage
     */
    public function sendTemplate(string $to, string $templateName, string $language, array $parameters = []): WhatsAppMessage
    {
        try {
            $to = $this->formatPhoneNumber($to);

            $components = [];
            if (!empty($parameters)) {
                $bodyParams = [];
                foreach ($parameters as $param) {
                    $bodyParams[] = [
                        'type' => 'text',
                        'text' => $param,
                    ];
                }

                $components[] = new Component([
                    'type' => 'body',
                    'parameters' => $bodyParams,
                ]);
            }

            $response = $this->client->sendTemplate($to, $templateName, $language, $components);

            Log::info('WhatsApp template sent', [
                'message_id' => $response->decodedBody()['messages'][0]['id'] ?? 'unknown',
                'to' => $to,
                'template' => $templateName,
            ]);

            if ($this->trackMessages) {
                return $this->storeMessage([
                    'message_id' => $response->decodedBody()['messages'][0]['id'] ?? 'unknown',
                    'phone_number' => $to,
                    'type' => MessageType::TEMPLATE,
                    'template_name' => $templateName,
                    'template_params' => $parameters,
                    'status' => MessageStatus::QUEUED,
                ]);
            }

            return $this->createWhatsAppMessage([
                'message_id' => $response->decodedBody()['messages'][0]['id'] ?? 'unknown',
                'phone_number' => $to,
                'type' => MessageType::TEMPLATE,
                'template_name' => $templateName,
            ]);

        } catch (\Exception $e) {
            Log::error('WhatsApp template failed', [
                'to' => $to,
                'template' => $templateName,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Update message status from webhook
     *
     * @param string $messageId WhatsApp message ID
     * @param string $status New status
     * @param array $data Additional webhook data
     * @return WhatsAppMessage|null
     */
    public function updateMessageStatus(string $messageId, string $status, array $data = []): ?WhatsAppMessage
    {
        if (!$this->trackMessages) {
            return null;
        }

        $message = WhatsAppMessage::where('message_id', $messageId)->first();

        if (!$message) {
            Log::warning('WhatsApp message not found for status update', [
                'message_id' => $messageId,
                'status' => $status,
            ]);

            return null;
        }

        // Update status
        try {
            $message->status = MessageStatus::from($status);
        } catch (\ValueError $e) {
            Log::warning('Invalid WhatsApp status value', [
                'status' => $status,
                'message_id' => $messageId,
            ]);
            return $message;
        }

        // Update timestamps based on status
        if ($message->status === MessageStatus::SENT && !$message->sent_at) {
            $message->sent_at = now();
        } elseif ($message->status === MessageStatus::DELIVERED && !$message->delivered_at) {
            $message->delivered_at = now();
        } elseif ($message->status === MessageStatus::READ && !$message->read_at) {
            $message->read_at = now();
        } elseif ($message->status === MessageStatus::FAILED && !$message->failed_at) {
            $message->failed_at = now();
        }

        // Update error information
        if (isset($data['error'])) {
            $message->error_code = $data['error']['code'] ?? null;
            $message->error_message = $data['error']['message'] ?? null;
        }

        $message->save();

        return $message;
    }

    /**
     * Format phone number to E.164 format
     *
     * @param string $phone Phone number
     * @return string Formatted phone number
     */
    protected function formatPhoneNumber(string $phone): string
    {
        // Remove non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Add + if missing
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }

        return $phone;
    }

    /**
     * Store message in database
     *
     * @param array $data Message data
     * @return WhatsAppMessage
     */
    protected function storeMessage(array $data): WhatsAppMessage
    {
        return WhatsAppMessage::create($data);
    }

    /**
     * Create WhatsAppMessage object (without storing)
     *
     * @param array $data
     * @return WhatsAppMessage
     */
    protected function createWhatsAppMessage(array $data): WhatsAppMessage
    {
        $message = new WhatsAppMessage();
        foreach ($data as $key => $value) {
            $message->$key = $value;
        }
        $message->status = MessageStatus::QUEUED;
        return $message;
    }

    /**
     * Get WhatsApp Cloud API client
     *
     * @return WhatsAppCloudApi
     */
    public function getClient(): WhatsAppCloudApi
    {
        return $this->client;
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
