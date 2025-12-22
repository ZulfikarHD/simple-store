<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;

class DashboardService
{
    /**
     * Mengambil statistik dashboard overview
     * untuk menampilkan ringkasan performa toko, yaitu:
     * total pesanan hari ini, pending orders, total sales, dan produk aktif
     *
     * @return array<string, mixed> Data statistik dashboard dengan metrics utama
     */
    public function getDashboardStats(): array
    {
        return [
            'today_orders' => $this->getTodayOrdersCount(),
            'pending_orders' => $this->getPendingOrdersCount(),
            'total_sales' => $this->getTotalSales(),
            'active_products' => $this->getActiveProductsCount(),
            'recent_orders' => $this->getRecentOrders(),
            'order_status_breakdown' => $this->getOrderStatusBreakdown(),
        ];
    }

    /**
     * Menghitung total pesanan hari ini
     * dengan filtering berdasarkan created_at date untuk tracking daily performance
     *
     * @return int Jumlah order yang dibuat hari ini
     */
    protected function getTodayOrdersCount(): int
    {
        return Order::whereDate('created_at', today())->count();
    }

    /**
     * Menghitung pending orders yang perlu diproses
     * dimana status pending menandakan order baru yang memerlukan konfirmasi admin
     *
     * @return int Jumlah order dengan status pending
     */
    protected function getPendingOrdersCount(): int
    {
        return Order::where('status', 'pending')->count();
    }

    /**
     * Menghitung total penjualan keseluruhan
     * dengan sum dari kolom total pada semua orders untuk tracking revenue
     *
     * @return float Total nilai penjualan dalam Rupiah
     */
    protected function getTotalSales(): float
    {
        return (float) Order::sum('total');
    }

    /**
     * Menghitung jumlah produk aktif yang tersedia
     * dimana is_active = 1 menandakan produk dapat ditampilkan ke customer
     *
     * @return int Jumlah produk yang sedang aktif
     */
    protected function getActiveProductsCount(): int
    {
        return Product::where('is_active', true)->count();
    }

    /**
     * Mengambil recent orders untuk ditampilkan di dashboard
     * dengan limit 5 pesanan terbaru dan eager loading relasi untuk optimasi query
     *
     * @return \Illuminate\Database\Eloquent\Collection Collection dari 5 order terbaru
     */
    protected function getRecentOrders()
    {
        return Order::with(['items.product'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer_name' => $order->customer_name,
                    'customer_phone' => $order->customer_phone,
                    'total' => $order->total,
                    'status' => $order->status,
                    'items_count' => $order->items->count(),
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'created_at_human' => $order->created_at->diffForHumans(),
                ];
            });
    }

    /**
     * Mengambil breakdown status order untuk analytics
     * dengan grouping berdasarkan status dan counting untuk visualisasi data
     * menggunakan selectRaw untuk clarity dan keamanan query
     *
     * @return \Illuminate\Support\Collection Collection berisi count per status
     */
    protected function getOrderStatusBreakdown()
    {
        return Order::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status => $item->count];
            });
    }
}
