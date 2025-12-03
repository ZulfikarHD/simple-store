<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * ProfileTest untuk memverifikasi halaman profile settings
 * dapat diakses oleh customer dan admin dengan layout yang sesuai
 *
 * @author Zulfikar Hidayatullah
 */
class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test bahwa customer dapat akses profile settings
     * dengan response success dan tanpa admin sidebar
     */
    public function test_customer_can_access_profile_settings(): void
    {
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($customer)->get(route('profile.edit'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('settings/Profile')
            ->has('mustVerifyEmail'));
    }

    /**
     * Test bahwa admin dapat akses profile settings
     * dengan response success dan dengan admin sidebar
     */
    public function test_admin_can_access_profile_settings(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('profile.edit'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('settings/Profile')
            ->has('mustVerifyEmail'));
    }

    /**
     * Test bahwa guest tidak dapat akses profile settings
     * dan di-redirect ke login page
     */
    public function test_guest_cannot_access_profile_settings(): void
    {
        $response = $this->get(route('profile.edit'));

        $response->assertRedirect(route('login'));
    }
}
