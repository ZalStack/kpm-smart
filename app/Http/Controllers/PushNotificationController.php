<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Services\PushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PushNotificationController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'subscription' => 'required|array',
            'subscription.endpoint' => 'required|string|max:500',
            'subscription.keys' => 'required|array',
            'subscription.keys.p256dh' => 'required|string',
            'subscription.keys.auth' => 'required|string',
        ]);

        try {
            $subscription = PushNotificationService::subscribe(
                auth()->id(),
                $request->input('subscription'),
                $request->userAgent()
            );

            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengaktifkan notifikasi',
                'subscription_id' => $subscription->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Push subscription failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengaktifkan notifikasi',
            ], 500);
        }
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => 'required|string',
        ]);

        try {
            PushNotificationService::unsubscribe(auth()->id(), $request->input('endpoint'));

            return response()->json([
                'success' => true,
                'message' => 'Berhasil menonaktifkan notifikasi',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menonaktifkan notifikasi',
            ], 500);
        }
    }

    public function status(): JsonResponse
    {
        return response()->json([
            'subscribed' => false,
            'subscription_count' => 0,
        ]);
    }

    public function vapidPublicKey(): JsonResponse
    {
        // Bila VAPID belum dikonfigurasi, kembalikan 503 + flag supaya frontend
        // bisa menonaktifkan UI notifikasi daripada crash saat fetch.
        if (! PushNotificationService::isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Push notification belum dikonfigurasi di server ini.',
                'public_key' => null,
                'configured' => false,
            ], 503);
        }

        return response()->json([
            'public_key' => PushNotificationService::getPublicKey(),
            'configured' => true,
        ]);
    }

    public function resubscribe(Request $request): JsonResponse
    {
        $request->validate([
            'old_endpoint' => 'required|string',
            'subscription' => 'required|array',
            'subscription.endpoint' => 'required|string',
            'subscription.keys' => 'required|array',
            'subscription.keys.p256dh' => 'required|string',
            'subscription.keys.auth' => 'required|string',
        ]);

        try {
            PushSubscription::where('endpoint', $request->input('old_endpoint'))->delete();

            $subscription = PushNotificationService::subscribe(
                auth()->id(),
                $request->input('subscription'),
                $request->userAgent()
            );

            return response()->json([
                'success' => true,
                'message' => 'Subscription updated',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resubscribe',
            ], 500);
        }
    }
}
