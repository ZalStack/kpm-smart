<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PackageApiController extends BaseApiController
{
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
     *
     * Endpoint ini hanya untuk admin (route dijaga `role:admin`), jadi kunci
     * jawaban tetap disertakan karena admin memang butuh untuk menyusun soal.
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
}
