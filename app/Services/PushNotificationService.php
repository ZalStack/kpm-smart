<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    private static ?WebPush $webPush = null;

    /**
     * WebPush hanya bisa dibuat kalau pasangan VAPID sudah dikonfigurasi.
     * Kalau kosong, `new WebPush()` akan melempar exception dan membuat
     * scheduled command `push:sps-reminder` gagal terus-menerus.
     */
    public static function isConfigured(): bool
    {
        return filled(config('services.vapid.public_key'))
            && filled(config('services.vapid.private_key'));
    }

    private static function getWebPush(): WebPush
    {
        if (self::$webPush === null) {
            if (! self::isConfigured()) {
                throw new \RuntimeException(
                    'VAPID keys belum dikonfigurasi. Isi VAPID_PUBLIC_KEY dan VAPID_PRIVATE_KEY di .env '
                    .'(generate dengan: php artisan web-push:generate-vapid-keys).'
                );
            }

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

    public static function getSubscriptions(int $userId): Collection
    {
        return PushSubscription::where('user_id', $userId)->get();
    }

    public static function sendToUser(int $userId, string $title, string $body, array $options = []): array
    {
        $subscriptions = self::getSubscriptions($userId);
        $results = [];

        // Tanpa VAPID keys, pengiriman tidak mungkin succeed. Keluar lebih awal supaya
        // scheduled command tidak mencoba mengirim ke ratusan user lalu gagal.
        if (! self::isConfigured()) {
            return [[
                'subscription_id' => null,
                'status' => 'skipped',
                'error' => 'VAPID keys belum dikonfigurasi.',
            ]];
        }

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
