<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'type',
        'period_start',
        'quantity',
        'idempotency_key',
        'reversed_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'quantity' => 'integer',
            'reversed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('reversed_at');
    }

    public function scopeForType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeForPeriod(Builder $query, CarbonInterface|string $periodStart): Builder
    {
        $date = $periodStart instanceof CarbonInterface ? $periodStart->toDateString() : $periodStart;

        return $query->whereDate('period_start', $date);
    }
}
