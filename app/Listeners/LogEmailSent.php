<?php

namespace App\Listeners;

use App\Models\EmailLog;
use Illuminate\Mail\Events\MessageSent;

class LogEmailSent
{
    public function handle(MessageSent $event): void
    {
        $message = $event->message;
        $to = $message->getTo();
        $toEmail = ! empty($to) ? array_key_first($to) : null;

        if ($toEmail) {
            $emailLog = EmailLog::where('to_email', $toEmail)
                ->where('status', 'sending')
                ->latest()
                ->first();

            if ($emailLog) {
                $emailLog->markAsSent();
            }
        }
    }
}
