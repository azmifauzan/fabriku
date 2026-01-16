<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Material Types (categories)
        Schema::create('material_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('unit'); // meter, kg, pcs, liter, etc
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'name']);
        });

        // Materials (raw materials/ingredients)
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_type_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('supplier_name')->nullable();
            $table->decimal('price_per_unit', 15, 2)->default(0);
            $table->decimal('stock_quantity', 15, 3)->default(0);
            $table->decimal('min_stock', 15, 3)->default(0);
            $table->string('unit');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'material_type_id']);
            $table->index(['tenant_id', 'name']);
        });

        // Material Attributes (dynamic attributes for materials)
        Schema::create('material_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->string('attribute_key'); // color, width, weight, expired_date, etc
            $table->string('attribute_value');
            $table->timestamps();

            $table->index(['material_id', 'attribute_key']);
        });

        // Material Receipts (stock in)
        Schema::create('material_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->string('receipt_number')->unique();
            $table->string('supplier_name');
            $table->decimal('quantity', 15, 3);
            $table->string('unit');
            $table->decimal('price_per_unit', 15, 2);
            $table->decimal('total_cost', 15, 2);
            $table->date('receipt_date');
            $table->string('batch_number')->nullable();
            $table->date('expired_date')->nullable(); // for food materials
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'material_id']);
            $table->index(['tenant_id', 'receipt_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_receipts');
        Schema::dropIfExists('material_attributes');
        Schema::dropIfExists('materials');
        Schema::dropIfExists('material_types');
    }
};
