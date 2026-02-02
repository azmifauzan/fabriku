<?php

namespace App\Services\Assistant;

use App\Models\AssistantConversation;
use App\Models\AssistantMessage;
use App\Models\AssistantUsage;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AssistantService
{
    private OpenAIService $openAI;

    private ?AssistantDataService $dataService = null;

    public function __construct(OpenAIService $openAI)
    {
        $this->openAI = $openAI;
    }

    /**
     * Process a message from the user
     */
    public function processMessage(User $user, string $message, string $channel = 'web'): array
    {
        // Initialize data service with tenant context
        $this->dataService = new AssistantDataService($user->tenant_id);

        // Check if user can access assistant (demo/expired checks)
        $accessCheck = $this->checkAccessibility($user);
        if (! $accessCheck['allowed']) {
            return [
                'success' => false,
                'message' => $accessCheck['message'],
                'type' => $accessCheck['type'],
            ];
        }

        // Check rate limits
        $rateLimit = $this->checkRateLimit($user);
        if (! $rateLimit['allowed']) {
            return [
                'success' => false,
                'message' => $rateLimit['message'],
                'type' => 'rate_limit',
            ];
        }

        // Get or create conversation
        $conversation = AssistantConversation::getOrCreateForUser(
            $user->id,
            $user->tenant_id,
            $channel
        );

        // Save user message
        $userMessage = AssistantMessage::createUserMessage($conversation->id, $message);

        // Update conversation last message timestamp
        $conversation->update(['last_message_at' => now()]);

        // Check if OpenAI is configured
        if (! $this->openAI->isConfigured()) {
            // Fallback to simple responses
            $response = $this->handleSimpleQuery($message, $user);

            $assistantMessage = AssistantMessage::createAssistantMessage(
                $conversation->id,
                $response['content'],
                null,
                ['fallback' => true]
            );

            return [
                'success' => true,
                'message' => $response['content'],
                'conversation_id' => $conversation->id,
                'message_id' => $assistantMessage->id,
            ];
        }

        // Build messages for OpenAI
        $messages = $this->buildMessages($conversation, $user, $message);

        // Call OpenAI
        $response = $this->openAI->chat($messages);

        if (! $response['success']) {
            return [
                'success' => false,
                'message' => $response['content'],
                'type' => 'error',
            ];
        }

        // Save assistant message
        $assistantMessage = AssistantMessage::createAssistantMessage(
            $conversation->id,
            $response['content'],
            ($response['tokens_input'] ?? 0) + ($response['tokens_output'] ?? 0),
            [
                'finish_reason' => $response['finish_reason'] ?? 'stop',
                'tool_calls' => $response['tool_calls'] ?? null,
            ]
        );

        // Update usage
        $this->updateUsage(
            $user,
            $response['tokens_input'] ?? 0,
            $response['tokens_output'] ?? 0
        );

        return [
            'success' => true,
            'message' => $response['content'],
            'conversation_id' => $conversation->id,
            'message_id' => $assistantMessage->id,
        ];
    }

    /**
     * Build messages array for OpenAI
     */
    private function buildMessages(AssistantConversation $conversation, User $user, string $currentMessage): array
    {
        $messages = [];

        // System prompt
        $messages[] = [
            'role' => 'system',
            'content' => $this->openAI->getSystemPrompt($user),
        ];

        // Add context data to system message
        $contextData = $this->getContextData();
        if ($contextData) {
            $messages[] = [
                'role' => 'system',
                'content' => "Data Konteks Terkini:\n".$contextData,
            ];
        }

        // Recent conversation history (last 10 messages)
        $recentMessages = $conversation->getRecentMessages(10);
        foreach ($recentMessages as $msg) {
            if ($msg->id !== null) { // Skip the current message we just saved
                $messages[] = $msg->toOpenAIFormat();
            }
        }

        // Current user message
        $messages[] = [
            'role' => 'user',
            'content' => $currentMessage,
        ];

        return $messages;
    }

    /**
     * Get context data for the assistant
     */
    private function getContextData(): ?string
    {
        if (! $this->dataService) {
            return null;
        }

        try {
            $summary = $this->dataService->getDashboardSummary();

            $lines = [
                '## Ringkasan Dashboard',
                "- Total Material: {$summary['materials']['total']} (Low stock: {$summary['materials']['low_stock']})",
                "- Total Inventory: {$summary['inventory']['total_quantity']} items",
                '- Penjualan Bulan Ini: Rp '.number_format($summary['sales_this_month']['total_amount'], 0, ',', '.')." ({$summary['sales_this_month']['total_orders']} orders)",
                "- Produksi Pending: {$summary['production']['pending']}",
                "- Preparation Pending: {$summary['preparation']['pending']}",
            ];

            return implode("\n", $lines);
        } catch (\Exception $e) {
            Log::warning('Failed to get context data for assistant', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Handle simple queries without AI (fallback)
     */
    private function handleSimpleQuery(string $message, User $user): array
    {
        $messageLower = strtolower($message);

        // Greeting
        if (preg_match('/^(halo|hai|hi|hello|selamat)/i', $message)) {
            return [
                'content' => "Halo {$user->name}! 👋 Saya Fabriku Assistant. Ada yang bisa saya bantu hari ini?",
            ];
        }

        // Sales queries
        if (str_contains($messageLower, 'penjualan') || str_contains($messageLower, 'sales')) {
            $summary = $this->dataService->getSalesSummary('month');

            return [
                'content' => "📊 **Ringkasan Penjualan Bulan Ini**\n\n".
                    '- Total: Rp '.number_format($summary['total_amount'], 0, ',', '.')."\n".
                    "- Jumlah Order: {$summary['total_orders']}\n".
                    '- Rata-rata: Rp '.number_format($summary['average_order'], 0, ',', '.')."\n".
                    '- Outstanding: Rp '.number_format($summary['outstanding'], 0, ',', '.'),
            ];
        }

        // Low stock queries
        if (str_contains($messageLower, 'stok') && (str_contains($messageLower, 'habis') || str_contains($messageLower, 'rendah') || str_contains($messageLower, 'low'))) {
            $lowStock = $this->dataService->getLowStockMaterials(5);

            if (empty($lowStock)) {
                return ['content' => '✅ Tidak ada material dengan stok rendah saat ini.'];
            }

            $lines = ["⚠️ **Material dengan Stok Rendah**\n"];
            foreach ($lowStock as $item) {
                $lines[] = "- **{$item['name']}**: {$item['stock']} {$item['unit']} (min: {$item['minimum']})";
            }

            return ['content' => implode("\n", $lines)];
        }

        // Default response
        return [
            'content' => "Maaf, saya belum bisa memproses permintaan Anda secara otomatis saat ini. 🤔\n\n".
                "Berikut yang bisa saya bantu:\n".
                "- Informasi penjualan (coba: \"berapa penjualan bulan ini?\")\n".
                "- Cek stok rendah (coba: \"material apa yang stok rendah?\")\n".
                "- Navigasi menu aplikasi\n\n".
                'Silakan coba pertanyaan lain atau hubungi support untuk bantuan lebih lanjut.',
        ];
    }

    /**
     * Check if user can access assistant (demo/expired checks)
     */
    private function checkAccessibility(User $user): array
    {
        $tenant = $user->tenant;

        // Demo account emails that should not have assistant access
        $demoEmails = [
            'admin@konveksi.com',
            'admin@kuemama.com',
            'admin@crafty.com',
            'admin@glowbeauty.com',
        ];

        // Check if this is a demo account
        if (in_array($user->email, $demoEmails)) {
            return [
                'allowed' => false,
                'type' => 'demo_account',
                'message' => '🤖 Fabriku Assistant tidak tersedia di akun demo. '.
                    'Untuk mencoba fitur AI Assistant, silakan daftar akun baru dan nikmati Free Trial 30 hari dengan kuota 50 pesan per hari!',
            ];
        }

        // Check if subscription is expired
        if (! $tenant->isActive()) {
            return [
                'allowed' => false,
                'type' => 'subscription_expired',
                'message' => '⏰ Masa aktif langganan Anda telah berakhir. '.
                    'Fabriku Assistant tidak dapat digunakan sampai Anda memperpanjang langganan. '.
                    'Silakan perpanjang langganan di menu Membership untuk melanjutkan menggunakan fitur ini.',
            ];
        }

        return ['allowed' => true];
    }

    /**
     * Check rate limits for user
     */
    private function checkRateLimit(User $user): array
    {
        $plan = $user->tenant->subscription_plan ?? 'trial';

        // Rate limits per day based on subscription plan
        // trial = Free Trial (30 days)
        // basic = Full Member (Rp 25.000/bulan)
        // pro = Pro Member (Rp 35.000/bulan) - 2.5x more quota than basic
        // enterprise = unlimited
        $limits = [
            'trial' => 50,
            'basic' => 200,
            'pro' => 500,
            'enterprise' => PHP_INT_MAX,
        ];

        $maxMessages = $limits[$plan] ?? 50;

        if (AssistantUsage::hasExceededLimit($user->tenant_id, $user->id, $maxMessages)) {
            return [
                'allowed' => false,
                'message' => "Anda telah mencapai batas {$maxMessages} pesan per hari untuk paket {$plan}. ".
                    'Upgrade paket Anda untuk mendapatkan lebih banyak akses.',
            ];
        }

        return ['allowed' => true];
    }

    /**
     * Update usage statistics
     */
    private function updateUsage(User $user, int $tokensInput, int $tokensOutput): void
    {
        $usage = AssistantUsage::getOrCreateToday($user->tenant_id, $user->id);
        $usage->incrementMessage($tokensInput, $tokensOutput);
    }

    /**
     * Get conversation history for a user
     */
    public function getConversationHistory(User $user, string $channel = 'web', int $limit = 50): array
    {
        $conversation = AssistantConversation::where('user_id', $user->id)
            ->where('channel', $channel)
            ->where('status', 'active')
            ->first();

        if (! $conversation) {
            return [];
        }

        return $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'role' => $m->role,
                'content' => $m->content,
                'created_at' => $m->created_at->toISOString(),
            ])
            ->toArray();
    }

    /**
     * Clear conversation history
     */
    public function clearConversation(User $user, string $channel = 'web'): bool
    {
        $conversation = AssistantConversation::where('user_id', $user->id)
            ->where('channel', $channel)
            ->where('status', 'active')
            ->first();

        if ($conversation) {
            $conversation->archiveAndCreateNew();

            return true;
        }

        return false;
    }

    /**
     * Get usage stats for a user
     */
    public function getUsageStats(User $user): array
    {
        $todayUsage = AssistantUsage::getDailyUsage($user->tenant_id, $user->id);

        $plan = $user->tenant->subscription_plan ?? 'trial';
        $limits = [
            'trial' => 50,
            'basic' => 200,
            'pro' => 1000,
            'enterprise' => PHP_INT_MAX,
        ];

        $maxMessages = $limits[$plan] ?? 50;

        return [
            'today' => $todayUsage,
            'limit' => $maxMessages,
            'remaining' => max(0, $maxMessages - $todayUsage['message_count']),
            'plan' => $plan,
        ];
    }
}
