<?php

namespace App\Jobs;

use App\Models\BusinessSite;
use App\Models\Lead;
use App\Models\SalesOrder;
use App\Models\Scopes\TenantScope;
use App\Models\User;
use App\Services\Telegram\TelegramService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyStorefrontRequest implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(public int $siteId, public string $kind, public int $recordId) {}

    public function handle(TelegramService $telegram): void
    {
        $site = BusinessSite::find($this->siteId);
        if (! $site) {
            return;
        }
        $record = $this->kind === 'lead'
            ? Lead::withoutGlobalScope(TenantScope::class)->where('tenant_id', $site->tenant_id)->find($this->recordId)
            : SalesOrder::withoutGlobalScope(TenantScope::class)->where('tenant_id', $site->tenant_id)->find($this->recordId);
        if (! $record) {
            return;
        }

        $recipients = $site->recipients()->where('users.tenant_id', $site->tenant_id)->where('users.is_active', true)->get();
        if ($recipients->isEmpty()) {
            $recipients = User::withoutGlobalScope(TenantScope::class)
                ->where('tenant_id', $site->tenant_id)->where('role', 'admin')->where('is_active', true)->get();
        }
        $title = $this->kind === 'lead' ? 'Prospek baru dari website' : 'Pesanan baru dari website';
        $reference = $this->kind === 'lead' ? "Prospek #{$record->id}" : $record->order_number;
        $link = url('/website#permintaan');
        $siteName = $site->profile['name'] ?? $site->tenant?->name ?? 'usaha Anda';
        $body = "{$title} untuk {$siteName}\n{$reference}\nBuka {$link} untuk melihat dan menindaklanjuti.\n";
        foreach ($recipients as $user) {
            try {
                Mail::raw($body, fn ($message) => $message->to($user->email)->subject("Fabriku · {$title}"));
            } catch (\Throwable $e) {
                Log::warning('Storefront email notification failed', ['site_id' => $site->id, 'user_id' => $user->id, 'kind' => $this->kind, 'error' => $e->getMessage()]);
                if ($record instanceof Lead) {
                    $record->update(['notification_failed_at' => now()]);
                }
            }
            if ($user->telegram_chat_id && $telegram->isConfigured()) {
                $result = $telegram->sendMessage($user->telegram_chat_id, e($title)."\n".e($reference)."\n".e($link));
                if (! ($result['success'] ?? false)) {
                    Log::warning('Storefront Telegram notification failed', ['site_id' => $site->id, 'user_id' => $user->id, 'kind' => $this->kind]);
                    if ($record instanceof Lead) {
                        $record->update(['notification_failed_at' => now()]);
                    }
                }
            }
        }
    }
}
