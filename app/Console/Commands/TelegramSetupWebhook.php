<?php

namespace App\Console\Commands;

use App\Services\Telegram\TelegramService;
use Illuminate\Console\Command;

class TelegramSetupWebhook extends Command
{
    protected $signature = 'telegram:setup-webhook {--url= : Custom webhook URL} {--delete : Delete webhook instead of setting it}';

    protected $description = 'Setup or delete Telegram webhook';

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

        if ($this->option('delete')) {
            $this->info('Deleting webhook...');
            $result = $this->telegram->deleteWebhook();

            if ($result['success']) {
                $this->info('Webhook deleted successfully.');

                return self::SUCCESS;
            }

            $this->error('Failed to delete webhook: '.($result['error'] ?? 'Unknown error'));

            return self::FAILURE;
        }

        $url = $this->option('url') ?? config('telegram.webhook_url');

        $this->info("Setting webhook to: {$url}");

        $result = $this->telegram->setWebhook($url);

        if ($result['success']) {
            $this->info('Webhook set successfully!');
            $this->table(['Setting', 'Value'], [
                ['Webhook URL', $result['webhook_url']],
                ['Bot Username', config('telegram.bot_username')],
            ]);

            return self::SUCCESS;
        }

        $this->error('Failed to set webhook: '.($result['error'] ?? 'Unknown error'));

        return self::FAILURE;
    }
}
