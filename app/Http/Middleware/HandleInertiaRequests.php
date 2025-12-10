<?php

namespace App\Http\Middleware;

use App\Models\Order;
use App\Services\CartService;
use App\Services\StoreSettingService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

/**
 * Middleware untuk handling Inertia.js requests
 * dengan shared data untuk cart, auth, dan order notifications
 *
 * @author Zulfikar Hidayatullah
 */
class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     * Termasuk pending_orders_count untuk notifikasi admin
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'cart' => fn () => [
                'total_items' => app(CartService::class)->getTotalItems(),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            // Store branding untuk logo, nama, dan tagline toko
            'store' => fn () => app(StoreSettingService::class)->getStoreBranding(),
            // Pending orders count untuk admin notifications
            'pending_orders_count' => fn () => $request->user()
                ? Order::where('status', 'pending')->count()
                : 0,
        ];
    }
}
