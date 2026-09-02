<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => RoleMiddleware::class,
        ]);

        // Webhook Midtrans dikirim server-to-server tanpa session/CSRF token;
        // keaslian payload diverifikasi di controller via signature SHA512 + Server Key.
        $middleware->validateCsrfTokens(except: [
            'payment/notification',
            'video-payment/notification',
        ]);

        // Keamanan: security headers untuk semua respons web.
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeadersMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, \Illuminate\Http\Request $request) {
            $message = 'Ukuran file yang diunggah terlalu besar. Maksimal 50 MB per video.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 413);
            }

            return redirect()
                ->back()
                ->withInput()
                ->with('error', '❌ ' . $message);
        });
    })->create();
