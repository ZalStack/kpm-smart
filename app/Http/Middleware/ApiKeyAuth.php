<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyAuth
{
    /**
     * Handle an incoming request.
     * Authenticate using API key header - no login required
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key') ?? $request->query('api_key');

        $allowedKeys = array_filter(explode(',', (string) config('app.api_keys', '')));

        // If an API key is provided and allowed keys are configured, validate it
        if ($apiKey && !empty($allowedKeys)) {
            if (!in_array($apiKey, $allowedKeys)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid API key',
                ], 401);
            }
        }

        return $next($request);
    }
}
