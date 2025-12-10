<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller untuk mengelola proses checkout
 * termasuk menampilkan form, proses order, dan halaman sukses
 * dengan integrasi WhatsApp untuk notifikasi pesanan
 */
class CheckoutController extends Controller
{
    /**
     * Constructor dengan dependency injection untuk services
     */
    public function __construct(
        public CartService $cartService,
        public OrderService $orderService
    ) {}

    /**
     * Menampilkan halaman checkout dengan form customer data
     * dan ringkasan pesanan dari cart, serta pre-fill data user
     * yang sudah login untuk mempercepat proses checkout
     */
    public function show(): Response|RedirectResponse
    {
        $cartData = $this->cartService->getCartData();

        // Redirect ke cart jika kosong (count diperlukan karena Collection selalu truthy)
        if (count($cartData['items']) === 0) {
            return redirect()->route('cart.show')
                ->with('error', 'Keranjang belanja kosong. Silakan tambahkan produk terlebih dahulu.');
        }

        // Pre-fill data dari authenticated user jika tersedia
        $user = auth()->user();
        $customerData = null;

        if ($user) {
            $customerData = [
                'name' => $user->name,
                'phone' => $user->phone,
                'address' => $user->address,
            ];
        }

        return Inertia::render('Checkout', [
            'cart' => $cartData,
            'customer' => $customerData,
        ]);
    }

    /**
     * Memproses checkout dan membuat order baru
     * dengan redirect ke WhatsApp untuk konfirmasi
     */
    public function store(CheckoutRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $order = $this->orderService->createOrder($validated);
            $whatsappUrl = $this->orderService->generateWhatsAppUrl($order);

            // Store WhatsApp URL in session untuk redirect dari halaman success
            session()->flash('whatsapp_url', $whatsappUrl);

            return redirect()->route('checkout.success', ['order' => $order->id]);
        } catch (\Exception $e) {
            return back()->withErrors([
                'checkout' => $e->getMessage(),
            ])->withInput();
        }
    }

    /**
     * Menampilkan halaman sukses setelah order berhasil dibuat
     * dengan detail pesanan dan link WhatsApp
     */
    public function success(Order $order): Response|RedirectResponse
    {
        // Pastikan order milik session yang sama atau baru dibuat
        // Untuk keamanan, kita hanya tampilkan jika ada flash whatsapp_url
        // atau order dibuat dalam 1 jam terakhir
        $isRecentOrder = $order->created_at->diffInHours(now()) < 1;

        if (! session()->has('whatsapp_url') && ! $isRecentOrder) {
            return redirect()->route('home')
                ->with('error', 'Pesanan tidak ditemukan atau sudah kedaluwarsa.');
        }

        $order->load('items');

        return Inertia::render('OrderSuccess', [
            'order' => $this->orderService->getOrderData($order),
            'whatsappUrl' => session('whatsapp_url', $this->orderService->generateWhatsAppUrl($order)),
        ]);
    }
}
