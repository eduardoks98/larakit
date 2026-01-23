<?php

namespace Eduardoks98\SmsTwilio\Http\Controllers;

use Eduardoks98\BaseApi\Http\Controllers\ApiController;
use Eduardoks98\SmsTwilio\Services\TwilioService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * Twilio Webhook Controller
 *
 * Handles status callback webhooks from Twilio
 *
 * @see https://www.twilio.com/docs/messaging/guides/webhook-request
 */
class TwilioWebhookController extends ApiController
{
    /**
     * Twilio Service
     *
     * @var TwilioService
     */
    protected TwilioService $twilioService;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->twilioService = new TwilioService();
    }

    /**
     * Handle status callback webhook
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleStatusCallback(Request $request): JsonResponse
    {
        try {
            // Log incoming webhook
            Log::info('Twilio status callback received', [
                'data' => $request->all(),
            ]);

            // Extract Twilio webhook parameters
            $messageSid = $request->input('MessageSid');
            $messageStatus = $request->input('MessageStatus');

            if (empty($messageSid) || empty($messageStatus)) {
                return $this->error('Missing required webhook parameters', 400);
            }

            // Update message status
            $message = $this->twilioService->updateMessageStatus(
                $messageSid,
                $messageStatus,
                $request->all()
            );

            if (!$message) {
                return $this->error('Message not found', 404);
            }

            return $this->success([
                'message' => 'Status updated successfully',
                'message_sid' => $messageSid,
                'status' => $messageStatus,
            ]);

        } catch (\Exception $e) {
            Log::error('Twilio webhook processing failed', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return $this->error('Webhook processing failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Handle incoming message webhook
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function handleIncomingMessage(Request $request)
    {
        try {
            // Log incoming message
            Log::info('Twilio incoming message received', [
                'from' => $request->input('From'),
                'to' => $request->input('To'),
                'body' => $request->input('Body'),
            ]);

            // Here you can implement custom logic for incoming messages
            // For example:
            // - Auto-reply
            // - Store in database
            // - Trigger notifications
            // - Forward to chat system

            // Return TwiML response (optional)
            return response(
                '<?xml version="1.0" encoding="UTF-8"?><Response></Response>',
                200,
                ['Content-Type' => 'text/xml']
            );

        } catch (\Exception $e) {
            Log::error('Incoming message processing failed', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response(
                '<?xml version="1.0" encoding="UTF-8"?><Response></Response>',
                500,
                ['Content-Type' => 'text/xml']
            );
        }
    }
}
