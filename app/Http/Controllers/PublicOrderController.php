<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerifyOrderPhoneRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller untuk mengelola akses public order via ULID
 * dengan phone verification untuk keamanan
 */
class PublicOrderController extends Controller
{
    /**
     * Constructor dengan dependency injection OrderService
     */
    public function __construct(
        public OrderService $orderService
    ) {}

    /**
     * Menampilkan halaman verifikasi nomor telepon untuk akses order
     * atau redirect ke admin panel jika user adalah admin yang sudah login
     */
    public function show(string $ulid): Response|RedirectResponse
    {
        $order = Order::where('access_ulid', $ulid)->firstOrFail();

        // Jika user adalah admin yang sudah login, redirect ke admin panel
        if (auth()->check() && auth()->user()->isAdmin()) {
            return redirect()->route('admin.orders.showByUlid', ['ulid' => $ulid]);
        }

        // Cek apakah akses sudah expired
        if ($order->isAccessExpired()) {
            return Inertia::render('PublicOrderView', [
                'order' => null,
                'ulid' => $ulid,
                'expired' => true,
                'orderNumber' => $order->order_number,
            ]);
        }

        return Inertia::render('PublicOrderView', [
            'order' => null,
            'ulid' => $ulid,
            'expired' => false,
        ]);
    }

    /**
     * Verifikasi nomor telepon dan tampilkan detail order
     * dengan rate limiting untuk mencegah brute force attack
     */
    public function verify(VerifyOrderPhoneRequest $request, string $ulid): Response|RedirectResponse
    {
        $validated = $request->validated();
        $order = Order::where('access_ulid', $ulid)->firstOrFail();

        // Cek apakah akses sudah expired
        if ($order->isAccessExpired()) {
            return back()->withErrors([
                'customer_phone' => 'Akses ke pesanan ini sudah tidak tersedia karena pesanan telah selesai atau dibatalkan.',
            ]);
        }

        // Verifikasi nomor telepon
        if (! $order->verifyCustomerPhone($validated['customer_phone'])) {
            return back()->withErrors([
                'customer_phone' => 'Nomor telepon tidak sesuai dengan data pesanan.',
            ])->withInput();
        }

        // Jika verifikasi berhasil, tampilkan detail order
        $orderData = $this->orderService->getOrderDetail($order);

        return Inertia::render('PublicOrderView', [
            'order' => $orderData,
            'ulid' => $ulid,
            'expired' => false,
            'verified' => true,
        ]);
    }
}
