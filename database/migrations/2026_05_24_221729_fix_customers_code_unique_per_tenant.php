<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            // PostgreSQL: safe DDL that won't abort the transaction
            DB::statement('ALTER TABLE customers DROP CONSTRAINT IF EXISTS customers_code_unique');
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS customers_tenant_code_unique ON customers (tenant_id, code)');
        } else {
            // MySQL/SQLite: inspect indexes via schema builder (driver-agnostic)
            $indexNames = collect(Schema::getIndexes('customers'))->pluck('name');

            $hasGlobal = $indexNames->contains('customers_code_unique');
            $hasTenant = $indexNames->contains('customers_tenant_code_unique');

            Schema::table('customers', function (Blueprint $table) use ($hasGlobal, $hasTenant) {
                if ($hasGlobal) {
                    $table->dropUnique('customers_code_unique');
                }
                if (! $hasTenant) {
                    $table->unique(['tenant_id', 'code'], 'customers_tenant_code_unique');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique('customers_tenant_code_unique');
            $table->unique('code', 'customers_code_unique');
        });
    }
};
