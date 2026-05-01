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
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropUnique('inventory_items_sku_unique');
            $table->unique(['tenant_id', 'sku'], 'inventory_items_tenant_id_sku_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropUnique('inventory_items_tenant_id_sku_unique');
            $table->unique('sku', 'inventory_items_sku_unique');
        });
    }
};
