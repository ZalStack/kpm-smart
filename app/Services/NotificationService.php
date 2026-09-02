<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationMail;

class NotificationService
{
    public static function create(int $userId, string $type, string $title, string $message, array $data = [], bool $sendEmail = true): Notification
    {
        $notification = Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);

        if ($sendEmail) {
            $user = User::find($userId);
            if ($user && $user->email) {
                try {
                    Mail::to($user->email)->send(new NotificationMail($notification, $user));
                } catch (\Exception $e) {
                    \Log::error('Failed to send notification email to ' . $user->email . ': ' . $e->getMessage());
                }
            }
        }

        return $notification;
    }

    public static function markAsRead(int $notificationId, ?int $userId = null): void
    {
        $query = Notification::where('id', $notificationId);
        if ($userId !== null) {
            $query->where('user_id', $userId);
        }
        $notification = $query->first();
        if ($notification) {
            $notification->markAsRead();
        }
    }

    public static function markAllAsRead(int $userId): void
    {
        Notification::forUser($userId)
            ->unread()
            ->update(['read_at' => now()]);
    }

    public static function getUnreadCount(int $userId): int
    {
        return Notification::forUser($userId)->unread()->count();
    }

    public static function getLatest(int $userId, int $limit = 5): Collection
    {
        return Notification::forUser($userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function getAll(int $userId, int $perPage = 20)
    {
        return Notification::forUser($userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
