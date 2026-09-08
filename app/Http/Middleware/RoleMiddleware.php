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
    public function handle(Request $request, Closure $next, string $role)
    {
        // 1. Belum login → paksa ke halaman login.
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu!');
        }

        $user = Auth::user();

        // 2. Akun dinonaktifkan → tolak akses dengan pesan yang jelas.
        if ($user->is_active === false) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('error', 'Akun Anda telah dinonaktifkan. Silakan hubungi admin.');
        }

        // 3. Role tidak sesuai → redirect ke dashboard role yang dimiliki user,
        //    bukan sekadar abort(403), agar pengalaman pengguna lebih baik.
        if ($user->role !== $role) {
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard')
                    ->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
            }

            return redirect()->route('user.dashboard')
                ->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        }

        return $next($request);
    }
}
