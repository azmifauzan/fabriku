<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contractor extends Model
{
    /** @use HasFactory<\Database\Factories\ContractorFactory> */
    use HasFactory, SoftDeletes;

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
                $contractor->tenant_id = auth()->user()->tenant_id;
            }

            if (empty($contractor->code)) {
                $contractor->code = self::generateCode();
            }
        });
    }

    public static function generateCode(): string
    {
        $lastContractor = self::withoutGlobalScope(TenantScope::class)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->latest('id')
            ->first();

        $nextNumber = $lastContractor ? (int) substr($lastContractor->code, -4) + 1 : 1;

        return 'CNT-'.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
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
