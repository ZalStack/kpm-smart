<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.custom');
        Paginator::defaultSimpleView('vendor.pagination.custom');

        // Sinkronisasi pesanan video -> tabel orders hanya SATU ARAH
        // (video_orders adalah sumber kebenaran; baris mirror di orders
        // bersifat read-model untuk halaman Pesanan/Transaksi/Laporan).
        \App\Models\VideoOrder::observe(\App\Observers\VideoOrderObserver::class);

        $this->configureRateLimiters();
    }

    /**
     * Keamanan: rate limiter untuk endpoint publik agar tidak mudah
     * di-spam / di-brute-force oleh hacker.
     */
    private function configureRateLimiters(): void
    {
        // Endpoint auth publik (register, lupa password): 10x/menit per IP.
        RateLimiter::for('auth-public', function (Request $request) {
            return Limit::perMinute(10)->by('auth:' . $request->ip());
        });

        // Form support publik: 10x/menit per IP (anti spam database).
        RateLimiter::for('support', function (Request $request) {
            return Limit::perMinute(10)->by('support:' . $request->ip());
        });

        // Verifikasi enroll key: 10x/menit per user/IP (anti brute-force key).
        RateLimiter::for('enroll-key', function (Request $request) {
            return Limit::perMinute(10)->by('enroll:' . ($request->user()?->id ?? $request->ip()));
        });

        // API publik (testimonials): 30x/menit per IP.
        RateLimiter::for('public-api', function (Request $request) {
            return Limit::perMinute(30)->by('api:' . $request->ip());
        });

        // Pembuatan tagihan Midtrans: 20x/menit per user (anti spam gateway).
        RateLimiter::for('payments', function (Request $request) {
            return Limit::perMinute(20)->by('pay:' . ($request->user()?->id ?? $request->ip()));
        });
    }
}
