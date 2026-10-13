<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Link website products to inventory rows by id (same as quick checkout / sales order items).
 * inventory_items.product_code is never filled by the inventory UI, so the old link matched nothing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_products', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'product_code']);
            $table->dropColumn('product_code');
        });

        Schema::table('site_products', function (Blueprint $table) {
            $table->foreignId('inventory_item_id')->after('tenant_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->unique(['tenant_id', 'inventory_item_id']);
        });
    }

    public function down(): void
    {
        Schema::table('site_products', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'inventory_item_id']);
            $table->dropConstrainedForeignId('inventory_item_id');
        });

        Schema::table('site_products', function (Blueprint $table) {
            $table->string('product_code')->after('tenant_id');
            $table->unique(['tenant_id', 'product_code']);
        });
    }
};
