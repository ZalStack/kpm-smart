<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\PracticeSession;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function userDashboard()
    {
        if (Auth::check() && in_array(Auth::user()->role, ['admin', 'super_admin', 'administrator', 'guru'], true)) {
            return redirect()->route('admin.dashboard');
        }

        $user = Auth::user();
        $query = Package::where('is_active', true);

        // Terapkan filter cerdas sesuai data Kelas, Bidang, dan Level Berbakat siswa
        \App\Support\StudentPackageFilter::apply($query, $user);

        $packages = $query->latest()->take(6)->get();

        // Rekap pengerjaan PR milik siswa yang sedang login
        $sessions = PracticeSession::where('status', 'completed')
            ->when($user, fn ($q) => $q->where('user_id', $user->id))
            ->selectRaw('COUNT(*) as total, MAX(total_score) as best, AVG(total_score) as avg_score')
            ->first();

        $totalAttempts = $sessions->total ?? 0;
        $bestScore = (float) ($sessions->best ?? 0);
        $averageScore = (float) ($sessions->avg_score ?? 0);

        // Catatan guru (notifikasi weekly_report_note dari sinkronisasi satelit)
        $teacherNotes = \App\Models\Notification::where('user_id', optional($user)->id)
            ->where('type', 'weekly_report_note')
            ->latest()
            ->take(6)
            ->get()
            ->map(fn ($note) => [
                'id' => $note->id,
                'title' => $note->title,
                'message' => $note->message,
                'teacher_name' => $note->data['teacher_name'] ?? 'Guru Pembimbing',
                'week_number' => $note->data['week_number'] ?? null,
                'year' => $note->data['year'] ?? null,
                'notes' => $note->data['notes'] ?? null,
                'pr_score' => $note->data['pr_score'] ?? null,
                'created_at' => $note->created_at?->format('d M Y H:i'),
            ])
            ->values();

        return Inertia::render('Dashboard/UserDashboard', [
            'packages' => $packages,
            'totalAttempts' => $totalAttempts,
            'bestScore' => $bestScore,
            'averageScore' => round($averageScore, 1),
            'gamification' => null,
            'teacherNotes' => $teacherNotes,
        ]);
    }

    public function adminDashboard()
    {
        $totalUsers = User::where('role', 'user')->count();
        $totalPackages = Package::count();
        $totalSessions = PracticeSession::where('status', 'completed')->count();

        $recentUsers = User::where('role', 'user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $packageStats = Package::withCount('practiceSessions')
            ->get()
            ->map(function ($package) {
                $completed = $package->practiceSessions()->where('status', 'completed')->count();
                $avgScore = $package->practiceSessions()->where('status', 'completed')->avg('total_score') ?? 0;

                return [
                    'name' => $package->title,
                    'sessions_count' => $package->practice_sessions_count ?? 0,
                    'completed_count' => $completed,
                    'avg_score' => round($avgScore, 1),
                ];
            });

        if ($packageStats->isEmpty()) {
            $packageStats = collect([
                [
                    'name' => 'Belum Ada Paket',
                    'sessions_count' => 0,
                    'completed_count' => 0,
                    'avg_score' => 0,
                ],
            ]);
        }

        return Inertia::render('Dashboard/AdminDashboard', [
            'totalUsers' => $totalUsers,
            'totalPackages' => $totalPackages,
            'totalSessions' => $totalSessions,
            'recentUsers' => $recentUsers,
            'packageStats' => $packageStats,
        ]);
    }
}
