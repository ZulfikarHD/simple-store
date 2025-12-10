<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * AccountController untuk halaman akun user
 * menampilkan profil, riwayat pesanan, dan detail pesanan
 *
 * @author Zulfikar Hidayatullah
 */
class AccountController extends Controller
{
    public function __construct(
        public OrderService $orderService
    ) {}

    /**
     * Menampilkan halaman akun utama dengan profil user
     * atau redirect ke login jika guest
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Account/Index');
    }

    /**
     * Menampilkan riwayat pesanan user
     * dengan list semua pesanan yang pernah dibuat
     */
    public function orders(Request $request): Response
    {
        $user = $request->user();

        $orders = [];

        if ($user) {
            $orders = Order::where('user_id', $user->id)
                ->with(['items'])
                ->latest()
                ->get()
                ->map(fn (Order $order) => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'total' => $order->total,
                    'status' => $order->status,
                    'items' => $order->items->map(fn ($item) => [
                        'id' => $item->id,
                        'product_name' => $item->product_name,
                        'quantity' => $item->quantity,
                        'subtotal' => $item->subtotal,
                    ]),
                    'items_count' => $order->items->count(),
                    'created_at' => $order->created_at->toISOString(),
                    'created_at_human' => $order->created_at->diffForHumans(),
                ]);
        }

        return Inertia::render('Account/Orders', [
            'orders' => $orders,
        ]);
    }

    /**
     * Menampilkan detail pesanan user
     * dengan validasi kepemilikan order
     */
    public function orderShow(Request $request, Order $order): Response
    {
        $user = $request->user();

        // Pastikan order milik user yang sedang login
        abort_unless($order->user_id === $user->id, HttpResponse::HTTP_FORBIDDEN);

        return Inertia::render('Account/OrderDetail', [
            'order' => $this->orderService->getOrderDetail($order),
        ]);
    }
}
