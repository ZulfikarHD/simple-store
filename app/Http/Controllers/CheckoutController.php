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
        // dengan pemisahan nama depan dan nama belakang
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        $customerData = null;

        if ($user) {
            // Split nama user menjadi first name dan last name
            $nameParts = $this->splitFullName($user->name);

            $customerData = [
                'first_name' => $nameParts['first_name'],
                'last_name' => $nameParts['last_name'],
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
     * Memisahkan nama lengkap menjadi nama depan dan nama belakang
     * dengan asumsi kata pertama adalah nama depan, sisanya nama belakang
     *
     * @param  string|null  $fullName  Nama lengkap user
     * @return array{first_name: string, last_name: string}
     */
    private function splitFullName(?string $fullName): array
    {
        if (empty($fullName)) {
            return ['first_name' => '', 'last_name' => ''];
        }

        $parts = preg_split('/\s+/', trim($fullName), 2);

        return [
            'first_name' => $parts[0] ?? '',
            'last_name' => $parts[1] ?? '',
        ];
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
