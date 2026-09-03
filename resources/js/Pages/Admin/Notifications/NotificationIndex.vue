<script setup>
import { inject,  ref, onMounted, onBeforeUnmount } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Badge from '@/Components/ui/badge/Badge.vue';
import Pagination from '@/Components/shared/Pagination.vue';
const route = inject('route');

const props = defineProps({
    notifications: { type: Object, required: true },
    unreadCount: { type: Number, default: 0 },
});

const localNotifications = ref(props.notifications.data || []);
const processing = ref(false);

function getIcon(type) {
    const icons = {
        order: '🛒', testimonial: '💬', support: '🆘', enroll: '🎓', video: '📹',
    };
    return icons[type] || '🔔';
}

function getIconBg(type) {
    const bgs = {
        order: 'bg-yellow-100 text-yellow-700', testimonial: 'bg-primary/10 text-primary', support: 'bg-red-100 text-red-700',
        enroll: 'bg-green-100 text-green-700', video: 'bg-pink-100 text-pink-700',
    };
    return bgs[type] || 'bg-muted text-muted-foreground';
}

function markAsRead(id, index) {
    const notif = localNotifications.value[index];
    if (notif && !notif.is_read) {
        fetch(route('admin.notifications.mark-read', id), {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
        }).then(r => r.json()).then(data => {
            localNotifications.value[index].is_read = true;
            updateSidebarBadge(data.unread_count);
        });
    }
}

function markAllRead() {
    processing.value = true;
    fetch(route('admin.notifications.mark-all-read'), {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
    }).then(r => r.json()).then(data => {
        localNotifications.value.forEach(n => n.is_read = true);
        updateSidebarBadge(0);
        processing.value = false;
    }).catch(() => { processing.value = false; });
}

function updateSidebarBadge(count) {
    const badge = document.querySelector('#notifBtn span');
    if (badge) {
        badge.textContent = count;
        badge.parentElement.style.display = count > 0 ? '' : 'none';
    }
}

function formatDate(d) {
    if (!d) return '';
    const dt = new Date(d);
    return dt.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <AdminLayout>
        <Head title="Notifikasi - Admin" />

        <template #header-title>Semua Notifikasi</template>
        <template #header-sub>{{ notifications.total || localNotifications.length }} notifikasi</template>

        <div class="flex items-center justify-between mb-6">
            <p class="text-sm text-muted-foreground">{{ unreadCount }} belum dibaca</p>
            <Button variant="outline" size="sm" @click="markAllRead" :disabled="processing">
                {{ processing ? 'Memproses...' : '✅ Tandai Semua Dibaca' }}
            </Button>
        </div>

        <div class="space-y-3">
            <div v-if="localNotifications.length === 0" class="text-center py-16 bg-card rounded-xl border">
                <div class="text-5xl mb-4">🔔</div>
                <h3 class="text-lg font-bold text-muted-foreground">Tidak Ada Notifikasi</h3>
            </div>

            <div v-for="(notif, idx) in localNotifications" :key="notif.id"
                 @click="markAsRead(notif.id, idx)"
                 :class="[
                     'notif-card bg-card rounded-xl border p-4 cursor-pointer transition-all hover:shadow-md',
                     !notif.is_read ? 'border-l-4 border-l-primary' : ''
                 ]">
                <div class="flex items-start gap-3">
                    <div :class="['w-10 h-10 rounded-full flex items-center justify-center text-lg flex-shrink-0', getIconBg(notif.type)]">
                        {{ getIcon(notif.type) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <h4 :class="['text-sm', !notif.is_read ? 'font-bold' : 'font-medium']">{{ notif.title }}</h4>
                            <div v-if="!notif.is_read" class="w-2.5 h-2.5 rounded-full bg-primary flex-shrink-0 mt-1"></div>
                        </div>
                        <p class="text-sm text-muted-foreground mt-0.5 line-clamp-2">{{ notif.message }}</p>
                        <p class="text-xs text-muted-foreground mt-2">{{ notif.created_at }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <Pagination :links="notifications.links" />
        </div>
    </AdminLayout>
</template>
