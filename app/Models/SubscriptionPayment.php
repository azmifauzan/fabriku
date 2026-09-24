<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SubscriptionPayment extends Model
{
    protected $fillable = [
        'tenant_id',
        'amount',
        'proof_path',
        'status',
        'admin_id',
        'rejection_reason',
        'plan_type',
        'duration_months',
        'kind',
        'billing_cycle',
        'period_start',
        'period_end',
        'core_amount',
        'pro_amount',
        'payment_method',
        'provider',
        'provider_order_id',
        'provider_payment_id',
        'provider_amount',
        'provider_fee',
        'payment_url',
        'provider_payload',
        'event_id',
        'paid_at',
    ];

    protected $appends = ['proof_url'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'core_amount' => 'decimal:2',
            'pro_amount' => 'decimal:2',
            'provider_amount' => 'integer',
            'provider_fee' => 'integer',
            'duration_months' => 'integer',
            'period_start' => 'datetime',
            'period_end' => 'datetime',
            'provider_payload' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    /**
     * Get the proof URL for the payment.
     */
    public function getProofUrlAttribute(): ?string
    {
        if (! $this->proof_path) {
            return null;
        }

        // Check if it's stored in S3 (fabriku_s3)
        if (str_starts_with($this->proof_path, 'tenants/')) {
            return Storage::disk(config('filesystems.uploads_disk', 'fabriku_s3'))->temporaryUrl(
                $this->proof_path,
                now()->addMinutes(config('filesystems.url_ttl_minutes', 25))
            );
        }

        // Fallback for old files stored in public disk
        return Storage::disk('public')->url($this->proof_path);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function admin()
    {
        return $this->belongsTo(AdminUser::class, 'admin_id');
    }
}
