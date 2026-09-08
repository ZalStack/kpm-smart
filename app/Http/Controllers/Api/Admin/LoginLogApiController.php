<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\LoginLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LoginLogApiController extends BaseApiController
{
    /**
     * Get paginated login logs
     */
    public function index(Request $request): JsonResponse
    {
        $query = LoginLog::with('user:id,name,email,role');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ip_address', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $perPage = (int) $request->input('per_page', 20);
        $logs = $query->orderBy('login_at', 'desc')->paginate($perPage);

        return $this->sendResponse($logs, 'Log aktivitas login berhasil dimuat.');
    }
}
