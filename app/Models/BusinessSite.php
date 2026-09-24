<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessSite extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'mode',
        'slug',
        'custom_domain',
        'cloudflare_hostname_id',
        'domain_status',
        'active_theme_version_id',
        'profile',
        'seo',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'profile' => 'array',
            'seo' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function activeThemeVersion(): BelongsTo
    {
        return $this->belongsTo(SiteThemeVersion::class, 'active_theme_version_id');
    }

    public function themeVersions(): HasMany
    {
        return $this->hasMany(SiteThemeVersion::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(SiteProduct::class, 'tenant_id', 'tenant_id');
    }

    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'business_site_recipients')
            ->withTimestamps();
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Storefront is open for orders when published, not paused, not suspended, and tenant is active.
     */
    public function canAcceptOrders(): bool
    {
        return $this->isPublished() &&
            ! $this->isPaused() &&
            ! $this->isSuspended() &&
            $this->tenant &&
            $this->tenant->isActive();
    }

    public function getStorefrontUrl(): string
    {
        if ($this->custom_domain && $this->domain_status === 'active') {
            return 'https://'.$this->custom_domain;
        }

        $base = config('app.storefront_domain', 'fabriku.biz.id');

        return "https://{$this->slug}.{$base}";
    }

    public static function isReservedSlug(string $slug): bool
    {
        $reserved = config('plans.reserved_slugs', [
            'www', 'app', 'admin', 'api', 'mail', 'smtp', 'ftp',
            'blog', 'help', 'status', 'toko', 'cdn', 'static', 'assets',
            'fabriku', 'satsetui', 'repliz',
        ]);

        return in_array(strtolower(trim($slug)), $reserved, true);
    }
}
