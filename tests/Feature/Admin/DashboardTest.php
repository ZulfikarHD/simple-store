<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test bahwa guest tidak dapat akses dashboard admin
     * dengan redirect ke login page untuk autentikasi
     */
    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test bahwa authenticated user dapat akses dashboard
     * dengan response success dan render page yang tepat
     */
    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Admin/Dashboard'));
    }

    /**
     * Test dashboard menampilkan statistik yang benar
     * dengan data orders, products, dan calculations yang akurat
     */
    public function test_dashboard_displays_correct_statistics(): void
    {
        $user = User::factory()->admin()->create();

        // Buat test data dengan factory untuk simulate real data
        Product::factory()->count(5)->create(['is_active' => true]);
        Product::factory()->count(2)->create(['is_active' => false]);

        // Buat orders dengan berbagai status
        Order::factory()->count(3)->create([
            'status' => 'pending',
            'total' => 100000,
            'created_at' => now(),
        ]);

        Order::factory()->count(2)->create([
            'status' => 'confirmed',
            'total' => 150000,
            'created_at' => now()->subDay(),
        ]);

        Order::factory()->create([
            'status' => 'delivered',
            'total' => 200000,
            'created_at' => now()->subDays(2),
        ]);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Dashboard')
            ->has('stats')
            ->where('stats.today_orders', 3)
            ->where('stats.pending_orders', 3)
            ->where('stats.active_products', 5)
            ->where('stats.total_sales', 800000));
    }

    /**
     * Test dashboard menampilkan recent orders
     * dengan data yang ter-sort dan formatted dengan benar
     */
    public function test_dashboard_displays_recent_orders(): void
    {
        $user = User::factory()->admin()->create();

        // Buat 7 orders untuk test limit 5
        Order::factory()->count(7)->create();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Dashboard')
            ->has('stats.recent_orders', 5)
            ->has('stats.recent_orders.0', fn ($order) => $order
                ->has('id')
                ->has('order_number')
                ->has('customer_name')
                ->has('customer_phone')
                ->has('total')
                ->has('status')
                ->has('items_count')
                ->has('created_at')
                ->has('created_at_human')));
    }

    /**
     * Test dashboard menampilkan order status breakdown
     * dengan grouping yang benar per status
     */
    public function test_dashboard_displays_order_status_breakdown(): void
    {
        $user = User::factory()->admin()->create();

        // Buat orders dengan berbagai status
        Order::factory()->count(5)->create(['status' => 'pending']);
        Order::factory()->count(3)->create(['status' => 'confirmed']);
        Order::factory()->count(2)->create(['status' => 'delivered']);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Dashboard')
            ->has('stats.order_status_breakdown')
            ->where('stats.order_status_breakdown.pending', 5)
            ->where('stats.order_status_breakdown.confirmed', 3)
            ->where('stats.order_status_breakdown.delivered', 2));
    }

    /**
     * Test dashboard handles empty data dengan graceful
     * tanpa error ketika tidak ada orders atau products
     */
    public function test_dashboard_handles_empty_data(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Dashboard')
            ->where('stats.today_orders', 0)
            ->where('stats.pending_orders', 0)
            ->where('stats.active_products', 0)
            ->where('stats.total_sales', 0)
            ->has('stats.recent_orders', 0));
    }
}
