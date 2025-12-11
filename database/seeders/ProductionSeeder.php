<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

/**
 * ProductionSeeder untuk setup awal aplikasi di production
 * Seeder ini akan membuat admin user pertama dengan credentials yang aman
 * serta store settings default yang dapat diubah melalui admin panel
 *
 * Penggunaan:
 * php artisan db:seed --class=ProductionSeeder
 *
 * @author Zulfikar Hidayatullah
 */
class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Seeder ini akan:
     * 1. Memeriksa apakah sudah ada admin user
     * 2. Membuat admin user baru dengan input interaktif
     * 3. Mengisi store settings default
     */
    public function run(): void
    {
        $this->command->info('🚀 Simple Store - Production Setup');
        $this->command->newLine();

        // Cek apakah sudah ada admin
        if (User::where('role', 'admin')->exists()) {
            $this->command->warn('⚠️  Admin user sudah ada di database.');

            if (! confirm('Apakah Anda ingin membuat admin baru?', default: false)) {
                $this->command->info('Setup dibatalkan.');

                return;
            }
        }

        // Buat admin user dengan input interaktif
        $this->createAdminUser();

        // Seed store settings
        $this->command->newLine();
        if (confirm('Apakah Anda ingin mengisi store settings default?', default: true)) {
            $this->call(StoreSettingSeeder::class);
            $this->command->info('✅ Store settings berhasil dibuat.');
        }

        $this->command->newLine();
        $this->command->info('✨ Production setup selesai!');
        $this->command->info('📝 Anda dapat login ke /login menggunakan credentials yang telah dibuat.');
    }

    /**
     * Membuat admin user dengan input interaktif dan validasi
     * menggunakan Laravel Prompts untuk UX yang lebih baik
     */
    private function createAdminUser(): void
    {
        $this->command->info('📋 Buat Admin User');
        $this->command->newLine();

        // Input nama dengan validasi
        $name = text(
            label: 'Nama Lengkap Admin',
            placeholder: 'contoh: Zulfikar Hidayatullah',
            required: true,
            validate: fn ($value) => $this->validateName($value)
        );

        // Input email dengan validasi
        $email = text(
            label: 'Email Admin',
            placeholder: 'contoh: admin@example.com',
            required: true,
            validate: fn ($value) => $this->validateEmail($value)
        );

        // Input password dengan validasi
        $password = password(
            label: 'Password',
            placeholder: 'Minimal 8 karakter',
            required: true,
            validate: fn ($value) => $this->validatePassword($value)
        );

        // Konfirmasi password
        $passwordConfirmation = password(
            label: 'Konfirmasi Password',
            required: true,
            validate: fn ($value) => $value === $password
                ? null
                : 'Password tidak sama.'
        );

        // Buat admin user
        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);

            $this->command->newLine();
            $this->command->info('✅ Admin user berhasil dibuat!');
            $this->command->table(
                ['Field', 'Value'],
                [
                    ['Nama', $user->name],
                    ['Email', $user->email],
                    ['Role', $user->role],
                    ['Created At', $user->created_at->format('Y-m-d H:i:s')],
                ]
            );
        } catch (\Exception $e) {
            $this->command->error('❌ Gagal membuat admin user: '.$e->getMessage());
            exit(1);
        }
    }

    /**
     * Validasi nama dengan aturan Laravel
     */
    private function validateName(string $value): ?string
    {
        $validator = Validator::make(
            ['name' => $value],
            ['name' => ['required', 'string', 'max:255', 'min:3']]
        );

        if ($validator->fails()) {
            return $validator->errors()->first('name');
        }

        return null;
    }

    /**
     * Validasi email dengan aturan Laravel
     * termasuk pengecekan unique di database
     */
    private function validateEmail(string $value): ?string
    {
        $validator = Validator::make(
            ['email' => $value],
            ['email' => ['required', 'string', 'email', 'max:255', 'unique:users,email']]
        );

        if ($validator->fails()) {
            return $validator->errors()->first('email');
        }

        return null;
    }

    /**
     * Validasi password dengan aturan Laravel
     * minimal 8 karakter
     */
    private function validatePassword(string $value): ?string
    {
        $validator = Validator::make(
            ['password' => $value],
            ['password' => ['required', 'string', 'min:8']]
        );

        if ($validator->fails()) {
            return $validator->errors()->first('password');
        }

        return null;
    }
}
