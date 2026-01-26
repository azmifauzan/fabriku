<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Services\MaterialStockService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PreparationOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'order_number',
        'pattern_id',
        'prepared_by',
        'output_quantity',
        'output_unit',
        'material_usage',
        'waste_percentage',
        'status',
        'preparation_date',
        'completed_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'preparation_date' => 'date',
            'completed_date' => 'date',
            'material_usage' => 'array',
            'waste_percentage' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (PreparationOrder $order) {
            if (auth()->check() && ! $order->tenant_id) {
                $order->tenant_id = auth()->user()->tenant_id;
            }

            // Auto-generate order number
            if (! $order->order_number && $order->tenant_id) {
                $year = now()->year;
                $nextNumber = 1;

                // Find the highest order number for this tenant and year
                $lastOrder = PreparationOrder::withoutGlobalScope(TenantScope::class)
                    ->where('tenant_id', $order->tenant_id)
                    ->whereYear('created_at', $year)
                    ->orderBy('id', 'desc')
                    ->first();

                if ($lastOrder && $lastOrder->order_number) {
                    // Extract number from order_number using regex
                    if (preg_match('/(\d+)$/', $lastOrder->order_number, $matches)) {
                        $nextNumber = (int) $matches[1] + 1;
                    }
                }

                // Loop until we find a unique order number
                do {
                    $orderNumber = sprintf('PRP-%d-%04d', $year, $nextNumber);
                    $exists = PreparationOrder::withoutGlobalScope(TenantScope::class)
                        ->where('tenant_id', $order->tenant_id)
                        ->where('order_number', $orderNumber)
                        ->exists();

                    if ($exists) {
                        $nextNumber++;
                    }
                } while ($exists);

                $order->order_number = $orderNumber;
            }
        });

        // Auto deduct stock when status changed to completed (updating)
        static::updating(function (PreparationOrder $order) {
            if ($order->isDirty('status') && $order->status === 'completed' && $order->getOriginal('status') !== 'completed') {
                app(MaterialStockService::class)->deductStock($order);
            }
        });

        // Also handle case when order is created directly with completed status
        static::created(function (PreparationOrder $order) {
            if ($order->status === 'completed') {
                app(MaterialStockService::class)->deductStock($order);
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function pattern(): BelongsTo
    {
        return $this->belongsTo(Pattern::class);
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'prepared_by');
    }

    public function productionOrders(): HasMany
    {
        return $this->hasMany(ProductionOrder::class);
    }

    public function materialUsages(): HasMany
    {
        return $this->hasMany(PreparationMaterialUsage::class);
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'in_progress']);
    }

    public function canBeDeleted(): bool
    {
        return $this->status === 'draft' && $this->productionOrders()->doesntExist();
    }
}
