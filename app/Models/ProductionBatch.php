<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'production_order_id',
        'batch_number',
        'received_by',
        'quantity_received',
        'quantity_good',
        'quantity_defect',
        'quantity_reject',
        'grade',
        'labor_cost_actual',
        'production_cost',
        'production_date',
        'received_date',
        'expiry_date',
        'qc_notes',
        'defect_reasons',
        'qc_checklist',
    ];

    protected function casts(): array
    {
        return [
            'production_date' => 'date',
            'received_date' => 'date',
            'expiry_date' => 'date',
            'labor_cost_actual' => 'decimal:2',
            'production_cost' => 'decimal:2',
            'qc_checklist' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (ProductionBatch $batch) {
            if (auth()->check() && ! $batch->tenant_id) {
                $batch->tenant_id = auth()->user()->tenant_id;
            }

            // Auto-generate batch number
            if (! $batch->batch_number) {
                $year = now()->year;
                $lastBatch = ProductionBatch::withoutGlobalScope(TenantScope::class)
                    ->where('tenant_id', auth()->user()->tenant_id)
                    ->whereYear('created_at', $year)
                    ->latest('id')
                    ->first();

                $nextNumber = $lastBatch ? ((int) substr($lastBatch->batch_number, -3)) + 1 : 1;
                $batch->batch_number = sprintf('PB-%d-%03d', $year, $nextNumber);
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    // Quality helpers
    public function isGradeA(): bool
    {
        return $this->grade === 'A';
    }

    public function isGradeB(): bool
    {
        return $this->grade === 'B';
    }

    public function isGradeC(): bool
    {
        return $this->grade === 'C';
    }

    public function isReject(): bool
    {
        return $this->grade === 'reject';
    }

    public function qualityPercentage(): float
    {
        if ($this->quantity_received === 0) {
            return 0;
        }

        return round(($this->quantity_good / $this->quantity_received) * 100, 2);
    }

    public function defectPercentage(): float
    {
        if ($this->quantity_received === 0) {
            return 0;
        }

        return round((($this->quantity_defect + $this->quantity_reject) / $this->quantity_received) * 100, 2);
    }

    public function isNearExpiry(int $days = 7): bool
    {
        if (! $this->expiry_date) {
            return false;
        }

        return $this->expiry_date->diffInDays(now()) <= $days;
    }

    public function isExpired(): bool
    {
        if (! $this->expiry_date) {
            return false;
        }

        return $this->expiry_date->isPast();
    }

    // Scopes
    public function scopeByQuality($query, string $grade)
    {
        return $query->where('grade', $grade);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('received_date', '>=', now()->subDays($days));
    }

    public function scopeNearExpiry($query, int $days = 7)
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now());
    }
}
