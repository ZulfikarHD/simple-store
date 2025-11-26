# Sprint 2 - CUST-008: WhatsApp Integration

**Author:** Zulfikar Hidayatullah  
**Sprint:** Sprint 2 (Week 2-3)  
**Story Points:** 3  
**Status:** ✅ Completed  
**Date:** 2024-11-26

---

## Overview

CUST-008 merupakan implementasi WhatsApp integration yang bertujuan untuk memungkinkan customer melakukan konfirmasi pesanan via WhatsApp Business, yaitu: generate pesan otomatis dengan detail order, redirect ke WhatsApp dengan pre-filled message, dan penyimpanan order ke database sebelum redirect.

## User Story

```
As a customer, I want to confirm my order via WhatsApp
so I can communicate directly with the seller for payment and delivery
```

## Acceptance Criteria

- ✅ Order tersimpan ke database sebelum redirect ke WhatsApp
- ✅ Generate WhatsApp message dengan format yang informatif
- ✅ Pre-filled message berisi detail order lengkap
- ✅ Redirect ke WhatsApp Business number dari config
- ✅ Cart dikosongkan setelah order berhasil

---

## Technical Implementation

### Backend Architecture

#### 1. OrderService

File: `app/Services/OrderService.php`

Service untuk mengelola pembuatan order dan WhatsApp integration, yaitu:

```php
/**
 * OrderService untuk mengelola operasi pembuatan order
 * dengan integrasi WhatsApp message generation
 */
class OrderService
{
    public function __construct(public CartService $cartService) {}

    /**
     * Membuat order baru dari cart items
     */
    public function createOrder(array $customerData): Order
    {
        $cart = $this->cartService->getCart();

        if ($cart->items->isEmpty()) {
            throw new \Exception('Keranjang belanja kosong.');
        }

        return DB::transaction(function () use ($cart, $customerData) {
            $subtotal = $cart->subtotal;
            $deliveryFee = 0;
            $total = $subtotal + $deliveryFee;

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

            // Copy cart items ke order items
            foreach ($cart->items as $cartItem) {
                $order->items()->create([
                    'product_id' => $cartItem->product_id,
                    'product_name' => $cartItem->product->name,
                    'product_price' => $cartItem->product->price,
                    'quantity' => $cartItem->quantity,
                    'subtotal' => $cartItem->subtotal,
                ]);
            }

            // Clear cart setelah order berhasil
            $this->cartService->clearCart();

            return $order;
        });
    }

    /**
     * Generate WhatsApp URL dengan pre-filled message
     */
    public function generateWhatsAppUrl(Order $order): string
    {
        $phoneNumber = config('services.whatsapp.phone_number');
        $message = $order->generateWhatsAppMessage();
        $encodedMessage = urlencode($message);

        return "https://wa.me/{$phoneNumber}?text={$encodedMessage}";
    }
}
```

**Key Features:**
- Database transaction untuk atomic operation
- Menyimpan product_name dan product_price di order_items (snapshot harga saat pembelian)
- Auto-clear cart setelah order berhasil
- WhatsApp URL generation dengan encoded message

#### 2. Order Model - WhatsApp Message Generation

File: `app/Models/Order.php`

Method untuk generate WhatsApp message dengan format yang informatif:

```php
/**
 * Generate WhatsApp message untuk order ini
 */
public function generateWhatsAppMessage(): string
{
    $items = $this->items->map(function ($item) {
        return "• {$item->product_name} x{$item->quantity} = Rp " . 
            number_format($item->subtotal, 0, ',', '.');
    })->implode("\n");

    return "🛒 *PESANAN BARU*\n\n"
        . "📋 No. Pesanan: {$this->order_number}\n"
        . "👤 Nama: {$this->customer_name}\n"
        . "📱 Telepon: {$this->customer_phone}\n"
        . "📍 Alamat: {$this->customer_address}\n\n"
        . "*Detail Pesanan:*\n{$items}\n\n"
        . "💰 Subtotal: Rp " . number_format($this->subtotal, 0, ',', '.') . "\n"
        . "🚚 Ongkir: Rp " . number_format($this->delivery_fee, 0, ',', '.') . "\n"
        . "*Total: Rp " . number_format($this->total, 0, ',', '.') . "*\n\n"
        . ($this->notes ? "📝 Catatan: {$this->notes}\n\n" : '')
        . "Terima kasih telah memesan! 🙏";
}
```

