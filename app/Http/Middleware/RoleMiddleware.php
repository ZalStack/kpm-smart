<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Urutan pengecekan (dari atas ke bawah):
     * 1. Pastikan user sudah login.
     * 2. Pastikan akun user masih aktif (is_active).
     * 3. Pastikan role user sesuai dengan role yang dibutuhkan route.
     *    Jika tidak sesuai, redirect ke dashboard role-nya sendiri agar
     *    tidak sekadar mendapat halaman error 403.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string    $role   Role yang dibutuhkan oleh route yang diakses.
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $isApi = $request->is('api/*') || $request->expectsJson();

        // 1. Belum login → tolak akses atau redirect ke login
        if (!Auth::check()) {
            if ($isApi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Silakan login terlebih dahulu.',
                ], 401);
            }

            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu!');
        }

        $user = Auth::user();

        // 2. Akun dinonaktifkan → logout dan tolak akses
        if ($user->is_active === false) {
            Auth::logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            if ($isApi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda telah dinonaktifkan. Silakan hubungi admin.',
                ], 403);
            }

            return redirect()->route('login')
                ->with('error', 'Akun Anda telah dinonaktifkan. Silakan hubungi admin.');
        }

        // 3. Normalisasi allowed roles
        $allowedRoles = [];
        foreach ($roles as $r) {
            $parts = explode(',', (string) $r);
            foreach ($parts as $p) {
                $trimmed = trim($p);
                if ($trimmed !== '') {
                    $allowedRoles[] = $trimmed;
                }
            }
        }

        $userRole = (string) $user->role;
        $isAdminRole = in_array($userRole, ['admin', 'super_admin', 'administrator', 'guru'], true);

        // Jika rute membutuhkan role 'admin', user dengan role admin/super_admin/administrator/guru diizinkan
        $hasAccess = in_array($userRole, $allowedRoles, true)
            || (in_array('admin', $allowedRoles, true) && $isAdminRole);

        if (!$hasAccess) {
            if ($isApi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak! Anda tidak memiliki izin untuk mengakses resource ini.',
                ], 403);
            }

            if ($isAdminRole) {
                return redirect()->route('admin.dashboard')
                    ->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
            }

            return redirect()->route('user.dashboard')
                ->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        }

        return $next($request);
    }
}
