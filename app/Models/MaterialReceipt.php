<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'receipt_number',
        'material_id',
        'supplier_name',
        'receipt_date',
        'quantity',
        'unit_price',
        'total_price',
        'rolls_count',
        'length_per_roll',
        'batch_number',
        'notes',
        'received_by',
        'attachments',
    ];

    protected function casts(): array
    {
        return [
            'receipt_date' => 'date',
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'length_per_roll' => 'decimal:2',
            'attachments' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (MaterialReceipt $receipt) {
            if (auth()->check() && ! $receipt->tenant_id) {
                $receipt->tenant_id = auth()->user()->tenant_id;
            }

            if (auth()->check() && ! $receipt->received_by) {
                $receipt->received_by = auth()->id();
            }
        });

        static::created(function (MaterialReceipt $receipt) {
            // Update material stock
            $receipt->material->increment('current_stock', $receipt->quantity);
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
