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
        Schema::table('staff', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('tenant_id')->constrained('users')->nullOnDelete();
            $table->foreignId('role_id')->nullable()->after('position')->constrained('roles')->nullOnDelete();
        });

        // Add max_staff_per_tenant system setting
        \App\Models\SystemSetting::set('max_staff_per_tenant', 5, 'number');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
    }
};
