import { ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function usePushNotification() {
    const page = usePage();
    const isSupported = ref(false);
    const permission = ref('default');
    const isSubscribed = ref(false);
    const isLoading = ref(false);
    const error = ref(null);
    let registration = null;

    const csrfToken = () => page.props.csrfToken;

    async function init() {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            isSupported.value = false;
            return;
        }

        isSupported.value = true;
        permission.value = Notification.permission;

        try {
            registration = await navigator.serviceWorker.register('/sw.js');
            await navigator.serviceWorker.ready;
            await checkSubscription();
        } catch (e) {
            console.error('Service Worker registration failed:', e);
            error.value = 'Gagal mendaftarkan service worker';
        }
    }

    async function checkSubscription() {
        if (!registration) return;

        try {
            const subscription = await registration.pushManager.getSubscription();
            isSubscribed.value = subscription !== null;

            if (isSubscribed.value) {
                await syncSubscription(subscription);
            }
        } catch (e) {
            console.error('Check subscription failed:', e);
        }
    }

    async function syncSubscription(subscription) {
        try {
            const csrf = csrfToken();
            await fetch('/push/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ subscription: subscription.toJSON() }),
            });
        } catch (e) {
            console.error('Sync subscription failed:', e);
        }
    }

    async function requestPermission() {
        if (!isSupported.value) {
            error.value = 'Browser Anda tidak mendukung notifikasi';
            return false;
        }

        isLoading.value = true;
        error.value = null;

        try {
            const result = await Notification.requestPermission();
            permission.value = result;

            if (result === 'granted') {
                await subscribe();
                return true;
            } else if (result === 'denied') {
                error.value = 'Izin notifikasi ditolak. Aktifkan melalui pengaturan browser.';
                return false;
            } else {
                error.value = 'Izin notifikasi belum diberikan';
                return false;
            }
        } catch (e) {
            error.value = 'Gagal meminta izin notifikasi';
            return false;
        } finally {
            isLoading.value = false;
        }
    }

    async function subscribe() {
        if (!registration) {
            error.value = 'Service worker belum siap';
            return false;
        }

        isLoading.value = true;
        error.value = null;

        try {
            const response = await fetch('/push/vapid-key', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const data = await response.json();
            const publicKey = data.public_key;

            if (!publicKey) {
                error.value = 'VAPID key tidak ditemukan';
                return false;
            }

            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(publicKey),
            });

            const csrf = csrfToken();
            const result = await fetch('/push/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ subscription: subscription.toJSON() }),
            });

            const resultData = await result.json();

            if (resultData.success) {
                isSubscribed.value = true;
                return true;
            } else {
                error.value = resultData.message || 'Gagal mengaktifkan notifikasi';
                return false;
            }
        } catch (e) {
            console.error('Subscribe failed:', e);
            error.value = 'Gagal mengaktifkan notifikasi';
            return false;
        } finally {
            isLoading.value = false;
        }
    }

    async function unsubscribe() {
        if (!registration) return false;

        isLoading.value = true;
        error.value = null;

        try {
            const subscription = await registration.pushManager.getSubscription();
            if (!subscription) {
                isSubscribed.value = false;
                return true;
            }

            const endpoint = subscription.endpoint;
            await subscription.unsubscribe();

            const csrf = csrfToken();
            await fetch('/push/unsubscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ endpoint }),
            });

            isSubscribed.value = false;
            return true;
        } catch (e) {
            console.error('Unsubscribe failed:', e);
            error.value = 'Gagal menonaktifkan notifikasi';
            return false;
        } finally {
            isLoading.value = false;
        }
    }

    function urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
        const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    onMounted(() => {
        init();
    });

    return {
        isSupported,
        permission,
        isSubscribed,
        isLoading,
        error,
        requestPermission,
        unsubscribe,
    };
}
