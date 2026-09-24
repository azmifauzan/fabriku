<?php

namespace App\Services\Storefront;

use App\Models\BusinessSite;
use App\Models\Tenant;

class Storefront
{
    protected static ?BusinessSite $currentSite = null;

    protected static ?Tenant $currentTenant = null;

    /**
     * Create a scoped query helper explicitly bound to a tenant.
     */
    public static function for(Tenant $tenant): StorefrontTenantScope
    {
        return new StorefrontTenantScope($tenant);
    }

    /**
     * Bind active storefront site and tenant to the current request lifecycle.
     */
    public static function bind(BusinessSite $site, Tenant $tenant): void
    {
        static::$currentSite = $site;
        static::$currentTenant = $tenant;

        app()->instance('storefront_site', $site);
        app()->instance('storefront_tenant', $tenant);
    }

    public static function currentSite(): ?BusinessSite
    {
        return static::$currentSite ?? (app()->bound('storefront_site') ? app('storefront_site') : null);
    }

    public static function currentTenant(): ?Tenant
    {
        return static::$currentTenant ?? (app()->bound('storefront_tenant') ? app('storefront_tenant') : null);
    }

    public static function clear(): void
    {
        static::$currentSite = null;
        static::$currentTenant = null;

        if (app()->bound('storefront_site')) {
            app()->forgetInstance('storefront_site');
        }
        if (app()->bound('storefront_tenant')) {
            app()->forgetInstance('storefront_tenant');
        }
    }
}
