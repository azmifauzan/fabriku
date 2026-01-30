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
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();

            // Adjustment type: opening_balance, correction, damage, loss, found, return
            $table->string('adjustment_type');

            // Stock quantities
            $table->integer('quantity_before');
            $table->integer('quantity_after');
            $table->integer('adjustment_quantity'); // positive for add, negative for subtract

            // Additional info
            $table->string('reason');
            $table->text('notes')->nullable();

            // Audit trail
            $table->foreignId('adjusted_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            // Indexes for common queries
            $table->index(['tenant_id', 'inventory_item_id']);
            $table->index(['tenant_id', 'adjustment_type']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
