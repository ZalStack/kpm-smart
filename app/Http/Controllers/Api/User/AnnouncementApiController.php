<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AnnouncementApiController extends BaseApiController
{
    /**
     * Get all active announcements for user
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);

        $announcements = Announcement::where('is_active', true)
            ->with('creator:id,name')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return $this->sendResponse($announcements, 'Daftar pengumuman berhasil dimuat.');
    }

    /**
     * Get announcement detail for user
     */
    public function show(Announcement $announcement): JsonResponse
    {
        if (!$announcement->is_active) {
            return $this->sendError('Pengumuman tidak ditemukan atau sudah dinonaktifkan.', [], 404);
        }

        $announcement->load('creator:id,name');

        return $this->sendResponse($announcement, 'Detail pengumuman berhasil dimuat.');
    }
}
