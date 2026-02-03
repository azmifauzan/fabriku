<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule demo data reset every hour
Schedule::command('demo:reset')
    ->hourly()
    ->withoutOverlapping()
    ->onSuccess(function () {
        Log::info('Scheduled demo:reset completed successfully');
    })
    ->onFailure(function () {
        Log::error('Scheduled demo:reset failed');
    })
    ->before(function () {
        Log::info('Starting scheduled demo:reset');
    });

// Send trial reminder emails daily at 9 AM
Schedule::command('trial:send-reminders')
    ->dailyAt('09:00')
    ->withoutOverlapping()
    ->onSuccess(function () {
        Log::info('Scheduled trial:send-reminders completed successfully');
    })
    ->onFailure(function () {
        Log::error('Scheduled trial:send-reminders failed');
    });
