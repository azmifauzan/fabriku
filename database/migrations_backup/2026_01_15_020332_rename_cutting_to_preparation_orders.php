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
        // Create preparation_orders table
        Schema::create('preparation_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pattern_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_number')->unique();
            $table->date('order_date');
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('output_quantity', 10, 2);
            $table->string('output_unit', 20)->default('pieces');
            $table->json('materials_used');
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->timestamps();

            $table->index(['tenant_id', 'order_date']);
            $table->index(['tenant_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preparation_orders');
    }
};
