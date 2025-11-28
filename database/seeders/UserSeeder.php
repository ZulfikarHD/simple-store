<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder khusus untuk membuat user data
 * termasuk admin user dan customer untuk testing
 *
 * @author Zulfikar Hidayatullah
 */
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedUsers();
    }

    /**
     * Membuat user admin dan beberapa customer untuk testing
     */
    private function seedUsers(): void
    {
        // Admin user with explicit password
        User::create([
            'name' => 'Admin Store',
            'email' => 'admin@test.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Customer demo user with explicit password
        User::create([
            'name' => 'Customer Demo',
            'email' => 'customer@test.com',
            'password' => Hash::make('customer123'),
            'role' => 'customer',
            'phone' => '081234567890',
            'address' => 'Jl. Contoh No. 123, Jakarta',
            'email_verified_at' => now(),
        ]);

        // Additional customers using factory (these will use the dynamic password pattern)
        User::factory()->customer()->count(5)->create();
    }
}
