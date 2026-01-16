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
        Schema::table('material_types', function (Blueprint $table) {
            $table->dropIndex(['category', 'is_active']);
            $table->dropColumn('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_types', function (Blueprint $table) {
            $table->string('category')->after('tenant_id');
            $table->index(['category', 'is_active']);
        });
    }
};
