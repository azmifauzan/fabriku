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
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('code', 50);
            $table->string('name');
            $table->string('type', 50); // kain, benang, aksesoris, dll
            $table->text('description')->nullable();
            $table->string('unit', 20)->default('meter'); // meter, roll, pcs, kg
            $table->decimal('standard_price', 15, 2)->nullable();
            $table->decimal('current_stock', 15, 2)->default(0);
            $table->decimal('reorder_point', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Unique constraint and indexes
            $table->unique(['tenant_id', 'code']);
            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
