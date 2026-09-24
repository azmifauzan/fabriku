<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Models\Traits\HasAuditLogs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class SiteProduct extends Model
{
    use HasAuditLogs, HasFactory;

    protected $fillable = [
        'tenant_id',
        'product_code',
        'slug',
        'title',
        'description',
        'image_path',
        'sort',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'sort' => 'integer',
            'is_visible' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (SiteProduct $item) {
            if (auth()->check() && ! $item->tenant_id) {
                $item->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class, 'product_code', 'product_code')
            ->where('inventory_items.tenant_id', $this->tenant_id);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true)->orderBy('sort')->orderBy('id');
    }

    /**
     * Get the lowest selling price among inventory rows for this product.
     */
    public function getStartingPrice(): float
    {
        $minPrice = $this->inventoryItems()
            ->whereNotNull('selling_price')
            ->where('selling_price', '>', 0)
            ->min('selling_price');

        return (float) ($minPrice ?? 0);
    }

    /**
     * Check if prices vary across different inventory batches/racks.
     */
    public function hasPriceVariation(): bool
    {
        $prices = $this->inventoryItems()
            ->whereNotNull('selling_price')
            ->distinct()
            ->pluck('selling_price');

        return $prices->count() > 1;
    }

    /**
     * Calculate total available stock across physical items.
     */
    public function getTotalAvailableStock(): float
    {
        $items = $this->inventoryItems()->get();

        return (float) $items->sum(function ($item) {
            return max(0, (float) $item->current_quantity - (float) $item->reserved_quantity);
        });
    }

    /**
     * Get availability label without revealing exact stock count:
     * - 'Tersedia'
     * - 'Stok terbatas' (<= minimum_stock)
     * - 'Habis'
     */
    public function getAvailabilityStatus(): string
    {
        $totalStock = $this->getTotalAvailableStock();

        if ($totalStock <= 0) {
            return 'Habis';
        }

        $maxMinStock = $this->inventoryItems()->max('minimum_stock');
        $minThreshold = ($maxMinStock && $maxMinStock > 0) ? (float) $maxMinStock : 5.0;

        if ($totalStock <= $minThreshold) {
            return 'Stok terbatas';
        }

        return 'Tersedia';
    }

    public function isAvailable(): bool
    {
        return $this->getTotalAvailableStock() > 0;
    }

    /**
     * Get product display image URL.
     */
    public function getImageUrl(): ?string
    {
        $path = $this->image_path;

        if (! $path) {
            $firstItem = $this->inventoryItems()->whereNotNull('image_path')->first();
            $path = $firstItem?->image_path;
        }

        if (! $path) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        if (str_starts_with($path, 'tenants/')) {
            return Storage::disk(config('filesystems.uploads_disk', 'fabriku_s3'))->url($path);
        }

        return Storage::disk('public')->url($path);
    }
}
