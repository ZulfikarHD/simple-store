<?php

namespace Tests\Feature\Admin;

use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test untuk Admin StoreSettingController
 * mencakup view settings dan update settings
 */
class StoreSettingControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test bahwa guest tidak dapat akses halaman settings
     */
    public function test_guest_cannot_access_settings(): void
    {
        $response = $this->get(route('admin.settings.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test bahwa guest tidak dapat update settings
     */
    public function test_guest_cannot_update_settings(): void
    {
        $response = $this->patch(route('admin.settings.update'), [
            'store_name' => 'Test Store',
        ]);

        $response->assertRedirect(route('login'));
    }

    /**
     * Test authenticated user dapat melihat halaman settings
     */
    public function test_authenticated_user_can_view_settings(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->get(route('admin.settings.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Settings/Index')
            ->has('settings'));
    }

    /**
     * Test dapat update informasi toko
     */
    public function test_can_update_store_information(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->patch(route('admin.settings.update'), [
            'store_name' => 'Toko Baru',
            'store_address' => 'Jl. Test No. 123',
            'store_phone' => '021-1234567',
            'whatsapp_number' => '6281234567890',
            'operating_hours' => [
                'monday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'tuesday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'wednesday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'thursday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'friday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'saturday' => ['open' => '09:00', 'close' => '22:00', 'is_open' => true],
                'sunday' => ['open' => '09:00', 'close' => '20:00', 'is_open' => false],
            ],
            'delivery_areas' => ['Jakarta Selatan', 'Jakarta Pusat'],
            'delivery_fee' => 15000,
            'minimum_order' => 50000,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('Toko Baru', StoreSetting::get('store_name'));
        $this->assertEquals('Jl. Test No. 123', StoreSetting::get('store_address'));
        $this->assertEquals('021-1234567', StoreSetting::get('store_phone'));
    }

    /**
     * Test dapat update nomor WhatsApp
     */
    public function test_can_update_whatsapp_number(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->patch(route('admin.settings.update'), [
            'store_name' => 'Test Store',
            'whatsapp_number' => '6289876543210',
            'operating_hours' => [
                'monday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'tuesday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'wednesday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'thursday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'friday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'saturday' => ['open' => '09:00', 'close' => '22:00', 'is_open' => true],
                'sunday' => ['open' => '09:00', 'close' => '20:00', 'is_open' => true],
            ],
            'delivery_fee' => 10000,
            'minimum_order' => 0,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals('6289876543210', StoreSetting::get('whatsapp_number'));
    }

    /**
     * Test dapat update jam operasional
     */
    public function test_can_update_operating_hours(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $operatingHours = [
            'monday' => ['open' => '10:00', 'close' => '22:00', 'is_open' => true],
            'tuesday' => ['open' => '10:00', 'close' => '22:00', 'is_open' => true],
            'wednesday' => ['open' => '10:00', 'close' => '22:00', 'is_open' => true],
            'thursday' => ['open' => '10:00', 'close' => '22:00', 'is_open' => true],
            'friday' => ['open' => '10:00', 'close' => '23:00', 'is_open' => true],
            'saturday' => ['open' => '10:00', 'close' => '23:00', 'is_open' => true],
            'sunday' => ['open' => '00:00', 'close' => '00:00', 'is_open' => false],
        ];

        $response = $this->actingAs($user)->patch(route('admin.settings.update'), [
            'store_name' => 'Test Store',
            'whatsapp_number' => '6281234567890',
            'operating_hours' => $operatingHours,
            'delivery_fee' => 10000,
            'minimum_order' => 0,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $savedHours = StoreSetting::get('operating_hours');
        $this->assertEquals('10:00', $savedHours['monday']['open']);
        $this->assertEquals('22:00', $savedHours['monday']['close']);
        $this->assertFalse($savedHours['sunday']['is_open']);
    }

    /**
     * Test dapat update pengaturan delivery
     */
    public function test_can_update_delivery_settings(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->patch(route('admin.settings.update'), [
            'store_name' => 'Test Store',
            'whatsapp_number' => '6281234567890',
            'operating_hours' => [
                'monday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'tuesday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'wednesday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'thursday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'friday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'saturday' => ['open' => '09:00', 'close' => '22:00', 'is_open' => true],
                'sunday' => ['open' => '09:00', 'close' => '20:00', 'is_open' => true],
            ],
            'delivery_areas' => ['Bekasi', 'Depok', 'Tangerang'],
            'delivery_fee' => 20000,
            'minimum_order' => 100000,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals(20000, StoreSetting::get('delivery_fee'));
        $this->assertEquals(100000, StoreSetting::get('minimum_order'));
        $this->assertEquals(['Bekasi', 'Depok', 'Tangerang'], StoreSetting::get('delivery_areas'));
    }

    /**
     * Test validasi nama toko wajib diisi
     */
    public function test_store_name_is_required(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->patch(route('admin.settings.update'), [
            'store_name' => '',
            'whatsapp_number' => '6281234567890',
            'operating_hours' => [
                'monday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'tuesday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'wednesday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'thursday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'friday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'saturday' => ['open' => '09:00', 'close' => '22:00', 'is_open' => true],
                'sunday' => ['open' => '09:00', 'close' => '20:00', 'is_open' => true],
            ],
            'delivery_fee' => 10000,
            'minimum_order' => 0,
        ]);

        $response->assertSessionHasErrors('store_name');
    }

    /**
     * Test validasi nomor WhatsApp wajib diisi
     */
    public function test_whatsapp_number_is_required(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->patch(route('admin.settings.update'), [
            'store_name' => 'Test Store',
            'whatsapp_number' => '',
            'operating_hours' => [
                'monday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'tuesday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'wednesday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'thursday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'friday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'saturday' => ['open' => '09:00', 'close' => '22:00', 'is_open' => true],
                'sunday' => ['open' => '09:00', 'close' => '20:00', 'is_open' => true],
            ],
            'delivery_fee' => 10000,
            'minimum_order' => 0,
        ]);

        $response->assertSessionHasErrors('whatsapp_number');
    }

    /**
     * Test validasi biaya pengiriman tidak boleh negatif
     */
    public function test_delivery_fee_cannot_be_negative(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->patch(route('admin.settings.update'), [
            'store_name' => 'Test Store',
            'whatsapp_number' => '6281234567890',
            'operating_hours' => [
                'monday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'tuesday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'wednesday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'thursday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'friday' => ['open' => '08:00', 'close' => '21:00', 'is_open' => true],
                'saturday' => ['open' => '09:00', 'close' => '22:00', 'is_open' => true],
                'sunday' => ['open' => '09:00', 'close' => '20:00', 'is_open' => true],
            ],
            'delivery_fee' => -5000,
            'minimum_order' => 0,
        ]);

        $response->assertSessionHasErrors('delivery_fee');
    }

    /**
     * Test settings page menampilkan data yang tersimpan
     */
    public function test_settings_page_shows_saved_data(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        // Set initial settings
        StoreSetting::set('store_name', 'My Awesome Store', 'string', 'general');
        StoreSetting::set('whatsapp_number', '6281111222333', 'string', 'whatsapp');

        $response = $this->actingAs($user)->get(route('admin.settings.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Settings/Index')
            ->where('settings.store_name', 'My Awesome Store')
            ->where('settings.whatsapp_number', '6281111222333'));
    }
}
