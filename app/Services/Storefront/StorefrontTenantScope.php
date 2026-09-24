<?php

namespace App\Services\Storefront;

use App\Models\BusinessSite;
use App\Models\Service;
use App\Models\SiteProduct;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;

class StorefrontTenantScope
{
    public function __construct(
        protected Tenant $tenant
    ) {}

    public function tenant(): Tenant
    {
        return $this->tenant;
    }

    public function site(): ?BusinessSite
    {
        return BusinessSite::where('tenant_id', $this->tenant->id)->first();
    }

    /**
     * Query visible site products for this tenant explicitly.
     */
    public function products(): Builder
    {
        return SiteProduct::withoutGlobalScopes()
            ->where('tenant_id', $this->tenant->id)
            ->where('is_visible', true)
            ->orderBy('sort')
            ->orderBy('id');
    }

    /**
     * Query public and active services for this tenant explicitly.
     */
    public function services(): Builder
    {
        return Service::withoutGlobalScopes()
            ->where('tenant_id', $this->tenant->id)
            ->where('is_public', true)
            ->where('is_active', true);
    }
}
