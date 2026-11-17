<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Package;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PackageApiController extends BaseApiController
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
     * Get paginated packages list with filters
     */
    public function index(Request $request): JsonResponse
    {
        $query = Package::query();

        if ($request->filled('bidang')) {
            $query->where('bidang', $request->bidang);
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->has('is_active') && $request->is_active !== null && $request->is_active !== '') {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 15);
        $packages = $query->withCount('practiceSessions')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Ringkas saja: `cards` dan `questions` sengaja tidak ikut di respons
        // daftar. Selain membebani payload, keduanya memuat kunci jawaban yang
        // tidak perlu muncul saat admin hanya sedang melihat daftar paket.
        $packages->getCollection()->transform(function (Package $pkg) {
            return [
                'id' => $pkg->id,
                'title' => $pkg->title,
                'description' => $pkg->description,
                'kelas' => $pkg->kelas,
                'thumbnail' => $pkg->thumbnail,
                'bidang' => $pkg->bidang,
                'level' => $pkg->level,
                'start_date' => $pkg->start_date,
                'end_date' => $pkg->end_date,
                'start_time' => $pkg->start_time,
                'end_time' => $pkg->end_time,
                'schedule_status' => $pkg->schedule_status,
                'show_answer_key' => $pkg->show_answer_key,
                'show_explanation' => $pkg->show_explanation,
                'show_score' => $pkg->show_score,
                'is_active' => $pkg->is_active,
                'cards_count' => count($pkg->cards ?? []),
                'questions_count' => count($pkg->questions ?? []),
                'practice_sessions_count' => $pkg->practice_sessions_count,
                'created_at' => $pkg->created_at,
                'updated_at' => $pkg->updated_at,
            ];
        });

        return $this->sendResponse($packages, 'Daftar paket berhasil dimuat.');
    }

    /**
     * Get package detail
     */
    public function show(Package $package): JsonResponse
    {
        $package->loadCount('practiceSessions');

        return $this->sendResponse([
            'package' => $package,
            'cards_count' => count($package->cards ?? []),
            'questions_count' => count($package->questions ?? []),
        ], 'Detail paket berhasil dimuat.');
    }

    /**
     * Create new package
     */
    public function store(Request $request): JsonResponse
    {
        $this->ensureNotGuru();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'kelas' => 'nullable|string|max:50',
            'thumbnail' => 'nullable|string|max:500',
            'bidang' => 'nullable|string|max:100',
            'level' => 'nullable|string|max:50',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'start_time' => 'nullable|string|max:8',
            'end_time' => 'nullable|string|max:8',
            'show_answer_key' => 'nullable|boolean',
            'show_explanation' => 'nullable|boolean',
            'show_score' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'cards' => 'nullable|array',
            'questions' => 'nullable|array',
        ]);

        $package = Package::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'kelas' => $validated['kelas'] ?? null,
            'thumbnail' => $validated['thumbnail'] ?? null,
            'bidang' => $validated['bidang'] ?? null,
            'level' => $validated['level'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
            'show_answer_key' => $validated['show_answer_key'] ?? true,
            'show_explanation' => $validated['show_explanation'] ?? false,
            'show_score' => $validated['show_score'] ?? true,
            'is_active' => $validated['is_active'] ?? true,
            'cards' => $validated['cards'] ?? [],
            'questions' => $validated['questions'] ?? [],
        ]);

        return $this->sendResponse($package, 'Paket soal berhasil dibuat.', 201);
    }

    /**
     * Update package
     */
    public function update(Request $request, Package $package): JsonResponse
    {
        $this->ensureNotGuru();

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'kelas' => 'nullable|string|max:50',
            'thumbnail' => 'nullable|string|max:500',
            'bidang' => 'nullable|string|max:100',
            'level' => 'nullable|string|max:50',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'start_time' => 'nullable|string|max:8',
            'end_time' => 'nullable|string|max:8',
            'show_answer_key' => 'nullable|boolean',
            'show_explanation' => 'nullable|boolean',
            'show_score' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
            'cards' => 'nullable|array',
            'questions' => 'nullable|array',
        ]);

        $package->update($validated);

        return $this->sendResponse($package->fresh(), 'Paket soal berhasil diperbarui.');
    }

    /**
     * Delete package
     */
    public function destroy(Package $package): JsonResponse
    {
        $this->ensureNotGuru();

        $package->delete();

        return $this->sendResponse(null, 'Paket soal berhasil dihapus.');
    }
}
