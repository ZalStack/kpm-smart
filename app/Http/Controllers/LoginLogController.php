<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use App\Support\SearchHelper;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LoginLogController extends Controller
{
    public function index(Request $request)
    {
        $query = LoginLog::with('user');

        if ($request->filled('search')) {
            $search = $request->search;
            $escapedSearch = SearchHelper::escapeLike($search);
            $query->whereHas('user', function ($q) use ($escapedSearch) {
                $q->where('name', 'like', "%{$escapedSearch}%")
                  ->orWhere('email', 'like', "%{$escapedSearch}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('login_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('login_at', '<=', $request->date_to);
        }

        $logs = $query->orderBy('login_at', 'desc')->paginate(10)->withQueryString();

        $todayCount = LoginLog::whereDate('login_at', today())->count();
        $weekCount = LoginLog::whereBetween('login_at', [now()->startOfWeek(), now()->endOfWeek()])->count();

        return Inertia::render('Admin/LoginLogs/LoginLogIndex', [
            'logs' => $logs,
            'todayCount' => $todayCount,
            'weekCount' => $weekCount,
        ]);
    }
}
