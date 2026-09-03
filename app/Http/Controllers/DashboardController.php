<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Package;
use App\Models\PracticeSession;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function userDashboard()
    {
        $user = Auth::user();
        $packages = Package::where('is_active', true)->take(6)->get();
        $sessions = PracticeSession::where('user_id', $user->id)
            ->where('status', 'completed')
            ->selectRaw('COUNT(*) as total, MAX(total_score) as best, AVG(total_score) as avg_score')
            ->first();

        $totalAttempts = $sessions->total ?? 0;
        $bestScore = $sessions->best ?? 0;
        $averageScore = $sessions->avg_score ?? 0;

        return Inertia::render('Dashboard/UserDashboard', [
            'packages' => $packages,
            'totalAttempts' => $totalAttempts,
            'bestScore' => $bestScore,
            'averageScore' => round($averageScore, 1),
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
            ->map(function($package) {
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
                ]
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
