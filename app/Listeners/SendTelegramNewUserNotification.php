<?php

namespace App\Listeners;

use App\Events\TenantRegistered;
use App\Services\Telegram\TelegramService;
use Illuminate\Support\Facades\Log;

class SendTelegramNewUserNotification
{
    public function __construct(private TelegramService $telegram) {}

    public function handle(TenantRegistered $event): void
    {
        if (! $this->telegram->isConfigured()) {
            Log::warning('Telegram not configured, skipping new user notification');

            return;
        }

        $tenant = $event->tenant;
        $user = $event->user;

        $message = "🆕 <b>User Baru Terdaftar!</b>\n\n".
            "🏢 <b>Bisnis:</b> {$tenant->name}\n".
            '📦 <b>Kategori:</b> '.ucfirst($tenant->business_category)."\n".
            "👤 <b>Nama:</b> {$user->name}\n".
            "📧 <b>Email:</b> {$user->email}\n".
            "📅 <b>Tanggal:</b> {$tenant->created_at->format('d M Y H:i')}\n".
            "⏳ <b>Trial:</b> 30 hari (s/d {$tenant->subscription_expires_at->format('d M Y')})\n\n".
            '🔗 <a href="'.url('/admin/tenants').'">Lihat di Admin Panel</a>';

        $result = $this->telegram->sendAdminNotification($message);

        if (! $result['success']) {
            Log::error('Failed to send Telegram notification for new user', [
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'error' => $result['error'] ?? 'Unknown error',
            ]);
        }
    }
}
