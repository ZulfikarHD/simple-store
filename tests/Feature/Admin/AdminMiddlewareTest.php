<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test untuk EnsureUserIsAdmin middleware
 * memastikan hanya admin yang dapat mengakses halaman admin
 *
 * @author Zulfikar Hidayatullah
 */
class AdminMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test customer tidak dapat mengakses admin dashboard
     */
    public function test_customer_cannot_access_admin_dashboard(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)->get(route('admin.dashboard'));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error');
    }

    /**
     * Test customer tidak dapat mengakses admin products
     */
    public function test_customer_cannot_access_admin_products(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)->get(route('admin.products.index'));

        $response->assertRedirect(route('home'));
    }

    /**
     * Test customer tidak dapat mengakses admin orders
     */
    public function test_customer_cannot_access_admin_orders(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)->get(route('admin.orders.index'));

        $response->assertRedirect(route('home'));
    }

    /**
     * Test customer tidak dapat mengakses admin settings
     */
    public function test_customer_cannot_access_admin_settings(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)->get(route('admin.settings.index'));

        $response->assertRedirect(route('home'));
    }

    /**
     * Test customer tidak dapat mengakses admin categories
     */
    public function test_customer_cannot_access_admin_categories(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)->get(route('admin.categories.index'));

        $response->assertRedirect(route('home'));
    }

    /**
     * Test admin dapat mengakses admin dashboard
     */
    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
    }

    /**
     * Test AJAX request dari customer mengembalikan 403
     */
    public function test_customer_ajax_request_returns_403(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)
            ->withHeader('Accept', 'application/json')
            ->get(route('admin.dashboard'));

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.',
        ]);
    }
}
