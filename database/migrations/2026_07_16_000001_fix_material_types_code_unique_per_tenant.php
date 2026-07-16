<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_types', function (Blueprint $table) {
            $table->dropUnique('material_types_code_unique');
            $table->unique(['tenant_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::table('material_types', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'code']);
            $table->unique('code', 'material_types_code_unique');
        });
    }
};
