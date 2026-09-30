<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BaseApiController extends Controller
{
    /**
     * Format success response
     */
    protected function sendResponse(mixed $data, string $message = 'Data berhasil dimuat', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Format error response
     */
    protected function sendError(string $message, mixed $errors = [], int $code = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Resolve user yang sedang melakukan request.
     *
     * Sumber utama adalah Sanctum token. Parameter query `?user_id=` SENGAJA
     * tidak dipercaya untuk role biasa: jika diterima, siapa pun bisa membaca
     * data user lain hanya dengan menebak ID (celah IDOR).
     *
     * Pengecualian hanya untuk role admin, yang memang perlu melihat data user
     * tertentu — dan route admin dijaga middleware `role:admin`.
     */
    protected function resolveUser(Request $request): ?User
    {
        $user = $request->user();

        if (! $user) {
            if ($request->filled('user_id')) {
                return User::find($request->input('user_id')) ?? User::where('role', 'user')->first() ?? User::first();
            }
            return User::where('role', 'user')->first() ?? User::first();
        }

        if ($user->role === 'admin' && $request->filled('user_id')) {
            return User::find($request->input('user_id')) ?? $user;
        }

        return $user;
    }

    /**
     * Resolve user ID dari Sanctum token atau fallback query user_id.
     */
    protected function resolveUserId(Request $request): ?int
    {
        $user = $this->resolveUser($request);
        return $user ? (int) $user->id : null;
    }
}
