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
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('order_number')->unique();
            $table->foreignId('cutting_result_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['internal', 'external'])->default('internal');
            $table->foreignId('contractor_id')->nullable()->constrained()->cascadeOnDelete();
            $table->integer('quantity_requested');
            $table->integer('quantity_produced')->default(0);
            $table->integer('quantity_good')->default(0);
            $table->integer('quantity_reject')->default(0);
            $table->decimal('labor_cost', 15, 2)->default(0);
            $table->date('requested_date');
            $table->date('promised_date')->nullable();
            $table->date('sent_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->enum('status', ['pending', 'sent', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->text('notes')->nullable();
            $table->text('completion_notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'contractor_id']);
            $table->index(['tenant_id', 'requested_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_orders');
    }
};
