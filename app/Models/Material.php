<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'type',
        'description',
        'unit',
        'standard_price',
        'current_stock',
        'reorder_point',
        'is_active',
        'attributes',
    ];

    protected function casts(): array
    {
        return [
            'standard_price' => 'decimal:2',
            'current_stock' => 'decimal:2',
            'reorder_point' => 'decimal:2',
            'is_active' => 'boolean',
            'attributes' => 'array',
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

    public function receipts(): HasMany
    {
        return $this->hasMany(MaterialReceipt::class);
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(MaterialAttribute::class);
    }

    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->reorder_point;
    }
}
