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

            $payload = [
                'email' => $user->email,
                'name' => $user->name,
                'username' => $user->username ?: explode('@', $user->email)[0],
                'kelas' => $user->student_class,
                'class_name' => $user->student_class,
                'bidang' => $user->bidang,
                'asal_sekolah' => $user->school_name,
                'school_name' => $user->school_name,
                'phone' => $user->phone,
                'address' => $user->address,
                'parent_name' => $user->parent_name,
                'parent_phone' => $user->parent_phone,
                'place_of_birth' => $user->place_of_birth,
                'date_of_birth' => $user->date_of_birth,
                'parent_profession' => $user->parent_profession,
                'parent_education' => $user->parent_education,
                'level' => (string) $user->level,
                'is_active' => (bool) $user->is_active,
                'source_satellite' => 'kpm_smart',
                'role' => 'peserta',
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
