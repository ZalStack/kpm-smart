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
    }
}
