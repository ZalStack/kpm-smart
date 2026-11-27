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

            $photoRel = $uploadedPhotoPath ?: $user->profile_photo;
            $photoFile = null;
            if ($photoRel && ! str_starts_with($photoRel, 'http://') && ! str_starts_with($photoRel, 'https://')) {
                $possiblePaths = [
                    storage_path('app/public/'.ltrim($photoRel, '/')),
                    public_path('storage/'.ltrim($photoRel, '/')),
                    public_path(ltrim($photoRel, '/')),
                ];
                foreach ($possiblePaths as $p) {
                    if (file_exists($p) && is_file($p)) {
                        $photoFile = $p;
                        break;
                    }
                }
            }

            $http = Http::withHeaders([
                'X-API-Key' => $apiKey,
                'Accept' => 'application/json',
            ])->timeout(20);

            if ($photoFile) {
                $payload['profile_photo'] = $photoRel;
                $payload['avatar'] = $photoRel;
                $http = $http->attach('profile_photo', file_get_contents($photoFile), basename($photoFile));
            } elseif ($photoRel) {
                if (str_starts_with($photoRel, 'http://') || str_starts_with($photoRel, 'https://')) {
                    $payload['profile_photo'] = $photoRel;
                    $payload['avatar'] = $photoRel;
                } else {
                    $fullUrl = url('storage/'.ltrim($photoRel, '/'));
                    $payload['profile_photo'] = $fullUrl;
                    $payload['avatar'] = $fullUrl;
                }
            }

            $response = $http->post("{$parentUrl}/api/v1/profile/update", $payload);

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

    /**
     * Memeriksa status akses pengerjaan PR siswa berdasarkan perizinan & keropak di Web Induk
     */
    public static function checkStudentPrAccess(User $user): array
    {
        if ($user->role !== 'user') {
            return ['allowed' => true, 'message' => 'Admin memiliki akses penuh.'];
        }

        // Cek direct local database kpm-student-smart jika file SQLite ada (Environment Local)
        $indukDb = base_path('../kpm-student-smart/database/database.sqlite');
        if (file_exists($indukDb)) {
            try {
                $pdo = new \PDO("sqlite:{$indukDb}", null, null, [
                    \PDO::ATTR_TIMEOUT => 2,
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                ]);

                // 1. Cek setting kropak_perizinan_enabled
                $settingStmt = $pdo->prepare("SELECT value FROM settings WHERE key = 'kropak_perizinan_enabled' LIMIT 1");
                $settingStmt->execute();
                $settingVal = $settingStmt->fetchColumn();
                if ($settingVal !== false && $settingVal === '0') {
                    return ['allowed' => true, 'message' => 'Fitur penguncian keropak perizinan nonaktif di Super Admin.'];
                }

                // 2. Cari student berdasarkan email / username
                $userStmt = $pdo->prepare('SELECT id FROM users WHERE email = :email OR username = :username LIMIT 1');
                $userStmt->execute([
                    ':email' => $user->email,
                    ':username' => $user->username ?: explode('@', $user->email)[0],
                ]);
                $indukUserId = $userStmt->fetchColumn();

                if (! $indukUserId) {
                    return ['allowed' => true, 'message' => 'Siswa tidak ditemukan di Web Induk.'];
                }

                $studentStmt = $pdo->prepare('SELECT id FROM students WHERE user_id = :user_id LIMIT 1');
                $studentStmt->execute([':user_id' => $indukUserId]);
                $indukStudentId = $studentStmt->fetchColumn();

                if (! $indukStudentId) {
                    return ['allowed' => true, 'message' => 'Data student tidak ditemukan di Web Induk.'];
                }

                $now = now();
                $startOfWeek = $now->copy()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString();
                $endOfWeek = $now->copy()->endOfWeek(\Carbon\Carbon::SUNDAY)->toDateString();

                // 3. Cek kehadiran pekan berjalan
                $attStmt = $pdo->prepare("
                    SELECT id, status, infaq_verified, date FROM attendances 
                    WHERE student_id = :student_id 
                    AND date >= :start_date AND date <= :end_date 
                    ORDER BY date DESC LIMIT 1
                ");
                $attStmt->execute([
                    ':student_id' => $indukStudentId,
                    ':start_date' => $startOfWeek,
                    ':end_date' => $endOfWeek,
                ]);
                $att = $attStmt->fetch(\PDO::FETCH_ASSOC);

                // Cek kropak_verifications approved
                $kvStmt = $pdo->prepare("
                    SELECT id FROM kropak_verifications 
                    WHERE student_id = :student_id 
                    AND status = 'approved'
                    AND created_at >= :start_time AND created_at <= :end_time
                    LIMIT 1
                ");
                $kvStmt->execute([
                    ':student_id' => $indukStudentId,
                    ':start_time' => $startOfWeek.' 00:00:00',
                    ':end_time' => $endOfWeek.' 23:59:59',
                ]);
                $hasApprovedKropak = (bool) $kvStmt->fetchColumn();

                // Cek permission_requests approved
                $prStmt = $pdo->prepare("
                    SELECT id FROM permission_requests 
                    WHERE student_id = :student_id 
                    AND status = 'approved'
                    AND ((start_date >= :sd1 AND start_date <= :ed1) OR (end_date >= :sd2 AND end_date <= :ed2) OR (start_date <= :sd3 AND end_date >= :ed3))
                    LIMIT 1
                ");
                $prStmt->execute([
                    ':student_id' => $indukStudentId,
                    ':sd1' => $startOfWeek, ':ed1' => $endOfWeek,
                    ':sd2' => $startOfWeek, ':ed2' => $endOfWeek,
                    ':sd3' => $startOfWeek, ':ed3' => $endOfWeek,
                ]);
                $hasApprovedPermission = (bool) $prStmt->fetchColumn();

                if ($att && $att['status'] === 'hadir') {
                    if (! empty($att['infaq_verified']) || $hasApprovedKropak) {
                        return ['allowed' => true, 'message' => 'Kehadiran dan infaq keropak tercentang. Akses PR terbuka.'];
                    }
                    return [
                        'allowed' => false,
                        'reason' => 'unverified_kropak_attendance',
                        'message' => 'Akses PR pekan ini terkunci karena infaq keropak belum tercentang/diselesaikan. Silakan bayar keropak di kelas atau lakukan transfer di SI ADIK-KK untuk membuka akses.',
                    ];
                }

                if ($hasApprovedPermission || $hasApprovedKropak) {
                    return ['allowed' => true, 'message' => 'Perizinan / transfer keropak telah disetujui admin. Akses PR terbuka.'];
                }

                // Cek permission_requests pending
                $prPendingStmt = $pdo->prepare("
                    SELECT id FROM permission_requests 
                    WHERE student_id = :student_id 
                    AND status = 'pending'
                    AND ((start_date >= :sd1 AND start_date <= :ed1) OR (end_date >= :sd2 AND end_date <= :ed2))
                    LIMIT 1
                ");
                $prPendingStmt->execute([
                    ':student_id' => $indukStudentId,
                    ':sd1' => $startOfWeek, ':ed1' => $endOfWeek,
                    ':sd2' => $startOfWeek, ':ed2' => $endOfWeek,
                ]);
                if ($prPendingStmt->fetchColumn()) {
                    return [
                        'allowed' => false,
                        'reason' => 'pending_permission_verification',
                        'message' => 'Pengajuan perizinan kehadiran Anda masih menunggu verifikasi dari guru/admin. Akses PR akan terbuka setelah diverifikasi.',
                    ];
                }

                return [
                    'allowed' => false,
                    'reason' => 'locked_default',
                    'message' => 'Akses PR pekan ini terkunci. Akses akan terbuka otomatis saat presensi & infaq keropak tercentang oleh guru di kelas, atau perizinan kehadiran telah disetujui.',
                ];
            } catch (\Throwable $e) {
                Log::warning('ParentSyncService direct check exception: '.$e->getMessage());
            }
        }

        // Cek via HTTP REST API ke Web Induk (Production cross-server)
        try {
            $parentUrl = rtrim(config('app.parent_app_url', env('PARENT_APP_URL', 'https://kelas-khusus.kpmacademic.io')), '/');
            $apiKey = config('services.parent_api_key', env('PARENT_APP_API_KEY', env('API_SECRET_KEY', 'kpm-satellite-secret-key-2026')));

            $response = Http::withHeaders([
                'X-API-Key' => $apiKey,
                'Accept' => 'application/json',
            ])->timeout(4)->get("{$parentUrl}/api/v1/pr-access/check", [
                'email' => $user->email,
                'username' => $user->username,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['allowed']) && ! $data['allowed']) {
                    return [
                        'allowed' => false,
                        'reason' => $data['reason'] ?? 'unverified_kropak_perizinan',
                        'message' => $data['message'] ?? 'Akses pengerjaan PR pekan ini terkunci karena belum menyelesaikan transfer keropak perizinan.',
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('ParentSyncService HTTP check exception: '.$e->getMessage());
        }

        return ['allowed' => true, 'message' => 'Akses PR diizinkan.'];
    }
}
