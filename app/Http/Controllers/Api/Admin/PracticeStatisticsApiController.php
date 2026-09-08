<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\PracticeSession;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PracticeStatisticsApiController extends BaseApiController
{
    /**
     * Get paginated practice statistics / sessions
     */
    public function index(Request $request): JsonResponse
    {
        $query = PracticeSession::with(['user:id,name,email,student_class,bidang,level,school_name', 'package:id,title'])
            ->where('status', 'completed');

        if ($request->filled('package_id')) {
            $query->where('package_id', $request->package_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 15);
        $sessions = $query->orderBy('finished_at', 'desc')->paginate($perPage);

        return $this->sendResponse($sessions, 'Daftar statistik latihan berhasil dimuat.');
    }

    /**
     * Get practice session detail
     */
    public function show(PracticeSession $session): JsonResponse
    {
        $session->load(['user', 'package']);

        return $this->sendResponse($session, 'Detail sesi latihan berhasil dimuat.');
    }
}
