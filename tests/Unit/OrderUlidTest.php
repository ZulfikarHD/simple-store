<?php

namespace Tests\Unit;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests untuk ULID functionality pada Order model
 *
 * @author Zulfikar Hidayatullah
 */
class OrderUlidTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test ULID auto-generation saat order dibuat
     */
    public function test_ulid_is_auto_generated_on_order_creation(): void
    {
        $order = Order::factory()->create();

        $this->assertNotNull($order->access_ulid);
        $this->assertEquals(26, strlen($order->access_ulid));
        $this->assertMatchesRegularExpression('/^[0-9A-HJKMNP-TV-Z]{26}$/', $order->access_ulid);
    }

    /**
     * Test ULID unique untuk setiap order
     */
    public function test_each_order_has_unique_ulid(): void
    {
        $order1 = Order::factory()->create();
        $order2 = Order::factory()->create();

        $this->assertNotEquals($order1->access_ulid, $order2->access_ulid);
    }

    /**
     * Test isAccessExpired untuk delivered order
     */
    public function test_delivered_order_is_expired(): void
    {
        $order = Order::factory()->create([
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        $this->assertTrue($order->isAccessExpired());
    }

    /**
     * Test isAccessExpired untuk cancelled order
     */
    public function test_cancelled_order_is_expired(): void
    {
        $order = Order::factory()->create([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        $this->assertTrue($order->isAccessExpired());
    }

    /**
     * Test isAccessExpired untuk pending order (tidak expired)
     */
    public function test_pending_order_is_not_expired(): void
    {
        $order = Order::factory()->create([
            'status' => 'pending',
        ]);

        $this->assertFalse($order->isAccessExpired());
    }

    /**
     * Test verifyCustomerPhone dengan nomor yang sama
     */
    public function test_verify_customer_phone_with_matching_number(): void
    {
        $order = Order::factory()->create([
            'customer_phone' => '+6281234567890',
        ]);

        $this->assertTrue($order->verifyCustomerPhone('081234567890'));
        $this->assertTrue($order->verifyCustomerPhone('6281234567890'));
        $this->assertTrue($order->verifyCustomerPhone('+6281234567890'));
    }

    /**
     * Test verifyCustomerPhone dengan nomor berbeda
     */
    public function test_verify_customer_phone_with_different_number(): void
    {
        $order = Order::factory()->create([
            'customer_phone' => '+6281234567890',
        ]);

        $this->assertFalse($order->verifyCustomerPhone('089876543210'));
    }

    /**
     * Test phone normalization menghapus spasi dan dash
     */
    public function test_phone_normalization_removes_spaces_and_dashes(): void
    {
        $order = Order::factory()->create([
            'customer_phone' => '+6281234567890',
        ]);

        $this->assertTrue($order->verifyCustomerPhone('081-234-567-890'));
        $this->assertTrue($order->verifyCustomerPhone('0812 3456 7890'));
    }

    /**
     * Test mass assignment protection untuk status fields
     */
    public function test_status_fields_are_guarded_from_mass_assignment(): void
    {
        $order = Order::create([
            'order_number' => 'TEST-001',
            'customer_name' => 'Test User',
            'customer_phone' => '+6281234567890',
            'customer_address' => 'Test Address',
            'subtotal' => 100000,
            'delivery_fee' => 10000,
            'total' => 110000,
            'status' => 'delivered', // Should be ignored by mass assignment
        ]);

        // Status seharusnya tidak ter-set via mass assignment
        $this->assertNotEquals('delivered', $order->status);
        $this->assertNull($order->status);
    }

    /**
     * Test WhatsApp message menggunakan ULID URL
     */
    public function test_whatsapp_message_includes_ulid_url(): void
    {
        $order = Order::factory()->create([
            'customer_name' => 'Test Customer',
            'status' => 'pending',
        ]);

        $order->load('items'); // Load items relation
        $message = $order->generateWhatsAppMessage();

        $this->assertStringContainsString($order->access_ulid, $message);
        $this->assertStringContainsString('orders/'.$order->access_ulid, $message);
    }
}
