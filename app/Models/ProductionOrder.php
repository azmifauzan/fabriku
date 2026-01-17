<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'order_number',
        'preparation_order_id',
        'type',
        'contractor_id',
        'quantity_requested',
        'quantity_produced',
        'quantity_good',
        'quantity_reject',
        'labor_cost',
        'estimated_completion_date',
        'sent_date',
        'completed_date',
        'status',
        'priority',
        'notes',
        'completion_notes',
    ];

    protected function casts(): array
    {
        return [
            'estimated_completion_date' => 'date',
            'sent_date' => 'date',
            'completed_date' => 'date',
            'labor_cost' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (ProductionOrder $order) {
            if (auth()->check() && ! $order->tenant_id) {
                $order->tenant_id = auth()->user()->tenant_id;
            }

            // Auto-generate order number
            if (! $order->order_number && $order->tenant_id) {
                $year = now()->year;
                $nextNumber = 1;

                // Find the highest order number for this tenant and year
                $lastOrder = ProductionOrder::withoutGlobalScope(TenantScope::class)
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
                    $orderNumber = sprintf('PO-%d-%03d', $year, $nextNumber);
                    $exists = ProductionOrder::withoutGlobalScope(TenantScope::class)
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
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function preparationOrder(): BelongsTo
    {
        return $this->belongsTo(PreparationOrder::class);
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ProductionBatch::class);
    }

    // Status helpers
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isInternal(): bool
    {
        return $this->type === 'internal';
    }

    public function isExternal(): bool
    {
        return $this->type === 'external';
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'pending', 'sent'], true);
    }

    public function canBeDeleted(): bool
    {
        return $this->status === 'draft' && $this->batches()->doesntExist();
    }

    public function canBeSent(): bool
    {
        return $this->isExternal() && in_array($this->status, ['draft', 'pending'], true);
    }

    public function canBeReceived(): bool
    {
        return in_array($this->status, ['sent', 'in_progress']);
    }

    public function efficiency(): float
    {
        if ($this->quantity_produced === 0) {
            return 0;
        }

        return round(($this->quantity_good / $this->quantity_produced) * 100, 2);
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByContractor($query, int $contractorId)
    {
        return $query->where('contractor_id', $contractorId);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'pending']);
    }

    public function scopeInProgress($query)
    {
        return $query->whereIn('status', ['sent', 'in_progress']);
    }
}
