<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementApiController extends BaseApiController
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

    /**
     * Create announcement
     */
    public function store(Request $request): JsonResponse
    {
        $this->ensureNotGuru();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'priority' => 'nullable|in:normal,penting,urgent',
            'is_active' => 'nullable|boolean',
        ]);

        $announcement = Announcement::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'priority' => $validated['priority'] ?? 'normal',
            'is_active' => isset($validated['is_active']) ? (bool) $validated['is_active'] : true,
            'created_by' => Auth::id(),
        ]);

        $announcement->load('creator:id,name');

        return $this->sendResponse($announcement, 'Pengumuman berhasil dibuat.', 201);
    }

    /**
     * Update announcement
     */
    public function update(Request $request, Announcement $announcement): JsonResponse
    {
        $this->ensureNotGuru();

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'priority' => 'nullable|in:normal,penting,urgent',
            'is_active' => 'nullable|boolean',
        ]);

        $announcement->update($validated);
        $announcement->load('creator:id,name');

        return $this->sendResponse($announcement, 'Pengumuman berhasil diperbarui.');
    }

    /**
     * Delete announcement
     */
    public function destroy(Announcement $announcement): JsonResponse
    {
        $this->ensureNotGuru();

        $announcement->delete();

        return $this->sendResponse(null, 'Pengumuman berhasil dihapus.');
    }
}
