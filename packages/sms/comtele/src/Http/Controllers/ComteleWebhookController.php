<?php

namespace Eduardoks98\SmsComtele\Http\Controllers;

use Eduardoks98\BaseApi\Http\Controllers\ApiController;
use Eduardoks98\SmsComtele\Services\ComteleService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * Comtele Webhook Controller
 *
 * Handles webhooks from Comtele for delivery status and replies
 *
 * @see https://docs.comtele.com.br/
 */
class ComteleWebhookController extends ApiController
{
    /**
     * Comtele Service
     *
     * @var ComteleService
     */
    protected ComteleService $comteleService;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->comteleService = new ComteleService();
    }

    /**
     * Handle status callback webhook
     *
     * Receives delivery status updates from Comtele
     *
     * Expected payload:
     * {
     *   "Status": "Delivered|Processed|Error",
     *   "PhoneNumber": "11987654321",
     *   "Sender": "sender_id",
     *   "StatusDate": "2024-01-24 10:30:00"
     * }
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleStatusCallback(Request $request): JsonResponse
    {
        try {
            // Log incoming webhook
            Log::info('Comtele status callback received', [
                'data' => $request->all(),
            ]);

            // Extract webhook parameters
            $status = $request->input('Status');
            $phoneNumber = $request->input('PhoneNumber');
            $sender = $request->input('Sender');

            if (empty($status) || empty($phoneNumber) || empty($sender)) {
                return $this->error('Missing required webhook parameters', 400);
            }

            // Find message by sender and phone number
            // Note: Comtele doesn't send requestUniqueId in webhooks,
            // so we need to match by sender + phone number + recent date
            $message = \Eduardoks98\SmsComtele\Models\ComteleMessage::where('sender', $sender)
                ->where(function($query) use ($phoneNumber) {
                    $query->where('receivers', $phoneNumber)
                          ->orWhere('receivers', 'like', "%{$phoneNumber}%");
                })
                ->where('created_at', '>=', now()->subDays(7))
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$message) {
                Log::warning('Comtele message not found for webhook', [
                    'sender' => $sender,
                    'phone_number' => $phoneNumber,
                ]);

                // Still return success to avoid webhook retries
                return $this->success([
                    'message' => 'Webhook received but message not found',
                ]);
            }

            // Update message status
            $this->comteleService->updateMessageStatus(
                $message->request_unique_id,
                $status,
                $request->all()
            );

            return $this->success([
                'message' => 'Status updated successfully',
                'status' => $status,
                'phone_number' => $phoneNumber,
            ]);

        } catch (\Exception $e) {
            Log::error('Comtele webhook processing failed', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return $this->error('Webhook processing failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Handle reply callback webhook
     *
     * Receives SMS replies from recipients
     *
     * Expected payload:
     * {
     *   "Sender": "sender_id",
     *   "SentContent": "Original message",
     *   "ReceivedContent": "Reply from user",
     *   "ReceiveDate": "2024-01-24 10:35:00"
     * }
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleReplyCallback(Request $request): JsonResponse
    {
        try {
            // Log incoming reply
            Log::info('Comtele reply callback received', [
                'data' => $request->all(),
            ]);

            // Extract reply data
            $sender = $request->input('Sender');
            $sentContent = $request->input('SentContent');
            $receivedContent = $request->input('ReceivedContent');
            $receiveDate = $request->input('ReceiveDate');

            if (empty($sender) || empty($receivedContent)) {
                return $this->error('Missing required webhook parameters', 400);
            }

            // Here you can implement custom logic for handling replies
            // For example:
            // - Store replies in database
            // - Trigger notifications
            // - Forward to chat system
            // - Auto-response logic

            Log::info('SMS reply received', [
                'sender' => $sender,
                'original_message' => $sentContent,
                'reply' => $receivedContent,
                'date' => $receiveDate,
            ]);

            return $this->success([
                'message' => 'Reply received successfully',
                'sender' => $sender,
            ]);

        } catch (\Exception $e) {
            Log::error('Comtele reply webhook processing failed', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return $this->error('Webhook processing failed: ' . $e->getMessage(), 500);
        }
    }
}
