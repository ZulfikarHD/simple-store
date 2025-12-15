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
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status', 'start_date', 'end_date', 'per_page']);
        $orders = $this->orderService->getFilteredOrders($filters);
        $statuses = $this->orderService->getAvailableStatuses();
        $statusCounts = $this->orderService->getAllStatusCounts();

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'statuses' => $statuses,
            'filters' => $filters,
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
