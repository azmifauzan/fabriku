<?php

namespace App\Providers;

use App\Models\InventoryItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Observers\InventoryObserver;
use App\Observers\SalesOrderItemObserver;
use App\Observers\SalesOrderObserver;
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
        SalesOrder::observe(SalesOrderObserver::class);
        SalesOrderItem::observe(SalesOrderItemObserver::class);
    }
}
