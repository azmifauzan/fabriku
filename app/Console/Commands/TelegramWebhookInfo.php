<?php

namespace App\Console\Commands;

use App\Services\Telegram\TelegramService;
use Illuminate\Console\Command;

class TelegramWebhookInfo extends Command
{
    protected $signature = 'telegram:webhook-info';

    protected $description = 'Get current Telegram webhook information';

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

        $this->info('Getting webhook info...');

        $result = $this->telegram->getWebhookInfo();

        if (! $result['success']) {
            $this->error('Failed to get webhook info: '.($result['error'] ?? 'Unknown error'));

            return self::FAILURE;
        }

        $info = $result['info'];

        $this->table(['Setting', 'Value'], [
            ['URL', $info['url'] ?? '(not set)'],
            ['Has Custom Certificate', $info['has_custom_certificate'] ? 'Yes' : 'No'],
            ['Pending Update Count', $info['pending_update_count'] ?? 0],
            ['IP Address', $info['ip_address'] ?? '-'],
            ['Last Error Date', isset($info['last_error_date']) ? date('Y-m-d H:i:s', $info['last_error_date']) : '-'],
            ['Last Error Message', $info['last_error_message'] ?? '-'],
            ['Max Connections', $info['max_connections'] ?? '-'],
            ['Allowed Updates', isset($info['allowed_updates']) ? implode(', ', $info['allowed_updates']) : '-'],
        ]);

        return self::SUCCESS;
    }
}
