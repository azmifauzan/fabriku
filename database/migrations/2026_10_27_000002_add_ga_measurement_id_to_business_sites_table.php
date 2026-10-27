<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_sites', function (Blueprint $table) {
            $table->string('ga_measurement_id', 22)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('business_sites', function (Blueprint $table) {
            $table->dropColumn('ga_measurement_id');
        });
    }
};
