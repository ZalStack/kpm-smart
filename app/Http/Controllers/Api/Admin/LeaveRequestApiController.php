<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LeaveRequestApiController extends BaseApiController
{
    /**
     * Get paginated leave requests with count summary
     */
    public function index(Request $request): JsonResponse
    {
        $counts = [
            'total' => LeaveRequest::count(),
            'pending' => LeaveRequest::where('status', 'pending')->count(),
            'approved' => LeaveRequest::where('status', 'approved')->count(),
            'rejected' => LeaveRequest::where('status', 'rejected')->count(),
        ];

        $query = LeaveRequest::with('user:id,name,email,student_class,bidang,level,school_name');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 15);
        $leaveRequests = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return $this->sendResponse([
            'counts' => $counts,
            'leave_requests' => $leaveRequests,
        ], 'Daftar pengajuan izin berhasil dimuat.');
    }

    /**
     * Get single leave request detail
     */
    public function show($id): JsonResponse
    {
        $leaveRequest = LeaveRequest::with('user')->find($id);

        if (!$leaveRequest) {
            return $this->sendError('Pengajuan izin tidak ditemukan.', [], 404);
        }

        return $this->sendResponse($leaveRequest, 'Detail pengajuan izin berhasil dimuat.');
    }
}
