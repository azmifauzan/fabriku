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
        Schema::create('material_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->string('attribute_name'); // corak, gramasi, lebar, dll
            $table->string('attribute_value'); // polos, 300gsm, 150cm, dll
            $table->timestamps();

            $table->index(['material_id', 'attribute_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_attributes');
    }
};
