<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->uuid('storefront_checkout_key')->nullable();
            $table->unique(['tenant_id', 'storefront_checkout_key']);
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'storefront_checkout_key']);
            $table->dropColumn('storefront_checkout_key');
        });
    }
};
