<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * OrderService untuk mengelola operasi order management
 * termasuk checkout flow dengan WhatsApp integration
 * serta admin panel dengan fitur filtering, search, dan status update
 */
class OrderService
{
    /**
     * Constructor dengan dependency injection untuk CartService dan StoreSettingService
     * yang digunakan untuk checkout flow dan WhatsApp integration
     */
    public function __construct(
        public CartService $cartService,
        public StoreSettingService $storeSettingService
    ) {}

    /**
     * Daftar status order yang tersedia dalam sistem, yaitu:
     * pending, confirmed, preparing, ready, delivered, cancelled
     */
    public const STATUSES = [
        'pending' => 'Menunggu',
        'confirmed' => 'Dikonfirmasi',
        'preparing' => 'Diproses',
        'ready' => 'Siap',
        'delivered' => 'Dikirim',
        'cancelled' => 'Dibatalkan',
    ];

    // =========================================================================
    // CHECKOUT FLOW METHODS
    // =========================================================================

    /**
     * Membuat order baru dari cart items dengan customer data
     * menggunakan database transaction untuk atomic operation
     *
     * @param  array<string, mixed>  $customerData  Data customer dari checkout form
     * @return Order Order yang baru dibuat dengan items relationship
     *
     * @throws \Exception Ketika keranjang belanja kosong
     */
    public function createOrder(array $customerData): Order
    {
        $cart = $this->cartService->getCart();

        if ($cart->items->isEmpty()) {
            throw new \Exception('Keranjang belanja kosong.');
        }

        return DB::transaction(function () use ($cart, $customerData) {
            $subtotal = $cart->subtotal;
            $deliveryFee = $this->storeSettingService->getDeliveryFee();
            $total = $subtotal + $deliveryFee;

            // Buat order record dengan customer data dan user_id jika authenticated
            // Status diset secara explicit untuk security (protected dari mass assignment)
            $order = new Order;
            $order->user_id = auth()->id();
            $order->customer_name = $customerData['customer_name'];
            $order->customer_phone = $customerData['customer_phone'];
            $order->customer_address = $customerData['customer_address'];
            $order->notes = $customerData['notes'] ?? null;
            $order->subtotal = $subtotal;
            $order->delivery_fee = $deliveryFee;
            $order->total = $total;
            $order->status = 'pending';
            $order->save();

            // Copy cart items ke order items (snapshot harga saat pembelian)
            foreach ($cart->items as $cartItem) {
                $order->items()->create([
                    'product_id' => $cartItem->product_id,
                    'product_name' => $cartItem->product->name,
                    'product_price' => $cartItem->product->price,
                    'quantity' => $cartItem->quantity,
                    'subtotal' => $cartItem->subtotal,
                ]);
            }

            // Clear cart setelah order berhasil dibuat
            $this->cartService->clearCart();

            // Load items untuk return
            $order->load('items');

            return $order;
        });
    }

    /**
     * Generate WhatsApp URL dengan pre-filled message untuk konfirmasi pesanan
     * menggunakan nomor WhatsApp owner dari StoreSettingService
     *
     * @return string URL WhatsApp dengan format https://wa.me/{phone}?text={message}
     */
    public function generateWhatsAppUrl(Order $order): string
    {
        $phoneNumber = $this->storeSettingService->getWhatsAppNumber();
        $message = $order->generateWhatsAppMessage();
        $encodedMessage = urlencode($message);

        return "https://wa.me/{$phoneNumber}?text={$encodedMessage}";
    }

