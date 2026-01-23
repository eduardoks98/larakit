<?php

namespace Eduardoks98\SmsTwilio\Services;

use Eduardoks98\SmsTwilio\Enums\MessageDirection;
use Eduardoks98\SmsTwilio\Enums\MessageStatus;
use Eduardoks98\SmsTwilio\Models\TwilioMessage;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;

/**
 * Twilio SMS Service
 *
 * Handles sending SMS messages via Twilio API
 *
 * Based on official Twilio documentation:
 * @see https://www.twilio.com/docs/messaging/api/message-resource
 * @see https://github.com/twilio/twilio-php
 */
class TwilioService
{
    /**
     * Twilio Client instance
     *
     * @var Client
     */
    protected Client $client;

    /**
     * From phone number or Messaging Service SID
     *
     * @var string
     */
    protected string $from;

    /**
     * Whether to track delivery status in database
     *
     * @var bool
     */
    protected bool $trackDelivery;

    /**
     * Webhook URL for status callbacks
     *
     * @var string|null
     */
    protected ?string $webhookUrl;

    /**
     * Constructor
     *
     * @param string|null $accountSid Twilio Account SID
     * @param string|null $authToken Twilio Auth Token
     */
    public function __construct(?string $accountSid = null, ?string $authToken = null)
    {
        $sid = $accountSid ?? config('sms-twilio.account_sid');
        $token = $authToken ?? config('sms-twilio.auth_token');

        if (empty($sid) || empty($token)) {
            throw new \InvalidArgumentException('Twilio Account SID and Auth Token are required');
        }

        $this->client = new Client($sid, $token);

        // Use Messaging Service SID if available, otherwise use phone number
        $this->from = config('sms-twilio.messaging_service_sid')
            ?? config('sms-twilio.from');

        if (empty($this->from)) {
            throw new \InvalidArgumentException('Twilio From number or Messaging Service SID is required');
        }

        $this->trackDelivery = config('sms-twilio.track_delivery', true);

        $this->webhookUrl = config('sms-twilio.webhook.enabled')
            ? config('sms-twilio.webhook.url')
            : null;
    }

