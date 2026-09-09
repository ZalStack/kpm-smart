<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use App\Models\PracticeSession;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    private static ?WebPush $webPush = null;

    private static function getWebPush(): WebPush
    {
        if (self::$webPush === null) {
            self::$webPush = new WebPush([
                'VAPID' => [
                    'subject' => config('app.url', 'http://localhost'),
                    'publicKey' => config('services.vapid.public_key'),
                    'privateKey' => config('services.vapid.private_key'),
                ],
            ]);
            self::$webPush->setTTL(60 * 60 * 24 * 7); // 7 days
        }

        return self::$webPush;
    }

    public static function subscribe(int $userId, array $subscription, ?string $userAgent = null): PushSubscription
    {
        $endpoint = $subscription['endpoint'];
        $keys = $subscription['keys'] ?? [];

        return PushSubscription::updateOrCreate(
            [
                'user_id' => $userId,
                'endpoint' => $endpoint,
            ],
            [
                'p256dh' => $keys['p256dh'] ?? '',
                'auth' => $keys['auth'] ?? '',
                'user_agent' => $userAgent,
                'last_used_at' => now(),
            ]
        );
    }

    public static function unsubscribe(int $userId, string $endpoint): bool
    {
        return PushSubscription::where('user_id', $userId)
            ->where('endpoint', $endpoint)
            ->delete() > 0;
    }

    public static function unsubscribeAll(int $userId): int
    {
        return PushSubscription::where('user_id', $userId)->delete();
    }

    public static function getSubscriptions(int $userId): Collection
    {
        return PushSubscription::where('user_id', $userId)->get();
    }

    public static function getAllSubscriptions(): Collection
    {
        return PushSubscription::with('user:id,name,email,role,is_active')
            ->whereHas('user', function ($query) {
                $query->where('role', 'user')->where('is_active', true);
            })
            ->get();
    }

    public static function sendToUser(int $userId, string $title, string $body, array $options = []): array
    {
        $subscriptions = self::getSubscriptions($userId);
        $results = [];

        foreach ($subscriptions as $sub) {
            try {
                $pushSubscription = Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'publicKey' => $sub->p256dh,
                    'authToken' => $sub->auth,
                ]);

                $payload = json_encode([
                    'title' => $title,
                    'body' => $body,
                    'icon' => $options['icon'] ?? '/favicon.ico',
                    'badge' => $options['badge'] ?? '/favicon.ico',
                    'url' => $options['url'] ?? '/dashboard',
                    'tag' => $options['tag'] ?? 'kpm-smart-notification',
                    'requireInteraction' => $options['requireInteraction'] ?? false,
                ]);

                $webPush = self::getWebPush();
                $report = $webPush->sendOneNotification($pushSubscription, $payload);

                $sub->markUsed();

                if ($report->isSuccess()) {
                    $results[] = ['subscription_id' => $sub->id, 'status' => 'success'];
                } else {
                    $results[] = [
                        'subscription_id' => $sub->id,
                        'status' => 'failed',
                        'error' => $report->getReason(),
                    ];
                    // Remove invalid subscription
                    if ($report->isSubscriptionExpired()) {
                        $sub->delete();
                    }
                }
            } catch (\Exception $e) {
                Log::error("Push notification failed for subscription {$sub->id}: " . $e->getMessage());
                $results[] = [
                    'subscription_id' => $sub->id,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    public static function sendToAllUsers(string $title, string $body, array $options = []): array
    {
        $subscriptions = self::getAllSubscriptions();
        $results = [];

        $webPush = self::getWebPush();
        $payload = json_encode([
            'title' => $title,
            'body' => $body,
            'icon' => $options['icon'] ?? '/favicon.ico',
            'badge' => $options['badge'] ?? '/favicon.ico',
            'url' => $options['url'] ?? '/dashboard',
            'tag' => $options['tag'] ?? 'kpm-smart-notification',
            'requireInteraction' => $options['requireInteraction'] ?? false,
        ]);

        foreach ($subscriptions as $sub) {
            try {
                $pushSubscription = Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'publicKey' => $sub->p256dh,
                    'authToken' => $sub->auth,
                ]);

                $report = $webPush->sendOneNotification($pushSubscription, $payload);

                $sub->markUsed();

                if ($report->isSuccess()) {
                    $results[] = ['user_id' => $sub->user_id, 'status' => 'success'];
                } else {
                    $results[] = [
                        'user_id' => $sub->user_id,
                        'status' => 'failed',
                        'error' => $report->getReason(),
                    ];
                    if ($report->isSubscriptionExpired()) {
                        $sub->delete();
                    }
                }
            } catch (\Exception $e) {
                Log::error("Push notification failed for user {$sub->user_id}: " . $e->getMessage());
                $results[] = [
                    'user_id' => $sub->user_id,
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    public static function hasDoneSpsToday(int $userId): bool
    {
        return PracticeSession::where('user_id', $userId)
            ->whereDate('created_at', today())
            ->where('status', 'completed')
            ->exists();
    }

    public static function getSpsReminderUsers(): Collection
    {
        return User::where('role', 'user')
            ->where('is_active', true)
            ->whereDoesntHave('practiceSessions', function ($query) {
                $query->whereDate('created_at', today())
                    ->where('status', 'completed');
            })
            ->whereHas('pushSubscriptions')
            ->get();
    }

    public static function getPublicKey(): ?string
    {
        return config('services.vapid.public_key');
    }
}
