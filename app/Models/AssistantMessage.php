<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssistantMessage extends Model
{
    protected $fillable = [
        'conversation_id',
        'role',
        'content',
        'tokens_used',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'tokens_used' => 'integer',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AssistantConversation::class, 'conversation_id');
    }

    public function scopeFromUser($query)
    {
        return $query->where('role', 'user');
    }

    public function scopeFromAssistant($query)
    {
        return $query->where('role', 'assistant');
    }

    public function scopeFromSystem($query)
    {
        return $query->where('role', 'system');
    }

    /**
     * Create a user message
     */
    public static function createUserMessage(int $conversationId, string $content): self
    {
        return self::create([
            'conversation_id' => $conversationId,
            'role' => 'user',
            'content' => $content,
        ]);
    }

    /**
     * Create an assistant message
     */
    public static function createAssistantMessage(
        int $conversationId,
        string $content,
        ?int $tokensUsed = null,
        ?array $metadata = null
    ): self {
        return self::create([
            'conversation_id' => $conversationId,
            'role' => 'assistant',
            'content' => $content,
            'tokens_used' => $tokensUsed,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Format for OpenAI API
     */
    public function toOpenAIFormat(): array
    {
        return [
            'role' => $this->role,
            'content' => $this->content,
        ];
    }
}
