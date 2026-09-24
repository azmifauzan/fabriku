<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->string('kind', 30)->nullable()->after('status');
            $table->string('billing_cycle', 20)->nullable()->after('kind');
            $table->timestamp('period_start')->nullable()->after('billing_cycle');
            $table->timestamp('period_end')->nullable()->after('period_start');
            $table->decimal('core_amount', 15, 2)->default(0)->after('amount');
            $table->decimal('pro_amount', 15, 2)->default(0)->after('core_amount');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->dropColumn([
                'kind',
                'billing_cycle',
                'period_start',
                'period_end',
                'core_amount',
                'pro_amount',
            ]);
        });
    }
};
