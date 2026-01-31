<?php

namespace App\Console\Commands;

use App\Mail\TrialReminderEmail;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTrialReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trial:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send trial expiration reminder emails to tenants';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking for trial accounts that need reminders...');

        $tenants = Tenant::where('subscription_plan', 'trial')
            ->where('is_active', true)
            ->whereNotNull('subscription_expires_at')
            ->get();

        $sentCount = 0;

        foreach ($tenants as $tenant) {
            $daysLeft = now()->diffInDays($tenant->subscription_expires_at, false);

            // Skip if already expired
            if ($daysLeft < 0) {
                continue;
            }

            $adminUser = $tenant->users()->where('role', 'admin')->first();

            if (! $adminUser) {
                continue;
            }

            // Check for 7 days reminder
            if ($daysLeft <= 7 && $daysLeft > 6 && is_null($tenant->trial_reminder_7days_sent_at)) {
                Mail::to($adminUser->email)->send(new TrialReminderEmail($tenant, 7));
                $tenant->update(['trial_reminder_7days_sent_at' => now()]);
                $this->info("Sent 7-day reminder to {$tenant->name}");
                $sentCount++;
            }

            // Check for 3 days reminder
            if ($daysLeft <= 3 && $daysLeft > 2 && is_null($tenant->trial_reminder_3days_sent_at)) {
                Mail::to($adminUser->email)->send(new TrialReminderEmail($tenant, 3));
                $tenant->update(['trial_reminder_3days_sent_at' => now()]);
                $this->info("Sent 3-day reminder to {$tenant->name}");
                $sentCount++;
            }

            // Check for 1 day reminder
            if ($daysLeft <= 1 && $daysLeft > 0 && is_null($tenant->trial_reminder_1day_sent_at)) {
                Mail::to($adminUser->email)->send(new TrialReminderEmail($tenant, 1));
                $tenant->update(['trial_reminder_1day_sent_at' => now()]);
                $this->info("Sent 1-day reminder to {$tenant->name}");
                $sentCount++;
            }
        }

        $this->info("Sent {$sentCount} reminder emails.");

        return Command::SUCCESS;
    }
}
