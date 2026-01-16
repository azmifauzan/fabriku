<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'material_type_id',
        'code',
        'name',
        'supplier_name',
        'price_per_unit',
        'stock_quantity',
        'min_stock',
        'reorder_point',
        'unit',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'price_per_unit' => 'decimal:2',
            'stock_quantity' => 'decimal:3',
            'min_stock' => 'decimal:3',
            'reorder_point' => 'decimal:3',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Material $material) {
            if (auth()->check() && ! $material->tenant_id) {
                $material->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function materialType(): BelongsTo
    {
        return $this->belongsTo(MaterialType::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(MaterialReceipt::class);
    }

    public function materialAttributes(): HasMany
    {
        return $this->hasMany(MaterialAttribute::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->min_stock;
    }
}
