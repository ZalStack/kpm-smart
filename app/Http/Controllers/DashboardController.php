<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Package;
use App\Models\Order;
use App\Models\PracticeSession;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function userDashboard()
    {
        $user = Auth::user();
        $packages = Package::where('is_active', true)->take(6)->get();
        $orders = Order::where('user_id', $user->id)
            ->where('payment_status', 'paid')
            ->with('package')
            ->get();
        $sessions = PracticeSession::where('user_id', $user->id)
            ->where('status', 'completed')
            ->selectRaw('COUNT(*) as total, MAX(total_score) as best, AVG(total_score) as avg_score')
            ->first();
        $recentOrders = Order::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->with(['package', 'videoOrder.video'])
            ->take(5)
            ->get();

        // Video Pembahasan
        $videos = \App\Models\Video::where('is_active', true)
            ->with('package')
            ->latest()
            ->take(4)
            ->get();

        // Peta status akses video user: active | awaiting_activation | pending_payment | expired
        $videoAccessMap = \App\Models\VideoOrder::where('user_id', $user->id)
            ->whereIn('payment_status', ['paid', 'pending'])
            ->latest()
            ->get()
            ->unique('video_id')
            ->mapWithKeys(fn ($order) => [$order->video_id => $order->accessStatus()])
            ->toArray();

        $totalAttempts = $sessions->total ?? 0;
        $bestScore = $sessions->best ?? 0;
        $averageScore = $sessions->avg_score ?? 0;

        return view('dashboard.user', compact(
            'user', 'packages', 'orders',
            'recentOrders', 'totalAttempts', 'bestScore', 'averageScore',
            'videos', 'videoAccessMap'
        ));
    }

    public function adminDashboard()
    {
        $totalUsers = User::where('role', 'user')->count();
        $totalPackages = Package::count();
        $totalOrders = Order::count();
        $paidOrders = Order::where('payment_status', 'paid')->count();
        $totalRevenue = Order::where('payment_status', 'paid')->sum('total_price') ?? 0;
        $totalSessions = PracticeSession::where('status', 'completed')->count();

        $recentUsers = User::where('role', 'user')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentOrders = Order::with(['user', 'package', 'videoOrder.video'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Package Stats - FIX: Handle empty data
        $packageStats = Package::withCount('orders')
            ->withSum('orders', 'total_price')
            ->get()
            ->map(function($package) {
                return [
                    'name' => $package->title,
                    'orders_count' => $package->orders_count ?? 0,
                    'revenue' => $package->orders_sum_total_price ?? 0,
                ];
            });

        // Jika tidak ada data, tambahkan dummy data untuk menghindari error
        if ($packageStats->isEmpty()) {
            $packageStats = collect([
                [
                    'name' => 'Belum Ada Paket',
                    'orders_count' => 0,
                    'revenue' => 0,
                ]
            ]);
        }

        // activity_logs di-cast ke array oleh Eloquent — akses langsung
        // sebagai array (jangan json_decode manual, menyebabkan TypeError).
        $recentActivities = User::whereNotNull('activity_logs')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get()
            ->map(function($user) {
                $logs = $user->activity_logs;

                return is_array($logs) ? $logs : [];
            })
            ->flatten(1)
            ->take(10);

        // Ekspresi bulan mengikuti driver database (MONTH() tidak ada di SQLite,
        // strftime() tidak ada di MySQL) agar kode portabel & bisa dites.
        $monthSql = \Illuminate\Support\Facades\DB::getDriverName() === 'sqlite'
            ? "CAST(strftime('%m', \"created_at\") AS INTEGER)"
            : 'MONTH(created_at)';

        // Monthly Revenue
        $monthlyRevenue = Order::where('payment_status', 'paid')
            ->whereYear('created_at', date('Y'))
            ->selectRaw($monthSql . ' as month, SUM(total_price) as total')
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Isi bulan yang kosong dengan 0
        for ($i = 1; $i <= 12; $i++) {
            if (!isset($monthlyRevenue[$i])) {
                $monthlyRevenue[$i] = 0;
            }
        }

        $monthlyOrders = Order::where('payment_status', 'paid')
            ->whereYear('created_at', date('Y'))
            ->selectRaw($monthSql . ' as month, COUNT(*) as count')
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        for ($i = 1; $i <= 12; $i++) {
            if (!isset($monthlyOrders[$i])) {
                $monthlyOrders[$i] = 0;
            }
        }

        $pendingVideoActivations = \App\Models\VideoOrder::where('payment_status', 'paid')
            ->where('access_granted', false)
            ->count();

        return view('dashboard.admin', compact(
            'totalUsers', 'totalPackages', 'totalOrders',
            'paidOrders', 'totalRevenue', 'totalSessions',
            'recentUsers', 'recentOrders', 'packageStats',
            'recentActivities', 'monthlyRevenue', 'monthlyOrders',
            'pendingVideoActivations'
        ));
    }
}
