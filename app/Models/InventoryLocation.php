<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryLocation extends Model
{
    /** @use HasFactory<\Database\Factories\InventoryLocationFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'capacity',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (InventoryLocation $location) {
            if (! $location->tenant_id) {
                $location->tenant_id = auth()->user()->tenant_id;
            }

            if (empty($location->code)) {
                $location->code = self::generateCode();
            }
        });
    }

    public static function generateCode(): string
    {
        $lastLocation = self::withoutGlobalScope(TenantScope::class)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->latest('id')
            ->first();

        $nextNumber = $lastLocation ? (int) substr($lastLocation->code, -4) + 1 : 1;

        return 'LOC-'.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class, 'location_id');
    }

    // Helper methods
    public function getFullLocationAttribute(): string
    {
        return $this->code.' - '.$this->name;
    }

    public function getAvailableCapacityAttribute(): ?int
    {
        if (! $this->capacity) {
            return PHP_INT_MAX; // Unlimited capacity
        }

        $usedCapacity = $this->inventoryItems()->sum('current_quantity');

        return max(0, $this->capacity - $usedCapacity);
    }

    public function getUsedCapacityAttribute(): int
    {
        return $this->inventoryItems()->sum('current_quantity');
    }

    public function isAvailable(): bool
    {
        return $this->is_active && ($this->available_capacity === null || $this->available_capacity > 0);
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->active()
            ->where(function ($q) {
                $q->whereNull('capacity')
                    ->orWhereRaw('capacity > (SELECT COALESCE(SUM(current_quantity), 0) FROM inventory_items WHERE location_id = inventory_locations.id)');
            });
    }
}
