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
        Schema::table('patterns', function (Blueprint $table) {
            // Add category field
            $table->string('category', 50)->default('other')->after('name');

            // Drop complex fields
            $table->dropColumn(['estimated_time', 'standard_waste_percentage']);
        });

        // Add index for category
        Schema::table('patterns', function (Blueprint $table) {
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patterns', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn('category');
            $table->decimal('estimated_time', 8, 2)->nullable();
            $table->decimal('standard_waste_percentage', 5, 2)->default(5);
        });
    }
};
