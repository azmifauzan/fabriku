<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Inventory Locations (racks, shelves)
        Schema::create('inventory_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('zone')->nullable(); // A, B, C, D, E etc
            $table->string('rack')->nullable();
            $table->string('type')->default('rack'); // rack, shelf, bin, etc
            $table->integer('capacity')->nullable();
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'name']);
        });

        // Inventory Items (finished goods)
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->foreignId('production_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('inventory_locations')->nullOnDelete();
            $table->string('product_name');
            $table->string('product_code')->nullable();
            $table->integer('target_quantity');
            $table->integer('current_quantity');
            $table->integer('reserved_quantity')->default(0);
            $table->integer('minimum_stock')->default(0);
            $table->string('quality_grade')->default('A'); // A, B, Reject
            $table->enum('status', ['available', 'reserved', 'damaged', 'expired'])->default('available');
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('selling_price', 15, 2)->nullable();
            $table->date('production_date')->nullable();
            $table->date('expired_date')->nullable(); // for food products
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'production_order_id']);
            $table->index(['tenant_id', 'location_id']);
            $table->index(['tenant_id', 'sku']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('inventory_locations');
    }
};
