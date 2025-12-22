<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PasswordVerificationController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\StaffManagementController;
use App\Http\Controllers\Admin\StoreSettingController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PublicOrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProductController::class, 'index'])->name('home');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

// Google OAuth routes
Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

// Public order view routes dengan ULID-based access dan phone verification
// untuk secure order viewing tanpa expose admin panel structure
Route::get('/orders/{ulid}', [PublicOrderController::class, 'show'])
    ->name('orders.view')
    ->where('ulid', '[0-9A-HJKMNP-TV-Z]{26}');

Route::post('/orders/{ulid}/verify', [PublicOrderController::class, 'verify'])
    ->name('orders.verify')
    ->middleware('throttle:5,15')
    ->where('ulid', '[0-9A-HJKMNP-TV-Z]{26}');

// Cart routes untuk operasi keranjang belanja dengan rate limiting
// GET cart menggunakan throttle:cart-view (lebih permissive)
// POST/PATCH/DELETE menggunakan throttle:cart (lebih strict)
Route::middleware(['throttle:cart-view'])->group(function () {
    Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
});

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

    // Admin access order via ULID untuk secure URL sharing
    Route::get('orders/ulid/{ulid}', [OrderController::class, 'showByUlid'])
        ->name('orders.showByUlid')
        ->where('ulid', '[0-9A-HJKMNP-TV-Z]{26}');

    // Store settings routes untuk konfigurasi toko
    Route::get('/settings', [StoreSettingController::class, 'index'])->name('settings.index');
    Route::patch('/settings', [StoreSettingController::class, 'update'])->name('settings.update');

    // Upload routes dengan rate limiting untuk mencegah resource exhaustion
    Route::middleware('throttle:uploads')->group(function () {
        Route::post('/settings/upload-logo', [StoreSettingController::class, 'uploadLogo'])->name('settings.uploadLogo');
        Route::post('/settings/upload-favicon', [StoreSettingController::class, 'uploadFavicon'])->name('settings.uploadFavicon');
    });

    // API routes untuk mobile alert system (polling pending orders)
    Route::get('/api/orders/pending', [OrderApiController::class, 'pendingOrders'])->name('api.orders.pending');
    Route::patch('/api/orders/{order}/quick-status', [OrderApiController::class, 'quickStatusUpdate'])->name('api.orders.quickStatus');

    // Password verification route untuk aksi sensitif dengan progressive rate limiting
    Route::middleware('throttle:password-verify')
        ->post('/api/verify-password', [PasswordVerificationController::class, 'verify'])
        ->name('api.verifyPassword');

    // Staff management routes untuk mengelola admin dan staff accounts
    Route::prefix('staff')->name('staff.')->group(function () {
        Route::get('/', [StaffManagementController::class, 'index'])->name('index');
        Route::get('/create', [StaffManagementController::class, 'create'])->name('create');
        Route::post('/', [StaffManagementController::class, 'store'])->name('store');
        Route::get('/{staff}/edit', [StaffManagementController::class, 'edit'])->name('edit');
        Route::patch('/{staff}', [StaffManagementController::class, 'update'])->name('update');
        Route::patch('/{staff}/password', [StaffManagementController::class, 'updatePassword'])->name('updatePassword');
        Route::delete('/{staff}', [StaffManagementController::class, 'destroy'])->name('destroy');
    });
});

require __DIR__.'/settings.php';
