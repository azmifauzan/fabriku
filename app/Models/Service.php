<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Models\Traits\HasAuditLogs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Service extends Model
{
    use HasAuditLogs;
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'price' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Service $service) {
            if (! $service->tenant_id && auth()->check()) {
                $service->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function salesOrderItems(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function consumables(): HasMany
    {
        return $this->hasMany(ServiceConsumable::class);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true)->where('is_active', true);
    }

    public function getPriceDisplay(): string
    {
        if ($this->price_label === 'hubungi_kami') {
            return 'Hubungi Kami';
        }

        $formatted = 'Rp '.number_format((float) $this->price, 0, ',', '.');

        if ($this->price_label === 'mulai_dari') {
            return 'Mulai dari '.$formatted;
        }

        return $formatted;
    }

    public function getImageUrl(): ?string
    {
        $path = $this->image_path;

        if (! $path) {
            return null;
        }

        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        if (str_starts_with($path, 'tenants/')) {
            return Storage::disk(config('filesystems.uploads_disk', 'fabriku_s3'))->url($path);
        }

        return Storage::disk('public')->url($path);
    }
}
