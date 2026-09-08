<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserApiController extends BaseApiController
{
    /**
     * Get paginated users list with filters
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('bidang')) {
            $query->where('bidang', $request->bidang);
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->has('is_active') && $request->is_active !== null && $request->is_active !== '') {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('school_name', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 15);
        $users = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return $this->sendResponse($users, 'Daftar user berhasil dimuat.');
    }

    /**
     * Get user detail
     */
    public function show(User $user): JsonResponse
    {
        $user->loadCount(['practiceSessions as completed_sessions_count' => function ($q) {
            $q->where('status', 'completed');
        }]);

        $recentSessions = $user->practiceSessions()
            ->with('package:id,title')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return $this->sendResponse([
            'user' => $user,
            'recent_sessions' => $recentSessions,
        ], 'Detail user berhasil dimuat.');
    }
}
