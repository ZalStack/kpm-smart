<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $notifications = $userId ? NotificationService::getAll($userId, 20) : collect();
        $unreadCount = $userId ? NotificationService::getUnreadCount($userId) : 0;

        return Inertia::render('Notifications/NotificationIndex', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    public function adminIndex()
    {
        $userId = auth()->id();
        $notifications = $userId ? NotificationService::getAll($userId, 20) : collect();
        $unreadCount = $userId ? NotificationService::getUnreadCount($userId) : 0;

        return Inertia::render('Admin/Notifications/NotificationIndex', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    public function dropdown(Request $request): JsonResponse
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json([
                'notifications' => [],
                'unread_count' => 0,
            ]);
        }

        $notifications = NotificationService::getLatest($userId, 5);
        $unreadCount = NotificationService::getUnreadCount($userId);

        $data = $notifications->map(function ($notif) {
            return [
                'id' => $notif->id,
                'type' => $notif->type,
                'title' => $notif->title,
                'message' => $notif->message,
                'data' => $notif->data,
                'is_read' => $notif->isRead(),
                'created_at' => $notif->created_at->diffForHumans(),
            ];
        });

        return response()->json([
            'notifications' => $data,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAsRead(int $id): JsonResponse
    {
        $userId = auth()->id();
        if ($userId) {
            NotificationService::markAsRead($id, $userId);
        }

        return response()->json([
            'success' => true,
            'unread_count' => $userId ? NotificationService::getUnreadCount($userId) : 0,
        ]);
    }

    public function markAllAsRead(): JsonResponse
    {
        $userId = auth()->id();
        if ($userId) {
            NotificationService::markAllAsRead($userId);
        }

        return response()->json([
            'success' => true,
            'unread_count' => 0,
        ]);
    }

    public function unreadCount(): JsonResponse
    {
        $userId = auth()->id();
        return response()->json([
            'unread_count' => $userId ? NotificationService::getUnreadCount($userId) : 0,
        ]);
    }
}
