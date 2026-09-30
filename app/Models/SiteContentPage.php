<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteContentPage extends Model
{
    protected $fillable = [
        'tenant_id',
        'business_site_id',
        'slug',
        'title',
        'seo_title',
        'seo_description',
        'html',
        'css',
        'show_in_footer',
        'is_published',
        'sort',
    ];

    protected function casts(): array
    {
        return [
            'show_in_footer' => 'boolean',
            'is_published' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function businessSite(): BelongsTo
    {
        return $this->belongsTo(BusinessSite::class);
    }
}
