<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssistantConversation extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'channel',
        'external_chat_id',
        'status',
        'context',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'last_message_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AssistantMessage::class, 'conversation_id');
    }

    public function pendingActions(): HasMany
    {
        return $this->hasMany(AssistantPendingAction::class, 'conversation_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    /**
     * Get the active conversation for a user on a specific channel
     */
    public static function getOrCreateForUser(int $userId, int $tenantId, string $channel = 'web'): self
    {
        return self::firstOrCreate(
            [
                'user_id' => $userId,
                'tenant_id' => $tenantId,
                'channel' => $channel,
                'status' => 'active',
            ],
            [
                'context' => [],
                'last_message_at' => now(),
            ]
        );
    }

    /**
     * Archive this conversation and create a new one
     */
    public function archiveAndCreateNew(): self
    {
        $this->update(['status' => 'archived']);

        return self::create([
            'tenant_id' => $this->tenant_id,
            'user_id' => $this->user_id,
            'channel' => $this->channel,
            'status' => 'active',
            'context' => [],
            'last_message_at' => now(),
        ]);
    }

    /**
     * Get recent messages for context
     */
    public function getRecentMessages(int $limit = 10): Collection
    {
        return $this->messages()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();
    }
}
