<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Landing Page
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
});

Route::post('logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Protected Routes
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Material Management
    Route::resource('materials', \App\Http\Controllers\MaterialController::class);
    Route::resource('material-receipts', \App\Http\Controllers\MaterialReceiptController::class);
    Route::resource('material-types', \App\Http\Controllers\MaterialTypeController::class);
    Route::resource('staff', \App\Http\Controllers\StaffController::class);

    // Pattern & Preparation Management (renamed from Cutting)
    Route::resource('patterns', \App\Http\Controllers\PatternController::class);
    Route::resource('preparation-orders', \App\Http\Controllers\PreparationOrderController::class);

    // Production Management
    Route::resource('contractors', \App\Http\Controllers\ContractorController::class);
    Route::resource('production-orders', \App\Http\Controllers\ProductionOrderController::class);
    Route::post('production-orders/{production_order}/send', [\App\Http\Controllers\ProductionOrderController::class, 'send'])
        ->name('production-orders.send');
    Route::post('production-orders/{production_order}/receive', [\App\Http\Controllers\ProductionOrderController::class, 'receive'])
        ->name('production-orders.receive');
    Route::post('production-orders/{production_order}/mark-complete', [\App\Http\Controllers\ProductionOrderController::class, 'markComplete'])
        ->name('production-orders.mark-complete');

    // Inventory Management
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::resource('locations', \App\Http\Controllers\InventoryLocationController::class);
        Route::resource('items', \App\Http\Controllers\InventoryItemController::class);
    });

    // Sales Management
    Route::resource('customers', \App\Http\Controllers\CustomerController::class);
    Route::resource('sales-orders', \App\Http\Controllers\SalesOrderController::class);

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('material', [\App\Http\Controllers\ReportController::class, 'material'])->name('material');
        Route::get('inventory', [\App\Http\Controllers\ReportController::class, 'inventory'])->name('inventory');
        Route::get('sales', [\App\Http\Controllers\ReportController::class, 'sales'])->name('sales');
        Route::get('production', [\App\Http\Controllers\ReportController::class, 'production'])->name('production');
    });
});
