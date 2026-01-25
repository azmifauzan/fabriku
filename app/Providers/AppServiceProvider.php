<?php

namespace App\Providers;

use App\Models\InventoryItem;
use App\Observers\InventoryObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observers
        InventoryItem::observe(InventoryObserver::class);
        \App\Models\SalesOrder::observe(\App\Observers\SalesOrderObserver::class);
        \App\Models\SalesOrderItem::observe(\App\Observers\SalesOrderItemObserver::class);
    }
}
