// Service Worker for KPM SMART Push Notifications
const CACHE_NAME = 'kpm-smart-v1';

// Install event
self.addEventListener('install', (event) => {
    self.skipWaiting();
});

// Activate event
self.addEventListener('activate', (event) => {
    event.waitUntil(clients.claim());
});

// Push event - handle incoming push notifications
self.addEventListener('push', (event) => {
    if (!event.data) return;

    let payload;
    try {
        payload = event.data.json();
    } catch (e) {
        payload = {
            title: 'KPM SMART',
            body: event.data.text(),
            icon: '/favicon.ico',
            badge: '/favicon.ico',
        };
    }

    const options = {
        body: payload.body || 'Ada pengingat baru untuk Anda',
        icon: payload.icon || '/favicon.ico',
        badge: payload.badge || '/favicon.ico',
        vibrate: [100, 50, 100],
        data: {
            url: payload.url || '/dashboard',
            dateOfArrival: Date.now(),
        },
        actions: payload.actions || [
            { action: 'open', title: 'Buka Aplikasi' },
            { action: 'dismiss', title: 'Tutup' },
        ],
        tag: payload.tag || 'kpm-smart-notification',
        renotify: true,
        requireInteraction: payload.requireInteraction || false,
        silent: false,
    };

    event.waitUntil(
        self.registration.showNotification(payload.title || 'KPM SMART', options)
    );
});

// Notification click event
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    if (event.action === 'dismiss') return;

    const urlToOpen = event.notification.data?.url || '/dashboard';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
            // Check if there's already a window open
            for (const client of windowClients) {
                if (client.url.includes(self.location.origin) && 'focus' in client) {
                    client.navigate(urlToOpen);
                    return client.focus();
                }
            }
            // Open new window if none exists
            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        })
    );
});

// Handle subscription change
self.addEventListener('pushsubscriptionchange', (event) => {
    event.waitUntil(
        self.registration.pushManager.subscribe(event.oldSubscription.options).then((subscription) => {
            // Notify the server about the new subscription
            return fetch('/api/v1/push/resubscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    old_endpoint: event.oldSubscription.endpoint,
                    subscription: subscription.toJSON(),
                }),
            });
        })
    );
});
