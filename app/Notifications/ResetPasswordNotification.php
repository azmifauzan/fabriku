<?php

namespace App\Notifications;

use App\Mail\ResetPasswordEmail;
use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;

class ResetPasswordNotification extends BaseResetPassword
{
    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): ResetPasswordEmail
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return new ResetPasswordEmail(
            resetUrl: $resetUrl,
            userName: $notifiable->name
        );
    }
}
