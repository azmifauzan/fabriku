<?php

namespace App\Listeners;

use App\Events\PaymentProofUploaded;
use App\Services\Telegram\TelegramService;
use Illuminate\Support\Facades\Log;

class SendTelegramPaymentNotification
{
    public function __construct(private TelegramService $telegram) {}

    public function handle(PaymentProofUploaded $event): void
    {
        if (! $this->telegram->isConfigured()) {
            Log::warning('Telegram not configured, skipping payment notification');

            return;
        }

        $payment = $event->payment;
        $tenant = $payment->tenant;

        $planLabel = match ($payment->plan_type) {
            'yearly' => 'Tahunan (12 bulan)',
            'monthly' => 'Bulanan',
            default => ucfirst($payment->plan_type),
        };

        $message = "💰 <b>Bukti Pembayaran Baru!</b>\n\n".
            "🏢 <b>Bisnis:</b> {$tenant->name}\n".
            "📦 <b>Paket:</b> {$planLabel}\n".
            '💵 <b>Jumlah:</b> Rp '.number_format($payment->amount, 0, ',', '.')."\n".
            "📅 <b>Tanggal:</b> {$payment->created_at->format('d M Y H:i')}\n".
            "⏳ <b>Status:</b> Menunggu Verifikasi\n\n".
            "⚡ <b>Segera verifikasi pembayaran!</b>\n\n".
            '🔗 <a href="'.url('/admin/payments').'">Verifikasi di Admin Panel</a>';

        $result = $this->telegram->sendAdminNotification($message);

        if (! $result['success']) {
            Log::error('Failed to send Telegram notification for payment', [
                'payment_id' => $payment->id,
                'tenant_id' => $tenant->id,
                'error' => $result['error'] ?? 'Unknown error',
            ]);
        }
    }
}
