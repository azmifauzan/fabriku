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
        Schema::create('inventory_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name')->comment('Human readable name');
            $table->string('zone', 10)->comment('Storage zone, e.g., A, B, C');
            $table->string('rack', 20)->comment('Rack code, e.g., A-01, B-02');
            $table->text('description')->nullable()->comment('Location description');
            $table->integer('capacity')->nullable()->comment('Max stock quantity this location can hold');
            $table->integer('temperature_min')->nullable()->comment('Minimum temperature in Celsius');
            $table->integer('temperature_max')->nullable()->comment('Maximum temperature in Celsius');
            $table->enum('status', ['active', 'inactive', 'maintenance'])
                ->default('active')
                ->comment('Location operational status');
            $table->text('notes')->nullable()->comment('Additional notes or maintenance information');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'name']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'zone']);
            $table->index(['zone', 'rack']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_locations');
    }
};
