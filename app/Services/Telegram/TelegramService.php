<?php

namespace App\Services\Telegram;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private ?string $botToken;

    private string $apiBaseUrl;

    public function __construct()
    {
        $this->botToken = config('telegram.bot_token');
        $this->apiBaseUrl = 'https://api.telegram.org/bot'.($this->botToken ?? '');
    }

    /**
     * Check if Telegram is configured
     */
    public function isConfigured(): bool
    {
        return ! empty($this->botToken);
    }

    /**
     * Send a message to a specific chat
     */
    public function sendMessage(string $chatId, string $text, array $options = []): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'error' => 'Telegram not configured'];
        }

        $params = array_merge([
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ], $options);

        try {
            $http = Http::timeout(30);

            // Disable SSL verification in local development
            if (config('app.env') === 'local') {
                $http = $http->withOptions(['verify' => false]);
            }

            $response = $http->post("{$this->apiBaseUrl}/sendMessage", $params);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message_id' => $response->json('result.message_id'),
                ];
            }

            Log::warning('Telegram sendMessage failed', [
                'chat_id' => $chatId,
                'response' => $response->json(),
            ]);

            return [
                'success' => false,
                'error' => $response->json('description', 'Unknown error'),
            ];
        } catch (\Exception $e) {
            Log::error('Telegram sendMessage exception', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send a message to admin
     */
    public function sendAdminNotification(string $text): array
    {
        $adminChatId = config('telegram.admin_chat_id');

        if (empty($adminChatId)) {
            return ['success' => false, 'error' => 'Admin chat ID not configured'];
        }

        return $this->sendMessage($adminChatId, $text);
    }

    /**
     * Set webhook URL for the bot
     */
    public function setWebhook(?string $url = null): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'error' => 'Telegram not configured'];
        }

        $webhookUrl = $url ?? config('telegram.webhook_url');
        $secret = config('telegram.webhook_secret');

        $params = [
            'url' => $webhookUrl,
            'allowed_updates' => ['message', 'callback_query'],
        ];

        if ($secret) {
            $params['secret_token'] = $secret;
        }

        try {
            $http = Http::timeout(30);

            if (config('app.env') === 'local') {
                $http = $http->withOptions(['verify' => false]);
            }

            $response = $http->post("{$this->apiBaseUrl}/setWebhook", $params);

            if ($response->successful() && $response->json('ok')) {
                return ['success' => true, 'webhook_url' => $webhookUrl];
            }

            return [
                'success' => false,
                'error' => $response->json('description', 'Unknown error'),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Remove webhook
     */
    public function deleteWebhook(): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'error' => 'Telegram not configured'];
        }

        try {
            $http = Http::timeout(30);

            if (config('app.env') === 'local') {
                $http = $http->withOptions(['verify' => false]);
            }

            $response = $http->post("{$this->apiBaseUrl}/deleteWebhook");

            return [
                'success' => $response->successful() && $response->json('ok'),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get webhook info
     */
    public function getWebhookInfo(): array
    {
        if (! $this->isConfigured()) {
            return ['success' => false, 'error' => 'Telegram not configured'];
        }

        try {
            $http = Http::timeout(30);

            if (config('app.env') === 'local') {
                $http = $http->withOptions(['verify' => false]);
            }

            $response = $http->get("{$this->apiBaseUrl}/getWebhookInfo");

            if ($response->successful()) {
                return ['success' => true, 'info' => $response->json('result')];
            }

            return ['success' => false, 'error' => $response->json('description', 'Unknown error')];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Verify webhook request comes from Telegram
     */
    public function verifyWebhookRequest(?string $secretToken): bool
    {
        $expectedSecret = config('telegram.webhook_secret');

        // If no secret is configured, allow all requests (development mode)
        if (empty($expectedSecret)) {
            return true;
        }

        return $secretToken === $expectedSecret;
    }

    /**
     * Send connect success message to user
     */
    public function sendConnectSuccessMessage(string $chatId, User $user): array
    {
        $message = "✅ <b>Koneksi Berhasil!</b>\n\n".
            "Akun Fabriku Anda telah terhubung:\n".
            "👤 Nama: {$user->name}\n".
            "📧 Email: {$user->email}\n".
            "🏢 Bisnis: {$user->tenant->name}\n\n".
            "Anda sekarang bisa menggunakan Fabriku Assistant langsung dari Telegram!\n\n".
            "Ketik pesan Anda untuk mulai chat dengan assistant, atau gunakan:\n".
            "• /help - Bantuan\n".
            "• /status - Cek status akun\n".
            '• /disconnect - Putuskan koneksi';

        return $this->sendMessage($chatId, $message);
    }

    /**
     * Send welcome message for new users
     */
    public function sendWelcomeMessage(string $chatId): array
    {
        $message = "👋 <b>Selamat datang di Fabriku Assistant!</b>\n\n".
            "Untuk menghubungkan akun Telegram Anda dengan akun Fabriku, silakan:\n\n".
            "1️⃣ Login ke aplikasi Fabriku di https://fabriku.my.id\n".
            "2️⃣ Buka menu <b>Pengaturan → Telegram</b>\n".
            "3️⃣ Klik tombol <b>Hubungkan Telegram</b>\n".
            "4️⃣ Salin kode yang muncul\n".
            "5️⃣ Kirim kode tersebut ke bot ini\n\n".
            "Contoh: <code>CONNECT ABC12345</code>\n\n".
            'Setelah terhubung, Anda bisa langsung chat dengan Fabriku Assistant dari sini!';

        return $this->sendMessage($chatId, $message);
    }
}