    /**
     * Send SMS message
     *
     * @param string $to Recipient phone number (E.164 format)
     * @param string $body Message content
     * @param array $options Additional options (mediaUrl, statusCallback, etc)
     * @return TwilioMessage
     * @throws TwilioException
     */
    public function send(string $to, string $body, array $options = []): TwilioMessage
    {
        try {
            // Format phone number to E.164
            $to = $this->formatPhoneNumber($to);

            // Validate phone number if enabled
            if (config('sms-twilio.validate_phone', true)) {
                $this->validatePhoneNumber($to);
            }

            // Prepare message parameters
            $params = array_merge([
                'body' => $body,
            ], $options);

            // Add status callback if webhook is enabled
            if ($this->webhookUrl && !isset($params['statusCallback'])) {
                $params['statusCallback'] = $this->webhookUrl;
            }

            // Determine if using Messaging Service SID or phone number
            if (str_starts_with($this->from, 'MG')) {
                // Messaging Service SID
                $params['messagingServiceSid'] = $this->from;
            } else {
                // Phone number
                $params['from'] = $this->from;
            }

            // Send message via Twilio API
            $message = $this->client->messages->create($to, $params);

            Log::info('Twilio SMS sent successfully', [
                'message_sid' => $message->sid,
                'to' => $to,
                'status' => $message->status,
            ]);

            // Store message in database if tracking is enabled
            if ($this->trackDelivery) {
                return $this->storeMessage($message);
            }

            // Return minimal TwilioMessage object
            return $this->createTwilioMessage($message);

        } catch (TwilioException $e) {
            Log::error('Twilio SMS send failed', [
                'to' => $to,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);

            throw $e;
        }
    }

    /**
     * Send SMS with template variables
     *
     * @param string $to Recipient phone number
     * @param string $template Message template with {variable} placeholders
     * @param array $variables Variables to replace in template
     * @param array $options Additional options
     * @return TwilioMessage
     */
    public function sendTemplate(string $to, string $template, array $variables = [], array $options = []): TwilioMessage
    {
        // Replace template variables
        $body = $template;
        foreach ($variables as $key => $value) {
            $body = str_replace('{' . $key . '}', $value, $body);
        }

        return $this->send($to, $body, $options);
    }

    /**
     * Send bulk SMS to multiple recipients
     *
     * @param array $recipients Array of phone numbers
     * @param string $body Message content
     * @param array $options Additional options
     * @return array Array of TwilioMessage objects
     */
    public function sendBulk(array $recipients, string $body, array $options = []): array
    {
        $messages = [];

        foreach ($recipients as $to) {
            try {
                $messages[] = $this->send($to, $body, $options);
            } catch (TwilioException $e) {
                Log::warning('Bulk SMS failed for recipient', [
                    'to' => $to,
                    'error' => $e->getMessage(),
                ]);

                // Continue sending to other recipients
                continue;
            }
        }

        return $messages;
    }

    /**
     * Get message status from Twilio
     *
     * @param string $messageSid Twilio message SID
     * @return array Message data
     * @throws TwilioException
     */
    public function getMessageStatus(string $messageSid): array
    {
        try {
            $message = $this->client->messages($messageSid)->fetch();

            return [
                'sid' => $message->sid,
                'status' => $message->status,
                'to' => $message->to,
                'from' => $message->from,
                'body' => $message->body,
                'numSegments' => $message->numSegments,
                'price' => $message->price,
                'priceUnit' => $message->priceUnit,
                'errorCode' => $message->errorCode,
                'errorMessage' => $message->errorMessage,
                'dateSent' => $message->dateSent?->format('Y-m-d H:i:s'),
                'dateUpdated' => $message->dateUpdated?->format('Y-m-d H:i:s'),
            ];

        } catch (TwilioException $e) {
            Log::error('Failed to fetch Twilio message status', [
                'message_sid' => $messageSid,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Update message status from webhook
     *
     * @param string $messageSid Twilio message SID
     * @param string $status New status
     * @param array $data Additional webhook data
     * @return TwilioMessage|null
     */
    public function updateMessageStatus(string $messageSid, string $status, array $data = []): ?TwilioMessage
    {
        if (!$this->trackDelivery) {
            return null;
        }

        $message = TwilioMessage::where('message_sid', $messageSid)->first();

        if (!$message) {
            Log::warning('Twilio message not found for status update', [
                'message_sid' => $messageSid,
                'status' => $status,
            ]);

            return null;
        }

        // Update status
        $message->status = MessageStatus::from($status);

        // Update timestamps based on status
        if ($message->status === MessageStatus::SENT && !$message->sent_at) {
            $message->sent_at = now();
        } elseif ($message->status === MessageStatus::DELIVERED && !$message->delivered_at) {
            $message->delivered_at = now();
        } elseif ($message->status?->isFailure() && !$message->failed_at) {
            $message->failed_at = now();
        }

        // Update price and error information
        if (isset($data['MessagePrice'])) {
            $message->price = $data['MessagePrice'];
        }

        if (isset($data['MessagePriceUnit'])) {
            $message->price_unit = $data['MessagePriceUnit'];
        }

        if (isset($data['ErrorCode'])) {
            $message->error_code = $data['ErrorCode'];
        }

        if (isset($data['ErrorMessage'])) {
            $message->error_message = $data['ErrorMessage'];
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

        // Add country code if missing
        if (!str_starts_with($phone, '+')) {
            $countryCode = config('sms-twilio.default_country_code', '55');

            // Check if phone already has country code (without +)
            if (!str_starts_with($phone, $countryCode)) {
                $phone = $countryCode . $phone;
            }

            $phone = '+' . $phone;
        }

        return $phone;
    }

    /**
     * Validate phone number format
     *
     * @param string $phone Phone number in E.164 format
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validatePhoneNumber(string $phone): void
    {
        // E.164 format: +[country code][number]
        // Example: +15551234567
        if (!preg_match('/^\+[1-9]\d{1,14}$/', $phone)) {
            throw new \InvalidArgumentException("Invalid phone number format: {$phone}. Expected E.164 format (e.g., +5511987654321)");
        }
    }

    /**
     * Store message in database
     *
     * @param \Twilio\Rest\Api\V2010\Account\MessageInstance $message Twilio message object
     * @return TwilioMessage
     */
    protected function storeMessage($message): TwilioMessage
    {
        return TwilioMessage::create([
            'message_sid' => $message->sid,
            'from' => $message->from,
            'to' => $message->to,
            'body' => $message->body,
            'status' => MessageStatus::from($message->status),
            'direction' => MessageDirection::from($message->direction),
            'num_segments' => $message->numSegments,
            'price' => $message->price,
            'price_unit' => $message->priceUnit,
            'error_code' => $message->errorCode,
            'error_message' => $message->errorMessage,
            'sent_at' => $message->dateSent?->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Create TwilioMessage object from Twilio API response
     *
     * @param \Twilio\Rest\Api\V2010\Account\MessageInstance $message
     * @return TwilioMessage
     */
    protected function createTwilioMessage($message): TwilioMessage
    {
        $twilioMessage = new TwilioMessage();
        $twilioMessage->message_sid = $message->sid;
        $twilioMessage->from = $message->from;
        $twilioMessage->to = $message->to;
        $twilioMessage->body = $message->body;
        $twilioMessage->status = MessageStatus::from($message->status);
        $twilioMessage->direction = MessageDirection::from($message->direction);
        $twilioMessage->num_segments = $message->numSegments;
        $twilioMessage->price = $message->price;
        $twilioMessage->price_unit = $message->priceUnit;

        return $twilioMessage;
    }

    /**
     * Get Twilio client instance
     *
     * @return Client
     */
    public function getClient(): Client
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
