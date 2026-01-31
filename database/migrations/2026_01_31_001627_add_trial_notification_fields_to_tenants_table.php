<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->timestamp('trial_reminder_7days_sent_at')->nullable()->after('subscription_expires_at');
            $table->timestamp('trial_reminder_3days_sent_at')->nullable()->after('trial_reminder_7days_sent_at');
            $table->timestamp('trial_reminder_1day_sent_at')->nullable()->after('trial_reminder_3days_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'trial_reminder_7days_sent_at',
                'trial_reminder_3days_sent_at',
                'trial_reminder_1day_sent_at',
            ]);
        });
    }
};
