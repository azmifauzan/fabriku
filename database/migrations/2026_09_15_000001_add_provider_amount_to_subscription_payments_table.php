<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->unsignedInteger('provider_amount')->nullable()->after('amount');
            $table->unsignedInteger('provider_fee')->nullable()->after('provider_amount');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->dropColumn(['provider_amount', 'provider_fee']);
        });
    }
};
