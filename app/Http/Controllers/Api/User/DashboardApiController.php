<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Controllers\GamificationController;
use App\Models\Announcement;
use App\Models\Package;
use App\Models\PracticeSession;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardApiController extends BaseApiController
{
    /**
     * Get student dashboard data
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        // Ambil paket aktif sesuai bidang/level jika terisi
        $packagesQuery = Package::where('is_active', true);
        if ($user && $user->bidang) {
            $packagesQuery->where(function ($q) use ($user) {
                $q->where('bidang', $user->bidang)->orWhereNull('bidang');
            });
        }
        if ($user && $user->level) {
            $packagesQuery->where(function ($q) use ($user) {
                $q->where('level', $user->level)->orWhereNull('level');
            });
        }
        $packages = $packagesQuery->take(6)->get();

        $totalAttempts = 0;
        $bestScore = 0.0;
        $averageScore = 0.0;
        $gamification = null;

        if ($user) {
            // Statistik pengerjaan
            $sessions = PracticeSession::where('user_id', $user->id)
                ->where('status', 'completed')
                ->selectRaw('COUNT(*) as total, MAX(total_score) as best, AVG(total_score) as avg_score')
                ->first();

            $totalAttempts = (int) ($sessions->total ?? 0);
            $bestScore = (float) ($sessions->best ?? 0);
            $averageScore = round((float) ($sessions->avg_score ?? 0), 1);

            // Data gamifikasi
            $gamification = app(GamificationController::class)->getGamificationData($user->id);
        }

        // Pengumuman aktif terbaru
        $recentAnnouncements = Announcement::where('is_active', true)
            ->latest()
            ->take(3)
            ->get(['id', 'title', 'content', 'created_at']);

        return $this->sendResponse([
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'bidang' => $user->bidang,
                'level' => $user->level,
                'student_class' => $user->student_class,
                'school_name' => $user->school_name,
                'profile_photo_url' => $user->profile_photo_url,
            ] : null,
            'statistics' => [
                'total_attempts' => $totalAttempts,
                'best_score' => $bestScore,
                'average_score' => $averageScore,
            ],
            'gamification' => $gamification,
            // Ringkas saja. Mengirim model Package mentah di sini membocorkan
            // array `questions` yang memuat correct_answer + explanation, sehingga
            // siswa bisa menghafal kunci jawaban sebelum mengerjakan tugas.
            'recent_packages' => $packages->map(fn (Package $pkg) => [
                'id' => $pkg->id,
                'title' => $pkg->title,
                'description' => $pkg->description,
                'thumbnail' => $pkg->thumbnail,
                'bidang' => $pkg->bidang,
                'level' => $pkg->level,
                'schedule_status' => $pkg->schedule_status,
                'total_cards' => count($pkg->cards ?? []),
                'total_questions' => count($pkg->questions ?? []),
                'start_date' => $pkg->start_date,
                'end_date' => $pkg->end_date,
            ])->values(),
            'recent_announcements' => $recentAnnouncements,
        ], 'Data dasbor siswa berhasil dimuat.');
    }
}
