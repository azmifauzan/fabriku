<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Reset all demo tenants hourly across all environments
Schedule::command('demo:reset')
    ->hourly()
    ->withoutOverlapping();

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

// Send weekly campaign emails to tenant admins every Monday at 10:00 WIB
Schedule::command('campaign:send-weekly')
    ->weeklyOn(1, '10:00')
    ->timezone('Asia/Jakarta')
    ->withoutOverlapping()
    ->onSuccess(function () {
        Log::info('Scheduled campaign:send-weekly completed successfully');
    })
    ->onFailure(function () {
        Log::error('Scheduled campaign:send-weekly failed');
    });

