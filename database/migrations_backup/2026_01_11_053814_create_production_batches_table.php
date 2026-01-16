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
        Schema::create('production_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('production_order_id')->constrained()->cascadeOnDelete();
            $table->string('batch_number');
            $table->integer('quantity_received');
            $table->integer('quantity_good')->default(0);
            $table->integer('quantity_defect')->default(0);
            $table->integer('quantity_reject')->default(0);
            $table->enum('grade', ['A', 'B', 'C', 'reject'])->default('A');
            $table->decimal('labor_cost_actual', 15, 2)->nullable();
            $table->decimal('production_cost', 15, 2)->nullable();
            $table->date('production_date');
            $table->date('received_date');
            $table->date('expiry_date')->nullable(); // For food products
            $table->text('qc_notes')->nullable();
            $table->text('defect_reasons')->nullable();
            $table->json('qc_checklist')->nullable(); // JSON for flexible QC data
            $table->timestamps();

            $table->index(['tenant_id', 'production_order_id']);
            $table->index(['tenant_id', 'batch_number']);
            $table->index(['tenant_id', 'production_date']);
            $table->index(['tenant_id', 'grade']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_batches');
    }
};
