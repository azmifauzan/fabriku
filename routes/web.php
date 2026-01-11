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
});
