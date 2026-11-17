<?php

use App\Services\Campaign\CampaignSchedule;
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

// Campaign emails to tenant admins; schedule is configured in the admin panel (CampaignSchedule)
Schedule::command('campaign:send-weekly')
    ->everyMinute()
    ->when(fn () => app(CampaignSchedule::class)->isDue())
    ->withoutOverlapping()
    ->onSuccess(function () {
        Log::info('Scheduled campaign:send-weekly completed successfully');
    })
    ->onFailure(function () {
        Log::error('Scheduled campaign:send-weekly failed');
    });
