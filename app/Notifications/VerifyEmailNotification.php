<?php

namespace App\Notifications;

use App\Mail\VerifyEmail;
use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class VerifyEmailNotification extends BaseVerifyEmail implements ShouldQueue
{
    use Queueable;

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): VerifyEmail
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return new VerifyEmail(
            verificationUrl: $verificationUrl,
            userName: $notifiable->name
        );
    }
}
