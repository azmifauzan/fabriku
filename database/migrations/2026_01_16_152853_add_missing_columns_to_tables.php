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
        // Add missing columns to material_types table
        if (! Schema::hasColumn('material_types', 'sort_order')) {
            Schema::table('material_types', function (Blueprint $table) {
                $table->integer('sort_order')->default(0)->after('description');
            });
        }

        if (! Schema::hasColumn('material_types', 'is_active')) {
            Schema::table('material_types', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('sort_order');
            });
        }

        // Rename current_quantity to current_stock in inventory_items table
        if (Schema::hasColumn('inventory_items', 'current_quantity') && ! Schema::hasColumn('inventory_items', 'current_stock')) {
            Schema::table('inventory_items', function (Blueprint $table) {
                $table->renameColumn('current_quantity', 'current_stock');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove added columns from material_types
        $dropColumns = [];
        if (Schema::hasColumn('material_types', 'sort_order')) {
            $dropColumns[] = 'sort_order';
        }
        if (Schema::hasColumn('material_types', 'is_active')) {
            $dropColumns[] = 'is_active';
        }
        if ($dropColumns !== []) {
            Schema::table('material_types', function (Blueprint $table) use ($dropColumns) {
                $table->dropColumn($dropColumns);
            });
        }

        // Rename current_stock back to current_quantity in inventory_items
        if (Schema::hasColumn('inventory_items', 'current_stock') && ! Schema::hasColumn('inventory_items', 'current_quantity')) {
            Schema::table('inventory_items', function (Blueprint $table) {
                $table->renameColumn('current_stock', 'current_quantity');
            });
        }
    }
};
