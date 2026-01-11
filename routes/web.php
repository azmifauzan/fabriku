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

    // Pattern & Cutting Management
    Route::resource('patterns', \App\Http\Controllers\PatternController::class);
    Route::resource('cutting-orders', \App\Http\Controllers\CuttingOrderController::class);

    // Production Management
    Route::resource('contractors', \App\Http\Controllers\ContractorController::class);
    Route::resource('production-orders', \App\Http\Controllers\ProductionOrderController::class);
    Route::post('production-orders/{production_order}/send', [\App\Http\Controllers\ProductionOrderController::class, 'send'])
        ->name('production-orders.send');
    Route::post('production-orders/{production_order}/receive', [\App\Http\Controllers\ProductionOrderController::class, 'receive'])
        ->name('production-orders.receive');

    // Inventory Management
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::resource('locations', \App\Http\Controllers\InventoryLocationController::class);
        Route::resource('items', \App\Http\Controllers\InventoryItemController::class);
    });
});
