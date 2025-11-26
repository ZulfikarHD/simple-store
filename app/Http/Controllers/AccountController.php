<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * AccountController untuk halaman akun user
 * menampilkan profil dan riwayat pesanan
 *
 * @author Zulfikar Hidayatullah
 */
class AccountController extends Controller
{
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
}
