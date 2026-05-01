<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropUnique('sales_orders_order_number_unique');
            $table->unique(['tenant_id', 'order_number']);
        });

        Schema::table('production_orders', function (Blueprint $table) {
            $table->dropUnique('production_orders_order_number_unique');
            $table->unique(['tenant_id', 'order_number']);
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'order_number']);
            $table->unique('order_number');
        });

        Schema::table('production_orders', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'order_number']);
            $table->unique('order_number');
        });
    }
};
