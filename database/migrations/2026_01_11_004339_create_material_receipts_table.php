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
        Schema::create('material_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('receipt_number', 50);
            $table->foreignId('material_id')->constrained()->restrictOnDelete();
            $table->string('supplier_name');
            $table->date('receipt_date');
            $table->decimal('quantity', 15, 2);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_price', 15, 2);
            $table->integer('rolls_count')->nullable();
            $table->decimal('length_per_roll', 10, 2)->nullable();
            $table->string('batch_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('attachments')->nullable();
            $table->timestamps();

            // Unique constraint and indexes
            $table->unique(['tenant_id', 'receipt_number']);
            $table->index('material_id');
            $table->index('receipt_date');
            $table->index('batch_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_receipts');
    }
};
