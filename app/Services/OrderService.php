<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * OrderService untuk mengelola operasi pembuatan order
 * dengan integrasi WhatsApp message generation
 * mencakup create order, clear cart, dan generate WhatsApp URL
 */
class OrderService
{
    public function __construct(public CartService $cartService) {}

    /**
     * Membuat order baru dari cart items
     * dengan menyimpan data customer dan items ke database
     *
     * @param  array<string, mixed>  $customerData  Data customer dari checkout form
     * @return Order Order yang berhasil dibuat
     *
     * @throws \Exception Jika cart kosong atau terjadi error saat transaksi
     */
    public function createOrder(array $customerData): Order
    {
        $cart = $this->cartService->getCart();

        if ($cart->items->isEmpty()) {
            throw new \Exception('Keranjang belanja kosong. Tidak dapat melanjutkan checkout.');
        }

        return DB::transaction(function () use ($cart, $customerData) {
            // Hitung subtotal dan total
            $subtotal = $cart->subtotal;
            $deliveryFee = 0; // Free delivery untuk saat ini
            $total = $subtotal + $deliveryFee;

            // Buat order baru
            $order = Order::create([
                'customer_name' => $customerData['customer_name'],
                'customer_phone' => $customerData['customer_phone'],
                'customer_address' => $customerData['customer_address'],
                'notes' => $customerData['notes'] ?? null,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
                'status' => 'pending',
            ]);

            // Tambahkan items ke order
            foreach ($cart->items as $cartItem) {
                $order->items()->create([
                    'product_id' => $cartItem->product_id,
                    'product_name' => $cartItem->product->name,
                    'product_price' => $cartItem->product->price,
                    'quantity' => $cartItem->quantity,
                    'subtotal' => $cartItem->subtotal,
                ]);
            }

            // Reload order dengan items untuk WhatsApp message
            $order->load('items');

            // Kosongkan cart setelah order berhasil
            $this->cartService->clearCart();

            return $order;
        });
    }

    /**
     * Generate WhatsApp URL dengan pre-filled message
     * menggunakan nomor WhatsApp dari config
     *
     * @param  Order  $order  Order yang akan dikirim ke WhatsApp
     * @return string URL WhatsApp dengan encoded message
     */
    public function generateWhatsAppUrl(Order $order): string
    {
        $phoneNumber = config('services.whatsapp.phone_number', '6281234567890');
        $message = $order->generateWhatsAppMessage();
        $encodedMessage = urlencode($message);

        return "https://wa.me/{$phoneNumber}?text={$encodedMessage}";
    }

    /**
     * Mendapatkan data order untuk response frontend
     * dengan format yang siap digunakan di halaman success
     *
     * @param  Order  $order  Order yang akan diformat
     * @return array<string, mixed>
     */
    public function getOrderData(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'customer_address' => $order->customer_address,
            'notes' => $order->notes,
            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_name' => $item->product_name,
                    'product_price' => $item->product_price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->subtotal,
                    'formatted_subtotal' => $item->formatted_subtotal,
                ];
            }),
            'subtotal' => $order->subtotal,
            'delivery_fee' => $order->delivery_fee,
            'total' => $order->total,
            'formatted_total' => $order->formatted_total,
            'status' => $order->status,
            'created_at' => $order->created_at->format('d M Y H:i'),
        ];
    }
}
