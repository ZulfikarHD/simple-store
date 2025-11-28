<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test untuk Admin OrderController
 * mencakup view orders, order detail, dan update status
 */
class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test bahwa guest tidak dapat akses halaman orders
     */
    public function test_guest_cannot_access_orders_index(): void
    {
        $response = $this->get(route('admin.orders.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test bahwa guest tidak dapat akses halaman detail order
     */
    public function test_guest_cannot_access_order_detail(): void
    {
        $order = Order::factory()->create();

        $response = $this->get(route('admin.orders.show', $order));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test authenticated user dapat melihat daftar orders
     */
    public function test_authenticated_user_can_view_orders_list(): void
    {
        $user = User::factory()->admin()->create();
        Order::factory()->count(5)->create();

        $response = $this->actingAs($user)->get(route('admin.orders.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Orders/Index')
            ->has('orders.data', 5)
            ->has('statuses'));
    }

    /**
     * Test dapat mencari orders berdasarkan order number
     */
    public function test_can_search_orders_by_order_number(): void
    {
        $user = User::factory()->admin()->create();
        $order1 = Order::factory()->create(['order_number' => 'ORD-20241126-AAAAA']);
        Order::factory()->create(['order_number' => 'ORD-20241126-BBBBB']);

        $response = $this->actingAs($user)->get(route('admin.orders.index', ['search' => 'AAAAA']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Orders/Index')
            ->has('orders.data', 1)
            ->where('orders.data.0.order_number', $order1->order_number));
    }

    /**
     * Test dapat mencari orders berdasarkan nama customer
     */
    public function test_can_search_orders_by_customer_name(): void
    {
        $user = User::factory()->admin()->create();
        Order::factory()->create(['customer_name' => 'John Doe']);
        Order::factory()->create(['customer_name' => 'Jane Smith']);

        $response = $this->actingAs($user)->get(route('admin.orders.index', ['search' => 'John']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('orders.data', 1)
            ->where('orders.data.0.customer_name', 'John Doe'));
    }

    /**
     * Test dapat mencari orders berdasarkan nomor telepon
     */
    public function test_can_search_orders_by_phone(): void
    {
        $user = User::factory()->admin()->create();
        Order::factory()->create(['customer_phone' => '081234567890']);
        Order::factory()->create(['customer_phone' => '089876543210']);

        $response = $this->actingAs($user)->get(route('admin.orders.index', ['search' => '081234']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('orders.data', 1));
    }

    /**
     * Test dapat filter orders berdasarkan status
     */
    public function test_can_filter_orders_by_status(): void
    {
        $user = User::factory()->admin()->create();
        Order::factory()->count(3)->create(['status' => 'pending']);
        Order::factory()->count(2)->create(['status' => 'confirmed']);
        Order::factory()->count(1)->create(['status' => 'delivered']);

        $response = $this->actingAs($user)->get(route('admin.orders.index', ['status' => 'pending']));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('orders.data', 3));
    }

    /**
     * Test dapat filter orders berdasarkan date range
     */
    public function test_can_filter_orders_by_date_range(): void
    {
        $user = User::factory()->admin()->create();
        Order::factory()->create(['created_at' => now()->subDays(5)]);
        Order::factory()->create(['created_at' => now()->subDays(2)]);
        Order::factory()->create(['created_at' => now()]);

        $response = $this->actingAs($user)->get(route('admin.orders.index', [
            'start_date' => now()->subDays(3)->format('Y-m-d'),
            'end_date' => now()->format('Y-m-d'),
        ]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('orders.data', 2));
    }

    /**
     * Test dapat melihat detail order
     */
    public function test_can_view_order_detail(): void
    {
        $user = User::factory()->admin()->create();
        $order = Order::factory()->create([
            'customer_name' => 'Test Customer',
            'status' => 'pending',
        ]);
        $product = Product::factory()->create();
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($user)->get(route('admin.orders.show', $order));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Orders/Show')
            ->has('order')
            ->has('statuses')
            ->where('order.customer_name', 'Test Customer')
            ->where('order.status', 'pending'));
    }

    /**
     * Test dapat update status order ke confirmed
     */
    public function test_can_update_order_status_to_confirmed(): void
    {
        $user = User::factory()->admin()->create();
        $order = Order::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($user)->patch(route('admin.orders.updateStatus', $order), [
            'status' => 'confirmed',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'confirmed',
        ]);
        $order->refresh();
        $this->assertNotNull($order->confirmed_at);
    }

    /**
     * Test dapat update status order ke preparing
     */
    public function test_can_update_order_status_to_preparing(): void
    {
        $user = User::factory()->admin()->create();
        $order = Order::factory()->create(['status' => 'confirmed']);

        $response = $this->actingAs($user)->patch(route('admin.orders.updateStatus', $order), [
            'status' => 'preparing',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $order->refresh();
        $this->assertEquals('preparing', $order->status);
        $this->assertNotNull($order->preparing_at);
    }

    /**
     * Test dapat update status order ke ready
     */
    public function test_can_update_order_status_to_ready(): void
    {
        $user = User::factory()->admin()->create();
        $order = Order::factory()->create(['status' => 'preparing']);

        $response = $this->actingAs($user)->patch(route('admin.orders.updateStatus', $order), [
            'status' => 'ready',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $order->refresh();
        $this->assertEquals('ready', $order->status);
        $this->assertNotNull($order->ready_at);
    }

    /**
     * Test dapat update status order ke delivered
     */
    public function test_can_update_order_status_to_delivered(): void
    {
        $user = User::factory()->admin()->create();
        $order = Order::factory()->create(['status' => 'ready']);

        $response = $this->actingAs($user)->patch(route('admin.orders.updateStatus', $order), [
            'status' => 'delivered',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $order->refresh();
        $this->assertEquals('delivered', $order->status);
        $this->assertNotNull($order->delivered_at);
    }

    /**
     * Test dapat cancel order dengan alasan
     */
    public function test_can_cancel_order_with_reason(): void
    {
        $user = User::factory()->admin()->create();
        $order = Order::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($user)->patch(route('admin.orders.updateStatus', $order), [
            'status' => 'cancelled',
            'cancellation_reason' => 'Stok habis',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
        $this->assertEquals('Stok habis', $order->cancellation_reason);
        $this->assertNotNull($order->cancelled_at);
    }

    /**
     * Test validasi wajib cancellation_reason saat status cancelled
     */
    public function test_cancellation_requires_reason(): void
    {
        $user = User::factory()->admin()->create();
        $order = Order::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($user)->patch(route('admin.orders.updateStatus', $order), [
            'status' => 'cancelled',
        ]);

        $response->assertSessionHasErrors('cancellation_reason');
    }

    /**
     * Test validasi status harus valid
     */
    public function test_status_must_be_valid(): void
    {
        $user = User::factory()->admin()->create();
        $order = Order::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($user)->patch(route('admin.orders.updateStatus', $order), [
            'status' => 'invalid_status',
        ]);

        $response->assertSessionHasErrors('status');
    }

    /**
     * Test pagination orders berfungsi dengan benar
     */
    public function test_orders_pagination_works(): void
    {
        $user = User::factory()->admin()->create();
        Order::factory()->count(15)->create();

        $response = $this->actingAs($user)->get(route('admin.orders.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('orders.data', 10)
            ->where('orders.total', 15)
            ->where('orders.last_page', 2));
    }

    /**
     * Test order detail menampilkan items dengan benar
     */
    public function test_order_detail_shows_items_correctly(): void
    {
        $user = User::factory()->admin()->create();
        $order = Order::factory()->create();
        $product = Product::factory()->create();
        OrderItem::factory()->count(3)->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
        ]);

        $response = $this->actingAs($user)->get(route('admin.orders.show', $order));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('order.items', 3));
    }
}
