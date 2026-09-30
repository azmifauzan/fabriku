<?php

use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\StorefrontController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Storefront Routes
|--------------------------------------------------------------------------
|
| Independent from Inertia admin session routes. Rendered via Blade and
| scoped to the resolved BusinessSite and Tenant via ResolveStorefront.
|
*/

Route::get('/', [StorefrontController::class, 'home'])->name('storefront.home');
Route::get('/produk', [StorefrontController::class, 'products'])->name('storefront.products');
Route::get('/produk/{slug}', [StorefrontController::class, 'productDetail'])->name('storefront.product.detail');
Route::get('/layanan', [StorefrontController::class, 'services'])->name('storefront.services');
Route::get('/layanan/{slug}', [StorefrontController::class, 'serviceDetail'])->name('storefront.service.detail');
Route::get('/halaman/{slug}', [StorefrontController::class, 'contentPage'])->name('storefront.content-page');
Route::get('/keranjang', [StorefrontController::class, 'cart'])->name('storefront.cart');
Route::post('/keranjang', [CartController::class, 'add'])->middleware('throttle:30,1')->name('storefront.cart.add');
Route::post('/keranjang/ubah', [CartController::class, 'update'])->middleware('throttle:30,1')->name('storefront.cart.update');
Route::post('/checkout', [CartController::class, 'checkout'])->middleware('throttle:5,1')->name('storefront.checkout');
Route::post('/prospek', [StorefrontController::class, 'submitLead'])
    ->middleware('throttle:5,1')
    ->name('storefront.lead.store');
Route::get('/privasi', [StorefrontController::class, 'privacy'])->name('storefront.privacy');
Route::get('/lapor', [StorefrontController::class, 'report'])->name('storefront.report');
Route::post('/lapor', [StorefrontController::class, 'submitReport'])->middleware('throttle:3,1')->name('storefront.report.store');
Route::get('/robots.txt', [StorefrontController::class, 'robots'])->name('storefront.robots');
Route::get('/sitemap.xml', [StorefrontController::class, 'sitemap'])->name('storefront.sitemap');
