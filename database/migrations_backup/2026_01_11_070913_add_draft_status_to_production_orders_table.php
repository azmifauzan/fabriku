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
        Schema::table('production_orders', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'status']);
            $table->dropColumn('status');
        });

        Schema::table('production_orders', function (Blueprint $table) {
            $table->enum('status', ['draft', 'pending', 'sent', 'in_progress', 'completed', 'cancelled'])->default('draft')->after('completed_date');
            $table->index(['tenant_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_orders', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'status']);
            $table->dropColumn('status');
        });

        Schema::table('production_orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'sent', 'in_progress', 'completed', 'cancelled'])->default('pending')->after('completed_date');
            $table->index(['tenant_id', 'status']);
        });
    }
};
