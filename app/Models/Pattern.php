<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pattern extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'category',
        'size',
        'output_quantity',
        'description',
        'material_requirements',
        'estimated_labor_cost',
        'instructions',
    ];

    protected function casts(): array
    {
        return [
            'material_requirements' => 'array',
            'estimated_labor_cost' => 'decimal:2',
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

    // Removed: materials() relationship - no more BOM

    public function preparationOrders(): HasMany
    {
        return $this->hasMany(PreparationOrder::class);
    }

    public function canBeDeleted(): bool
    {
        return $this->preparationOrders()->doesntExist();
    }
}
