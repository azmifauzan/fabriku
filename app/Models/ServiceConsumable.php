<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceConsumable extends Model
{
    protected $guarded = ['id'];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (ServiceConsumable $consumable) {
            if (! $consumable->tenant_id && auth()->check()) {
                $consumable->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
