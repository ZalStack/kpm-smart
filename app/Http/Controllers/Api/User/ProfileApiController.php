<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProfileApiController extends BaseApiController
{
    /**
     * Get student profile data
     */
    public function show(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request, 'user');

        if (!$user) {
            return $this->sendError('Data profil siswa tidak ditemukan.', [], 404);
        }

        return $this->sendResponse([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
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
            'created_at' => $user->created_at,
        ], 'Profil siswa berhasil dimuat.');
    }
}
