<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usage_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('type', 50)->index();
            $table->date('period_start')->index();
            $table->integer('quantity')->default(1);
            $table->string('idempotency_key')->unique();
            $table->timestamp('reversed_at')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'type', 'period_start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_events');
    }
};
