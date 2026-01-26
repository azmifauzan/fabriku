<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PreparationMaterialUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'preparation_order_id',
        'material_receipt_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
    ];

    public function preparationOrder(): BelongsTo
    {
        return $this->belongsTo(PreparationOrder::class);
    }

    public function materialReceipt(): BelongsTo
    {
        return $this->belongsTo(MaterialReceipt::class);
    }
}
