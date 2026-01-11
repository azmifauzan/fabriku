<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CuttingOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'order_number',
        'pattern_id',
        'order_date',
        'target_date',
        'target_quantity',
        'status',
        'cutter_id',
        'started_at',
        'completed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'target_date' => 'date',
            'started_at' => 'date',
            'completed_at' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (CuttingOrder $order) {
            if (auth()->check() && ! $order->tenant_id) {
                $order->tenant_id = auth()->user()->tenant_id;
            }

            // Auto-generate order number
            if (! $order->order_number) {
                $year = now()->year;
                $lastOrder = CuttingOrder::withoutGlobalScope(TenantScope::class)
                    ->where('tenant_id', auth()->user()->tenant_id)
                    ->whereYear('created_at', $year)
                    ->latest('id')
                    ->first();

                $nextNumber = $lastOrder ? ((int) substr($lastOrder->order_number, -3)) + 1 : 1;
                $order->order_number = sprintf('CO-%d-%03d', $year, $nextNumber);
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function pattern(): BelongsTo
    {
        return $this->belongsTo(Pattern::class);
    }

    public function cutter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cutter_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(CuttingResult::class);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'in_progress']);
    }

    public function canBeDeleted(): bool
    {
        return $this->status === 'draft';
    }
}
