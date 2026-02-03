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
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->after('tenant_id')->constrained()->nullOnDelete();
            $table->string('event', 50)->after('user_id'); // created, updated, deleted, restored
            $table->string('auditable_type')->after('event');
            $table->unsignedBigInteger('auditable_id')->after('auditable_type');
            $table->json('old_values')->nullable()->after('auditable_id');
            $table->json('new_values')->nullable()->after('old_values');
            $table->string('url', 500)->nullable()->after('new_values');
            $table->string('ip_address', 45)->nullable()->after('url');
            $table->text('user_agent')->nullable()->after('ip_address');
            $table->json('metadata')->nullable()->after('user_agent');

            // Indexes for better query performance
            $table->index(['tenant_id', 'created_at']);
            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['user_id', 'created_at']);
            $table->index('event');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropForeign(['user_id']);

            $table->dropIndex(['tenant_id', 'created_at']);
            $table->dropIndex(['auditable_type', 'auditable_id']);
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['event']);

            $table->dropColumn([
                'tenant_id',
                'user_id',
                'event',
                'auditable_type',
                'auditable_id',
                'old_values',
                'new_values',
                'url',
                'ip_address',
                'user_agent',
                'metadata',
            ]);
        });
    }
};
