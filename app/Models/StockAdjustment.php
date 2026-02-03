<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Models\Traits\HasAuditLogs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    use HasAuditLogs, HasFactory;

    // Adjustment types
    public const TYPE_OPENING_BALANCE = 'opening_balance';

    public const TYPE_CORRECTION = 'correction';

    public const TYPE_DAMAGE = 'damage';

    public const TYPE_LOSS = 'loss';

    public const TYPE_FOUND = 'found';

    public const TYPE_RETURN = 'return';

    protected $fillable = [
        'tenant_id',
        'inventory_item_id',
        'adjustment_type',
        'quantity_before',
        'quantity_after',
        'adjustment_quantity',
        'reason',
        'notes',
        'adjusted_by',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (StockAdjustment $adjustment) {
            if (! $adjustment->tenant_id) {
                $adjustment->tenant_id = auth()->user()->tenant_id;
            }
            if (! $adjustment->adjusted_by) {
                $adjustment->adjusted_by = auth()->id();
            }
        });
    }

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function adjustedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Helper methods
    public function isOpeningBalance(): bool
    {
        return $this->adjustment_type === self::TYPE_OPENING_BALANCE;
    }

    public function isCorrection(): bool
    {
        return $this->adjustment_type === self::TYPE_CORRECTION;
    }

    public function isApproved(): bool
    {
        return ! is_null($this->approved_by);
    }

    public function getAdjustmentLabelAttribute(): string
    {
        return match ($this->adjustment_type) {
            self::TYPE_OPENING_BALANCE => 'Stock Awal',
            self::TYPE_CORRECTION => 'Koreksi',
            self::TYPE_DAMAGE => 'Rusak',
            self::TYPE_LOSS => 'Hilang',
            self::TYPE_FOUND => 'Ditemukan',
            self::TYPE_RETURN => 'Retur',
            default => ucfirst($this->adjustment_type),
        };
    }

    public static function getAdjustmentTypes(): array
    {
        return [
            self::TYPE_OPENING_BALANCE => 'Stock Awal',
            self::TYPE_CORRECTION => 'Koreksi',
            self::TYPE_DAMAGE => 'Rusak',
            self::TYPE_LOSS => 'Hilang',
            self::TYPE_FOUND => 'Ditemukan',
            self::TYPE_RETURN => 'Retur',
        ];
    }
}
