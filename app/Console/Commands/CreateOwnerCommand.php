<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Command untuk membuat owner/admin pertama kali
 * digunakan saat initial setup production
 */
class CreateOwnerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'store:create-owner
                            {--name= : Nama lengkap owner}
                            {--email= : Email owner}
                            {--password= : Password owner}
                            {--phone= : Nomor telepon owner (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat admin/owner account untuk akses pertama kali ke aplikasi';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🏪 Simple Store - Create Owner Account');
        $this->newLine();

        // Get input dengan prompts jika tidak ada options
        $name = $this->option('name') ?? $this->ask('Nama lengkap owner');
        $email = $this->option('email') ?? $this->ask('Email owner');
        $password = $this->option('password') ?? $this->secret('Password owner (min 8 karakter)');
        $phone = $this->option('phone') ?? $this->ask('Nomor telepon (optional, enter untuk skip)', '');

        // Validation
        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return Command::FAILURE;
        }

        // Check jika sudah ada admin
        if (User::where('role', 'admin')->exists()) {
            if (! $this->confirm('⚠️  Sudah ada admin di sistem. Tetap buat admin baru?', false)) {
                $this->info('Operasi dibatalkan.');

                return Command::SUCCESS;
            }
        }

        // Create owner
        try {
            $owner = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'phone' => $phone ?: null,
                'email_verified_at' => now(), // Auto-verified untuk owner
            ]);

            // Set role explicitly (SECURE)
            $owner->role = 'admin';
            $owner->save();

            $this->newLine();
            $this->info('✅ Owner account berhasil dibuat!');
            $this->newLine();
            $this->table(
                ['Field', 'Value'],
                [
                    ['ID', $owner->id],
                    ['Nama', $owner->name],
                    ['Email', $owner->email],
                    ['Role', $owner->role],
                    ['Phone', $owner->phone ?? '-'],
                ]
            );
            $this->newLine();
            $this->info('🔐 Sekarang Anda bisa login dengan email dan password yang telah dibuat.');
            $this->info('📱 Setelah login, Anda bisa menambahkan staff lain via Admin Panel.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
