<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\LeaveRequest;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveRequestApiController extends BaseApiController
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

    /**
     * Update leave request status (approved/rejected)
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $this->ensureNotGuru();

        $leaveRequest = LeaveRequest::with('user')->find($id);

        if (!$leaveRequest) {
            return $this->sendError('Pengajuan izin tidak ditemukan.', [], 404);
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_note' => 'nullable|string|max:500',
        ]);

        $leaveRequest->update([
            'status' => $validated['status'],
            'admin_note' => $validated['admin_note'] ?? null,
        ]);

        $statusLabel = $validated['status'] === 'approved' ? 'Disetujui' : 'Ditolak';

        try {
            NotificationService::create(
                $leaveRequest->user_id,
                'leave',
                'Pengajuan Izin ' . $statusLabel,
                'Pengajuan izin anda telah ' . strtolower($statusLabel) . ' oleh admin.',
                ['leave_request_id' => $leaveRequest->id],
                false
            );
        } catch (\Throwable $e) {
            // Ignored if email fails
        }

        return $this->sendResponse($leaveRequest->fresh(), "Pengajuan izin berhasil {$statusLabel}.");
    }
}
