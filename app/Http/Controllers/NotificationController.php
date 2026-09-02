<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = NotificationService::getAll(auth()->id(), 20);
        $unreadCount = NotificationService::getUnreadCount(auth()->id());

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    public function adminIndex()
    {
        $notifications = NotificationService::getAll(auth()->id(), 10);
        $unreadCount = NotificationService::getUnreadCount(auth()->id());

        return view('admin.notifications.index', compact('notifications', 'unreadCount'));
    }

    public function dropdown(Request $request): JsonResponse
    {
        $notifications = NotificationService::getLatest(auth()->id(), 5);
        $unreadCount = NotificationService::getUnreadCount(auth()->id());

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
        NotificationService::markAsRead($id, auth()->id());

        return response()->json([
            'success' => true,
            'unread_count' => NotificationService::getUnreadCount(auth()->id()),
        ]);
    }

    public function markAllAsRead(): JsonResponse
    {
        NotificationService::markAllAsRead(auth()->id());

        return response()->json([
            'success' => true,
            'unread_count' => 0,
        ]);
    }

    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'unread_count' => NotificationService::getUnreadCount(auth()->id()),
        ]);
    }
}
