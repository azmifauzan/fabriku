<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard', [
            'stats' => [
                'total_materials' => 0,
                'total_inventory' => 0,
                'total_sales_month' => 0,
                'pending_orders' => 0,
            ],
        ]);
    }
}
