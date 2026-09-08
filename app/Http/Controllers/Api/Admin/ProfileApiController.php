<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProfileApiController extends BaseApiController
{
    /**
     * Get admin profile data
     */
    public function show(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request, 'admin');

        if (!$user) {
            return $this->sendError('Data profil admin tidak ditemukan.', [], 404);
        }

        return $this->sendResponse([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'phone' => $user->phone,
            'profile_photo' => $user->profile_photo,
            'profile_photo_url' => $user->profile_photo_url,
            'created_at' => $user->created_at,
        ], 'Profil admin berhasil dimuat.');
    }
}
