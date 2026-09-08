<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\LoginLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class AuthController extends BaseApiController
{
    /**
     * Login via API to receive Sanctum Bearer Token
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validasi gagal.', $validator->errors(), 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->sendError('Email atau password salah.', [], 401);
        }

        if ($user->is_active === false) {
            return $this->sendError('Akun Anda telah dinonaktifkan. Silakan hubungi admin.', [], 403);
        }

        $deviceName = $request->input('device_name', 'api-client');
        $token = $user->createToken($deviceName)->plainTextToken;

        $user->update(['last_login_at' => now()]);

        LoginLog::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent() ?? 'API Client',
            'location' => null,
            'login_at' => now(),
        ]);

        return $this->sendResponse([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'phone' => $user->phone,
                'student_class' => $user->student_class,
                'bidang' => $user->bidang,
                'level' => $user->level,
                'school_name' => $user->school_name,
                'profile_photo' => $user->profile_photo,
                'profile_photo_url' => $user->profile_photo_url,
                'is_active' => $user->is_active,
            ],
        ], 'Login berhasil.');
    }

    /**
     * Get user profile (authenticated or via user_id / default user)
     */
    public function me(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);

        if (!$user) {
            return $this->sendError('Data profil tidak ditemukan.', [], 404);
        }

        return $this->sendResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'phone' => $user->phone,
                'student_class' => $user->student_class,
                'bidang' => $user->bidang,
                'level' => $user->level,
                'school_name' => $user->school_name,
                'address' => $user->address,
                'gender' => $user->gender,
                'religion' => $user->religion,
                'profile_photo' => $user->profile_photo,
                'profile_photo_url' => $user->profile_photo_url,
                'is_active' => $user->is_active,
                'last_login_at' => $user->last_login_at,
            ],
        ], 'Data profil berhasil diambil.');
    }

    /**
     * Logout and revoke active token
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return $this->sendResponse(null, 'Logout berhasil.');
    }
}
