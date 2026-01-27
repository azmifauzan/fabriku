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
        'production_order_id',
        'location_id',
        'product_name',
        'product_code',
        'target_quantity',
        'current_quantity',
        'reserved_quantity',
        'minimum_stock',
        'quality_grade',
        'status',
        'unit_cost',
        'selling_price',
        'expired_date',
        'notes',
        'image_path',
    ];

    protected $appends = [
        'current_stock',
        'reserved_stock',
        'inventory_location_id',
        'name',
        'pattern',
        'batch_number',
        'expiry_date',
        'image_url',
    ];

    protected function casts(): array
    {
        return [
            'unit_cost' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'expired_date' => 'date',
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
            if ($item->expired_date) {
                if ($item->expired_date->isPast()) {
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

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function inventoryLocation(): BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class, 'location_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class, 'location_id');
    }

    // Helper methods
    public function getAvailableStockAttribute(): int
    {
        return max(0, $this->current_quantity - $this->reserved_quantity);
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->current_quantity <= $this->minimum_stock;
    }

    public function getTotalValueAttribute(): float
    {
        return $this->current_quantity * $this->unit_cost;
    }

    public function isLowStock(): bool
    {
        return $this->current_quantity <= $this->minimum_stock;
    }

    public function isExpiringSoon(int $days = 7): bool
    {
        if (! $this->expired_date) {
            return false;
        }

        return $this->expired_date->diffInDays(now()) <= $days;
    }

    public function isExpired(): bool
    {
        if (! $this->expired_date) {
            return false;
        }

        return $this->expired_date->isPast();
    }

    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (! $this->expired_date) {
            return null;
        }

        return max(0, $this->expired_date->diffInDays(now()));
    }

    public function getCategoryLabelAttribute(): string
    {
        return $this->tenant?->getCategoryLabel() ?? 'Lainnya';
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

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return \Storage::disk('fabriku_s3')->temporaryUrl(
            $this->image_path,
            now()->addMinutes(30)
        );
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

        $this->increment('reserved_quantity', $quantity);

        return true;
    }

    public function releaseReservedStock(int $quantity): bool
    {
        if ($this->reserved_quantity < $quantity) {
            return false;
        }

        $this->decrement('reserved_quantity', $quantity);

        return true;
    }

    public function deductStock(int $quantity): bool
    {
        if ($this->current_quantity < $quantity) {
            return false;
        }

        $this->decrement('current_quantity', $quantity);

        return true;
    }

    public function getCurrentStockAttribute(): int
    {
        return (int) ($this->attributes['current_quantity'] ?? 0);
    }

    public function setCurrentStockAttribute(int $value): void
    {
        $this->attributes['current_quantity'] = $value;
    }

    public function getReservedStockAttribute(): int
    {
        return (int) ($this->attributes['reserved_quantity'] ?? 0);
    }

    public function setReservedStockAttribute(int $value): void
    {
        $this->attributes['reserved_quantity'] = $value;
    }

    // Static methods
    public static function generateSku(InventoryItem $item): string
    {
        // Get tenant's business category
        $tenant = Tenant::find($item->tenant_id);
        $category = $tenant?->business_category ?? 'OTHER';

        // Generate prefix based on category
        $prefix = match (strtoupper($category)) {
            'GARMENT' => 'INV-GRM',
            'FOOD' => 'INV-FOOD',
            'CRAFT' => 'INV-CRFT',
            default => 'INV-ITM',
        };

        // Find next sequential number for this tenant and prefix
        $lastItem = static::withoutGlobalScope(TenantScope::class)
            ->where('tenant_id', $item->tenant_id)
            ->where('sku', 'LIKE', $prefix.'%')
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastItem && preg_match('/(\d+)$/', $lastItem->sku, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        }

        // Generate SKU with uniqueness check
        do {
            $sku = sprintf('%s-%04d', $prefix, $nextNumber);
            $exists = static::withoutGlobalScope(TenantScope::class)
                ->where('tenant_id', $item->tenant_id)
                ->where('sku', $sku)
                ->exists();

            if ($exists) {
                $nextNumber++;
            }
        } while ($exists);

        return $sku;
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->whereHas('tenant', function ($tenantQuery) use ($category) {
            $tenantQuery->where('business_category', $category);
        });
    }

    public function scopeExpiring($query, int $days = 7)
    {
        return $query->whereNotNull('expired_date')
            ->where('expired_date', '<=', now()->addDays($days))
            ->where('expired_date', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expired_date')
            ->where('expired_date', '<', now());
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('current_quantity', '<=', 'minimum_stock');
    }

    public function getInventoryLocationIdAttribute(): ?int
    {
        return $this->location_id;
    }

    public function setInventoryLocationIdAttribute(?int $value): void
    {
        $this->attributes['location_id'] = $value;
    }

    public function getNameAttribute(): string
    {
        return (string) ($this->product_name ?? '');
    }

    public function setNameAttribute(string $value): void
    {
        $this->attributes['product_name'] = $value;
    }

    public function getPatternAttribute(): ?Pattern
    {
        return $this->productionOrder?->preparationOrder?->pattern;
    }

    public function getBatchNumberAttribute(): ?string
    {
        return null;
    }

    public function getExpiryDateAttribute(): mixed
    {
        return $this->expired_date;
    }

    public function scopeInLocation($query, int $locationId)
    {
        return $query->where('location_id', $locationId);
    }

    public function scopeByQualityGrade($query, string $grade)
    {
        return $query->where('quality_grade', $grade);
    }
}
