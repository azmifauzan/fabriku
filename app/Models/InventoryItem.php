<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    /** @use HasFactory<\Database\Factories\InventoryItemFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'sku',
        'name',
        'description',
        'pattern_id',
        'attributes',
        'category',
        'current_stock',
        'reserved_stock',
        'minimum_stock',
        'unit_cost',
        'selling_price',
        'inventory_location_id',
        'batch_number',
        'production_date',
        'expiry_date',
        'best_before_date',
        'quality_grade',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'attributes' => 'array',
            'unit_cost' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'production_date' => 'date',
            'expiry_date' => 'date',
            'best_before_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (InventoryItem $item) {
            if (! $item->tenant_id) {
                $item->tenant_id = auth()->user()->tenant_id;
            }

            // Auto-generate SKU if not provided
            if (! $item->sku) {
                $item->sku = static::generateSku($item);
            }
        });

        static::saving(function (InventoryItem $item) {
            // Auto-update status based on expiry for food items
            if ($item->category === 'food' && $item->expiry_date) {
                if ($item->expiry_date->isPast()) {
                    $item->status = 'expired';
                }
            }
        });
    }

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function pattern(): BelongsTo
    {
        return $this->belongsTo(Pattern::class);
    }

    public function inventoryLocation(): BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class);
    }

    // Helper methods
    public function getAvailableStockAttribute(): int
    {
        return max(0, $this->current_stock - $this->reserved_stock);
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->current_stock <= $this->minimum_stock;
    }

    public function getTotalValueAttribute(): float
    {
        return $this->current_stock * $this->unit_cost;
    }

    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->minimum_stock;
    }

    public function isExpiringSoon(int $days = 7): bool
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

    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (! $this->expiry_date) {
            return null;
        }

        return max(0, $this->expiry_date->diffInDays(now()));
    }

    public function getCategoryLabelAttribute(): string
    {
        return match ($this->category) {
            'garment' => 'Garment',
            'food' => 'Makanan',
            'craft' => 'Kerajinan',
            default => 'Lainnya',
        };
    }

    public function getQualityGradeLabelAttribute(): string
    {
        return match ($this->quality_grade) {
            'A' => 'Grade A (Perfect)',
            'B' => 'Grade B (Minor defects)',
            'C' => 'Grade C (Major defects)',
            'reject' => 'Reject (Not saleable)',
            default => $this->quality_grade,
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'available' => 'bg-green-100 text-green-800 dark:bg-green-800/20 dark:text-green-400',
            'reserved' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800/20 dark:text-yellow-400',
            'damaged' => 'bg-red-100 text-red-800 dark:bg-red-800/20 dark:text-red-400',
            'expired' => 'bg-gray-100 text-gray-800 dark:bg-gray-800/20 dark:text-gray-400',
            default => 'bg-blue-100 text-blue-800 dark:bg-blue-800/20 dark:text-blue-400',
        };
    }

    public function reserveStock(int $quantity): bool
    {
        if ($this->available_stock < $quantity) {
            return false;
        }

        $this->increment('reserved_stock', $quantity);

        return true;
    }

    public function releaseReservedStock(int $quantity): bool
    {
        if ($this->reserved_stock < $quantity) {
            return false;
        }

        $this->decrement('reserved_stock', $quantity);

        return true;
    }

    public function deductStock(int $quantity): bool
    {
        if ($this->current_stock < $quantity) {
            return false;
        }

        $this->decrement('current_stock', $quantity);

        return true;
    }

    // Static methods
    public static function generateSku(InventoryItem $item): string
    {
        $prefix = match ($item->category) {
            'garment' => 'GRM',
            'food' => 'FOOD',
            'craft' => 'CRF',
            default => 'ITM',
        };

        $count = static::where('category', $item->category)->count() + 1;

        return $prefix.'-'.str_pad($count, 6, '0', STR_PAD_LEFT);
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeExpiring($query, int $days = 7)
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

    public function scopeLowStock($query)
    {
        return $query->whereRaw('current_stock <= minimum_stock');
    }

    public function scopeInLocation($query, int $locationId)
    {
        return $query->where('inventory_location_id', $locationId);
    }

    public function scopeByQualityGrade($query, string $grade)
    {
        return $query->where('quality_grade', $grade);
    }
}
