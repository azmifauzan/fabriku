<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Patterns/Recipes
        Schema::create('patterns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->integer('output_quantity')->default(1); // how many pieces produced
            $table->text('description')->nullable();
            $table->decimal('estimated_labor_cost', 15, 2)->default(0);
            $table->text('instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'code']);
            $table->index(['tenant_id', 'name']);
            $table->index(['tenant_id', 'is_active']);
        });

        // Preparation Orders (cutting for garment, mixing for food)
        Schema::create('preparation_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('order_number');
            $table->foreignId('pattern_id')->constrained()->cascadeOnDelete();
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('output_quantity');
            $table->string('output_unit', 20)->default('pieces');
            $table->json('material_usage')->nullable(); // actual material used: [{ material_id, quantity }]
            $table->decimal('waste_percentage', 5, 2)->default(0);
            $table->enum('status', ['draft', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->date('preparation_date');
            $table->date('completed_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'order_number']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'preparation_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preparation_orders');
        Schema::dropIfExists('patterns');
    }
};
