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
        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->uuid('batch_id')->nullable()->after('adjustment_type')->index();
            $table->string('supplier_name')->nullable()->after('notes');
            $table->string('purchase_invoice')->nullable()->after('supplier_name');
            $table->decimal('unit_cost', 15, 2)->nullable()->after('purchase_invoice');
        });
    }

    public function down(): void
    {
        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->dropColumn(['batch_id', 'supplier_name', 'purchase_invoice', 'unit_cost']);
        });
    }
};
