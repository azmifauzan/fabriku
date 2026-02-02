<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssistantUsage extends Model
{
    protected $table = 'assistant_usage';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'date',
        'message_count',
        'tokens_input',
        'tokens_output',
        'actions_count',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'message_count' => 'integer',
            'tokens_input' => 'integer',
            'tokens_output' => 'integer',
            'actions_count' => 'integer',
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

    /**
     * Get or create today's usage record
     */
    public static function getOrCreateToday(int $tenantId, int $userId): self
    {
        return self::firstOrCreate(
            [
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'date' => now()->toDateString(),
            ],
            [
                'message_count' => 0,
                'tokens_input' => 0,
                'tokens_output' => 0,
                'actions_count' => 0,
            ]
        );
    }

    /**
     * Increment message count
     */
    public function incrementMessage(int $tokensInput = 0, int $tokensOutput = 0): void
    {
        $this->increment('message_count');
        $this->increment('tokens_input', $tokensInput);
        $this->increment('tokens_output', $tokensOutput);
    }

    /**
     * Increment action count
     */
    public function incrementAction(): void
    {
        $this->increment('actions_count');
    }

    /**
     * Get daily usage for tenant
     */
    public static function getDailyUsage(int $tenantId, ?int $userId = null): array
    {
        $query = self::where('tenant_id', $tenantId)
            ->where('date', now()->toDateString());

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $usage = $query->first();

        return [
            'message_count' => $usage?->message_count ?? 0,
            'tokens_input' => $usage?->tokens_input ?? 0,
            'tokens_output' => $usage?->tokens_output ?? 0,
            'actions_count' => $usage?->actions_count ?? 0,
        ];
    }

    /**
     * Check if user has exceeded daily limit
     */
    public static function hasExceededLimit(int $tenantId, int $userId, int $maxMessages): bool
    {
        $usage = self::getDailyUsage($tenantId, $userId);

        return $usage['message_count'] >= $maxMessages;
    }
}
