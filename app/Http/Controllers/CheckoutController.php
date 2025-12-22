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
     * serta proper error handling untuk security
     */
    public function store(CheckoutRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $order = $this->orderService->createOrder($validated);
            $whatsappUrl = $this->orderService->generateWhatsAppUrl($order);

            // Store WhatsApp URL dan ULID in session untuk security verification
            session()->flash('whatsapp_url', $whatsappUrl);
            session()->put('checkout_order_ulid', $order->access_ulid);
            session()->put('checkout_order_created_at', now());

            return redirect()->route('checkout.success', ['order' => $order->id]);
        } catch (\InvalidArgumentException $e) {
            // Business logic errors - safe to show user
            return back()->withErrors([
                'checkout' => $e->getMessage(),
            ])->withInput();
        } catch (\Exception $e) {
            // Log full error untuk debugging tanpa expose ke user
            \Log::error('Checkout failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input' => $request->except(['_token']),
            ]);

            // Show generic error message untuk security
            return back()->withErrors([
                'checkout' => 'Terjadi kesalahan saat memproses pesanan. Silakan coba lagi atau hubungi customer service.',
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
     * dengan detail pesanan dan link WhatsApp menggunakan ULID verification untuk security
     */
    public function success(Order $order): Response|RedirectResponse
    {
        $user = auth()->user();

        // Validasi akses order dengan beberapa metode untuk security:
        // 1. Check session ULID (untuk immediate access setelah checkout)
        // 2. Check jika authenticated user adalah pemilik order
        // 3. Session timeout 15 menit untuk security

        $sessionUlid = session('checkout_order_ulid');
        $sessionCreatedAt = session('checkout_order_created_at');

        // Validate session-based access
        $isSessionOrder = $sessionUlid === $order->access_ulid
            && $sessionCreatedAt
            && now()->diffInMinutes($sessionCreatedAt) < 15;

        // Validate user ownership
        $isUserOrder = $user && $order->user_id === $user->id;

        // Deny access jika tidak memenuhi kondisi keamanan
        if (! $isSessionOrder && ! $isUserOrder) {
            return redirect()->route('home')
                ->with('error', 'Akses ke halaman ini tidak diizinkan. Silakan gunakan link yang dikirim via WhatsApp.');
        }

        $order->load('items');

        return Inertia::render('OrderSuccess', [
            'order' => $this->orderService->getOrderData($order),
            'whatsappUrl' => session('whatsapp_url', $this->orderService->generateWhatsAppUrl($order)),
        ]);
    }
}
