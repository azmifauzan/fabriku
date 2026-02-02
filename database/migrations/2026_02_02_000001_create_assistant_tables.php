<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Conversation sessions
        Schema::create('assistant_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('channel', 50)->default('web'); // web, telegram, whatsapp
            $table->string('external_chat_id')->nullable(); // For Telegram/WhatsApp
            $table->string('status', 50)->default('active'); // active, archived
            $table->json('context')->nullable(); // Conversation context/memory
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'user_id']);
            $table->index('channel');
            $table->index('status');
        });

        // Individual messages
        Schema::create('assistant_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('assistant_conversations')->cascadeOnDelete();
            $table->string('role', 20); // user, assistant, system
            $table->text('content');
            $table->integer('tokens_used')->nullable();
            $table->json('metadata')->nullable(); // Intent, entities, function calls, etc.
            $table->timestamps();

            $table->index('conversation_id');
            $table->index('role');
        });

        // Pending actions awaiting confirmation
        Schema::create('assistant_pending_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('assistant_conversations')->cascadeOnDelete();
            $table->foreignId('message_id')->nullable()->constrained('assistant_messages')->cascadeOnDelete();
            $table->string('action_type', 100);
            $table->json('action_data');
            $table->string('status', 50)->default('pending'); // pending, confirmed, cancelled, expired
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('expires_at');
        });

        // Usage tracking for billing/limits
        Schema::create('assistant_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->integer('message_count')->default(0);
            $table->integer('tokens_input')->default(0);
            $table->integer('tokens_output')->default(0);
            $table->integer('actions_count')->default(0);
            $table->timestamps();

            $table->unique(['tenant_id', 'user_id', 'date']);
            $table->index(['tenant_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assistant_usage');
        Schema::dropIfExists('assistant_pending_actions');
        Schema::dropIfExists('assistant_messages');
        Schema::dropIfExists('assistant_conversations');
    }
};
