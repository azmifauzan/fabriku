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
        Schema::table('business_sites', function (Blueprint $table) {
            $table->unsignedBigInteger('draft_theme_version_id')->nullable()->after('active_theme_version_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_sites', function (Blueprint $table) {
            $table->dropColumn('draft_theme_version_id');
        });
    }
};