    /**
     * Mendapatkan data order yang diformat untuk frontend OrderSuccess page
     * termasuk formatted prices, items dengan subtotal, dan ULID untuk secure access
     *
     * @return array<string, mixed> Data order yang siap digunakan di frontend
     */
    public function getOrderData(Order $order): array
    {
        $order->load('items');

        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'access_ulid' => $order->access_ulid,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'customer_address' => $order->customer_address,
            'notes' => $order->notes,
            'items' => $order->items->map(fn ($item) => [
                'id' => $item->id,
                'product_name' => $item->product_name,
                'product_price' => $item->product_price,
                'quantity' => $item->quantity,
                'subtotal' => $item->subtotal,
                'formatted_subtotal' => 'Rp '.number_format($item->subtotal, 0, ',', '.'),
            ])->all(),
            'subtotal' => $order->subtotal,
            'delivery_fee' => $order->delivery_fee,
            'total' => $order->total,
            'formatted_total' => 'Rp '.number_format($order->total, 0, ',', '.'),
            'status' => $order->status,
            'created_at' => $order->created_at->format('d M Y, H:i'),
        ];
    }

    // =========================================================================
    // ADMIN PANEL METHODS
    // =========================================================================

    /**
     * Mendapatkan daftar order dengan pagination dan filter, yaitu:
     * - Pencarian berdasarkan order number, nama customer, atau nomor telepon
     * - Filter berdasarkan status order
     * - Filter berdasarkan tanggal (date range)
     * - Termasuk waiting_minutes untuk urgency indicators
     *
     * @param  array<string, mixed>  $filters  Parameter filter dari request
     * @return LengthAwarePaginator<Order>
     */
    public function getFilteredOrders(array $filters = []): LengthAwarePaginator
    {
        $query = Order::query()
            ->with(['items'])
            ->latest();

        // Filter pencarian berdasarkan order number, nama customer, atau nomor telepon
        // dengan sanitization untuk mencegah SQL injection dan performance DoS
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);

            // Validasi search length untuk security
            if (strlen($search) < 2) {
                throw new \InvalidArgumentException('Pencarian harus minimal 2 karakter.');
            }

            if (strlen($search) > 50) {
                throw new \InvalidArgumentException('Pencarian terlalu panjang (maksimal 50 karakter).');
            }

            // Escape special LIKE characters untuk mencegah wildcard abuse
            $search = str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], $search);

            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan status order
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter berdasarkan tanggal mulai (start date)
        if (! empty($filters['start_date'])) {
            $query->whereDate('created_at', '>=', $filters['start_date']);
        }

        // Filter berdasarkan tanggal akhir (end date)
        if (! empty($filters['end_date'])) {
            $query->whereDate('created_at', '<=', $filters['end_date']);
        }

        $perPage = (int) ($filters['per_page'] ?? 10);

        $paginated = $query->paginate($perPage)->withQueryString();

        // Transform data untuk menambahkan waiting_minutes
        $paginated->through(function (Order $order) {
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'customer_address' => $order->customer_address,
                'total' => $order->total,
                'status' => $order->status,
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->subtotal,
                ]),
                'created_at' => $order->created_at->toISOString(),
                'waiting_minutes' => $order->created_at->diffInMinutes(now()),
            ];
        });

        return $paginated;
    }

    /**
     * Mendapatkan detail order lengkap dengan items dan timestamps
     * untuk ditampilkan di halaman detail order
     *
     * @return array<string, mixed> Data order yang telah diformat untuk frontend
     */
    public function getOrderDetail(Order $order): array
    {
        $order->load(['items', 'user']);

        $data = [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'customer_address' => $order->customer_address,
            'notes' => $order->notes,
            'subtotal' => $order->subtotal,
            'delivery_fee' => $order->delivery_fee,
            'total' => $order->total,
            'status' => $order->status,
            'status_label' => self::STATUSES[$order->status] ?? $order->status,
            'items' => $order->items->map(fn ($item) => [
                'id' => $item->id,
                'product_name' => $item->product_name,
                'product_price' => $item->product_price,
                'quantity' => $item->quantity,
                'subtotal' => $item->subtotal,
                'notes' => $item->notes,
            ]),
            'items_count' => $order->items->count(),
            'cancellation_reason' => $order->cancellation_reason,
            'timestamps' => [
                'created_at' => $order->created_at?->format('Y-m-d H:i:s'),
                'created_at_human' => $order->created_at?->diffForHumans(),
                'confirmed_at' => $order->confirmed_at?->format('Y-m-d H:i:s'),
                'preparing_at' => $order->preparing_at?->format('Y-m-d H:i:s'),
                'ready_at' => $order->ready_at?->format('Y-m-d H:i:s'),
                'delivered_at' => $order->delivered_at?->format('Y-m-d H:i:s'),
                'cancelled_at' => $order->cancelled_at?->format('Y-m-d H:i:s'),
            ],
        ];

        // Sertakan WhatsApp URL untuk order dengan status pending
        // agar user dapat mengirim ulang konfirmasi jika sebelumnya lupa
        if ($order->status === 'pending') {
            $data['whatsapp_url'] = $this->generateWhatsAppUrl($order);
        }

        return $data;
    }

    /**
     * Update status order dengan timestamp logging
     * dimana setiap perubahan status akan dicatat dengan waktu yang sesuai
     * menggunakan explicit methods untuk bypass mass assignment protection
     *
     * @param  string|null  $reason  Alasan pembatalan (required jika status = cancelled)
     * @return array{success: bool, message: string}
     */
    public function updateOrderStatus(Order $order, string $status, ?string $reason = null): array
    {
        // Gunakan explicit methods dari Order model untuk bypass mass assignment protection
        // Methods ini sudah handle status dan timestamp secara aman
        $success = match ($status) {
            'confirmed' => $order->confirm(),
            'preparing' => $order->startPreparing(),
            'ready' => $order->markReady(),
            'delivered' => $order->markDelivered(),
            'cancelled' => $order->cancel($reason),
            default => false,
        };

        if (! $success) {
            return [
                'success' => false,
                'message' => 'Gagal mengupdate status pesanan.',
            ];
        }

        return [
            'success' => true,
            'message' => 'Status pesanan berhasil diperbarui menjadi '.self::STATUSES[$status].'.',
        ];
    }

    /**
     * Mendapatkan daftar status yang tersedia untuk dropdown
     *
     * @return array<string, string> Array status dengan key sebagai value dan label sebagai display
     */
    public function getAvailableStatuses(): array
    {
        return self::STATUSES;
    }

    /**
     * Menghitung jumlah order berdasarkan status
     * untuk menampilkan badge count di navigasi
     */
    public function getOrderCountByStatus(string $status): int
    {
        return Order::where('status', $status)->count();
    }

    /**
     * Mendapatkan jumlah order untuk semua status
     * untuk menampilkan badge count pada filter tabs
     *
     * @return array<int, array{status: string, count: int}>
     */
    public function getAllStatusCounts(): array
    {
        return Order::query()
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->map(fn ($item) => [
                'status' => $item->status,
                'count' => $item->count,
            ])
            ->toArray();
    }
}
