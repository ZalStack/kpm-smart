<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ParentSyncService
{
    public static bool $isSyncing = false;

    /**
     * Kirim data perubahan / penambahan user ke Web Induk
     */
    public static function syncUserToParent(User $user, ?string $plainPassword = null, ?string $uploadedPhotoPath = null): bool
    {
        if (self::$isSyncing) {
            return true;
        }

        try {
            $parentUrl = rtrim(config('app.parent_app_url', env('PARENT_APP_URL', 'https://kelas-khusus.kpmacademic.io')), '/');
            $apiKey = config('services.parent_api_key', env('PARENT_APP_API_KEY', env('API_SECRET_KEY', 'kpm-satellite-secret-key-2026')));

            $clean = function (?string $val): ?string {
                $t = trim((string) $val);

                return $t === '' ? null : $t;
            };

            $rawDob = trim((string) $user->date_of_birth);
            $dob = (! empty($rawDob) && strtotime($rawDob)) ? date('Y-m-d', strtotime($rawDob)) : null;

            $payload = [
                'email' => $user->email,
                'name' => $user->name,
                'username' => $user->username ?: explode('@', $user->email)[0],
                'kelas' => $user->student_class,
                'class_name' => $user->student_class,
                'bidang' => $user->bidang,
                'asal_sekolah' => $user->school_name,
                'school_name' => $user->school_name,
                'phone' => $clean($user->phone),
                'address' => $clean($user->address),
                'parent_name' => $clean($user->parent_name),
                'parent_phone' => $clean($user->parent_phone),
                'place_of_birth' => $clean($user->place_of_birth),
                'date_of_birth' => $dob,
                'parent_profession' => $clean($user->parent_profession),
                'parent_education' => $clean($user->parent_education),
                'level' => (string) $user->level,
                'is_active' => (bool) $user->is_active,
                'source_satellite' => 'kpm_smart',
                'role' => $user->role === 'admin' ? 'administrator' : 'peserta',
            ];

            if ($plainPassword) {
                $payload['password'] = $plainPassword;
            }

            if ($uploadedPhotoPath || $user->profile_photo) {
                $payload['profile_photo'] = $uploadedPhotoPath ?: $user->profile_photo;
                $payload['avatar'] = $uploadedPhotoPath ?: $user->profile_photo;
            }

            $response = Http::withHeaders([
                'X-API-Key' => $apiKey,
                'Accept' => 'application/json',
            ])->timeout(10)->post("{$parentUrl}/api/v1/profile/update", $payload);

            Log::info("ParentSyncService: Syncing {$user->email} to Web Induk", [
                'parentUrl' => $parentUrl,
                'status' => $response->status(),
                'success' => $response->successful(),
                'response' => $response->json(),
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('Gagal sync user kpm-smart ke Web Induk: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Kirim instruksi hapus user ke Web Induk
     */
    public static function deleteUserFromParent(User $user): bool
    {
        if (self::$isSyncing) {
            return true;
        }

        try {
            $parentUrl = rtrim(config('app.parent_app_url', env('PARENT_APP_URL', 'https://kelas-khusus.kpmacademic.io')), '/');
            $apiKey = config('services.parent_api_key', env('PARENT_APP_API_KEY', env('API_SECRET_KEY', 'kpm-satellite-secret-key-2026')));

            $response = Http::withHeaders([
                'X-API-Key' => $apiKey,
                'Accept' => 'application/json',
            ])->timeout(5)->post("{$parentUrl}/api/v1/profile/delete", [
                'email' => $user->email,
                'source_satellite' => 'kpm_smart',
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('Gagal delete user kpm-smart ke Web Induk: '.$e->getMessage());

            return false;
        }
    }
}
