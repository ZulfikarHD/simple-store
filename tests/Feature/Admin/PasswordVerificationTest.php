<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test untuk Admin PasswordVerificationController
 * memastikan verifikasi password berfungsi dengan benar
 * untuk mengamankan aksi sensitif di admin panel
 *
 * @author Zulfikar Hidayatullah
 */
class PasswordVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test bahwa guest tidak dapat akses endpoint verifikasi password
     */
    public function test_guest_cannot_verify_password(): void
    {
        $response = $this->postJson(route('admin.api.verifyPassword'), [
            'password' => 'password',
        ]);

        $response->assertUnauthorized();
    }

    /**
     * Test bahwa customer tidak dapat akses endpoint verifikasi password
     * dengan JSON request akan return 403 Forbidden
     */
    public function test_customer_cannot_verify_password(): void
    {
        $customer = User::factory()->customer()->create([
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->actingAs($customer)->postJson(route('admin.api.verifyPassword'), [
            'password' => 'secret123',
        ]);

        $response->assertForbidden();
    }

    /**
     * Test admin dapat verifikasi password dengan password yang benar
     */
    public function test_admin_can_verify_correct_password(): void
    {
        $admin = User::factory()->admin()->create([
            'password' => bcrypt('admin-password'),
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.api.verifyPassword'), [
            'password' => 'admin-password',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Password berhasil diverifikasi.',
        ]);
    }

    /**
     * Test admin tidak dapat verifikasi dengan password yang salah
     */
    public function test_admin_cannot_verify_wrong_password(): void
    {
        $admin = User::factory()->admin()->create([
            'password' => bcrypt('correct-password'),
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.api.verifyPassword'), [
            'password' => 'wrong-password',
        ]);

        $response->assertUnprocessable();
        $response->assertJson([
            'success' => false,
            'message' => 'Password yang Anda masukkan salah.',
        ]);
    }

    /**
     * Test validasi password wajib diisi
     */
    public function test_password_is_required(): void
    {
        $admin = User::factory()->admin()->create([
            'password' => bcrypt('admin-password'),
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.api.verifyPassword'), [
            'password' => '',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['password']);
    }

    /**
     * Test bahwa password field tidak boleh kosong
     */
    public function test_password_cannot_be_null(): void
    {
        $admin = User::factory()->admin()->create([
            'password' => bcrypt('admin-password'),
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.api.verifyPassword'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['password']);
    }
}
