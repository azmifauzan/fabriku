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
        Schema::table('preparation_orders', function (Blueprint $table) {
            // Drop the old foreign key constraint to users table
            $table->dropForeign(['prepared_by']);

            // Re-add foreign key constraint to staff table
            $table->foreign('prepared_by')->references('id')->on('staff')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('preparation_orders', function (Blueprint $table) {
            // Drop the foreign key constraint to staff table
            $table->dropForeign(['prepared_by']);

            // Re-add the old foreign key constraint to users table
            $table->foreign('prepared_by')->references('id')->on('users')->nullOnDelete();
        });
    }
};
