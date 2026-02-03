<?php

namespace App\Listeners;

use App\Models\EmailLog;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Auth;

class LogEmailSending
{
    public function handle(MessageSending $event): void
    {
        $message = $event->message;
        $to = $message->getTo();
        $toEmail = ! empty($to) ? array_key_first($to) : null;
        $toName = ! empty($to) && $toEmail ? ($to[$toEmail] ?? null) : null;

        $from = $message->getFrom();
        $fromEmail = ! empty($from) ? array_key_first($from) : null;
        $fromName = ! empty($from) && $fromEmail ? ($from[$fromEmail] ?? null) : null;

        $user = Auth::user();

        EmailLog::create([
            'tenant_id' => $user?->tenant_id,
            'user_id' => $user?->id,
            'mailable_class' => $event->data['__laravel_notification'] ?? null,
            'subject' => $message->getSubject() ?? 'No Subject',
            'to_email' => $toEmail ?? 'unknown',
            'to_name' => $toName,
            'from_email' => $fromEmail,
            'from_name' => $fromName,
            'status' => 'sending',
        ]);
    }
}
