<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('material_receipts', function (Blueprint $table) {
            $table->decimal('remaining_quantity', 15, 3)->after('quantity')->default(0);
            $table->string('status')->default('active')->after('remaining_quantity'); // active, exhausted
            $table->string('barcode')->nullable()->unique()->after('status');
        });

        // Initialize remaining_quantity for existing records
        DB::statement('UPDATE material_receipts SET remaining_quantity = quantity');

        Schema::create('preparation_material_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('preparation_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_receipt_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 15, 3);
            $table->timestamps();

            $table->index(['preparation_order_id', 'material_receipt_id'], 'prep_mat_usage_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preparation_material_usages');

        Schema::table('material_receipts', function (Blueprint $table) {
            $table->dropColumn(['remaining_quantity', 'status', 'barcode']);
        });
    }
};
