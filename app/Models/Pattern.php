<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pattern extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'product_type',
        'size',
        'description',
        'estimated_time',
        'standard_waste_percentage',
        'image_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'estimated_time' => 'decimal:2',
            'standard_waste_percentage' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Pattern $pattern) {
            if (auth()->check() && ! $pattern->tenant_id) {
                $pattern->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function materials(): BelongsToMany
    {
        return $this->belongsToMany(Material::class, 'pattern_materials')
            ->withPivot('quantity_needed', 'notes')
            ->withTimestamps();
    }

    public function cuttingOrders(): HasMany
    {
        return $this->hasMany(CuttingOrder::class);
    }

    public function calculateMaterialCost(): float
    {
        return $this->materials->sum(function ($material) {
            return (float) $material->standard_price * (float) $material->pivot->quantity_needed;
        });
    }
}
