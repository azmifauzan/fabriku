<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Models\Traits\HasAuditLogs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    use HasAuditLogs, HasFactory;

    protected $fillable = [
        'tenant_id',
        'business_site_id',
        'service_id',
        'source',
        'source_ref',
        'name',
        'phone',
        'message',
        'consent_at',
        'consent_text',
        'status',
        'assigned_user_id',
        'contacted_at',
        'closed_reason',
        'sales_order_id',
        'idempotency_key',
        'notification_failed_at',
    ];

    protected $excludedFromAudit = [
        'name',
        'phone',
        'message',
        'consent_text',
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'consent_at' => 'datetime',
            'contacted_at' => 'datetime',
            'notification_failed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (self $lead) {
            if (! $lead->tenant_id && auth()->check()) {
                $lead->tenant_id = auth()->user()->tenant_id;
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function businessSite(): BelongsTo
    {
        return $this->belongsTo(BusinessSite::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function salesOrder(): BelongsTo
    {
        return $this->belongsTo(SalesOrder::class);
    }
}
