<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LeaveRequestApiController extends BaseApiController
{
    /**
     * Get user's submitted leave requests
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $this->resolveUserId($request);
        $perPage = (int) $request->input('per_page', 10);

        $leaveRequests = LeaveRequest::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return $this->sendResponse($leaveRequests, 'Daftar pengajuan izin berhasil dimuat.');
    }

    /**
     * Submit new leave request
     */
    public function store(Request $request): JsonResponse
    {
        $userId = $this->resolveUserId($request);

        if (! $userId) {
            return $this->sendError('Unauthenticated.', [], 401);
        }

        $validated = $request->validate([
            'reason' => 'required|string|min:3|max:1000',
            'proof_file' => 'nullable',
        ]);

        $proofPath = null;
        if ($request->hasFile('proof_file')) {
            $proofPath = $request->file('proof_file')->store('leave-proofs', 'public');
        } elseif (is_string($request->input('proof_file'))) {
            $proofPath = $request->input('proof_file');
        }

        $leaveRequest = LeaveRequest::create([
            'user_id' => $userId,
            'reason' => $validated['reason'],
            'proof_file' => $proofPath,
            'status' => 'pending',
        ]);

        $user = Auth::user() ?? User::find($userId);
        $userName = $user ? $user->name : 'Siswa';

        // Beri notifikasi ke admin
        try {
            $admins = User::where('role', 'admin')->pluck('id');
            foreach ($admins as $adminId) {
                NotificationService::create(
                    $adminId,
                    'leave',
                    'Pengajuan Izin Baru',
                    $userName . ' mengajukan izin: "' . Str::limit($validated['reason'], 80) . '"',
                    ['leave_request_id' => $leaveRequest->id],
                    false
                );
            }
        } catch (\Throwable $e) {
            // Ignored
        }

        return $this->sendResponse($leaveRequest, 'Pengajuan izin berhasil dikirim.', 201);
    }
}
