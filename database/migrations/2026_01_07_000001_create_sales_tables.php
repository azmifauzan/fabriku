<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sales Orders
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('order_number')->unique();
            $table->string('invoice_number')->nullable();
            $table->string('resi_number')->nullable();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->date('order_date');
            $table->date('delivery_date')->nullable();
            $table->enum('channel', ['offline', 'online', 'marketplace', 'reseller'])->default('offline');
            $table->enum('status', ['draft', 'confirmed', 'processing', 'completed', 'cancelled'])->default('draft');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('discount_percentage', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->string('payment_method')->nullable();
            $table->enum('payment_status', ['unpaid', 'pending', 'partial', 'paid', 'refunded'])->default('unpaid');
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->date('payment_due_date')->nullable();
            $table->date('shipped_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->text('shipping_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'order_date']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'payment_status']);
        });

        // Sales Order Items
        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->string('product_name')->nullable();
            $table->string('sku')->nullable();
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['sales_order_id', 'inventory_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_items');
        Schema::dropIfExists('sales_orders');
    }
};
