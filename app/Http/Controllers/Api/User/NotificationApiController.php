<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationApiController extends BaseApiController
{
    /**
     * Get user's notifications
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $this->resolveUserId($request, 'user');
        $perPage = (int) $request->input('per_page', 15);

        $notifications = Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return $this->sendResponse($notifications, 'Daftar notifikasi berhasil dimuat.');
    }

    /**
     * Get user's unread notifications count
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $userId = $this->resolveUserId($request, 'user');

        $count = Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();

        return $this->sendResponse(['unread_count' => $count], 'Jumlah notifikasi belum dibaca.');
    }
}
