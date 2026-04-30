<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE sales_orders DROP CONSTRAINT sales_orders_status_check");
            DB::statement("ALTER TABLE sales_orders ADD CONSTRAINT sales_orders_status_check CHECK (status IN ('draft', 'confirmed', 'processing', 'shipped', 'completed', 'cancelled'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("ALTER TABLE sales_orders DROP CONSTRAINT sales_orders_status_check");
            DB::statement("ALTER TABLE sales_orders ADD CONSTRAINT sales_orders_status_check CHECK (status IN ('draft', 'confirmed', 'processing', 'completed', 'cancelled'))");
        }
    }
};
