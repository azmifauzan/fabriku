<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('style', 50); // minimal, modern, playful, elegant
            $table->string('mode', 50); // produk, jasa, gabungan
            $table->json('artifact'); // metadata, shell.html, sections, theme.json
            $table->string('css_path')->nullable();
            $table->string('preview_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('business_sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained('tenants')->cascadeOnDelete();
            $table->string('mode', 50)->default('produk'); // produk, jasa, gabungan
            $table->string('slug')->unique();
            $table->string('custom_domain')->nullable()->unique();
            $table->string('cloudflare_hostname_id')->nullable();
            $table->string('domain_status', 50)->default('none'); // none, pending, active, failed
            $table->unsignedBigInteger('active_theme_version_id')->nullable();
            $table->json('profile')->nullable();
            $table->json('seo')->nullable();
            $table->string('status', 50)->default('draft'); // draft, published, paused, suspended
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('site_theme_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('business_site_id')->constrained('business_sites')->cascadeOnDelete();
            $table->string('source', 50)->default('catalog'); // catalog, ai
            $table->foreignId('site_template_id')->nullable()->constrained('site_templates')->nullOnDelete();
            $table->string('satsetui_generation_id')->nullable();
            $table->integer('version')->default(1);
            $table->json('theme'); // theme.json
            $table->json('sections'); // sections array
            $table->string('css_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'business_site_id']);
        });

        Schema::create('business_site_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_site_id')->constrained('business_sites')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['business_site_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_site_recipients');
        Schema::dropIfExists('site_theme_versions');
        Schema::dropIfExists('business_sites');
        Schema::dropIfExists('site_templates');
    }
};
