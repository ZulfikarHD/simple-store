<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\StoreSettingController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProductController::class, 'index'])->name('home');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

// Cart routes untuk operasi keranjang belanja dengan rate limiting
Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
Route::middleware(['throttle:cart'])->group(function () {
    Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/cart/{cartItem}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');
});

// Checkout routes untuk proses pemesanan
// Memerlukan authentication dan rate limiting untuk keamanan
Route::middleware(['auth', 'throttle:checkout'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
});

// Account routes untuk profil dan riwayat pesanan user
// Halaman index bisa diakses guest (akan tampilkan prompt login)
// Halaman orders dan order detail memerlukan authentication
Route::get('/account', [AccountController::class, 'index'])->name('account.index');
Route::middleware('auth')->group(function () {
    Route::get('/account/orders', [AccountController::class, 'orders'])->name('account.orders');
    Route::get('/account/orders/{order}', [AccountController::class, 'orderShow'])->name('account.orders.show');
});

// Admin routes dengan auth, verified, dan admin middleware untuk proteksi akses
// Hanya user dengan role admin yang dapat mengakses halaman-halaman ini
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('products', AdminProductController::class);
    Route::resource('categories', CategoryController::class);

    // Order management routes untuk admin
    Route::resource('orders', OrderController::class)->only(['index', 'show']);
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');

    // Store settings routes untuk konfigurasi toko
    Route::get('/settings', [StoreSettingController::class, 'index'])->name('settings.index');
    Route::patch('/settings', [StoreSettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/upload-logo', [StoreSettingController::class, 'uploadLogo'])->name('settings.uploadLogo');

    // API routes untuk mobile alert system (polling pending orders)
    Route::get('/api/orders/pending', [OrderApiController::class, 'pendingOrders'])->name('api.orders.pending');
    Route::patch('/api/orders/{order}/quick-status', [OrderApiController::class, 'quickStatusUpdate'])->name('api.orders.quickStatus');
});

require __DIR__.'/settings.php';
