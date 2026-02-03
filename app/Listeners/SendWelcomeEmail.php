<?php

namespace App\Listeners;

use App\Mail\WelcomeEmail;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWelcomeEmail
{
    /**
     * Handle the event.
     */
    public function handle(Verified $event): void
    {
        $user = $event->user;
        $tenant = $user->tenant;

        // Prevent duplicate welcome emails using cache lock
        $cacheKey = "welcome_email_sent_{$user->id}";

        if (Cache::has($cacheKey)) {
            Log::info('Welcome email already sent for user', ['user_id' => $user->id]);

            return;
        }

        // Set cache for 1 hour to prevent duplicates
        Cache::put($cacheKey, true, now()->addHour());

        Mail::to($user->email)
            ->send(new WelcomeEmail($user, $tenant));

        Log::info('Welcome email sent to user', ['user_id' => $user->id, 'email' => $user->email]);
    }
}
