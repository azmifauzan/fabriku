<?php

namespace App\Http\Controllers;

use App\Services\Telegram\TelegramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TelegramController extends Controller
{
    public function __construct(private TelegramService $telegram) {}

    /**
     * Show Telegram settings page
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Settings/Telegram', [
            'isConnected' => $user->hasTelegramConnected(),
            'telegramChatId' => $user->telegram_chat_id,
            'connectToken' => $user->telegram_connect_token,
            'tokenExpiresAt' => $user->telegram_connect_token_expires_at?->toISOString(),
        ]);
    }

    /**
     * Get current Telegram connection status (API)
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'connected' => $user->hasTelegramConnected(),
            'chat_id' => $user->telegram_chat_id,
            'bot_username' => config('telegram.bot_username'),
            'telegram_enabled' => $this->telegram->isConfigured(),
        ]);
    }

    /**
     * Generate a connect token for linking Telegram
     */
    public function generateToken(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasTelegramConnected()) {
            return back()->with('error', 'Telegram sudah terhubung. Putuskan koneksi terlebih dahulu.');
        }

        $user->generateTelegramConnectToken();

        return back()->with('success', 'Token berhasil dibuat!');
    }

    /**
     * Disconnect Telegram from account
     */
    public function disconnect(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasTelegramConnected()) {
            return back()->with('error', 'Telegram tidak terhubung.');
        }

        // Send disconnection notice to Telegram
        $this->telegram->sendMessage(
            $user->telegram_chat_id,
            "🔌 Koneksi Telegram telah diputus oleh pengguna dari aplikasi Fabriku.\n\n".
            'Ketik /start jika ingin menghubungkan kembali.'
        );

        $user->disconnectTelegram();

        return back()->with('success', 'Telegram berhasil diputuskan.');
    }

    /**
     * Test send message (for debugging)
     */
    public function testMessage(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasTelegramConnected()) {
            return back()->with('error', 'Telegram tidak terhubung.');
        }

        $result = $this->telegram->sendMessage(
            $user->telegram_chat_id,
            "🧪 <b>Pesan Tes</b>\n\nKoneksi Telegram Anda berfungsi dengan baik!"
        );

        if ($result['success']) {
            return back()->with('success', 'Pesan test berhasil dikirim!');
        }

        return back()->with('error', 'Gagal mengirim pesan test.');
    }
}
