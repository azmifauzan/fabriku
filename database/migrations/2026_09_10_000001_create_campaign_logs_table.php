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
        Schema::create('campaign_logs', function (Blueprint $table) {
            $table->id();
            $table->string('batch_id')->nullable()->index();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('recipient_email')->index();
            $table->string('recipient_name')->nullable();
            $table->string('business_category')->nullable()->index();
            $table->string('feature_key')->nullable()->index();
            $table->string('feature_name');
            $table->string('subject');
            $table->text('preview_text')->nullable();
            $table->longText('content_html');
            $table->text('prompt_used')->nullable();
            $table->string('llm_model_used')->nullable();
            $table->string('status')->default('sent')->index(); // sent, failed, pending
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable()->index();
            $table->timestamps();

            $table->index(['tenant_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_logs');
    }
};
