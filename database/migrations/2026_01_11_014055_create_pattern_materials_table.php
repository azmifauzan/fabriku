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
        Schema::create('pattern_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pattern_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity_needed', 10, 2)->comment('Quantity per unit product');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['pattern_id', 'material_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pattern_materials');
    }
};
