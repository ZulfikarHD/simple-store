<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test untuk model Order dan OrderItem
 * memverifikasi relationship, status tracking, dan helper methods
 */
class OrderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test pembuatan order dengan factory
     */
    public function test_can_create_order(): void
    {
        $order = Order::factory()->create([
            'customer_name' => 'John Doe',
            'customer_phone' => '081234567890',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'customer_name' => 'John Doe',
            'status' => 'pending',
        ]);
    }

    /**
     * Test auto-generate order number
     */
    public function test_auto_generates_order_number(): void
    {
        $order = Order::factory()->create();

        $this->assertNotNull($order->order_number);
        $this->assertStringStartsWith('ORD-', $order->order_number);
    }

    /**
     * Test relationship order belongs to user
     */
    public function test_order_belongs_to_user(): void
    {
        $user = User::factory()->customer()->create();
        $order = Order::factory()->forUser($user)->create();

        $this->assertInstanceOf(User::class, $order->user);
        $this->assertEquals($user->id, $order->user->id);
    }

    /**
     * Test relationship order has many items
     */
    public function test_order_has_many_items(): void
    {
        $category = Category::factory()->create();
        $products = Product::factory()->count(3)->create(['category_id' => $category->id]);
        $order = Order::factory()->create();

        foreach ($products as $product) {
            OrderItem::factory()->forProduct($product)->create(['order_id' => $order->id]);
        }

        $this->assertCount(3, $order->fresh()->items);
        $this->assertInstanceOf(OrderItem::class, $order->items->first());
    }

    /**
     * Test order status transition - confirm
     */
    public function test_can_confirm_order(): void
    {
        $order = Order::factory()->create(['status' => 'pending']);

        $order->confirm();

        $this->assertEquals('confirmed', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->confirmed_at);
    }

    /**
     * Test order status transition - start preparing
     */
    public function test_can_start_preparing_order(): void
    {
        $order = Order::factory()->confirmed()->create();

        $order->startPreparing();

        $this->assertEquals('preparing', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->preparing_at);
    }

    /**
     * Test order status transition - mark ready
     */
    public function test_can_mark_order_ready(): void
    {
        $order = Order::factory()->preparing()->create();

        $order->markReady();

        $this->assertEquals('ready', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->ready_at);
    }

    /**
     * Test order status transition - mark delivered
     */
    public function test_can_mark_order_delivered(): void
    {
        $order = Order::factory()->ready()->create();

        $order->markDelivered();

        $this->assertEquals('delivered', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->delivered_at);
    }

    /**
     * Test order cancellation
     */
    public function test_can_cancel_order_with_reason(): void
    {
        $order = Order::factory()->create();

        $order->cancel('Customer requested cancellation');

        $this->assertEquals('cancelled', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->cancelled_at);
        $this->assertEquals('Customer requested cancellation', $order->fresh()->cancellation_reason);
    }

    /**
     * Test scope pending pada order
     */
    public function test_scope_pending_filters_pending_orders(): void
    {
        Order::factory()->count(3)->create(['status' => 'pending']);
        Order::factory()->count(2)->create(['status' => 'confirmed']);

        $pendingOrders = Order::pending()->get();

        $this->assertCount(3, $pendingOrders);
    }

    /**
     * Test formatted total attribute
     */
    public function test_formatted_total_attribute(): void
    {
        $order = Order::factory()->create(['total' => 125000]);

        $this->assertEquals('Rp 125.000', $order->formatted_total);
    }

    /**
     * Test generate WhatsApp message
     */
    public function test_can_generate_whatsapp_message(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Nasi Goreng',
            'price' => 25000,
        ]);

        $order = Order::factory()->create([
            'customer_name' => 'John Doe',
            'subtotal' => 50000,
            'delivery_fee' => 10000,
            'total' => 60000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => 'Nasi Goreng',
            'product_price' => 25000,
            'quantity' => 2,
            'subtotal' => 50000,
        ]);

        $message = $order->fresh()->generateWhatsAppMessage();

        $this->assertStringContainsString('PESANAN BARU', $message);
        $this->assertStringContainsString($order->order_number, $message);
        $this->assertStringContainsString('John Doe', $message);
        $this->assertStringContainsString('Nasi Goreng', $message);
    }

    /**
     * Test user role helper methods
     */
    public function test_user_role_helper_methods(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->customer()->create();

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isCustomer());

        $this->assertTrue($customer->isCustomer());
        $this->assertFalse($customer->isAdmin());
    }

    /**
     * Test user has many orders relationship
     */
    public function test_user_has_many_orders(): void
    {
        $user = User::factory()->customer()->create();
        Order::factory()->count(3)->forUser($user)->create();

        $this->assertCount(3, $user->orders);
    }
}
