<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationApiController extends BaseApiController
{
    /**
     * Get user's notifications
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $this->resolveUserId($request);
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
        $userId = $this->resolveUserId($request);

        $count = Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->count();

        return $this->sendResponse(['unread_count' => $count], 'Jumlah notifikasi belum dibaca.');
    }

    /**
     * Mark single notification as read
     */
    public function markRead(Request $request, Notification $notification): JsonResponse
    {
        $userId = $this->resolveUserId($request);

        if ((int) $notification->user_id !== (int) $userId) {
            return $this->sendError('Akses ditolak!', [], 403);
        }

        $notification->markAsRead();

        return $this->sendResponse($notification->fresh(), 'Notifikasi berhasil ditandai telah dibaca.');
    }

    /**
     * Mark all notifications as read for current user
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $userId = $this->resolveUserId($request);

        if (! $userId) {
            return $this->sendError('Unauthenticated.', [], 401);
        }

        $updated = Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return $this->sendResponse([
            'marked_count' => $updated,
        ], 'Semua notifikasi berhasil ditandai telah dibaca.');
    }
}
