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
    ];

    protected $appends = ['proof_url'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'duration_months' => 'integer',
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
            return Storage::disk('fabriku_s3')->temporaryUrl(
                $this->proof_path,
                now()->addMinutes(30)
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
