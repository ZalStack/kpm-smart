<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\SearchHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AdminUserController extends Controller
{
    /**
     * Daftar semua user (management data users).
     */
    public function index(Request $request)
    {
        $query = User::query()->where('role', 'user');

        if ($search = $request->get('search')) {
            $escapedSearch = SearchHelper::escapeLike($search);
            $query->where(function ($q) use ($escapedSearch) {
                $q->where('name', 'like', "%{$escapedSearch}%")
                  ->orWhere('email', 'like', "%{$escapedSearch}%")
                  ->orWhere('student_name', 'like', "%{$escapedSearch}%")
                  ->orWhere('school_name', 'like', "%{$escapedSearch}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('is_active', $status === 'active');
        }

        $users = $query->latest()->paginate(10)->withQueryString();

        $totalUsers = User::where('role', 'user')->count();
        $activeUsers = User::where('role', 'user')->where('is_active', true)->count();
        $inactiveUsers = $totalUsers - $activeUsers;
        $newThisMonth = User::where('role', 'user')->whereMonth('created_at', now()->month)->count();

        return Inertia::render('Admin/Users/UserIndex', [
            'users' => $users,
            'search' => $search ?? '',
            'status' => $status ?? '',
            'totalUsers' => $totalUsers,
            'activeUsers' => $activeUsers,
        ]);
    }

    /**
     * Form tambah user baru.
     */
    public function create()
    {
        return Inertia::render('Admin/Users/UserCreate');
    }

    /**
     * Simpan user baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'student_class' => 'nullable|string|max:50',
            'bidang' => 'nullable|string|max:100',
            'level' => 'nullable|string|max:50',
            'school_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'gender' => 'nullable|string|in:Laki-laki,Perempuan',
            'religion' => 'nullable|string|max:50',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'phone' => $validated['phone'] ?? null,
            'student_name' => $validated['name'],
            'student_class' => $validated['student_class'] ?? null,
            'bidang' => $validated['bidang'] ?? null,
            'level' => $validated['level'] ?? null,
            'school_name' => $validated['school_name'] ?? null,
            'address' => $validated['address'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'religion' => $validated['religion'] ?? null,
        ]);

        $user->role = 'user';
        $user->is_verified = true;
        $user->is_active = true;
        $user->save();

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile_photos', 'public');
            $user->update(['profile_photo' => $path]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User "' . $user->name . '" berhasil ditambahkan. Email: ' . $user->email);
    }

    /**
     * Detail satu user, termasuk ringkasan pesanan/aktivitasnya.
     */
    public function show(User $user)
    {
        return Inertia::render('Admin/Users/UserShow', [
            'user' => $user,
        ]);
    }

    /**
     * Form edit user.
     */
    public function edit(User $user)
    {
        return Inertia::render('Admin/Users/UserEdit', [
            'user' => $user,
        ]);
    }

    /**
     * Update data user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'student_class' => 'nullable|string|max:50',
            'bidang' => 'nullable|string|max:100',
            'level' => 'nullable|string|max:50',
            'school_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'gender' => 'nullable|string|in:Laki-laki,Perempuan',
            'religion' => 'nullable|string|max:50',
            'profile_photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'student_name' => $validated['name'],
            'student_class' => $validated['student_class'] ?? null,
            'bidang' => $validated['bidang'] ?? null,
            'level' => $validated['level'] ?? null,
            'school_name' => $validated['school_name'] ?? null,
            'address' => $validated['address'] ?? null,
            'gender' => $validated['gender'] ?? null,
            'religion' => $validated['religion'] ?? null,
        ];

        if (!empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $data['profile_photo'] = $request->file('profile_photo')->store('profile_photos', 'public');
        }

        $user->update($data);

        return redirect()->route('admin.users.show', $user->id)
            ->with('success', 'Data user "' . $user->name . '" berhasil diperbarui.');
    }

    /**
     * Hapus user.
     */
    public function destroy(User $user)
    {
        if ($user->profile_photo) {
            Storage::disk('public')->delete($user->profile_photo);
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User "' . $userName . '" berhasil dihapus.');
    }

    /**
     * Toggle aktif/nonaktifkan akun user.
     */
    public function toggleActive(User $user)
    {
        $user->is_active = !$user->is_active;
        $user->save();

        return back()->with('success', $user->is_active
            ? 'Akun ' . $user->name . ' diaktifkan kembali.'
            : 'Akun ' . $user->name . ' dinonaktifkan.');
    }
}
