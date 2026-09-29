<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_sites', function (Blueprint $table) {
            $table->timestamp('setup_completed_at')->nullable()->after('status');
        });
        DB::table('business_sites')->whereNull('setup_completed_at')->update(['setup_completed_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('business_sites', function (Blueprint $table) {
            $table->dropColumn('setup_completed_at');
        });
    }
};
