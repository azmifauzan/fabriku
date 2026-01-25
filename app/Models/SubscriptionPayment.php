<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'duration_months' => 'integer',
        ];
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