**Message Format:**
- Emoji untuk visual appeal
- Bold text menggunakan WhatsApp formatting (*text*)
- Informasi customer lengkap
- Detail items dengan quantity dan subtotal
- Total order dengan format Rupiah

#### 3. WhatsApp Configuration

File: `config/services.php`

Config untuk WhatsApp Business number:

```php
'whatsapp' => [
    'phone_number' => env('WHATSAPP_PHONE_NUMBER', '6281234567890'),
],
```

File: `.env.example`

```
# WhatsApp Business Number
WHATSAPP_PHONE_NUMBER=6281234567890
```

#### 4. CheckoutController - Store Method

File: `app/Http/Controllers/CheckoutController.php`

Method untuk proses checkout dan redirect ke WhatsApp:

```php
public function store(CheckoutRequest $request): RedirectResponse
{
    $validated = $request->validated();

    try {
        $order = $this->orderService->createOrder($validated);
        $whatsappUrl = $this->orderService->generateWhatsAppUrl($order);

        session()->flash('whatsapp_url', $whatsappUrl);

        return redirect()->route('checkout.success', ['order' => $order->id]);
    } catch (\Exception $e) {
        return back()->withErrors([
            'checkout' => $e->getMessage(),
        ])->withInput();
    }
}
```

---

## Testing

### Feature Tests

```php
// Test WhatsApp URL generation
public function test_whatsapp_url_is_generated_correctly(): void
{
    // Setup cart with items
    $cartService = app(CartService::class);
    $cartService->addItem($product->id, 2);

    $orderService = app(OrderService::class);
    $order = $orderService->createOrder([...]);

    $whatsappUrl = $orderService->generateWhatsAppUrl($order);

    $this->assertStringStartsWith('https://wa.me/', $whatsappUrl);
    $this->assertStringContainsString('text=', $whatsappUrl);
}

// Test WhatsApp message content
public function test_whatsapp_message_contains_order_details(): void
{
    $order = $orderService->createOrder([
        'customer_name' => 'John Doe',
        'notes' => 'Catatan khusus',
    ]);

    $message = $order->generateWhatsAppMessage();

    $this->assertStringContainsString($order->order_number, $message);
    $this->assertStringContainsString('John Doe', $message);
    $this->assertStringContainsString('Catatan khusus', $message);
}
```

### Test Results

```
✓ whatsapp url is generated correctly
✓ whatsapp message contains order details
✓ cart is cleared after successful order
```

---

## WhatsApp Message Example

```
🛒 *PESANAN BARU*

📋 No. Pesanan: ORD-20241126-ABC12
👤 Nama: John Doe
📱 Telepon: 081234567890
📍 Alamat: Jl. Contoh No. 123, Jakarta Selatan

*Detail Pesanan:*
• Produk A x2 = Rp 50.000
• Produk B x1 = Rp 25.000

💰 Subtotal: Rp 75.000
🚚 Ongkir: Rp 0
*Total: Rp 75.000*

📝 Catatan: Tolong dibungkus dengan bubble wrap

Terima kasih telah memesan! 🙏
```

---

## Results

### Files Created/Modified

**New Files:**
- `app/Services/OrderService.php`

**Modified Files:**
- `app/Models/Order.php` - Added `generateWhatsAppMessage()` method
- `config/services.php` - Added WhatsApp configuration
- `.env.example` - Added WHATSAPP_PHONE_NUMBER

---

## Related Documentation

- [CUST-007: Checkout Form](./sprint-2-cust-007.md)
- [CUST-009: Order Confirmation](./sprint-2-cust-009.md)

