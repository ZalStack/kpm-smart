<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\User;
use App\Models\Package;
use App\Models\PracticeSession;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardApiController extends BaseApiController
{
    /**
     * Get admin dashboard summary data
     */
    public function index(Request $request): JsonResponse
    {
        $totalUsers = User::where('role', 'user')->count();
        $activeUsers = User::where('role', 'user')->where('is_active', true)->count();
        $totalPackages = Package::count();
        $activePackages = Package::where('is_active', true)->count();
        $totalSessions = PracticeSession::where('status', 'completed')->count();

        $recentUsers = User::where('role', 'user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get(['id', 'name', 'email', 'bidang', 'level', 'school_name', 'is_active', 'created_at']);

        $packageStats = Package::withCount('practiceSessions')
            ->get()
            ->map(function ($package) {
                $completed = $package->practiceSessions()->where('status', 'completed')->count();
                $avgScore = $package->practiceSessions()->where('status', 'completed')->avg('total_score') ?? 0;
                return [
                    'id' => $package->id,
                    'title' => $package->title,
                    'sessions_count' => $package->practice_sessions_count ?? 0,
                    'completed_count' => $completed,
                    'avg_score' => round($avgScore, 1),
                ];
            });

        return $this->sendResponse([
            'summary' => [
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
                'total_packages' => $totalPackages,
                'active_packages' => $activePackages,
                'total_completed_sessions' => $totalSessions,
            ],
            'recent_users' => $recentUsers,
            'package_stats' => $packageStats,
        ], 'Data dasbor admin berhasil dimuat.');
    }
}
