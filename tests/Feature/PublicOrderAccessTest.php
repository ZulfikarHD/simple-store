<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests untuk Public Order Access dengan ULID dan phone verification
 *
 * @author Zulfikar Hidayatullah
 */
class PublicOrderAccessTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test ULID auto-generation pada order creation
     */
    public function test_ulid_is_generated_automatically_on_order_creation(): void
    {
        $order = Order::factory()->create();

        $this->assertNotNull($order->access_ulid);
        $this->assertEquals(26, strlen($order->access_ulid));
        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $order->access_ulid);
    }

    /**
     * Test access order page dengan valid ULID menampilkan form verifikasi
     */
    public function test_can_access_order_page_with_valid_ulid(): void
    {
        $order = Order::factory()->create([
            'status' => 'pending',
        ]);

        $response = $this->get(route('orders.view', ['ulid' => $order->access_ulid]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('PublicOrderView')
            ->where('ulid', $order->access_ulid)
            ->where('expired', false)
            ->where('order', null)
        );
    }

    /**
     * Test access order dengan invalid ULID returns 404
     */
    public function test_invalid_ulid_returns_404(): void
    {
        $response = $this->get(route('orders.view', ['ulid' => '01JFEW9XYZABCDEFGHIJKLMNOP']));

        $response->assertNotFound();
    }

    /**
     * Test phone verification dengan nomor yang benar
     */
    public function test_phone_verification_with_correct_phone_succeeds(): void
    {
        $order = Order::factory()->create([
            'status' => 'pending',
            'customer_phone' => '+6281234567890',
        ]);

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('orders.verify', ['ulid' => $order->access_ulid]), [
                'customer_phone' => '081234567890', // Format berbeda tapi nomor sama
            ]);

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('PublicOrderView')
            ->where('verified', true)
            ->has('order')
        );
    }

    /**
     * Test phone verification dengan nomor yang salah
     */
    public function test_phone_verification_with_incorrect_phone_fails(): void
    {
        $order = Order::factory()->create([
            'status' => 'pending',
            'customer_phone' => '+6281234567890',
        ]);

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('orders.verify', ['ulid' => $order->access_ulid]), [
                'customer_phone' => '089876543210', // Nomor berbeda
            ]);

        $response->assertSessionHasErrors('customer_phone');
    }

    /**
     * Test access expired untuk order yang delivered
     */
    public function test_access_expired_for_delivered_orders(): void
    {
        $order = Order::factory()->create([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        $response = $this->get(route('orders.view', ['ulid' => $order->access_ulid]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('PublicOrderView')
            ->where('expired', true)
            ->where('orderNumber', $order->order_number)
        );
    }

    /**
     * Test access expired untuk order yang cancelled
     */
    public function test_access_expired_for_cancelled_orders(): void
    {
        $order = Order::factory()->create([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => 'Test cancellation',
        ]);

        $response = $this->get(route('orders.view', ['ulid' => $order->access_ulid]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('PublicOrderView')
            ->where('expired', true)
        );
    }

    /**
     * Test access tidak expired untuk order dengan status lain
     */
    public function test_access_not_expired_for_active_orders(): void
    {
        $statuses = ['pending', 'confirmed', 'preparing', 'ready'];

        foreach ($statuses as $status) {
            $order = Order::factory()->create([
                'status' => $status,
            ]);

            $this->assertFalse($order->isAccessExpired(), "Order with status {$status} should not be expired");
        }
    }

    /**
     * Test admin redirect ke admin panel saat akses ULID
     */
    public function test_admin_redirects_to_admin_panel_when_accessing_ulid(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $order = Order::factory()->create([
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('orders.view', ['ulid' => $order->access_ulid]));

        $response->assertRedirect(route('admin.orders.showByUlid', ['ulid' => $order->access_ulid]));
    }

    /**
     * Test rate limiting pada phone verification
     */
    public function test_phone_verification_is_rate_limited(): void
    {
        $order = Order::factory()->create([
            'status' => 'pending',
            'customer_phone' => '+6281234567890',
        ]);

        // Lakukan 6 request (limit adalah 5 per 15 menit)
        for ($i = 0; $i < 6; $i++) {
            $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
                ->post(route('orders.verify', ['ulid' => $order->access_ulid]), [
                    'customer_phone' => '089999999999', // Wrong phone
                ]);
        }

        // Request ke-6 harus di-throttle
        $response->assertStatus(429);
    }

    /**
     * Test phone normalization dalam verifikasi
     */
    public function test_phone_normalization_works_correctly(): void
    {
        $order = Order::factory()->create([
            'status' => 'pending',
            'customer_phone' => '+6281234567890',
        ]);

        // Test berbagai format nomor yang sama
        $phoneFormats = [
            '081234567890',      // Format lokal
            '6281234567890',     // Tanpa +
            '+6281234567890',    // Dengan +
            '081-234-567-890',   // Dengan strip
            '0812 3456 7890',    // Dengan spasi
        ];

        foreach ($phoneFormats as $phone) {
            $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
                ->post(route('orders.verify', ['ulid' => $order->access_ulid]), [
                    'customer_phone' => $phone,
                ]);

            $response->assertOk();
            $response->assertInertia(fn ($page) => $page
                ->where('verified', true)
            );
        }
    }

    /**
     * Test verifikasi tidak bisa dilakukan pada expired order
     */
    public function test_verification_fails_on_expired_order(): void
    {
        $order = Order::factory()->create([
            'status' => 'delivered',
            'delivered_at' => now(),
            'customer_phone' => '+6281234567890',
        ]);

        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('orders.verify', ['ulid' => $order->access_ulid]), [
                'customer_phone' => '081234567890',
            ]);

        $response->assertSessionHasErrors('customer_phone');
    }

    /**
     * Test admin dapat akses order via ULID
     */
    public function test_admin_can_access_order_via_ulid(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $order = Order::factory()->create([
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.orders.showByUlid', ['ulid' => $order->access_ulid]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Orders/Show')
        );
    }

    /**
     * Test checkout success dengan ULID verification
     */
    public function test_checkout_success_requires_valid_session_ulid(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => null, // Guest order, bukan milik user
            'status' => 'pending',
        ]);

        // Tanpa session ULID yang valid dan order bukan milik user, akses ditolak
        $response = $this->actingAs($user)
            ->get(route('checkout.success', ['order' => $order->id]));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('error');
    }

    /**
     * Test checkout success dengan valid session ULID
     */
    public function test_checkout_success_works_with_valid_session_ulid(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        // Set session dengan ULID yang valid
        session([
            'checkout_order_ulid' => $order->access_ulid,
            'checkout_order_created_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get(route('checkout.success', ['order' => $order->id]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('OrderSuccess')
            ->has('order')
        );
    }

    /**
     * Test mass assignment protection untuk status fields
     */
    public function test_status_fields_are_protected_from_mass_assignment(): void
    {
        // Attempt to create order dengan status fields via mass assignment
        $order = Order::create([
            'order_number' => 'TEST-001',
            'customer_name' => 'Test User',
            'customer_phone' => '+6281234567890',
            'customer_address' => 'Test Address',
            'subtotal' => 100000,
            'delivery_fee' => 10000,
            'total' => 110000,
            'status' => 'delivered', // Should be ignored
            'confirmed_at' => now(), // Should be ignored
        ]);

        // Status seharusnya tidak ter-set via mass assignment
        // dan akan null atau default value
        $this->assertNotEquals('delivered', $order->status);
    }
}
