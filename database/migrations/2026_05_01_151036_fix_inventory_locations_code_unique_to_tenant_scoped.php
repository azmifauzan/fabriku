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
        Schema::table('inventory_locations', function (Blueprint $table) {
            $table->dropUnique('inventory_locations_code_unique');
            $table->unique(['tenant_id', 'code'], 'inventory_locations_tenant_id_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_locations', function (Blueprint $table) {
            $table->dropUnique('inventory_locations_tenant_id_code_unique');
            $table->unique('code', 'inventory_locations_code_unique');
        });
    }
};
