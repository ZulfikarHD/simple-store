<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Feature test untuk checkout flow (CUST-007, CUST-008, CUST-009)
 * Memastikan fitur checkout form, order creation, WhatsApp integration,
 * dan order success page berfungsi dengan benar
 *
 * @author Zulfikar Hidayatullah
 */
class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup untuk setiap test dengan menonaktifkan Vite
     * karena manifest tidak tersedia saat testing
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /**
     * Test redirect ke cart ketika checkout dengan cart kosong
     */
    public function test_redirects_to_cart_when_checkout_with_empty_cart(): void
    {
        $response = $this->get('/checkout');

        $response->assertRedirect('/cart');
        $response->assertSessionHas('error');
    }

    /**
     * Test cart dikosongkan setelah order berhasil
     * Menggunakan Services untuk setup dan verify
     */
    public function test_cart_is_cleared_after_successful_order(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->for($category)->create([
            'is_active' => true,
            'price' => 25000,
        ]);

        $cartService = app(CartService::class);
        $cartService->addItem($product->id, 2);

        $this->assertEquals(2, $cartService->getTotalItems());

        $orderService = app(OrderService::class);
        $orderService->createOrder([
            'customer_name' => 'John Doe',
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Test No. 123, Jakarta Selatan, 12345',
        ]);

        $this->assertEquals(0, $cartService->getTotalItems());
    }

    /**
     * Test dapat membuat order via OrderService dengan data valid
     */
    public function test_can_create_order_via_service(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->for($category)->create([
            'name' => 'Test Product',
            'is_active' => true,
            'price' => 25000,
        ]);

        $cartService = app(CartService::class);
        $cartService->addItem($product->id, 2);

        $orderService = app(OrderService::class);
        $order = $orderService->createOrder([
            'customer_name' => 'John Doe',
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Test No. 123, Jakarta Selatan, 12345',
            'notes' => 'Catatan test',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'customer_name' => 'John Doe',
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Test No. 123, Jakarta Selatan, 12345',
            'notes' => 'Catatan test',
            'status' => 'pending',
            'subtotal' => 50000,
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_name' => 'Test Product',
            'product_price' => 25000,
            'quantity' => 2,
            'subtotal' => 50000,
        ]);
    }

    /**
     * Test validasi form checkout dengan customer_name kosong
     */
    public function test_validation_fails_when_customer_name_is_empty(): void
    {
        $response = $this->from('/checkout')->post('/checkout', [
            'customer_name' => '',
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Test No. 123, Jakarta Selatan',
        ]);

        $response->assertSessionHasErrors('customer_name');
    }

    /**
     * Test validasi form checkout dengan phone tidak valid
     */
    public function test_validation_fails_when_phone_format_invalid(): void
    {
        $response = $this->from('/checkout')->post('/checkout', [
            'customer_name' => 'John Doe',
            'customer_phone' => '123', // Too short
            'customer_address' => 'Jl. Test No. 123, Jakarta Selatan',
        ]);

        $response->assertSessionHasErrors('customer_phone');
    }

    /**
     * Test validasi form checkout dengan address terlalu pendek
     */
    public function test_validation_fails_when_address_too_short(): void
    {
        $response = $this->from('/checkout')->post('/checkout', [
            'customer_name' => 'John Doe',
            'customer_phone' => '081234567890',
            'customer_address' => 'Short', // Too short
        ]);

        $response->assertSessionHasErrors('customer_address');
    }

    /**
     * Test dapat melihat halaman success untuk recent order
     */
    public function test_can_view_order_success_page_for_recent_order(): void
    {
        // Create a recent order (within 1 hour)
        $order = Order::factory()->create([
            'customer_name' => 'John Doe',
            'order_number' => 'ORD-20231126-ABCDE',
            'created_at' => now(),
        ]);

        $response = $this->get("/checkout/success/{$order->id}");

        $response->assertInertia(fn (Assert $page) => $page
            ->component('OrderSuccess')
            ->has('order')
            ->where('order.order_number', 'ORD-20231126-ABCDE')
            ->where('order.customer_name', 'John Doe')
            ->has('whatsappUrl')
        );
    }

    /**
     * Test WhatsApp URL generation dengan format yang benar
     */
    public function test_whatsapp_url_is_generated_correctly(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->for($category)->create([
            'name' => 'Test Product',
            'is_active' => true,
            'price' => 25000,
        ]);

        $cartService = app(CartService::class);
        $cartService->addItem($product->id, 2);

        $orderService = app(OrderService::class);

        $order = $orderService->createOrder([
            'customer_name' => 'John Doe',
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Test No. 123',
        ]);

        $whatsappUrl = $orderService->generateWhatsAppUrl($order);

        $this->assertStringStartsWith('https://wa.me/', $whatsappUrl);
        $this->assertStringContainsString('text=', $whatsappUrl);
        $this->assertStringContainsString(urlencode('PESANAN BARU'), $whatsappUrl);
    }

    /**
     * Test WhatsApp message berisi informasi order yang lengkap
     */
    public function test_whatsapp_message_contains_order_details(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->for($category)->create([
            'name' => 'Test Product',
            'is_active' => true,
            'price' => 25000,
        ]);

        $cartService = app(CartService::class);
        $cartService->addItem($product->id, 2);

        $orderService = app(OrderService::class);

        $order = $orderService->createOrder([
            'customer_name' => 'John Doe',
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Test No. 123',
            'notes' => 'Catatan khusus',
        ]);

        $message = $order->generateWhatsAppMessage();

        $this->assertStringContainsString($order->order_number, $message);
        $this->assertStringContainsString('John Doe', $message);
        $this->assertStringContainsString('081234567890', $message);
        $this->assertStringContainsString('Test Product', $message);
        $this->assertStringContainsString('Catatan khusus', $message);
    }

    /**
     * Test tidak dapat checkout dengan cart kosong (via OrderService)
     */
    public function test_cannot_create_order_with_empty_cart(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Keranjang belanja kosong');

        $orderService = app(OrderService::class);
        $orderService->createOrder([
            'customer_name' => 'John Doe',
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Test No. 123, Jakarta',
        ]);
    }

    /**
     * Test order number di-generate otomatis dengan format yang benar
     */
    public function test_order_number_is_auto_generated(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->for($category)->create(['is_active' => true]);

        $cartService = app(CartService::class);
        $cartService->addItem($product->id, 1);

        $orderService = app(OrderService::class);

        $order = $orderService->createOrder([
            'customer_name' => 'John Doe',
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Test No. 123, Jakarta',
        ]);

        $this->assertNotNull($order->order_number);
        $this->assertMatchesRegularExpression('/^ORD-\d{8}-[A-Z0-9]{5}$/', $order->order_number);
    }

    /**
     * Test order items menyimpan harga produk saat pembelian
     */
    public function test_order_items_store_product_price_at_time_of_purchase(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->for($category)->create([
            'name' => 'Test Product',
            'is_active' => true,
            'price' => 25000,
        ]);

        $cartService = app(CartService::class);
        $cartService->addItem($product->id, 2);

        $orderService = app(OrderService::class);

        $order = $orderService->createOrder([
            'customer_name' => 'John Doe',
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Test No. 123, Jakarta',
        ]);

        $orderItem = $order->items->first();

        $this->assertEquals('Test Product', $orderItem->product_name);
        $this->assertEquals(25000, $orderItem->product_price);
        $this->assertEquals(2, $orderItem->quantity);
        $this->assertEquals(50000, $orderItem->subtotal);
    }

    /**
     * Test redirect ke home ketika akses order success tanpa session dan order lama
     */
    public function test_old_orders_redirect_to_home_without_session(): void
    {
        // Create an old order (more than 1 hour ago)
        $order = Order::factory()->create([
            'created_at' => now()->subHours(2),
        ]);

        $response = $this->get("/checkout/success/{$order->id}");

        $response->assertRedirect('/');
    }

    /**
     * Test checkout error ketika cart kosong via HTTP
     */
    public function test_checkout_post_with_empty_cart_returns_error(): void
    {
        $response = $this->post('/checkout', [
            'customer_name' => 'John Doe',
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Test No. 123, Jakarta Selatan, 12345',
        ]);

        $response->assertSessionHasErrors('checkout');
    }

    /**
     * Test OrderService getOrderData returns correct format
     */
    public function test_order_service_returns_correct_data_format(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->for($category)->create([
            'name' => 'Test Product',
            'is_active' => true,
            'price' => 25000,
        ]);

        $cartService = app(CartService::class);
        $cartService->addItem($product->id, 2);

        $orderService = app(OrderService::class);

        $order = $orderService->createOrder([
            'customer_name' => 'John Doe',
            'customer_phone' => '081234567890',
            'customer_address' => 'Jl. Test No. 123',
        ]);

        $orderData = $orderService->getOrderData($order);

        $this->assertArrayHasKey('id', $orderData);
        $this->assertArrayHasKey('order_number', $orderData);
        $this->assertArrayHasKey('customer_name', $orderData);
        $this->assertArrayHasKey('customer_phone', $orderData);
        $this->assertArrayHasKey('customer_address', $orderData);
        $this->assertArrayHasKey('items', $orderData);
        $this->assertArrayHasKey('subtotal', $orderData);
        $this->assertArrayHasKey('total', $orderData);
        $this->assertArrayHasKey('formatted_total', $orderData);

        $this->assertEquals('John Doe', $orderData['customer_name']);
        $this->assertCount(1, $orderData['items']);
    }
}
