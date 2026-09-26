<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MakeAdminCommand extends Command
{
    protected $signature = 'app:make-admin
                            {--email= : Email admin (wajib)}
                            {--name= : Nama admin (wajib)}
                            {--password= : Password (opsional, di-generate bila kosong)}';

    protected $description = 'Buat satu akun admin tanpa menjalankan seeder contoh.';

    public function handle(): int
    {
        if (! app()->environment('production')) {
            $this->warn('Command ini ditujukan untuk environment production.');
        }

        $email = (string) ($this->option('email') ?: '');
        $name = (string) ($this->option('name') ?: '');

        $validator = Validator::make(
            ['email' => $email, 'name' => $name],
            ['email' => 'required|email|max:255', 'name' => 'required|string|max:255']
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        if (User::where('email', $email)->exists()) {
            $this->error("User dengan email {$email} sudah ada.");

            return self::FAILURE;
        }

        $generated = false;
        $password = (string) ($this->option('password') ?: '');

        if ($password === '') {
            $password = Str::password(20);
            $generated = true;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'admin',
            'is_verified' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->info('Akun admin berhasil dibuat.');
        $this->line("  email    : {$email}");

        if ($generated) {
            $this->line("  password : {$password}");
            $this->newLine();
            $this->warn('Password di atas hanya ditampilkan SEKALI. Simpan sekarang, lalu segera ganti setelah login.');
        }

        return self::SUCCESS;
    }
}
