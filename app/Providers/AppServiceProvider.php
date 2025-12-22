<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     * Termasuk konfigurasi custom rate limiters untuk security
     */
    public function boot(): void
    {
        /**
         * Konfigurasi custom rate limiters untuk security
         * dengan pembatasan request berdasarkan session dan IP
         * untuk mencegah abuse dan DoS attacks
         */
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
}
