<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('product_code');
            $table->string('slug');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->integer('sort')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->unique(['tenant_id', 'product_code']);
            $table->unique(['tenant_id', 'slug']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->boolean('is_public')->default(false)->after('is_active');
            $table->text('public_description')->nullable()->after('is_public');
            $table->string('image_path')->nullable()->after('public_description');
            $table->string('price_label', 50)->nullable()->after('image_path');
            $table->string('slug')->nullable()->after('price_label');

            $table->index(['tenant_id', 'is_public', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex(['tenant_id', 'is_public', 'is_active']);
            $table->dropColumn([
                'is_public',
                'public_description',
                'image_path',
                'price_label',
                'slug',
            ]);
        });

        Schema::dropIfExists('site_products');
    }
};
