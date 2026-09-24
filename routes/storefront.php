<?php

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
Route::get('/keranjang', [StorefrontController::class, 'cart'])->name('storefront.cart');
Route::get('/robots.txt', [StorefrontController::class, 'robots'])->name('storefront.robots');
Route::get('/sitemap.xml', [StorefrontController::class, 'sitemap'])->name('storefront.sitemap');
