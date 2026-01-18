<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Production Orders - Simplified for UMKM
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('order_number')->unique();
            $table->foreignId('preparation_order_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['internal', 'external'])->default('internal');
            $table->foreignId('contractor_id')->nullable()->constrained()->cascadeOnDelete();
            $table->decimal('labor_cost', 15, 2)->default(0);
            $table->date('estimated_completion_date')->nullable();
            $table->date('sent_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->enum('status', ['draft', 'sent', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->text('notes')->nullable();
            $table->text('completion_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'contractor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_orders');
    }
};
