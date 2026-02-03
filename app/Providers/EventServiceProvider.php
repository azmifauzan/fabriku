<?php

namespace App\Providers;

use App\Events\PaymentProofUploaded;
use App\Events\TenantRegistered;
use App\Listeners\LogEmailSending;
use App\Listeners\LogEmailSent;
use App\Listeners\SendTelegramNewUserNotification;
use App\Listeners\SendTelegramPaymentNotification;
use App\Listeners\SendWelcomeEmail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void {}

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Event::listen(
            Verified::class,
            SendWelcomeEmail::class,
        );

        Event::listen(
            TenantRegistered::class,
            SendTelegramNewUserNotification::class,
        );

        Event::listen(
            PaymentProofUploaded::class,
            SendTelegramPaymentNotification::class,
        );

        // Email logging
        Event::listen(
            MessageSending::class,
            LogEmailSending::class,
        );

        Event::listen(
            MessageSent::class,
            LogEmailSent::class,
        );
    }
}
