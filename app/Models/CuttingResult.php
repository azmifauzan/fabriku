<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CuttingResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'cutting_order_id',
        'material_id',
        'material_used',
        'material_wasted',
        'waste_percentage',
        'actual_quantity',
        'defect_quantity',
        'efficiency_percentage',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'material_used' => 'decimal:2',
            'material_wasted' => 'decimal:2',
            'waste_percentage' => 'decimal:2',
            'efficiency_percentage' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CuttingResult $result) {
            // Auto-calculate waste percentage
            if ($result->material_used > 0) {
                $result->waste_percentage = ($result->material_wasted / $result->material_used) * 100;
            }

            // Auto-calculate efficiency
            $order = $result->cuttingOrder;
            if ($order && $order->target_quantity > 0) {
                $goodQuantity = $result->actual_quantity - $result->defect_quantity;
                $result->efficiency_percentage = ($goodQuantity / $order->target_quantity) * 100;
            }
        });

        // Deduct material stock after cutting result created
        static::created(function (CuttingResult $result) {
            $material = $result->material;
            if ($material) {
                $material->decrement('current_stock', $result->material_used);
            }
        });
    }

    public function cuttingOrder(): BelongsTo
    {
        return $this->belongsTo(CuttingOrder::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function getGoodQuantityAttribute(): int
    {
        return $this->actual_quantity - $this->defect_quantity;
    }
}
