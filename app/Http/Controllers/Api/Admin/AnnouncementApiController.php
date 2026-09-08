<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AnnouncementApiController extends BaseApiController
{
    /**
     * Get paginated announcements
     */
    public function index(Request $request): JsonResponse
    {
        $query = Announcement::with('creator:id,name');

        if ($request->has('is_active') && $request->is_active !== null && $request->is_active !== '') {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        $perPage = (int) $request->input('per_page', 15);
        $announcements = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return $this->sendResponse($announcements, 'Daftar pengumuman berhasil dimuat.');
    }

    /**
     * Get announcement detail
     */
    public function show(Announcement $announcement): JsonResponse
    {
        $announcement->load('creator:id,name');

        return $this->sendResponse($announcement, 'Detail pengumuman berhasil dimuat.');
    }
}
