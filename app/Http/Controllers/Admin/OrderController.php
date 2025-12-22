<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\StoreSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * OrderController untuk mengelola order management di admin panel
 * dengan fitur view orders, order detail, dan update status
 */
class OrderController extends Controller
{
    /**
     * Constructor dengan dependency injection OrderService
     */
    public function __construct(
        public OrderService $orderService
    ) {}

    /**
     * Menampilkan daftar order dengan pagination dan filter, yaitu:
     * - Pagination dengan 10 item per halaman
     * - Search berdasarkan order number, nama customer, atau nomor telepon
     * - Filter berdasarkan status dan date range
     * - Status counts untuk quick filter tabs
     * - WhatsApp URLs untuk setiap order pada mobile quick actions
     */
    public function index(Request $request): Response
    {
        // Validasi input untuk security dan data integrity
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'min:2', 'max:50'],
            'status' => ['nullable', 'string', 'in:pending,confirmed,preparing,ready,delivered,cancelled'],
            'start_date' => ['nullable', 'date', 'before_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date', 'before_or_equal:today'],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:100'],
        ]);

        $orders = $this->orderService->getFilteredOrders($validated);
        $statuses = $this->orderService->getAvailableStatuses();
        $statusCounts = $this->orderService->getAllStatusCounts();

        // Transform orders untuk menyertakan WhatsApp URLs untuk mobile quick actions
        // Menggunakan setCollection dengan map untuk memastikan data di-serialize dengan benar
        $transformedOrders = $orders->getCollection()->map(function ($order) {
            // Handle both Order model and array (from previous transformation)
            if ($order instanceof Order) {
                $orderArray = $order->toArray();
                $orderArray['whatsapp_urls'] = [
                    'confirmed' => $order->getWhatsAppToCustomerUrl('confirmed'),
                    'preparing' => $order->getWhatsAppToCustomerUrl('preparing'),
                    'ready' => $order->getWhatsAppToCustomerUrl('ready'),
                    'delivered' => $order->getWhatsAppToCustomerUrl('delivered'),
                    'cancelled' => $order->getWhatsAppToCustomerUrl('cancelled'),
                ];

                return $orderArray;
            }

            return $order;
        });
        $orders->setCollection($transformedOrders);

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'statuses' => $statuses,
            'filters' => $validated,
            'statusCounts' => $statusCounts,
        ]);
    }

    /**
     * Menampilkan detail order dengan customer info, items, dan status timeline
     * serta WhatsApp URLs untuk mengirim template message ke customer
     */
    public function show(Order $order, StoreSettingService $settingService): Response
    {
        $orderDetail = $this->orderService->getOrderDetail($order);
        $statuses = $this->orderService->getAvailableStatuses();

        // Generate WhatsApp URLs untuk setiap status template
        $whatsappUrls = [
            'confirmed' => $order->getWhatsAppToCustomerUrl('confirmed'),
            'preparing' => $order->getWhatsAppToCustomerUrl('preparing'),
            'ready' => $order->getWhatsAppToCustomerUrl('ready'),
            'delivered' => $order->getWhatsAppToCustomerUrl('delivered'),
            'cancelled' => $order->getWhatsAppToCustomerUrl('cancelled'),
        ];

        // Get customizable timeline icons dari settings
        $timelineIcons = $settingService->getTimelineIcons();

        return Inertia::render('Admin/Orders/Show', [
            'order' => $orderDetail,
            'statuses' => $statuses,
            'whatsappUrls' => $whatsappUrls,
            'timelineIcons' => $timelineIcons,
        ]);
    }

    /**
     * Menampilkan detail order berdasarkan ULID
     * untuk admin access via ULID-based secure URL
     */
    public function showByUlid(string $ulid, StoreSettingService $settingService): Response
    {
        $order = Order::where('access_ulid', $ulid)->firstOrFail();

        return $this->show($order, $settingService);
    }

    /**
     * Update status order dengan timestamp logging
     * dimana setiap perubahan status akan dicatat dengan waktu yang sesuai
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $result = $this->orderService->updateOrderStatus(
            $order,
            $request->validated('status'),
            $request->validated('cancellation_reason')
        );

        return redirect()
            ->back()
            ->with('success', $result['message']);
    }
}
