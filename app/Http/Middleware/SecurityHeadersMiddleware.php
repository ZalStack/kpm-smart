<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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

        // Security headers yang lebih ketat hanya untuk produksi.
        if (config('app.env') === 'production') {
            // Content-Security-Policy: membatasi sumber daya yang boleh dimuat halaman.
            $cspParts = [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.plyr.io https://app.midtrans.com https://app.sandbox.midtrans.com https://cdn.tailwindcss.com",
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com https://cdn.plyr.io https://cdn.tailwindcss.com",
                "font-src 'self' https://fonts.gstatic.com https://fonts.bunny.net",
                "img-src 'self' data: blob: https://ui-avatars.com https://drive.google.com",
                "frame-src https://drive.google.com https://app.midtrans.com https://app.sandbox.midtrans.com",
                "connect-src 'self' https://app.midtrans.com https://app.sandbox.midtrans.com",
                "frame-ancestors 'self'",
            ];
            $response->headers->set('Content-Security-Policy', implode('; ', $cspParts));

            $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
            $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
            $response->headers->set('Cross-Origin-Embedder-Policy', 'credentialless');

            // HSTS (wajib HTTPS).
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $response;
    }
}
