<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SyncFromParentCommand extends Command
{
    protected $signature = 'kpm:sync-parent';

    protected $description = 'Sinkronisasi seluruh data akun siswa dan guru dari Web Induk (kpm-student-smart)';

    public function handle(): int
    {
        $this->info('================================================================');
        $this->info('  SINKRONISASI DATA SISWA DARI WEB INDUK (KPM STUDENT SMART)');
        $this->info('================================================================');

        $parentUrl = rtrim(config('app.parent_app_url', env('PARENT_APP_URL', 'https://kelas-khusus.kpmacademic.io')), '/');
        $apiKey = config('services.parent_api_key', env('PARENT_APP_API_KEY', env('API_SECRET_KEY', 'kpm-satellite-secret-key-2026')));

        $this->line("Menghubungi Web Induk: {$parentUrl}/api/v1/users ...");

        try {
            $response = Http::withHeaders([
                'X-API-Key' => $apiKey,
                'Accept' => 'application/json',
            ])->timeout(30)->get("{$parentUrl}/api/v1/users");

            if (! $response->successful()) {
                $this->error("Gagal menghubungi Web Induk: HTTP {$response->status()}");
                return Command::FAILURE;
            }

            $users = $response->json('data') ?? $response->json();
            if (! is_array($users)) {
                $this->error('Format respons dari Web Induk tidak valid.');
                return Command::FAILURE;
            }

            $this->info('Ditemukan ' . count($users) . ' akun dari Web Induk. Memproses sinkronisasi...');

            $synced = 0;
            $activeEmails = [];
            foreach ($users as $pu) {
                if (empty($pu['email'])) {
                    continue;
                }

                $activeEmails[] = $pu['email'];
                $isGuru = ($pu['role'] ?? '') === 'guru';
                $role = in_array($pu['role'] ?? '', ['super_admin', 'administrator', 'admin']) ? 'admin' : ($isGuru ? 'guru' : 'user');
                $username = $pu['nis'] ?? ($pu['username'] ?? explode('@', $pu['email'])[0]);
                $avatar = ! empty($pu['foto_profil']) ? $pu['foto_profil'] : "{$parentUrl}/api/foto/".urlencode($pu['email']);

                $bSync = $pu['bidang'] ?? 'Matematika & IPA';
                $cSync = $bSync.' '.($pu['level'] ?? '');
                if (preg_match('/(mtk|matematika|math)/i', $cSync) && preg_match('/(ipa|sains|science)/i', $cSync)) {
                    $bSync = 'Matematika & IPA';
                }

                $rawClass = $pu['kelas'] ?? ($pu['class_name'] ?? 'VII-A');
                $cleanClass = trim(str_replace(["'", '"'], '', (string) $rawClass));

                $existingUser = User::where('email', $pu['email'])->first();
                $userData = [
                    'name' => $pu['name'] ?? 'User',
                    'username' => $username,
                    'student_name' => $pu['name'] ?? 'User',
                    'student_class' => $cleanClass,
                    'bidang' => $bSync,
                    'level' => $pu['level'] ?? 'Berbakat A',
                    'school_name' => $pu['asal_sekolah'] ?? 'KPM Academy',
                    'phone' => $pu['phone'] ?? null,
                    'address' => $pu['address'] ?? null,
                    'place_of_birth' => $pu['place_of_birth'] ?? null,
                    'date_of_birth' => $pu['date_of_birth'] ?? null,
                    'parent_name' => $pu['parent_name'] ?? null,
                    'parent_phone' => $pu['parent_phone'] ?? null,
                    'parent_profession' => $pu['parent_profession'] ?? null,
                    'parent_education' => $pu['parent_education'] ?? null,
                    'role' => $role,
                    'is_active' => true,
                    'is_verified' => true,
                    'profile_photo' => $avatar,
                ];

                if (! empty($pu['password'])) {
                    $userData['password'] = $pu['password'];
                } elseif (! $existingUser) {
                    $userData['password'] = 'kpm12345';
                }

                User::updateOrCreate(
                    ['email' => $pu['email']],
                    $userData
                );
                $synced++;
            }

            $this->info("✓ {$synced} user berhasil disinkronkan ke KPM Smart.");
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Terjadi kesalahan sinkronisasi: ' . $e->getMessage());
            Log::error('SyncFromParentCommand error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
