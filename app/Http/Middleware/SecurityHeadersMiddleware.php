<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Keamanan: tambahkan security headers pada semua respons untuk
     * memitigasi clickjacking, MIME sniffing, dan serangan umum lainnya.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Mencegah halaman di-embed di iframe situs lain (clickjacking).
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Mencegah browser menebak tipe konten (MIME sniffing).
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Membatasi informasi referrer yang bocor ke situs lain.
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Mematikan API browser yang tidak dipakai aplikasi.
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Nonaktifkan XSS Auditor yang sudah deprecated (bisa memperkenalkan XSS di browser lama).
        $response->headers->set('X-XSS-Protection', '0');

        $isProduction = config('app.env') === 'production';

        // Apakah dev server Vite sedang hidup ditentukan oleh keberadaan file
        // `public/hot` — itu fakta tentang filesystem, BUKAN tentang APP_ENV.
        // Karena itu pengecekan ini tidak boleh digabung dengan APP_ENV:
        // ada orang yang memakai APP_ENV=production di mesin lokal (untuk
        // menguji CSP), dan bila CSP tidak mengizinkan origin dev server,
        // seluruh aset dev diblokir browser dan halaman tidak bisa dipakai.
        $devOrigins = $this->viteDevOrigins();
        $hasDevServer = $devOrigins !== [];

        // Lapisan keamanan: `public/hot` tidak boleh ikut ter-deploy. Kalau
        // menunjuk ke loopback, itu jelas mesin development — wajar. Kalau
        // menunjuk ke host lain, itu hampir pasti salah konfigurasi.
        if ($hasDevServer && ! $this->isLoopbackHotFile()) {
            Log::error('public/hot menunjuk ke host non-loopback. Hapus file tersebut (npm run build) — aset production akan gagal dimuat.');
        }

        if ($isProduction) {
            // CATATAN: host api.iconify.design SENGAJA TIDAK ada di CSP ini.
            //
            // Aplikasi memakai <Icon icon="mdi:..."> dari @iconify/vue. Collection
            // ikon didaftarkan secara lokal lewat `addCollection()` di
            // resources/js/icons/index.js (dihasilkan oleh `npm run icons`), jadi
            // ikon dilayani dari bundel dan tidak ada request ke CDN sama sekali.
            // Karena itu connect-src cukup `'self'` — lebih ketat, tidak ada
            // ketergantungan pihak ketiga saat runtime, dan IP user tidak
            // terkirim ke Iconify.
            //
            // Jangan menambahkan host Iconify kembali tanpa alasan: kalau collection
            // lokal lupa di-register, ikon akan hilang, bukan sekadar "blocked".

            // Content-Security-Policy: membatasi sumber daya yang boleh dimuat halaman.
            $csp = [
                'default-src' => ["'self'"],
                'script-src' => ["'self'", "'unsafe-inline'", "'unsafe-eval'", 'https://cdn.jsdelivr.net', 'https://cdnjs.cloudflare.com', 'https://cdn.plyr.io', 'https://cdn.tailwindcss.com'],
                'style-src' => ["'self'", "'unsafe-inline'", 'https://fonts.googleapis.com', 'https://cdnjs.cloudflare.com', 'https://cdn.plyr.io', 'https://cdn.tailwindcss.com'],
                'font-src' => ["'self'", 'https://fonts.gstatic.com', 'https://fonts.bunny.net'],
                'img-src' => ["'self'", 'data:', 'blob:', 'https://ui-avatars.com', 'https://drive.google.com'],
                'frame-src' => ['https://drive.google.com'],
                'connect-src' => ["'self'"],
                'frame-ancestors' => ["'self'"],
            ];

            if ($hasDevServer) {
                // Modul dimuat via HTTP, HMR lewat WebSocket.
                $httpOrigins = array_values(array_filter($devOrigins, fn ($origin) => str_starts_with($origin, 'http://')));

                $csp['script-src'] = array_merge($csp['script-src'], $httpOrigins);
                $csp['style-src'] = array_merge($csp['style-src'], $httpOrigins);
                $csp['connect-src'] = array_merge($csp['connect-src'], $devOrigins);
            }

            $cspParts = [];
            foreach ($csp as $directive => $sources) {
                $cspParts[] = $directive . ' ' . implode(' ', $sources);
            }

            $response->headers->set('Content-Security-Policy', implode('; ', $cspParts));
        }

        // Header lintas-origin + HSTS selalu dipasang di production. Dev server
        // tidak boleh memengaruhi keputusan ini: cukup satu file `public/hot`
        // yang nyasar sudah cukup untuk mematikan HSTS tanpa jejak.
        if ($isProduction) {
            $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
            $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
            $response->headers->set('Cross-Origin-Embedder-Policy', 'credentialless');

            // HSTS (wajib HTTPS).
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $response;
    }

    /**
     * Origin Vite dev server yang sedang aktif, mis. ['http://127.0.0.1:5173', 'ws://127.0.0.1:5173'].
     * Mengembalikan array kosong kalau dev server tidak berjalan.
     */
    private function viteDevOrigins(): array
    {
        $hotFile = public_path('hot');

        if (! is_file($hotFile)) {
            return [];
        }

        $hotUrl = trim((string) file_get_contents($hotFile));

        if ($hotUrl === '') {
            return [];
        }

        $port = parse_url($hotUrl, PHP_URL_PORT) ?: 5173;

        // Hanya varian IPv4/nama host. Alasan IPv6 `[::1]` SENGAJA TIDAK
        // dimasukkan: grammar host-source di spesifikasi CSP tidak mengizinkan
        // karakter `[` dan `]`, jadi browser akan menolaknya dan mencetak
        // "contains an invalid source" di console pada setiap page load.
        // Vite dikonfigurasi bind ke 127.0.0.1 (lihat vite.config.js), jadi
        // varian IPv6 tidak pernah dipakai.
        $origins = [];

        foreach (['127.0.0.1', 'localhost'] as $host) {
            $origins[] = "http://{$host}:{$port}";
            $origins[] = "ws://{$host}:{$port}";
        }

        return $origins;
    }

    /**
     * Apakah `public/hot` menunjuk ke host loopback (mesin development sendiri)?
     *
     * Dipakai untuk membedakan dua kondisi yang sama-sama punya `public/hot`:
     * - `http://127.0.0.1:5173` → dev server lokal, wajar dan tidak perlu
     *   perlu diperingatkan.
     * - `http://dev.example.com:5173` → dev server remote, hampir pasti tidak
     *   sengaja dan asset production akan gagal dimuat.
     */
    private function isLoopbackHotFile(): bool
    {
        $hotFile = public_path('hot');

        if (! is_file($hotFile)) {
            return true;
        }

        $host = parse_url(trim((string) file_get_contents($hotFile)), PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return false;
        }

        return in_array(strtolower($host), ['127.0.0.1', 'localhost', '::1', '[::1]'], true)
            || str_starts_with($host, '127.');
    }
}
