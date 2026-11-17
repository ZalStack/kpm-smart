<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserApiController extends BaseApiController
{
    /**
     * Cegah role guru melakukan perubahan data (hanya pantau).
     */
    protected function ensureNotGuru(): void
    {
        if (Auth::check() && Auth::user()->role === 'guru') {
            abort(403, 'Akses Terbatas: Role Guru hanya memiliki hak pantau (monitoring) dan tidak dapat memodifikasi data.');
        }
    }

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

    /**
     * Create new user
     */
    public function store(Request $request): JsonResponse
    {
        $this->ensureNotGuru();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => ['nullable', 'string', Rule::in(['user', 'guru', 'admin'])],
            'phone' => 'nullable|string|max:20',
            'student_class' => 'nullable|string|max:50',
            'bidang' => 'nullable|string|max:100',
            'level' => 'nullable|string|max:50',
            'school_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'parent_name' => 'nullable|string|max:255',
            'parent_phone' => 'nullable|string|max:50',
            'place_of_birth' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|string|max:50',
            'parent_profession' => 'nullable|string|max:100',
            'parent_education' => 'nullable|string|max:100',
            'gender' => 'nullable|string|max:50',
            'religion' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'] ?? 'user',
            'phone' => $validated['phone'] ?? null,
            'student_name' => $validated['name'],
            'student_class' => $validated['student_class'] ?? null,
            'bidang' => $validated['bidang'] ?? null,
            'level' => $validated['level'] ?? null,
            'school_name' => $validated['school_name'] ?? null,
            'address' => $validated['address'] ?? null,
            'parent_name' => $validated['parent_name'] ?? null,
            'parent_phone' => $validated['parent_phone'] ?? null,
            'place_of_birth' => $validated['place_of_birth'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'parent_profession' => $validated['parent_profession'] ?? null,
            'parent_education' => $validated['parent_education'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'religion' => $validated['religion'] ?? null,
            'is_active' => isset($validated['is_active']) ? (bool) $validated['is_active'] : true,
            'is_verified' => true,
        ]);

        return $this->sendResponse($user, 'User berhasil dibuat.', 201);
    }

    /**
     * Update existing user
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $this->ensureNotGuru();

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'role' => ['sometimes', 'required', 'string', Rule::in(['user', 'guru', 'admin'])],
            'phone' => 'nullable|string|max:20',
            'student_class' => 'nullable|string|max:50',
            'bidang' => 'nullable|string|max:100',
            'level' => 'nullable|string|max:50',
            'school_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'parent_name' => 'nullable|string|max:255',
            'parent_phone' => 'nullable|string|max:50',
            'place_of_birth' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|string|max:50',
            'parent_profession' => 'nullable|string|max:100',
            'parent_education' => 'nullable|string|max:100',
            'gender' => 'nullable|string|max:50',
            'religion' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        if (isset($validated['name'])) {
            $validated['student_name'] = $validated['name'];
        }

        $user->update($validated);

        return $this->sendResponse($user->fresh(), 'User berhasil diperbarui.');
    }

    /**
     * Delete user
     */
    public function destroy(User $user): JsonResponse
    {
        $this->ensureNotGuru();

        if (Auth::id() === $user->id) {
            return $this->sendError('Tidak dapat menghapus akun Anda sendiri.', [], 400);
        }

        $user->delete();

        return $this->sendResponse(null, 'User berhasil dihapus.');
    }

    /**
     * Toggle user active status
     */
    public function toggleActive(User $user): JsonResponse
    {
        $this->ensureNotGuru();

        if (Auth::id() === $user->id) {
            return $this->sendError('Tidak dapat menonaktifkan akun Anda sendiri.', [], 400);
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $statusStr = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return $this->sendResponse($user, "Status user berhasil {$statusStr}.");
    }

    /**
     * Update user level
     */
    public function updateLevel(Request $request, User $user): JsonResponse
    {
        $this->ensureNotGuru();

        $validated = $request->validate([
            'level' => 'required|string|max:50',
        ]);

        $user->update(['level' => $validated['level']]);

        return $this->sendResponse($user, 'Level user berhasil diperbarui.');
    }
}
