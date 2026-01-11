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
        Schema::table('production_batches', function (Blueprint $table) {
            $table->foreignId('received_by')
                ->nullable()
                ->after('batch_number')
                ->constrained('users')
                ->nullOnDelete();

            $table->index(['tenant_id', 'received_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_batches', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'received_by']);
            $table->dropConstrainedForeignId('received_by');
        });
    }
};
