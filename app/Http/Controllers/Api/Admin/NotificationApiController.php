<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Notification;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationApiController extends BaseApiController
{
    /**
     * Cegah role guru melakukan perubahan data (hanya pantau).
     */
    protected function ensureNotGuru(): void
    {
        if (Auth::check() && Auth::user()->role === 'guru') {
            abort(403, 'Akses Terbatas: Role Guru hanya memiliki hak pantau (monitoring) dan tidak dapat memodifikasi data.');
        }
    }

    /**
     * Get paginated admin notifications
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $this->resolveUserId($request);
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
        $userId = $this->resolveUserId($request);
        $query = Notification::where('is_read', false);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $count = $query->count();

        return $this->sendResponse(['unread_count' => $count], 'Jumlah notifikasi belum dibaca.');
    }

    /**
     * Send notification to user or broadcast to all active users
     */
    public function send(Request $request): JsonResponse
    {
        $this->ensureNotGuru();

        $validated = $request->validate([
            'target' => 'required|in:user,all',
            'user_id' => 'required_if:target,user|nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'nullable|string|in:info,warning,success,leave,announcement',
            'data' => 'nullable|array',
        ]);

        $type = $validated['type'] ?? 'info';
        $title = $validated['title'];
        $message = $validated['message'];
        $data = $validated['data'] ?? [];

        if ($validated['target'] === 'user') {
            $notification = NotificationService::create(
                (int) $validated['user_id'],
                $type,
                $title,
                $message,
                $data,
                false
            );

            return $this->sendResponse($notification, 'Notifikasi berhasil dikirim ke user.', 201);
        }

        // Broadcast to all active students/users
        $userIds = User::where('is_active', true)->pluck('id');
        $createdCount = 0;
        foreach ($userIds as $uid) {
            Notification::create([
                'user_id' => $uid,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data,
            ]);
            $createdCount++;
        }

        return $this->sendResponse([
            'sent_count' => $createdCount,
        ], "Notifikasi berhasil disiarkan ke {$createdCount} user.", 201);
    }
}
