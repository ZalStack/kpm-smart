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
        $subscriptions = PushNotificationService::getSubscriptions(auth()->id());
        $hasPermission = $subscriptions->isNotEmpty();

        return response()->json([
            'subscribed' => $hasPermission,
            'subscription_count' => $subscriptions->count(),
        ]);
    }

    public function vapidPublicKey(): JsonResponse
    {
        return response()->json([
            'public_key' => PushNotificationService::getPublicKey(),
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
