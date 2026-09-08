<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Package;
use App\Models\PracticeSession;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PackageApiController extends BaseApiController
{
    /**
     * Get list of active packages for student with progress
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request, 'user', true);
        $query = Package::where('is_active', true);

        if ($request->filled('bidang')) {
            $query->where('bidang', $request->bidang);
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 15);
        $packages = $query->orderBy('created_at', 'desc')->paginate($perPage);

        // Tambahkan informasi status pengerjaan siswa pada masing-masing paket
        $packageIds = $packages->pluck('id');
        $userSessions = $user ? PracticeSession::where('user_id', $user->id)
            ->whereIn('package_id', $packageIds)
            ->get(['id', 'package_id', 'card_id', 'status', 'total_score']) : collect();

        $transformed = $packages->getCollection()->map(function ($pkg) use ($userSessions) {
            $sessions = $userSessions->where('package_id', $pkg->id);
            $completedSessions = $sessions->where('status', 'completed');
            $inProgressSession = $sessions->where('status', 'in_progress')->first();

            $totalCards = count($pkg->cards ?? []);
            $completedCards = $completedSessions->pluck('card_id')->unique()->count();

            return [
                'id' => $pkg->id,
                'title' => $pkg->title,
                'description' => $pkg->description,
                'bidang' => $pkg->bidang,
                'level' => $pkg->level,
                'cover_image' => $pkg->cover_image,
                'total_cards' => $totalCards,
                'completed_cards' => $completedCards,
                'total_questions' => count($pkg->questions ?? []),
                'is_completed' => $totalCards > 0 ? ($completedCards >= $totalCards) : false,
                'in_progress_session_id' => $inProgressSession ? $inProgressSession->id : null,
                'schedule_status' => $pkg->schedule_status,
                'schedule_start' => $pkg->schedule_start,
                'schedule_end' => $pkg->schedule_end,
                'created_at' => $pkg->created_at,
            ];
        });

        $packages->setCollection($transformed);

        return $this->sendResponse($packages, 'Daftar paket tugas berhasil dimuat.');
    }

    /**
     * Get package detail for student
     */
    public function show(Package $package, Request $request): JsonResponse
    {
        if (!$package->is_active) {
            return $this->sendError('Paket tugas tidak tersedia.', [], 404);
        }

        $user = $this->resolveUser($request, 'user', true);

        // Cari sesi yang sedang berjalan atau sudah selesai untuk paket ini
        $sessions = $user ? PracticeSession::where('user_id', $user->id)
            ->where('package_id', $package->id)
            ->get() : collect();

        $completedSessions = $sessions->where('status', 'completed');
        $inProgressSession = $sessions->where('status', 'in_progress')->first();

        // Rangkum status setiap card
        $cards = collect($package->cards ?? [])->map(function ($card) use ($sessions) {
            $cardSession = $sessions->where('card_id', $card['id'])->first();
            return [
                'id' => $card['id'],
                'title' => $card['title'] ?? 'Card',
                'description' => $card['description'] ?? '',
                'order' => $card['order'] ?? 0,
                'status' => $cardSession ? $cardSession->status : 'unstarted',
                'session_id' => $cardSession ? $cardSession->id : null,
                'score' => $cardSession && $cardSession->status === 'completed' ? $cardSession->total_score : null,
            ];
        });

        return $this->sendResponse([
            'package' => [
                'id' => $package->id,
                'title' => $package->title,
                'description' => $package->description,
                'bidang' => $package->bidang,
                'level' => $package->level,
                'schedule_status' => $package->schedule_status,
                'schedule_start' => $package->schedule_start,
                'schedule_end' => $package->schedule_end,
                'total_questions' => count($package->questions ?? []),
                'created_at' => $package->created_at,
            ],
            'cards' => $cards,
            'in_progress_session_id' => $inProgressSession ? $inProgressSession->id : null,
            'is_fully_completed' => count($package->cards ?? []) > 0 && $completedSessions->count() >= count($package->cards ?? []),
        ], 'Detail paket tugas berhasil dimuat.');
    }
}
