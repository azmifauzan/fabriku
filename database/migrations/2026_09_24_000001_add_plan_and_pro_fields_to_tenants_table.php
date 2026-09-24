<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('plan_code')->default('trial')->after('subscription_plan');
            $table->timestamp('pro_expires_at')->nullable()->after('subscription_expires_at');
            $table->boolean('pro_auto_renew')->default(false)->after('pro_expires_at');
        });

        // Migrate existing full plan tenants to core plan_code
        DB::table('tenants')
            ->where('subscription_plan', 'full')
            ->update(['plan_code' => 'core']);

        DB::table('tenants')
            ->where(function ($q) {
                $q->where('subscription_plan', '!=', 'full')
                    ->orWhereNull('subscription_plan');
            })
            ->update(['plan_code' => 'trial']);
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['plan_code', 'pro_expires_at', 'pro_auto_renew']);
        });
    }
};
