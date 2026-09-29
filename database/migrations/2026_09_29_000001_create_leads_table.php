<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_site_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('source', 50)->default('website');
            $table->string('source_ref')->nullable();
            $table->string('name', 150);
            $table->string('phone', 32);
            $table->text('message')->nullable();
            $table->timestamp('consent_at');
            $table->text('consent_text');
            $table->string('status', 50)->default('new');
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('contacted_at')->nullable();
            $table->text('closed_reason')->nullable();
            $table->foreignId('sales_order_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->uuid('idempotency_key')->nullable();
            $table->timestamp('notification_failed_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'idempotency_key']);
            $table->index(['tenant_id', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
