<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Models\Traits\HasAuditLogs;
use Database\Factories\ContractorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contractor extends Model
{
    /** @use HasFactory<ContractorFactory> */
    use HasAuditLogs, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'contact_person',
        'phone',
        'email',
        'address',
        'type',
        'specialty',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Contractor $contractor) {
            if (! $contractor->tenant_id) {
                $contractor->tenant_id = auth()->user()?->tenant_id;
            }

            if (empty($contractor->code)) {
                $contractor->code = self::generateCodeForTenant($contractor->tenant_id);
            }
        });
    }

    public static function generateCode(): string
    {
        // Get tenant_id from authenticated user
        $tenantId = auth()->user()?->tenant_id;

        if (! $tenantId) {
            throw new \Exception('Cannot generate contractor code without tenant_id');
        }

        return self::generateCodeForTenant($tenantId);
    }

    public static function generateCodeForTenant(?int $tenantId): string
    {
        if (! $tenantId) {
            throw new \Exception('Cannot generate contractor code without tenant_id');
        }

        $baseQuery = fn () => self::withoutGlobalScopes([TenantScope::class])
            ->withTrashed()
            ->where('tenant_id', $tenantId)
            ->where('code', 'like', 'CNT-%');

        $maxNumber = $baseQuery()
            ->selectRaw('MAX(CAST(SUBSTRING(code, 5) AS INTEGER)) as max_num')
            ->value('max_num') ?? 0;

        do {
            $maxNumber++;
            $code = 'CNT-'.str_pad($maxNumber, 4, '0', STR_PAD_LEFT);
        } while ($baseQuery()->where('code', $code)->exists());

        return $code;
    }

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function productionOrders(): HasMany
    {
        return $this->hasMany(ProductionOrder::class);
    }

    // Helper methods
    public function isIndividual(): bool
    {
        return $this->type === 'individual';
    }

    public function isCompany(): bool
    {
        return $this->type === 'company';
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeIndividual($query)
    {
        return $query->where('type', 'individual');
    }

    public function scopeCompany($query)
    {
        return $query->where('type', 'company');
    }
}
