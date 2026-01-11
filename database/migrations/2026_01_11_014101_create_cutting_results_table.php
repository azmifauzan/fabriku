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
        Schema::create('cutting_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cutting_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->constrained()->restrictOnDelete();
            $table->decimal('material_used', 10, 2)->comment('Actual material consumption');
            $table->decimal('material_wasted', 10, 2)->default(0)->comment('Waste/scrap material');
            $table->decimal('waste_percentage', 5, 2)->default(0)->comment('Calculated waste %');
            $table->integer('actual_quantity')->comment('Actual products cut');
            $table->integer('defect_quantity')->default(0)->comment('Defective cuts');
            $table->decimal('efficiency_percentage', 5, 2)->default(0)->comment('Efficiency rate');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('cutting_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cutting_results');
    }
};
