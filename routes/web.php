<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Landing Page
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// ==========================================
// ADMIN ROUTES
// ==========================================
Route::prefix('admin')->name('admin.')->group(function () {
    // Admin Authentication (Guest)
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [\App\Http\Controllers\Admin\AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('login', [\App\Http\Controllers\Admin\AdminAuthController::class, 'login']);
    });

    // Admin Logout
    Route::post('logout', [\App\Http\Controllers\Admin\AdminAuthController::class, 'logout'])
        ->middleware('auth:admin')
        ->name('logout');

    // Protected Admin Routes
    Route::middleware(['auth:admin', \App\Http\Middleware\AdminMiddleware::class])->group(function () {
        // Dashboard
        Route::get('/', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index'])->name('dashboard');

        // Tenant Management
        Route::resource('tenants', \App\Http\Controllers\Admin\AdminTenantController::class);
        Route::post('tenants/{tenant}/suspend', [\App\Http\Controllers\Admin\AdminTenantController::class, 'suspend'])->name('tenants.suspend');
        Route::post('tenants/{tenant}/activate', [\App\Http\Controllers\Admin\AdminTenantController::class, 'activate'])->name('tenants.activate');

        // User Management
        Route::resource('users', \App\Http\Controllers\Admin\AdminUserController::class);
        Route::post('users/{user}/reset-password', [\App\Http\Controllers\Admin\AdminUserController::class, 'resetPassword'])->name('users.reset-password');

        // Role Management
        Route::resource('roles', \App\Http\Controllers\Admin\AdminRoleController::class);

        // Audit Logs
        Route::get('audit-logs', [\App\Http\Controllers\Admin\AdminAuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('audit-logs/{auditLog}', [\App\Http\Controllers\Admin\AdminAuditLogController::class, 'show'])->name('audit-logs.show');
    });
});

// ==========================================
// TENANT ROUTES
// ==========================================


// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store']);
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
    Route::post('production-orders/{production_order}/start', [\App\Http\Controllers\ProductionOrderController::class, 'start'])
        ->name('production-orders.start');
    Route::post('production-orders/{production_order}/mark-complete', [\App\Http\Controllers\ProductionOrderController::class, 'markComplete'])
        ->name('production-orders.mark-complete');

    // Inventory Management
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('visualization', [\App\Http\Controllers\InventoryVisualizationController::class, 'index'])->name('visualization');
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
