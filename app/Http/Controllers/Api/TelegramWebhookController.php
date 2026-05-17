<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Assistant\AssistantService;
use App\Services\Telegram\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramWebhookController extends Controller
{
    public function __construct(
        private TelegramService $telegram,
        private AssistantService $assistant
    ) {}

    /**
     * Handle incoming Telegram webhook
     */
    public function handle(Request $request): JsonResponse
    {
        // Verify webhook request
        $secretToken = $request->header('X-Telegram-Bot-Api-Secret-Token');
        if (! $this->telegram->verifyWebhookRequest($secretToken)) {
            Log::warning('Telegram webhook verification failed');

            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $update = $request->all();

        Log::info('Telegram webhook received', [
            'update_id' => $update['update_id'] ?? null,
            'type' => isset($update['message']) ? 'message' : (isset($update['callback_query']) ? 'callback' : 'other'),
        ]);

        // Handle message updates
        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        }

        // Handle callback queries (for inline keyboards)
        if (isset($update['callback_query'])) {
            $this->handleCallbackQuery($update['callback_query']);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Handle incoming message
     */
    private function handleMessage(array $message): void
    {
        $chatId = (string) $message['chat']['id'];
        $text = $message['text'] ?? '';
        $fromUser = $message['from'] ?? [];

        Log::info('Processing Telegram message', [
            'chat_id' => $chatId,
            'from' => $fromUser['username'] ?? $fromUser['id'] ?? 'unknown',
        ]);

        // Handle commands
        if (str_starts_with($text, '/')) {
            $this->handleCommand($chatId, $text, $fromUser);

            return;
        }

        // Handle connect code (e.g., "CONNECT ABC12345" or just "ABC12345")
        if ($this->isConnectCode($text)) {
            $this->handleConnectCode($chatId, $text);

            return;
        }

        // Handle regular message - forward to assistant
        $this->handleAssistantMessage($chatId, $text);
    }

    /**
     * Handle bot commands
     */
    private function handleCommand(string $chatId, string $text, array $fromUser): void
    {
        $command = strtolower(explode(' ', $text)[0]);

        match ($command) {
            '/start' => $this->handleStartCommand($chatId, $fromUser),
            '/help' => $this->handleHelpCommand($chatId),
            '/status' => $this->handleStatusCommand($chatId),
            '/disconnect' => $this->handleDisconnectCommand($chatId),
            default => $this->telegram->sendMessage($chatId, '❓ Perintah tidak dikenal. Ketik /help untuk bantuan.'),
        };
    }

    /**
     * Handle /start command
     */
    private function handleStartCommand(string $chatId, array $fromUser): void
    {
        // Check if this chat is already connected to a user
        $user = User::where('telegram_chat_id', $chatId)->first();

        if ($user) {
            $this->telegram->sendMessage(
                $chatId,
                "👋 Halo <b>{$user->name}</b>!\n\n".
                "Akun Telegram Anda sudah terhubung dengan Fabriku.\n".
                "Silakan ketik pesan untuk mulai chat dengan assistant.\n\n".
                'Ketik /help untuk melihat daftar perintah.'
            );
        } else {
            $this->telegram->sendWelcomeMessage($chatId);
        }
    }

    /**
     * Handle /help command
     */
    private function handleHelpCommand(string $chatId): void
    {
        $user = User::where('telegram_chat_id', $chatId)->first();

        if ($user) {
            $message = "📚 <b>Bantuan Fabriku Assistant</b>\n\n".
                "<b>Perintah:</b>\n".
                "• /start - Mulai bot\n".
                "• /help - Tampilkan bantuan\n".
                "• /status - Lihat status akun\n".
                "• /disconnect - Putuskan koneksi Telegram\n\n".
                "<b>Fitur Chat:</b>\n".
                "Cukup ketik pertanyaan Anda dan assistant akan menjawab!\n\n".
                "Contoh:\n".
                "• \"Berapa penjualan bulan ini?\"\n".
                "• \"Material apa yang stok rendah?\"\n".
                '• "Ringkasan produksi hari ini"';
        } else {
            $message = "📚 <b>Bantuan</b>\n\n".
                "Untuk menggunakan Fabriku Assistant via Telegram, hubungkan akun Anda terlebih dahulu:\n\n".
                "1️⃣ Login ke Fabriku di https://fabriku.my.id\n".
                "2️⃣ Buka Pengaturan → Telegram\n".
                "3️⃣ Klik Hubungkan Telegram dan salin kode\n".
                "4️⃣ Kirim kode ke sini dengan format:\n".
                '   <code>CONNECT KODE_ANDA</code>';
        }

        $this->telegram->sendMessage($chatId, $message);
    }

    /**
     * Handle /status command
     */
    private function handleStatusCommand(string $chatId): void
    {
        $user = User::where('telegram_chat_id', $chatId)->first();

        if (! $user) {
            $this->telegram->sendMessage(
                $chatId,
                "❌ Akun Telegram Anda belum terhubung dengan Fabriku.\n\nKetik /start untuk instruksi cara menghubungkan."
            );

            return;
        }

        $tenant = $user->tenant;
        $expiresAt = $tenant->subscription_expires_at;
        $isExpired = $expiresAt ? $expiresAt->isPast() : false;

        $message = "📊 <b>Status Akun</b>\n\n".
            "👤 <b>Nama:</b> {$user->name}\n".
            "📧 <b>Email:</b> {$user->email}\n".
            "🏢 <b>Bisnis:</b> {$tenant->name}\n".
            '📦 <b>Paket:</b> '.ucfirst($tenant->subscription_plan ?? 'trial')."\n".
            '📅 <b>Berlaku:</b> '.($expiresAt ? $expiresAt->format('d M Y') : '-')."\n".
            '⚡ <b>Status:</b> '.($isExpired ? '❌ Expired' : '✅ Aktif');

        $this->telegram->sendMessage($chatId, $message);
    }

    /**
     * Handle /disconnect command
     */
    private function handleDisconnectCommand(string $chatId): void
    {
        $user = User::where('telegram_chat_id', $chatId)->first();

        if (! $user) {
            $this->telegram->sendMessage(
                $chatId,
                '❓ Akun Telegram ini tidak terhubung dengan akun Fabriku manapun.'
            );

            return;
        }

        $user->disconnectTelegram();

        $this->telegram->sendMessage(
            $chatId,
            "✅ Koneksi Telegram berhasil diputus.\n\n".
            "Akun Fabriku Anda tidak lagi terhubung dengan Telegram.\n".
            'Ketik /start jika ingin menghubungkan kembali.'
        );
    }

    /**
     * Check if text is a connect code
     */
    private function isConnectCode(string $text): bool
    {
        $text = strtoupper(trim($text));

        // Support "CONNECT XXXXXXXX" or just "XXXXXXXX"
        if (str_starts_with($text, 'CONNECT ')) {
            $code = substr($text, 8);

            return preg_match('/^[A-F0-9]{8}$/', $code);
        }

        return preg_match('/^[A-F0-9]{8}$/', $text);
    }

    /**
     * Handle connect code
     */
    private function handleConnectCode(string $chatId, string $text): void
    {
        $text = strtoupper(trim($text));

        // Extract code
        $code = str_starts_with($text, 'CONNECT ')
            ? substr($text, 8)
            : $text;

        // Check if this chat is already connected
        $existingUser = User::where('telegram_chat_id', $chatId)->first();
        if ($existingUser) {
            $this->telegram->sendMessage(
                $chatId,
                "⚠️ Telegram ini sudah terhubung dengan akun <b>{$existingUser->name}</b>.\n\n".
                'Ketik /disconnect terlebih dahulu jika ingin menghubungkan ke akun lain.'
            );

            return;
        }

        // Find user by connect token
        $user = User::findByTelegramConnectToken($code);

        if (! $user) {
            $this->telegram->sendMessage(
                $chatId,
                "❌ Kode tidak valid atau sudah kedaluwarsa.\n\n".
                "Silakan buat kode baru di aplikasi Fabriku:\n".
                'Pengaturan → Telegram → Hubungkan Telegram'
            );

            return;
        }

        // Connect the user
        $user->connectTelegram($chatId);

        // Send success message
        $this->telegram->sendConnectSuccessMessage($chatId, $user);

        Log::info('User connected Telegram', [
            'user_id' => $user->id,
            'chat_id' => $chatId,
        ]);
    }

    /**
     * Handle regular message - forward to assistant
     */
    private function handleAssistantMessage(string $chatId, string $text): void
    {
        // Find user by chat ID
        $user = User::where('telegram_chat_id', $chatId)->first();

        if (! $user) {
            $this->telegram->sendMessage(
                $chatId,
                "❌ Akun Telegram Anda belum terhubung.\n\n".
                "Hubungkan terlebih dahulu untuk menggunakan Fabriku Assistant.\n".
                'Ketik /start untuk instruksi.'
            );

            return;
        }

        // Process message through assistant
        $result = $this->assistant->processMessage($user, $text, 'telegram');

        if (! $result['success']) {
            $errorMessage = match ($result['type'] ?? 'error') {
                'rate_limit' => "⏳ {$result['message']}",
                'demo_account' => "🔒 {$result['message']}",
                'subscription_expired' => "⏰ {$result['message']}",
                default => "❌ {$result['message']}",
            };
            $this->telegram->sendMessage($chatId, $errorMessage);

            return;
        }

        // Send response
        $this->telegram->sendMessage($chatId, $result['message']);
    }

    /**
     * Handle callback query (inline keyboard buttons)
     */
    private function handleCallbackQuery(array $callbackQuery): void
    {
        Log::info('Telegram callback query received', ['id' => $callbackQuery['id'] ?? null]);
    }
}
