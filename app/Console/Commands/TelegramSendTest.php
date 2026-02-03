<?php

namespace App\Console\Commands;

use App\Services\Telegram\TelegramService;
use Illuminate\Console\Command;

class TelegramSendTest extends Command
{
    protected $signature = 'telegram:send-test {--message= : Custom test message}';

    protected $description = 'Send a test message to admin Telegram';

    public function __construct(private TelegramService $telegram)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if (! $this->telegram->isConfigured()) {
            $this->error('Telegram bot token is not configured. Set TELEGRAM_BOT_TOKEN in .env');

            return self::FAILURE;
        }

        $adminChatId = config('telegram.admin_chat_id');

        if (empty($adminChatId)) {
            $this->error('Admin chat ID is not configured. Set TELEGRAM_ADMIN_CHAT_ID in .env');

            return self::FAILURE;
        }

        $message = $this->option('message') ?? "🧪 <b>Pesan Tes dari Fabriku</b>\n\n".
            "Ini adalah pesan tes untuk memverifikasi koneksi Telegram Bot.\n\n".
            '⏰ Dikirim pada: '.now()->format('d M Y H:i:s')."\n".
            '🌐 Server: '.config('app.url');

        $this->info('Sending test message to admin...');

        $result = $this->telegram->sendAdminNotification($message);

        if ($result['success']) {
            $this->info('Test message sent successfully!');
            $this->line("Message ID: {$result['message_id']}");

            return self::SUCCESS;
        }

        $this->error('Failed to send message: '.($result['error'] ?? 'Unknown error'));

        return self::FAILURE;
    }
}
