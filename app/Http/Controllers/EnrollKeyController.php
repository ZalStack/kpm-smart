<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Package;
use App\Support\SearchHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class EnrollKeyController extends Controller
{
    /**
     * Display a listing of enroll keys.
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'package'])
            ->where('payment_status', 'paid')
            ->whereNotNull('enrollment');

        // Filter by enrollment status
        if ($request->filled('enrollment_status')) {
            switch ($request->enrollment_status) {
                case 'activated':
                    $query->whereJsonContains('enrollment->activated', true);
                    break;
                case 'not_activated':
                    $query->whereJsonContains('enrollment->activated', false);
                    break;
                case 'sent':
                    $query->whereJsonContains('enrollment->sent_by_admin', true);
                    break;
                case 'not_sent':
                    $query->whereJsonContains('enrollment->sent_by_admin', false);
                    break;
                case 'unlocked':
                    $query->whereJsonContains('enrollment->unlocked', true);
                    break;
                case 'locked':
                    $query->whereJsonContains('enrollment->unlocked', false);
                    break;
            }
        }

        // Filter by package
        if ($request->filled('package_id')) {
            $query->where('package_id', $request->package_id);
        }

        // Search by order number or user name
        if ($request->filled('search')) {
            $search = $request->search;
            $escapedSearch = SearchHelper::escapeLike($search);
            $query->where(function($q) use ($escapedSearch) {
                $q->where('order_number', 'LIKE', "%{$escapedSearch}%")
                  ->orWhereHas('user', function($userQuery) use ($escapedSearch) {
                      $userQuery->where('name', 'LIKE', "%{$escapedSearch}%")
                                ->orWhere('email', 'LIKE', "%{$escapedSearch}%");
                  })
                  ->orWhereJsonContains('enrollment->key', $escapedSearch);
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = in_array(strtolower((string) $request->get('sort_order', 'desc')), ['asc', 'desc'], true) ? strtolower($request->get('sort_order', 'desc')) : 'desc';
        $allowedSorts = ['order_number', 'total_price', 'created_at', 'payment_time'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $enrollKeys = $query->paginate(10)->withQueryString();

        // Summary statistics
        $stats = [
            'total_keys' => Order::where('payment_status', 'paid')->whereNotNull('enrollment')->count(),
            'activated' => Order::where('payment_status', 'paid')
                ->whereJsonContains('enrollment->activated', true)
                ->count(),
            'not_activated' => Order::where('payment_status', 'paid')
                ->whereJsonContains('enrollment->activated', false)
                ->count(),
            'sent_by_admin' => Order::where('payment_status', 'paid')
                ->whereJsonContains('enrollment->sent_by_admin', true)
                ->count(),
            'unlocked' => Order::where('payment_status', 'paid')
                ->whereJsonContains('enrollment->unlocked', true)
                ->count(),
        ];

        // Get packages for filter
        $packages = Package::where('is_active', true)->get();

        return view('admin.enroll-keys.index', compact(
            'enrollKeys',
            'stats',
            'packages',
            'request'
        ));
    }

    /**
     * Display the specified enroll key detail.
     */
    public function show(Order $enrollKey)
    {
        $enrollKey->load(['user', 'package', 'practiceSessions']);

        // Check if this is a valid enroll key order
        if ($enrollKey->payment_status !== 'paid' || empty($enrollKey->enrollment)) {
            return redirect()->route('admin.enroll-keys.index')
                ->with('error', 'This order does not have a valid enrollment key.');
        }

        // Get related enroll keys from same user
        $relatedKeys = Order::where('user_id', $enrollKey->user_id)
            ->where('payment_status', 'paid')
            ->whereNotNull('enrollment')
            ->where('id', '!=', $enrollKey->id)
            ->latest()
            ->take(5)
            ->get();

        // Get activity log from enrollment
        $activityLog = $enrollKey->enrollment['activity_log'] ?? [];

        return view('admin.enroll-keys.show', compact(
            'enrollKey',
            'relatedKeys',
            'activityLog'
        ));
    }

    /**
     * Activate enroll key by admin.
     */
    public function activate(Request $request, Order $enrollKey)
    {
        try {
            $enrollment = $enrollKey->enrollment ?? [];

            // Update enrollment data
            $enrollment['activated'] = true;
            $enrollment['sent_by_admin'] = true;
            $enrollment['activated_by'] = auth()->user()->name;
            $enrollment['activated_at'] = Carbon::now()->toDateTimeString();

            // Add to activity log
            $enrollment['activity_log'][] = [
                'action' => 'activated',
                'admin_id' => auth()->id(),
                'admin_name' => auth()->user()->name,
                'timestamp' => Carbon::now()->toDateTimeString(),
                'note' => $request->input('note', 'Enroll key activated by admin')
            ];

            $enrollKey->enrollment = $enrollment;
            $enrollKey->save();

            return redirect()->route('admin.enroll-keys.show', $enrollKey->id)
                ->with('success', 'Enroll key activated successfully');

        } catch (\Exception $e) {
            Log::error('Activate enroll key error: ' . $e->getMessage());
            return back()->with('error', 'Failed to activate enroll key. Silakan coba lagi.');
        }
    }

    /**
     * Send enroll key to user.
     */
    public function send(Request $request, Order $enrollKey)
    {
        try {
            $enrollment = $enrollKey->enrollment ?? [];
            $key = $enrollment['key'] ?? $this->generateEnrollKey();

            // Update enrollment data
            $enrollment['sent_by_admin'] = true;
            $enrollment['sent_at'] = Carbon::now()->toDateTimeString();
            $enrollment['sent_by'] = auth()->id();
            $enrollment['key'] = $key;

            // Add to activity log
            $enrollment['activity_log'][] = [
                'action' => 'sent',
                'admin_id' => auth()->id(),
                'admin_name' => auth()->user()->name,
                'timestamp' => Carbon::now()->toDateTimeString(),
                'note' => $request->input('note', 'Enroll key sent to user')
            ];

            $enrollKey->enrollment = $enrollment;
            $enrollKey->save();

            // Send email to user
            $this->sendEnrollKeyEmail($enrollKey, $key);

            return redirect()->route('admin.enroll-keys.show', $enrollKey->id)
                ->with('success', 'Enroll key sent successfully');

        } catch (\Exception $e) {
            Log::error('Send enroll key error: ' . $e->getMessage());
            return back()->with('error', 'Failed to send enroll key. Silakan coba lagi.');
        }
    }

    /**
     * Generate unique enroll key.
     */
    private function generateEnrollKey(int $maxAttempts = 10): string
    {
        $prefix = 'PKA';
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $random = '';

        for ($i = 0; $i < 8; $i++) {
            $random .= $characters[random_int(0, strlen($characters) - 1)];
        }

        $key = $prefix . '-' . $random . '-' . date('Ymd');

        // Check if key already exists
        $exists = Order::whereJsonContains('enrollment->key', $key)->exists();
        if ($exists && $maxAttempts > 1) {
            return $this->generateEnrollKey($maxAttempts - 1);
        }

        return $key;
    }

    /**
     * Send enroll key email to user.
     */
    private function sendEnrollKeyEmail(Order $order, $key)
    {
        try {
            $user = $order->user;
            $package = $order->package;

            // You can customize this email view
            Mail::send('emails.enroll-key', [
                'user' => $user,
                'package' => $package,
                'order' => $order,
                'key' => $key
            ], function ($message) use ($user) {
                $message->to($user->email, $user->name)
                        ->subject('Your Enrollment Key - PKA Litbang');
            });

        } catch (\Exception $e) {
            Log::error('Send enroll key email error: ' . $e->getMessage());
            // Don't throw, just log error
        }
    }

    /**
     * Bulk send enroll keys.
     */
    public function bulkSend(Request $request)
    {
        try {
            $orderIds = $request->input('order_ids', []);

            if (empty($orderIds)) {
                return back()->with('error', 'No orders selected');
            }

            $orders = Order::whereIn('id', $orderIds)
                ->where('payment_status', 'paid')
                ->get();

            $successCount = 0;
            $failedCount = 0;

            foreach ($orders as $order) {
                try {
                    $enrollment = $order->enrollment ?? [];
                    $key = $enrollment['key'] ?? $this->generateEnrollKey();

                    $enrollment['sent_by_admin'] = true;
                    $enrollment['sent_at'] = Carbon::now()->toDateTimeString();
                    $enrollment['sent_by'] = auth()->id();
                    $enrollment['key'] = $key;

                    $order->enrollment = $enrollment;
                    $order->save();

                    $this->sendEnrollKeyEmail($order, $key);
                    $successCount++;
                } catch (\Exception $e) {
                    Log::error('Bulk send error for order ' . $order->id . ': ' . $e->getMessage());
                    $failedCount++;
                }
            }

            return back()->with('success', "Successfully sent {$successCount} enroll keys. Failed: {$failedCount}");

        } catch (\Exception $e) {
            Log::error('Bulk send enroll keys error: ' . $e->getMessage());
            return back()->with('error', 'Failed to send enroll keys. Silakan coba lagi.');
        }
    }
}
