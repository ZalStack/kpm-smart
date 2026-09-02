<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Support\SearchHelper;
use Illuminate\Http\Request;

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

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search ?? '',
            'status' => $status ?? '',
            'totalUsers' => User::where('role', 'user')->count(),
            'activeUsers' => User::where('role', 'user')->where('is_active', true)->count(),
        ]);
    }

    /**
     * Detail satu user, termasuk ringkasan pesanan/aktivitasnya.
     */
    public function show(User $user)
    {
        $stats = [
            'total_orders' => Order::where('user_id', $user->id)->count(),
            'paid_orders' => Order::where('user_id', $user->id)->where('payment_status', 'paid')->count(),
            'total_spent' => Order::where('user_id', $user->id)->where('payment_status', 'paid')->sum('total_price') ?? 0,
        ];

        $user->load(['orders' => function ($query) {
            $query->latest()->with(['package', 'videoOrder.video'])->limit(20);
        }]);

        return view('admin.users.show', [
            'user' => $user,
            'stats' => $stats,
        ]);
    }

    /**
     * Toggle aktif/nonaktifkan akun user.
     */
    public function toggleActive(User $user)
    {
        // is_active tidak mass-assignable — tulis langsung ke properti.
        $user->is_active = !$user->is_active;
        $user->save();

        return back()->with('success', $user->is_active
            ? 'Akun ' . $user->name . ' diaktifkan kembali.'
            : 'Akun ' . $user->name . ' dinonaktifkan.');
    }
}
