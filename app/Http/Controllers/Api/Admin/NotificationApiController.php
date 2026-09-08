<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationApiController extends BaseApiController
{
    /**
     * Get paginated admin notifications
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $this->resolveUserId($request, 'admin');
        $query = Notification::query();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $perPage = (int) $request->input('per_page', 15);
        $notifications = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return $this->sendResponse($notifications, 'Daftar notifikasi berhasil dimuat.');
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $userId = $this->resolveUserId($request, 'admin');
        $query = Notification::where('is_read', false);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $count = $query->count();

        return $this->sendResponse(['unread_count' => $count], 'Jumlah notifikasi belum dibaca.');
    }
}
