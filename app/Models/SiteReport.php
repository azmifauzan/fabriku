<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteReport extends Model
{
    protected $fillable = ['business_site_id', 'tenant_id', 'category', 'details', 'contact_email', 'status'];

    public function businessSite(): BelongsTo
    {
        return $this->belongsTo(BusinessSite::class);
    }
}
