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
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('order_number', 50)->comment('SO-YYYY-NNNN format');
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->date('order_date');
            $table->enum('channel', ['offline', 'online', 'reseller', 'marketplace'])->default('offline');
            $table->enum('status', ['draft', 'confirmed', 'processing', 'shipped', 'completed', 'cancelled'])->default('draft');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->enum('payment_method', ['cash', 'transfer', 'credit_card', 'qris', 'cod'])->default('cash');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->date('payment_due_date')->nullable();
            $table->date('shipped_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->text('notes')->nullable();
            $table->text('shipping_address')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'order_number']);
            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'payment_status']);
            $table->index(['tenant_id', 'order_date']);
            $table->index(['tenant_id', 'channel']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
