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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            // Product identification
            $table->string('sku', 100)->comment('Stock Keeping Unit - unique identifier');
            $table->string('name')->comment('Product name');
            $table->text('description')->nullable();
            $table->foreignId('pattern_id')->nullable()->constrained()->nullOnDelete()
                ->comment('Link to pattern/recipe for this product');

            // Category-specific fields
            $table->json('attributes')->nullable()->comment('Category-specific attributes');
            $table->enum('category', ['garment', 'food', 'craft', 'other'])->default('garment');

            // Stock information
            $table->integer('current_stock')->default(0)->comment('Current available quantity');
            $table->integer('reserved_stock')->default(0)->comment('Reserved for sales orders');
            $table->integer('minimum_stock')->default(0)->comment('Minimum stock alert level');
            $table->decimal('unit_cost', 10, 2)->default(0)->comment('Cost per unit from production');
            $table->decimal('selling_price', 10, 2)->nullable()->comment('Recommended selling price');

            // Location & tracking
            $table->foreignId('inventory_location_id')->nullable()->constrained()->nullOnDelete()
                ->comment('Primary storage location');
            $table->string('batch_number', 100)->nullable()->comment('Production batch identifier');

            // Category-specific tracking
            $table->date('production_date')->nullable()->comment('When this batch was produced');
            $table->date('expiry_date')->nullable()->comment('Expiry date for perishable items (food)');
            $table->date('best_before_date')->nullable()->comment('Best before date for food items');
            $table->enum('quality_grade', ['A', 'B', 'C', 'reject'])->default('A')
                ->comment('Quality grade, especially for garment');

            // Status
            $table->enum('status', ['available', 'reserved', 'damaged', 'expired'])->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->unique(['tenant_id', 'sku']);
            $table->index(['tenant_id', 'category']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'expiry_date']);
            $table->index(['tenant_id', 'inventory_location_id']);
            $table->index(['tenant_id', 'pattern_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
