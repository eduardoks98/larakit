<?php

namespace Eduardoks98\WhatsAppOfficial\Http\Controllers;

use Eduardoks98\BaseApi\Http\Controllers\ApiController;
use Eduardoks98\WhatsAppOfficial\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * WhatsApp Webhook Controller
 *
 * Handles webhooks from WhatsApp Cloud API
 *
 * @see https://developers.facebook.com/docs/whatsapp/cloud-api/webhooks
 */
class WhatsAppWebhookController extends ApiController
{
    /**
     * WhatsApp Service
     *
     * @var WhatsAppService
     */
    protected WhatsAppService $whatsappService;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->whatsappService = new WhatsAppService();
    }

    /**
     * Handle webhook verification (GET request)
     *
     * WhatsApp sends GET request to verify webhook URL
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        $verifyToken = config('whatsapp.webhook.verify_token');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            Log::info('WhatsApp webhook verified successfully');
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        Log::warning('WhatsApp webhook verification failed', [
            'mode' => $mode,
            'token_match' => $token === $verifyToken,
        ]);

        return response('Forbidden', 403);
    }

    /**
     * Handle webhook events (POST request)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        try {
            Log::info('WhatsApp webhook received', [
                'data' => $request->all(),
            ]);

            $entry = $request->input('entry', [])[0] ?? null;

            if (!$entry) {
                return $this->success(['message' => 'No entry data']);
            }

            $changes = $entry['changes'][0] ?? null;

            if (!$changes) {
                return $this->success(['message' => 'No changes data']);
            }

            $value = $changes['value'] ?? [];

            // Handle status updates
            if (isset($value['statuses'])) {
                $this->handleStatusUpdate($value['statuses']);
            }

            // Handle incoming messages
            if (isset($value['messages'])) {
                $this->handleIncomingMessages($value['messages']);
            }

            return $this->success(['message' => 'Webhook processed']);

        } catch (\Exception $e) {
            Log::error('WhatsApp webhook processing failed', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            // Still return 200 to prevent WhatsApp retries
            return $this->success(['message' => 'Webhook received with errors']);
        }
    }

    /**
     * Handle status update webhooks
     *
     * @param array $statuses
     * @return void
     */
    protected function handleStatusUpdate(array $statuses): void
    {
        foreach ($statuses as $status) {
            $messageId = $status['id'] ?? null;
            $statusValue = $status['status'] ?? null;

            if (!$messageId || !$statusValue) {
                continue;
            }

            Log::info('WhatsApp status update', [
                'message_id' => $messageId,
                'status' => $statusValue,
            ]);

            // Update message status in database
            $this->whatsappService->updateMessageStatus(
                $messageId,
                $statusValue,
                $status
            );
        }
    }

    /**
     * Handle incoming message webhooks
     *
     * @param array $messages
     * @return void
     */
    protected function handleIncomingMessages(array $messages): void
    {
        foreach ($messages as $message) {
            $from = $message['from'] ?? null;
            $messageId = $message['id'] ?? null;
            $type = $message['type'] ?? 'unknown';

            if (!$from || !$messageId) {
                continue;
            }

            Log::info('WhatsApp incoming message', [
                'from' => $from,
                'message_id' => $messageId,
                'type' => $type,
            ]);

            // Extract message content based on type
            $content = null;
            switch ($type) {
                case 'text':
                    $content = $message['text']['body'] ?? null;
                    break;
                case 'image':
                case 'video':
                case 'audio':
                case 'document':
                    $content = $message[$type]['caption'] ?? 'Media message';
                    break;
            }

            // Here you can implement custom logic for incoming messages
            // For example:
            // - Store in database
            // - Trigger auto-reply
            // - Forward to chat system
            // - Send notification

            Log::info('Incoming message content', [
                'from' => $from,
                'type' => $type,
                'content' => $content,
            ]);
        }
    }
}
