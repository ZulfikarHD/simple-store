<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
        // Admin user
        User::factory()->admin()->create([
            'name' => 'Admin Store',
            'email' => 'admin@test.com',
        ]);

        // Customer users
        User::factory()->customer()->create([
            'name' => 'Customer Demo',
            'email' => 'customer@test.com',
            'phone' => '081234567890',
            'address' => 'Jl. Contoh No. 123, Jakarta',
        ]);

        // Additional customers
        User::factory()->customer()->count(5)->create();
    }
}
