<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StaffAccountCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected string $plainPassword,
        protected string $tenantName,
        protected string $roleName,
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Akun Staff Anda Telah Dibuat - '.config('app.name'))
            ->greeting('Halo '.$notifiable->name.'!')
            ->line('Akun staff Anda telah dibuat di **'.$this->tenantName.'** pada platform '.config('app.name').'.')
            ->line('Berikut adalah detail login Anda:')
            ->line('**Email:** '.$notifiable->email)
            ->line('**Password:** '.$this->plainPassword)
            ->line('**Role:** '.$this->roleName)
            ->action('Login Sekarang', url('/login'))
            ->line('Harap segera ubah password Anda setelah login pertama kali.')
            ->line('Terima kasih telah bergabung!');
    }
}
