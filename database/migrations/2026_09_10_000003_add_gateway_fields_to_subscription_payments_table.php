<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->string('proof_path')->nullable()->change();
            $table->string('status', 30)->default('pending')->change();
            $table->string('payment_method', 50)->default('manual')->after('amount');
            $table->string('provider', 50)->nullable()->after('payment_method');
            $table->string('provider_order_id')->nullable()->after('provider')->index();
            $table->string('provider_payment_id')->nullable()->after('provider_order_id')->index();
            $table->text('payment_url')->nullable()->after('provider_payment_id');
            $table->json('provider_payload')->nullable()->after('payment_url');
            $table->string('event_id')->nullable()->after('provider_payload')->index();
            $table->timestamp('paid_at')->nullable()->after('event_id');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'provider',
                'provider_order_id',
                'provider_payment_id',
                'payment_url',
                'provider_payload',
                'event_id',
                'paid_at',
            ]);
        });
    }
};
