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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenant_id')->after('id')->constrained('tenants')->cascadeOnDelete();
            $table->string('role', 50)->default('staff')->after('password');
            $table->string('phone', 50)->nullable()->after('email');
            $table->boolean('is_active')->default(true)->after('remember_token');
            $table->timestamp('last_login_at')->nullable()->after('is_active');

            $table->index('tenant_id');
            $table->index('email');
            $table->index('role');
            $table->unique(['tenant_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'email']);
            $table->dropIndex(['role']);
            $table->dropIndex(['email']);
            $table->dropIndex(['tenant_id']);
            $table->dropForeign(['tenant_id']);
            $table->dropColumn(['tenant_id', 'role', 'phone', 'is_active', 'last_login_at']);
        });
    }
};
