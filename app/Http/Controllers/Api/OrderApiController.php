<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * OrderApiController untuk endpoint API order management
 * digunakan untuk polling pending orders pada mobile alert system
 *
 * @author Zulfikar Hidayatullah
 */
class OrderApiController extends Controller
{
    /**
     * Mendapatkan pending orders untuk alert banner
     * dengan informasi minimal yang diperlukan untuk display
     */
    public function pendingOrders(Request $request): JsonResponse
    {
        $orders = Order::where('status', 'pending')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'total' => $order->total,
                'items_count' => $order->items()->count(),
                'created_at' => $order->created_at->toISOString(),
                'waiting_minutes' => $order->created_at->diffInMinutes(now()),
            ]);

        return response()->json([
            'orders' => $orders,
            'total_pending' => Order::where('status', 'pending')->count(),
        ]);
    }

    /**
     * Quick status update untuk order dari mobile alert
     * digunakan untuk konfirmasi langsung tanpa buka halaman detail
     */
    public function quickStatusUpdate(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:confirmed,preparing,ready,delivered,cancelled',
        ]);

        $status = $validated['status'];
        $now = now();

        $updateData = ['status' => $status];

        // Set timestamp berdasarkan status yang dipilih
        switch ($status) {
            case 'confirmed':
                $updateData['confirmed_at'] = $now;
                break;
            case 'preparing':
                $updateData['preparing_at'] = $now;
                break;
            case 'ready':
                $updateData['ready_at'] = $now;
                break;
            case 'delivered':
                $updateData['delivered_at'] = $now;
                break;
            case 'cancelled':
                $updateData['cancelled_at'] = $now;
                $updateData['cancellation_reason'] = $request->input('cancellation_reason');
                break;
        }

        $order->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Status pesanan berhasil diperbarui.',
            'order' => [
                'id' => $order->id,
                'status' => $order->status,
            ],
        ]);
    }
}

