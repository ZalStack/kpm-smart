<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

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
     * Resolve user from Sanctum token, query parameter user_id, or fallback by role
     */
    protected function resolveUser(\Illuminate\Http\Request $request, ?string $role = null, bool $allowNull = false): ?\App\Models\User
    {
        $user = auth('sanctum')->user() ?? $request->user();

        if ($user) {
            return $user;
        }

        if ($request->filled('user_id')) {
            return \App\Models\User::find($request->user_id);
        }

        if ($allowNull) {
            return null;
        }

        if ($role) {
            return \App\Models\User::where('role', $role)->first();
        }

        return \App\Models\User::first();
    }

    /**
     * Resolve user ID from query param, Sanctum token, or fallback by role
     */
    protected function resolveUserId(\Illuminate\Http\Request $request, ?string $role = null, bool $allowNull = false): ?int
    {
        $id = $request->input('user_id', $this->resolveUser($request, $role, $allowNull)?->id);
        return $id ? (int) $id : null;
    }
}
