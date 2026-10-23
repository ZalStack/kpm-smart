<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Package;
use App\Models\PracticeSession;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SatelliteApiController extends Controller
{
    /**
     * Get practice statistics for satellite sync
     * No authentication required - uses API key middleware
     */
    public function practiceStatistics(Request $request): JsonResponse
    {
        $query = PracticeSession::with(['user:id,name,email,student_class,bidang,level,school_name', 'package:id,title,kelas,bidang,level,start_date,end_date,created_at'])
            ->where('status', 'completed');

        // Filter by package_id if provided
        if ($request->has('package_id')) {
            $query->where('package_id', $request->input('package_id'));
        }

        // Search by user name/email if provided
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $sessions = $query->orderBy('finished_at', 'desc')
            ->paginate($request->input('per_page', 200));

        $packages = Package::where('is_active', true)
            ->select('id', 'title', 'kelas', 'bidang', 'level', 'start_date', 'end_date', 'is_active', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Practice statistics retrieved successfully',
            'data' => $sessions,
            'packages' => $packages,
        ]);
    }

    /**
     * Get active packages list for weekly PR count detection
     */
    public function packages(): JsonResponse
    {
        $packages = Package::where('is_active', true)
            ->select('id', 'title', 'kelas', 'bidang', 'level', 'start_date', 'end_date', 'is_active', 'created_at')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $packages,
        ]);
    }

    /**
     * Get users list for satellite sync
     * No authentication required - uses API key middleware
     */
    public function users(Request $request): JsonResponse
    {
        $query = User::query();

        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->input('role'));
        }

        // Search by name/email
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')
            ->paginate($request->input('per_page', 200));

        return response()->json([
            'success' => true,
            'message' => 'Users retrieved successfully',
            'data' => $users,
        ]);
    }

    /**
     * Sync weekly report and teacher notes from satellite (kpm-student-smart)
     * Creates notification in kpm-smart for the matched student and admin.
     */
    public function syncWeeklyReport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'nullable|integer',
            'student_name' => 'nullable|string',
            'student_email' => 'nullable|string',
            'week_number' => 'required|integer|min:1|max:52',
            'year' => 'required|integer',
            'teacher_name' => 'nullable|string',
            'pr_completed' => 'nullable|boolean',
            'pr_score' => 'nullable',
            'notes' => 'nullable|string|max:1000',
        ]);

        $notes = trim($validated['notes'] ?? '');
        $weekNumber = $validated['week_number'];
        $year = $validated['year'];
        $teacherName = $validated['teacher_name'] ?? 'Guru Pembimbing';
        $studentEmail = trim($validated['student_email'] ?? '');
        $studentName = trim($validated['student_name'] ?? '');

        Log::info('Satellite weekly report sync received', [
            'student_name' => $studentName,
            'student_email' => $studentEmail,
            'week_number' => $weekNumber,
            'year' => $year,
            'notes_length' => strlen($notes),
        ]);

        $notificationCreated = false;
        $matchedUserId = null;

        // If teacher provided a note, create notification in KPM Smart
        if (! empty($notes)) {
            $user = null;

            // 1. Try matching student by email in KPM Smart
            if (! empty($studentEmail)) {
                $user = User::whereRaw('LOWER(TRIM(email)) = ?', [strtolower($studentEmail)])->first();
            }

            // 2. Try matching student by name in KPM Smart
            if (! $user && ! empty($studentName)) {
                $user = User::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($studentName)])->first();
            }

            $displayName = $user ? $user->name : ($studentName ?: 'Siswa');

            // Send notification to the student if found
            if ($user) {
                $matchedUserId = $user->id;
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'weekly_report_note',
                    'title' => "Catatan Guru - Pekan {$weekNumber} ({$year})",
                    'message' => "Catatan dari {$teacherName}: {$notes}",
                    'data' => [
                        'week_number' => $weekNumber,
                        'year' => $year,
                        'teacher_name' => $teacherName,
                        'notes' => $notes,
                        'pr_score' => $validated['pr_score'] ?? null,
                        'source' => 'kpm_student_smart',
                    ],
                    'read_at' => null,
                ]);
                $notificationCreated = true;
            }
        }

        return response()->json([
            'success' => true,
            'message' => $notificationCreated ? 'Catatan dan notifikasi berhasil disimpan ke KPM Smart' : 'Laporan mingguan disinkronkan',
            'data' => [
                'synced_at' => now()->toDateTimeString(),
                'student_id' => $validated['student_id'] ?? null,
                'student_name' => $studentName,
                'matched_user_id' => $matchedUserId,
                'notification_created' => $notificationCreated,
                'week_number' => $weekNumber,
                'year' => $year,
            ],
        ]);
    }

    /**
     * Menerima sinkronisasi user realtime dari Web Induk via HTTP POST
     */
    public function syncUser(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'name' => 'required|string',
            'role' => 'nullable|string',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
            'kelas' => 'nullable|string',
            'bidang' => 'nullable|string',
            'asal_sekolah' => 'nullable|string',
            'level' => 'nullable|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'parent_name' => 'nullable|string',
            'parent_phone' => 'nullable|string',
            'place_of_birth' => 'nullable|string',
            'date_of_birth' => 'nullable|string',
            'parent_profession' => 'nullable|string',
            'parent_education' => 'nullable|string',
            'father_name' => 'nullable|string',
            'father_phone' => 'nullable|string',
            'father_profession' => 'nullable|string',
            'father_education' => 'nullable|string',
            'mother_name' => 'nullable|string',
            'mother_phone' => 'nullable|string',
            'mother_profession' => 'nullable|string',
            'mother_education' => 'nullable|string',
            'foto_profil' => 'nullable|string',
            'is_active' => 'nullable',
        ]);

        $role = in_array($validated['role'] ?? '', ['super_admin', 'administrator', 'admin', 'guru']) ? 'admin' : 'user';
        $username = $validated['username'] ?? explode('@', $validated['email'])[0];
        $avatar = $validated['foto_profil'] ?? null;

        $existing = User::where('email', $validated['email'])->first();
        $data = [
            'name' => $validated['name'],
            'username' => $username,
            'student_name' => $validated['name'],
            'student_class' => $validated['kelas'] ?? ($existing?->student_class ?? 'VII-A'),
            'bidang' => $validated['bidang'] ?? ($existing?->bidang ?? 'Matematika & IPA'),
            'level' => $validated['level'] ?? ($existing?->level ?? '1'),
            'school_name' => $validated['asal_sekolah'] ?? ($existing?->school_name ?? 'KPM Academy'),
            'role' => $role,
            'is_active' => filter_var($validated['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'is_verified' => true,
        ];

        foreach (['phone', 'address', 'parent_name', 'parent_phone', 'place_of_birth', 'date_of_birth', 'parent_profession', 'parent_education', 'father_name', 'father_phone', 'father_education', 'father_profession', 'mother_name', 'mother_phone', 'mother_education', 'mother_profession'] as $f) {
            if (array_key_exists($f, $validated)) {
                $data[$f] = $validated[$f];
            }
        }
        if ($avatar) {
            $data['profile_photo'] = $avatar;
        }
        if (! empty($validated['password'])) {
            $data['password'] = $validated['password'];
        } elseif (! $existing) {
            $data['password'] = 'kpm12345';
        }

        $user = User::updateOrCreate(['email' => $validated['email']], $data);

        return response()->json([
            'success' => true,
            'message' => "User {$user->name} berhasil disinkronkan di KPM Smart",
            'user' => $user,
        ]);
    }

    /**
     * Menerima sinkronisasi batch banyak user sekaligus dari Web Induk
     */
    public function syncBatch(Request $request): JsonResponse
    {
        $users = $request->input('users', []);
        if (! is_array($users)) {
            return response()->json(['success' => false, 'message' => 'Format users tidak valid'], 422);
        }

        $synced = 0;
        DB::beginTransaction();
        try {
            foreach ($users as $item) {
                if (empty($item['email']) || empty($item['name'])) {
                    continue;
                }
                $role = in_array($item['role'] ?? '', ['super_admin', 'administrator', 'admin', 'guru']) ? 'admin' : 'user';
                $username = $item['username'] ?? explode('@', $item['email'])[0];
                $existing = DB::table('users')->where('email', $item['email'])->first();

                $data = [
                    'name' => $item['name'],
                    'username' => $username,
                    'student_name' => $item['name'],
                    'student_class' => $item['kelas'] ?? ($existing->student_class ?? 'VII-A'),
                    'bidang' => $item['bidang'] ?? ($existing->bidang ?? 'Matematika & IPA'),
                    'level' => $item['level'] ?? ($existing->level ?? '1'),
                    'school_name' => $item['asal_sekolah'] ?? ($existing->school_name ?? 'KPM Academy'),
                    'role' => $role,
                    'is_active' => filter_var($item['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
                    'is_verified' => 1,
                    'updated_at' => now(),
                ];

                foreach (['phone', 'address', 'parent_name', 'parent_phone', 'place_of_birth', 'date_of_birth', 'parent_profession', 'parent_education', 'father_name', 'father_phone', 'father_education', 'father_profession', 'mother_name', 'mother_phone', 'mother_education', 'mother_profession'] as $f) {
                    if (array_key_exists($f, $item)) {
                        $data[$f] = $item[$f];
                    }
                }
                if (! empty($item['foto_profil'])) {
                    $data['profile_photo'] = $item['foto_profil'];
                }
                if (! empty($item['password'])) {
                    $pw = $item['password'];
                    $data['password'] = (str_starts_with($pw, '$2y$') || str_starts_with($pw, '$2a$')) ? $pw : bcrypt($pw);
                } elseif (! $existing) {
                    $data['password'] = bcrypt('kpm12345');
                }

                if ($existing) {
                    DB::table('users')->where('id', $existing->id)->update($data);
                } else {
                    $data['email'] = $item['email'];
                    $data['created_at'] = now();
                    DB::table('users')->insert($data);
                }
                $synced++;
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }

        return response()->json([
            'success' => true,
            'message' => "{$synced} user berhasil disinkronkan di KPM Smart",
            'synced_count' => $synced,
        ]);
    }

    /**
     * Menerima penghapusan user realtime dari Web Induk via HTTP POST
     */
    public function deleteUser(Request $request): JsonResponse
    {
        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        // JANGAN izinkan penghapusan akun admin via endpoint delete siswa satelit
        if ($user && $user->role !== 'admin') {
            $user->practiceSessions()->delete();
            $user->pushSubscriptions()->delete();
            $user->loginLogs()->delete();
            $user->delete();
        }

        return response()->json([
            'success' => true,
            'message' => "User {$email} berhasil diproses di KPM Smart",
        ]);
    }

    /**
     * Membersihkan siswa orphan di KPM Smart yang tidak ada di Web Induk
     */
    public function cleanOrphans(Request $request): JsonResponse
    {
        $activeEmails = $request->input('active_emails', []);
        $deletedCount = 0;

        if ($request->boolean('wipe_all') || (! empty($activeEmails) && is_array($activeEmails))) {
            $orphanUsers = $request->boolean('wipe_all')
                ? User::where('role', 'user')->get()
                : User::where('role', 'user')->whereNotIn('email', $activeEmails)->get();
            $deletedCount = $orphanUsers->count();
            foreach ($orphanUsers as $user) {
                $user->practiceSessions()->delete();
                $user->pushSubscriptions()->delete();
                $user->loginLogs()->delete();
                $user->delete();
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount} orphan user berhasil dibersihkan",
            'deleted_count' => $deletedCount,
        ]);
    }
}
