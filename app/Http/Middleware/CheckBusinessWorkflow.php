<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckBusinessWorkflow
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()->tenant->usesStockSalesOnly() && $request->routeIs(
            'materials.*', 'material-receipts.*', 'material-types.*',
            'patterns.*', 'preparation-orders.*', 'contractors.*',
            'production-orders.*', 'simple-production.*', 'purchase-receipts.*', 'services.*',
            'reports.material*', 'reports.production*', 'reports.purchase*', 'reports.service*',
            'website.services', 'website.services.*', 'website.leads.convert',
        )) {
            abort(403, 'Fitur ini tidak digunakan dalam mode stok dan penjualan saja.');
        }

        return $next($request);
    }
}
