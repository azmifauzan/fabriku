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
        // SQLite doesn't support modifying ENUM constraints easily, so we:
        // 1. Create new columns with no constraints
        // 2. Copy data
        // 3. Drop old columns
        // 4. Rename new columns

        Schema::table('patterns', function (Blueprint $table) {
            $table->string('product_type_new')->nullable();
            $table->string('size_new', 50)->nullable();
        });

        // Copy data
        DB::statement('UPDATE patterns SET product_type_new = product_type, size_new = size');

        // Drop old columns
        Schema::table('patterns', function (Blueprint $table) {
            $table->dropColumn(['product_type', 'size']);
        });

        // Rename new columns
        Schema::table('patterns', function (Blueprint $table) {
            $table->renameColumn('product_type_new', 'product_type');
            $table->renameColumn('size_new', 'size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert: create temp columns, copy, drop, rename back
        Schema::table('patterns', function (Blueprint $table) {
            $table->string('product_type_old')->nullable();
            $table->string('size_old')->nullable();
        });

        DB::statement('UPDATE patterns SET product_type_old = product_type, size_old = size');

        Schema::table('patterns', function (Blueprint $table) {
            $table->dropColumn(['product_type', 'size']);
        });

        Schema::table('patterns', function (Blueprint $table) {
            $table->renameColumn('product_type_old', 'product_type');
            $table->renameColumn('size_old', 'size');
        });
    }
};
