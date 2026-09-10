<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'tenant_id',
        'user_id',
        'recipient_email',
        'recipient_name',
        'business_category',
        'feature_key',
        'feature_name',
        'subject',
        'preview_text',
        'content_html',
        'prompt_used',
        'llm_model_used',
        'status',
        'error_message',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
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

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('business_category', $category);
    }
}
