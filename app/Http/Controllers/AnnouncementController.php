<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AnnouncementController extends Controller
{
    // Admin: list announcements
    public function adminIndex(Request $request)
    {
        $query = Announcement::with('creator')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $escapedSearch = \App\Support\SearchHelper::escapeLike($search);
            $query->where(function ($q) use ($escapedSearch) {
                $q->where('title', 'like', "%{$escapedSearch}%")
                  ->orWhere('content', 'like', "%{$escapedSearch}%");
            });
        }

        $announcements = $query->paginate(10)->withQueryString();

        return Inertia::render('Admin/Announcements/AnnouncementIndex', [
            'announcements' => $announcements,
            'filters' => $request->only(['search']),
            'stats' => [
                'total' => Announcement::count(),
                'active' => Announcement::where('is_active', true)->count(),
            ],
        ]);
    }

    // Admin: store announcement and broadcast to all users (role=user)
    public function adminStore(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:5000',
            'priority' => 'nullable|in:low,normal,high,urgent',
        ]);

        DB::beginTransaction();
        try {
            $announcement = Announcement::create([
                'title' => $validated['title'],
                'content' => $validated['content'],
                'priority' => $validated['priority'] ?? 'normal',
                'created_by' => auth()->id(),
                'is_active' => true,
            ]);

            // Broadcast to all active users with role=user
            $userIds = User::where('role', 'user')->where('is_active', true)->pluck('id');

            $now = now();
            $notifications = [];
            foreach ($userIds as $uid) {
                $notifications[] = [
                    'user_id' => $uid,
                    'type' => 'announcement',
                    'title' => $validated['title'],
                    'message' => mb_substr($validated['content'], 0, 150),
                    'data' => json_encode([
                        'announcement_id' => $announcement->id,
                        'priority' => $announcement->priority,
                    ]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!empty($notifications)) {
                // Chunk insert to avoid memory
                foreach (array_chunk($notifications, 500) as $chunk) {
                    Notification::insert($chunk);
                }
            }

            DB::commit();

            return redirect()->route('admin.announcements.index')->with('success', 'Pengumuman berhasil dibuat dan dikirim ke ' . count($userIds) . ' siswa!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat pengumuman: ' . $e->getMessage());
        }
    }

    public function adminDestroy(Announcement $announcement)
    {
        $announcement->delete();
        return redirect()->route('admin.announcements.index')->with('success', 'Pengumuman berhasil dihapus!');
    }

    public function adminToggle(Announcement $announcement)
    {
        $announcement->update(['is_active' => !$announcement->is_active]);
        return back()->with('success', 'Status pengumuman diperbarui!');
    }

    // User/Admin: show single announcement detail (for click from notification)
    public function show(Announcement $announcement)
    {
        if (!$announcement->is_active && auth()->user()->role !== 'admin') {
            abort(404);
        }

        // Mark related notification as read if exists
        $notification = Notification::where('user_id', auth()->id())
            ->where('type', 'announcement')
            ->whereJsonContains('data->announcement_id', $announcement->id)
            ->latest()
            ->first();

        if ($notification && !$notification->isRead()) {
            $notification->markAsRead();
        }

        // Determine layout based on role
        $page = auth()->user()->role === 'admin' ? 'Admin/Announcements/AnnouncementShow' : 'Announcements/AnnouncementShow';

        // For admin, still render admin layout page
        // For user, render user layout
        return Inertia::render($page, [
            'announcement' => $announcement->load('creator'),
            'notification' => $notification,
        ]);
    }
}
