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
}
