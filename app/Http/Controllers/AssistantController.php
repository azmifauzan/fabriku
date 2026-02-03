<?php

namespace App\Http\Controllers;

use App\Http\Requests\Assistant\SendMessageRequest;
use App\Services\Assistant\AssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssistantController extends Controller
{
    public function __construct(private AssistantService $assistantService) {}

    /**
     * Send a message to the assistant
     */
    public function sendMessage(SendMessageRequest $request): JsonResponse
    {
        $user = $request->user();
        $message = $request->validated('message');
        $channel = $request->validated('channel', 'web');

        $result = $this->assistantService->processMessage($user, $message, $channel);

        if (! $result['success']) {
            $statusCode = match ($result['type'] ?? 'error') {
                'rate_limit' => 429,
                'demo_account', 'subscription_expired' => 403,
                default => 500,
            };

            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'type' => $result['type'] ?? 'error',
            ], $statusCode);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'conversation_id' => $result['conversation_id'],
            'message_id' => $result['message_id'],
        ]);
    }

    /**
     * Get conversation history
     */
    public function getHistory(Request $request): JsonResponse
    {
        $user = $request->user();
        $channel = $request->query('channel', 'web');
        $limit = min((int) $request->query('limit', 50), 100);

        $messages = $this->assistantService->getConversationHistory($user, $channel, $limit);

        return response()->json([
            'success' => true,
            'messages' => $messages,
        ]);
    }

    /**
     * Clear conversation history
     */
    public function clearHistory(Request $request): JsonResponse
    {
        $user = $request->user();
        $channel = $request->input('channel', 'web');

        $cleared = $this->assistantService->clearConversation($user, $channel);

        return response()->json([
            'success' => true,
            'cleared' => $cleared,
            'message' => $cleared ? 'Riwayat percakapan telah dihapus.' : 'Tidak ada percakapan aktif.',
        ]);
    }

    /**
     * Get usage statistics
     */
    public function getUsage(Request $request): JsonResponse
    {
        $user = $request->user();
        $stats = $this->assistantService->getUsageStats($user);

        return response()->json([
            'success' => true,
            'usage' => $stats,
        ]);
    }

    /**
     * Get assistant status/info
     */
    public function getStatus(Request $request): JsonResponse
    {
        $user = $request->user();
        $stats = $this->assistantService->getUsageStats($user);

        return response()->json([
            'success' => true,
            'status' => [
                'available' => true,
                'usage' => $stats,
                'features' => [
                    'chat' => true,
                    'actions' => false, // Phase 2
                    'telegram' => ! empty(config('telegram.bot_token')),
                    'whatsapp' => false, // Phase 4
                ],
            ],
        ]);
    }
}
