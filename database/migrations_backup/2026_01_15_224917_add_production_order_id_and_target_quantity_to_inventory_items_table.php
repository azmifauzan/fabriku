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
            $table->foreignId('production_order_id')->nullable()->after('pattern_id')->constrained('production_orders')->nullOnDelete();
            $table->integer('target_quantity')->nullable()->after('production_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropForeign(['production_order_id']);
            $table->dropColumn(['production_order_id', 'target_quantity']);
        });
    }
};
