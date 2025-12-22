<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RateLimiterServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap rate limiting services untuk aplikasi
     * dengan konfigurasi terpusat untuk semua rate limiters
     * berdasarkan context dan sensitivity level
     */
    public function boot(): void
    {
        $this->configureCartLimiters();
        $this->configureCheckoutLimiters();
        $this->configureUploadLimiters();
        $this->configureSecurityLimiters();
    }

    /**
     * Konfigurasi rate limiters untuk cart operations
     * dengan pembatasan berbeda untuk read dan write operations
     * dimana write operations lebih restrictive untuk mencegah abuse
     */
    protected function configureCartLimiters(): void
    {
        RateLimiter::for('cart', function (Request $request) {
            // 20 requests per minute untuk cart modifications
            // menggunakan session ID sebagai identifier
            return Limit::perMinute(20)
                ->by($request->session()->getId())
                ->response(function () {
                    return response()->json([
                        'message' => 'Terlalu banyak permintaan. Silakan coba lagi nanti.',
                    ], 429);
                });
        });

        RateLimiter::for('cart-view', function (Request $request) {
            // 60 requests per minute untuk viewing cart
            // lebih permissive karena read-only operation
            return Limit::perMinute(60)
                ->by($request->session()->getId());
        });
    }

    /**
     * Konfigurasi rate limiters untuk checkout process
     * dengan strict limiting untuk mencegah spam orders
     * dan abuse pada payment gateway
     */
    protected function configureCheckoutLimiters(): void
    {
        RateLimiter::for('checkout', function (Request $request) {
            // 10 checkout attempts per hour untuk mencegah spam orders
            // menggunakan user ID jika authenticated, fallback ke IP
            return Limit::perHour(10)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return back()->withErrors([
                        'checkout' => 'Terlalu banyak percobaan checkout. Silakan coba lagi dalam 1 jam.',
                    ]);
                });
        });
    }

    /**
     * Konfigurasi rate limiters untuk file upload operations
     * untuk mencegah resource exhaustion dan storage abuse
     * dengan limit berdasarkan user authentication status
     */
    protected function configureUploadLimiters(): void
    {
        RateLimiter::for('uploads', function (Request $request) {
            // 5 upload requests per minute untuk mencegah resource exhaustion
            // menggunakan user ID sebagai identifier untuk admin uploads
            return Limit::perMinute(5)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return back()->withErrors([
                        'upload' => 'Terlalu banyak upload. Silakan tunggu sebentar sebelum mencoba lagi.',
                    ]);
                });
        });
    }

    /**
     * Konfigurasi rate limiters untuk security-sensitive operations
     * dengan progressive limiting untuk password verification
     * dimana multiple limit tiers diaplikasikan secara bersamaan
     */
    protected function configureSecurityLimiters(): void
    {
        RateLimiter::for('password-verify', function (Request $request) {
            // Progressive rate limiting untuk password verification
            // dimana 5 attempts per minute dan 10 per hour untuk keamanan tambahan
            return [
                Limit::perMinute(5)->by($request->user()?->id ?: $request->ip()),
                Limit::perHour(10)->by($request->user()?->id ?: $request->ip())
                    ->response(function () {
                        return response()->json([
                            'success' => false,
                            'message' => 'Terlalu banyak percobaan verifikasi password. Akun dikunci selama 1 jam.',
                        ], 429);
                    }),
            ];
        });
    }
}
