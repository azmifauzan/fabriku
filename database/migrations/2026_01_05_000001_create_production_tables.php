<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Production Orders
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('order_number')->unique();
            $table->foreignId('preparation_order_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['internal', 'external'])->default('internal');
            $table->foreignId('contractor_id')->nullable()->constrained()->cascadeOnDelete();
            $table->integer('quantity_requested')->default(0);
            $table->integer('quantity_produced')->default(0);
            $table->integer('quantity_good')->default(0);
            $table->integer('quantity_reject')->default(0);
            $table->decimal('labor_cost', 15, 2)->default(0);
            $table->date('estimated_completion_date')->nullable();
            $table->date('sent_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->enum('status', ['draft', 'pending', 'sent', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->text('notes')->nullable();
            $table->text('completion_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'contractor_id']);
        });

        // Production Batches (receiving batches from production)
        Schema::create('production_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('production_order_id')->constrained()->cascadeOnDelete();
            $table->string('batch_number')->unique();
            $table->integer('quantity_received');
            $table->integer('quantity_good')->default(0);
            $table->integer('quantity_grade_b')->default(0);
            $table->integer('quantity_reject')->default(0);
            $table->date('received_date');
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'production_order_id']);
            $table->index(['tenant_id', 'received_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_batches');
        Schema::dropIfExists('production_orders');
    }
};
