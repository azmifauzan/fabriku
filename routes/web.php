<?php

use App\Http\Controllers\Admin\AdminAuditLogController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminMonitoringController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Admin\AdminRoleController;
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\Admin\AdminTenantController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ContractorController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryItemCategoryController;
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\InventoryLocationController;
use App\Http\Controllers\InventoryVisualizationController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\MaterialReceiptController;
use App\Http\Controllers\MaterialTypeController;
use App\Http\Controllers\PatternController;
use App\Http\Controllers\PreparationOrderController;
use App\Http\Controllers\ProductionOrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SalesOrderController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TelegramController;
use App\Http\Middleware\AdminMiddleware;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Landing Page
Route::get('/', function () {
    $settings = SystemSetting::getAllForTenant(null); // null = global/system-wide settings

    return Inertia::render('Welcome', [
        'settings' => [
            'membership_price_monthly' => $settings['membership_price_monthly'] ?? 25000,
            'membership_price_yearly' => $settings['membership_price_yearly'] ?? 250000,
            'pro_price_monthly' => $settings['pro_price_monthly'] ?? 35000,
            'pro_price_yearly' => $settings['pro_price_yearly'] ?? 350000,
        ],
    ]);
})->name('home');

// ==========================================
// ADMIN ROUTES
// ==========================================
Route::prefix('admin')->name('admin.')->group(function () {
    // Admin Authentication (Guest)
    Route::middleware('guest:admin')->group(function () {
        Route::get('login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AdminAuthController::class, 'login']);
    });

    // Admin Logout
    Route::post('logout', [AdminAuthController::class, 'logout'])
        ->middleware('auth:admin')
        ->name('logout');

    // Protected Admin Routes
    Route::middleware(['auth:admin', AdminMiddleware::class])->group(function () {
        // Dashboard
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Tenant Management
        Route::resource('tenants', AdminTenantController::class);
        Route::post('tenants/{tenant}/suspend', [AdminTenantController::class, 'suspend'])->name('tenants.suspend');
        Route::post('tenants/{tenant}/activate', [AdminTenantController::class, 'activate'])->name('tenants.activate');

        // User Management
        Route::resource('users', AdminUserController::class);
        Route::post('users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset-password');

        // Role Management
        Route::resource('roles', AdminRoleController::class);

        // Payments
        Route::get('payments', [AdminPaymentController::class, 'index'])->name('payments.index');
        Route::post('payments/{payment}/approve', [AdminPaymentController::class, 'approve'])->name('payments.approve');
        Route::post('payments/{payment}/reject', [AdminPaymentController::class, 'reject'])->name('payments.reject');

        // Settings
        Route::get('settings', [AdminSettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [AdminSettingController::class, 'update'])->name('settings.update');

        // Audit Logs
        Route::get('audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('audit-logs/{auditLog}', [AdminAuditLogController::class, 'show'])->name('audit-logs.show');

        // Monitoring
        Route::get('monitoring', [AdminMonitoringController::class, 'index'])->name('monitoring.index');
        Route::post('monitoring/jobs/{uuid}/retry', [AdminMonitoringController::class, 'retryJob'])->name('monitoring.jobs.retry');
        Route::delete('monitoring/jobs/{uuid}', [AdminMonitoringController::class, 'deleteJob'])->name('monitoring.jobs.delete');
        Route::post('monitoring/jobs/retry-all', [AdminMonitoringController::class, 'retryAllJobs'])->name('monitoring.jobs.retry-all');
        Route::post('monitoring/jobs/flush', [AdminMonitoringController::class, 'flushJobs'])->name('monitoring.jobs.flush');
        Route::post('monitoring/run-command', [AdminMonitoringController::class, 'runCommand'])->name('monitoring.run-command');
        Route::post('monitoring/test-telegram', [AdminMonitoringController::class, 'testTelegram'])->name('monitoring.test-telegram');
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

    // Password Reset Routes
    Route::get('forgot-password', [PasswordResetController::class, 'create'])
        ->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'store'])
        ->name('password.email');
    Route::get('reset-password/{token}', [PasswordResetController::class, 'reset'])
        ->name('password.reset');
    Route::post('reset-password', [PasswordResetController::class, 'update'])
        ->name('password.update');
});

// Email Verification Routes
Route::middleware('auth')->group(function () {
    Route::get('verify-email', [EmailVerificationController::class, 'notice'])
        ->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

Route::post('logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Protected Routes
Route::middleware(['auth', 'verified', 'tenant', 'subscription.check'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Material Management
    Route::resource('materials', MaterialController::class)
        ->middleware('permission:material.view');
    Route::resource('material-receipts', MaterialReceiptController::class)
        ->middleware('permission:material.view');
    Route::resource('material-types', MaterialTypeController::class)
        ->middleware('permission:material.view');

    // Staff Management (admin-only enforced in controller)
    Route::resource('staff', StaffController::class);

    // Pattern & Preparation Management (renamed from Cutting)
    Route::resource('patterns', PatternController::class)
        ->middleware('permission:pattern.view');
    Route::resource('preparation-orders', PreparationOrderController::class)
        ->middleware('permission:preparation.view');

    // Production Management
    Route::resource('contractors', ContractorController::class)
        ->middleware('permission:production.view');
    Route::resource('production-orders', ProductionOrderController::class)
        ->middleware('permission:production.view');
    Route::post('production-orders/{production_order}/send', [ProductionOrderController::class, 'send'])
        ->name('production-orders.send')
        ->middleware('permission:production.edit');
    Route::post('production-orders/{production_order}/start', [ProductionOrderController::class, 'start'])
        ->name('production-orders.start')
        ->middleware('permission:production.edit');
    Route::post('production-orders/{production_order}/mark-complete', [ProductionOrderController::class, 'markComplete'])
        ->name('production-orders.mark-complete')
        ->middleware('permission:production.edit');

    // Inventory Management
    Route::prefix('inventory')->name('inventory.')->middleware('permission:inventory.view')->group(function () {
        Route::get('visualization', [InventoryVisualizationController::class, 'index'])->name('visualization');
        Route::get('locations/{location}/qrcode/print', [InventoryLocationController::class, 'printQrCode'])->name('locations.qrcode.print');
        Route::get('locations/{location}/qrcode/generate', [InventoryLocationController::class, 'generateQrCode'])->name('locations.qrcode.generate');
        Route::resource('locations', InventoryLocationController::class);
        Route::get('categories', [InventoryItemCategoryController::class, 'index'])->name('categories.index');
        Route::post('categories', [InventoryItemCategoryController::class, 'store'])->name('categories.store');

        // QR Code routes
        Route::get('items/{item}/qrcode/print', [InventoryItemController::class, 'printQrCode'])->name('items.qrcode.print');
        Route::get('items/{item}/qrcode/generate', [InventoryItemController::class, 'generateQrCode'])->name('items.qrcode.generate');
        Route::post('items/scan-lookup', [InventoryItemController::class, 'scanLookup'])->name('items.scan-lookup');

        // Stock adjustment routes (must be before resource to avoid conflicts)
        Route::post('items/{item}/adjust', [InventoryItemController::class, 'adjustStock'])->name('items.adjust');
        Route::get('items/{item}/adjustments', [InventoryItemController::class, 'adjustmentHistory'])->name('items.adjustments');

        Route::resource('items', InventoryItemController::class);
    });

    // Sales Management
    Route::resource('customers', CustomerController::class)
        ->middleware('permission:sales.view');
    Route::get('sales-orders/{sales_order}/print', [SalesOrderController::class, 'print'])->name('sales-orders.print')
        ->middleware('permission:sales.view');
    Route::get('sales-orders/{sales_order}/delivery-order', [SalesOrderController::class, 'deliveryOrder'])->name('sales-orders.delivery-order')
        ->middleware('permission:sales.view');
    Route::get('sales-orders/{sales_order}/export', [SalesOrderController::class, 'export'])->name('sales-orders.export')
        ->middleware('permission:sales.view');
    Route::resource('sales-orders', SalesOrderController::class)
        ->middleware('permission:sales.view');

    // Reports
    Route::prefix('reports')->name('reports.')->middleware('permission:report.view')->group(function () {
        Route::get('material', [ReportController::class, 'material'])->name('material');
        Route::get('material/export', [ReportController::class, 'exportMaterial'])->name('material.export');
        Route::get('inventory', [ReportController::class, 'inventory'])->name('inventory');
        Route::get('inventory/export', [ReportController::class, 'exportInventory'])->name('inventory.export');
        Route::get('sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('sales/export', [ReportController::class, 'exportSales'])->name('sales.export');
        Route::get('sales-recap', [ReportController::class, 'salesRecap'])->name('sales-recap');
        Route::get('sales-recap/export', [ReportController::class, 'exportSalesRecap'])->name('sales-recap.export');
        Route::get('production', [ReportController::class, 'production'])->name('production');
        Route::get('production/export', [ReportController::class, 'exportProduction'])->name('production.export');
    });

    // Settings
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');

    // Subscription
    Route::get('subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('subscription', [SubscriptionController::class, 'store'])->name('subscription.store');

    // Assistant API
    Route::prefix('assistant')->name('assistant.')->group(function () {
        Route::post('message', [AssistantController::class, 'sendMessage'])->name('message');
        Route::get('history', [AssistantController::class, 'getHistory'])->name('history');
        Route::post('clear', [AssistantController::class, 'clearHistory'])->name('clear');
        Route::get('usage', [AssistantController::class, 'getUsage'])->name('usage');
        Route::get('status', [AssistantController::class, 'getStatus'])->name('status');
    });

    // Telegram Integration
    Route::prefix('telegram')->name('telegram.')->group(function () {
        Route::post('generate-token', [TelegramController::class, 'generateToken'])->name('generate-token');
        Route::post('disconnect', [TelegramController::class, 'disconnect'])->name('disconnect');
        Route::post('test', [TelegramController::class, 'testMessage'])->name('test');
    });

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('telegram', [TelegramController::class, 'index'])->name('telegram');
    });
});
