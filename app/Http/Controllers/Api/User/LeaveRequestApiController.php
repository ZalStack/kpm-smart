<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LeaveRequestApiController extends BaseApiController
{
    /**
     * Get user's submitted leave requests
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $this->resolveUserId($request, 'user');
        $perPage = (int) $request->input('per_page', 10);

        $leaveRequests = LeaveRequest::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return $this->sendResponse($leaveRequests, 'Daftar pengajuan izin berhasil dimuat.');
    }
}
