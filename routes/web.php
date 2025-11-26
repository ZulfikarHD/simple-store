<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\StoreSettingController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProductController::class, 'index'])->name('home');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

// Cart routes untuk operasi keranjang belanja
Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');

// Checkout routes untuk proses pemesanan
Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');

// Admin routes dengan auth middleware untuk proteksi akses
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('products', AdminProductController::class);
    Route::resource('categories', CategoryController::class);

    // Order management routes untuk admin
    Route::resource('orders', OrderController::class)->only(['index', 'show']);
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');

    // Store settings routes untuk konfigurasi toko
    Route::get('/settings', [StoreSettingController::class, 'index'])->name('settings.index');
    Route::patch('/settings', [StoreSettingController::class, 'update'])->name('settings.update');
});

require __DIR__.'/settings.php';
