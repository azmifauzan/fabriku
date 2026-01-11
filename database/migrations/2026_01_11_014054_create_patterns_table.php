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
        Schema::create('patterns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('code')->comment('Pattern code, e.g., MKN-001');
            $table->string('name');
            $table->enum('product_type', ['mukena', 'daster', 'gamis', 'jilbab', 'lainnya']);
            $table->enum('size', ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'XXXL', 'all_size'])->nullable();
            $table->text('description')->nullable();
            $table->decimal('estimated_time', 8, 2)->nullable()->comment('Estimated cutting time in minutes');
            $table->decimal('standard_waste_percentage', 5, 2)->default(5)->comment('Expected waste %');
            $table->string('image_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
            $table->index('tenant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patterns');
    }
};
