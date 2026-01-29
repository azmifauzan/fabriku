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
        'unit',
        'price_per_unit',
        'total_cost',
        'rolls_count',
        'length_per_roll',
        'batch_number',
        'notes',
        'received_by',
        'attachments',
        'remaining_quantity',
        'status',
        'barcode',
        'image_path',
    ];

    protected $appends = ['image_url'];

    protected function casts(): array
    {
        return [
            'receipt_date' => 'date',
            'quantity' => 'decimal:2',
            'price_per_unit' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'length_per_roll' => 'decimal:2',
            'attachments' => 'array',
            'remaining_quantity' => 'decimal:3',
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
            $receipt->material->increment('stock_quantity', $receipt->quantity);
        });

        static::creating(function (MaterialReceipt $receipt) {
            if (empty($receipt->barcode)) {
                $receipt->barcode = $receipt->generateBarcode();
            }
            if (! isset($receipt->remaining_quantity)) {
                $receipt->remaining_quantity = $receipt->quantity;
            }
        });
    }

    public function generateBarcode(): string
    {
        // Simple barcode based on timestamp and random string or UUID
        // For now using uniqid to ensure uniqueness
        return 'BAT-'.strtoupper(uniqid());
    }

    public function usages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PreparationMaterialUsage::class);
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

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return \Illuminate\Support\Facades\Storage::disk('fabriku_s3')->temporaryUrl(
            $this->image_path,
            now()->addMinutes(30)
        );
    }
}
