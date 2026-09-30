<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\PracticeSession;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SatelliteApiController extends Controller
{
    /**
     * Get practice statistics for satellite sync
     * No authentication required - uses API key middleware
     */
    public function practiceStatistics(Request $request): JsonResponse
    {
        $query = PracticeSession::with(['user:id,name,email,student_class,bidang,level,school_name', 'package:id,title'])
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

        return response()->json([
            'success' => true,
            'message' => 'Practice statistics retrieved successfully',
            'data' => $sessions,
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
        if (!empty($notes)) {
            $user = null;

            // 1. Try matching student by email in KPM Smart
            if (!empty($studentEmail)) {
                $user = User::whereRaw('LOWER(TRIM(email)) = ?', [strtolower($studentEmail)])->first();
            }

            // 2. Try matching student by name in KPM Smart
            if (!$user && !empty($studentName)) {
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

            // Also create notification for Admin PKA Litbang (user_id = 1)
            $adminUser = User::where('role', 'admin')->first() ?? User::find(1);
            if ($adminUser && (!$user || $adminUser->id !== $user->id)) {
                Notification::create([
                    'user_id' => $adminUser->id,
                    'type' => 'weekly_report_note',
                    'title' => "Catatan Guru untuk {$displayName} (Pekan {$weekNumber})",
                    'message' => "Catatan evaluasi: {$notes}",
                    'data' => [
                        'week_number' => $weekNumber,
                        'year' => $year,
                        'teacher_name' => $teacherName,
                        'student_name' => $displayName,
                        'student_email' => $studentEmail,
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
}
