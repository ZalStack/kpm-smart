<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGuruIsReadOnly
{
    /**
     * Handle an incoming request.
     *
     * Role Guru hanya memiliki hak akses pantau (read-only).
     * Semua aksi state-changing (POST, PUT, PATCH, DELETE) ditolak dengan 403,
     * kecuali aksi dasar akun/sesi pribadi seperti logout, dropdown/baca notifikasi pribadi,
     * dan push subscription.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->role === 'guru') {
            $method = strtoupper($request->method());

            // Izinkan metode pembacaan (safe methods)
            if (in_array($method, ['GET', 'HEAD', 'OPTIONS'], true)) {
                return $next($request);
            }

            // Izinkan beberapa aksi pribadi (baca notifikasi sendiri, logout, push subscribe)
            $allowedRoutes = [
                'logout',
                'admin.notifications.dropdown',
                'admin.notifications.mark-read',
                'admin.notifications.mark-all-read',
                'push.subscribe',
                'push.unsubscribe',
                'push.resubscribe',
            ];

            $currentRoute = $request->route()?->getName();
            if ($currentRoute && in_array($currentRoute, $allowedRoutes, true)) {
                return $next($request);
            }

            $message = 'Akses Terbatas: Akun Guru hanya memiliki hak pantau (monitoring / read-only) dan tidak diizinkan membuat, mengubah, atau menghapus data.';

            if ($request->expectsJson() || $request->is('api/*') || $request->header('X-Inertia')) {
                abort(403, $message);
            }

            abort(403, $message);
        }

        return $next($request);
    }
}
