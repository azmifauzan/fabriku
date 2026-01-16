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
            // Drop index first before dropping the column
            $table->dropIndex(['tenant_id', 'requested_date']);

            // Remove quantity_requested, requested_date, and promised_date
            $table->dropColumn(['quantity_requested', 'requested_date', 'promised_date']);

            // Add estimated_completion_date
            $table->date('estimated_completion_date')->nullable()->after('labor_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_orders', function (Blueprint $table) {
            // Restore the old columns
            $table->integer('quantity_requested')->after('contractor_id');
            $table->date('requested_date')->after('labor_cost');
            $table->date('promised_date')->nullable()->after('requested_date');

            // Recreate the index
            $table->index(['tenant_id', 'requested_date']);

            // Remove estimated_completion_date
            $table->dropColumn('estimated_completion_date');
        });
    }
};
