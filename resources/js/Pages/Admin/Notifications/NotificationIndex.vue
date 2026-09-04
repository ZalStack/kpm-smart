<script setup>
import { inject,  ref, onMounted, onBeforeUnmount } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
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
        order: 'mdi:cart-outline', testimonial: 'mdi:chat-outline', support: 'mdi:lifebuoy', enroll: 'mdi:graduation-cap', video: 'mdi:video-outline',
    };
    return icons[type] || 'mdi:bell-outline';
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

        <div class="flex items-center justify-between mb-6 sm:mb-8 anim-fade-in-up">
            <div class="flex items-center gap-2">
                <p class="text-sm text-muted-foreground">{{ unreadCount }} belum dibaca</p>
            </div>
            <Button variant="outline" size="sm" class="gap-1.5 rounded-xl font-semibold hover:shadow-md transition-all active:scale-[0.97]" @click="markAllRead" :disabled="processing">
                <template v-if="processing">Memproses...</template>
                <template v-else><Icon icon="mdi:check-circle" class="w-4 h-4 inline-block align-middle mr-1" /> Tandai Semua Dibaca</template>
            </Button>
        </div>

        <div class="space-y-3">
            <div v-if="localNotifications.length === 0" class="text-center py-20 bg-card rounded-2xl border border-border/60 shadow-sm anim-fade-in-up">
                <div class="flex flex-col items-center gap-4">
                    <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-muted to-muted/50 flex items-center justify-center">
                        <Icon icon="mdi:bell-outline" class="w-12 h-12 text-muted-foreground/40" />
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-muted-foreground">Tidak Ada Notifikasi</h3>
                        <p class="text-sm text-muted-foreground/60 mt-1">Semua notifikasi sudah dibaca</p>
                    </div>
                </div>
            </div>

            <div v-for="(notif, idx) in localNotifications" :key="notif.id"
                 @click="markAsRead(notif.id, idx)"
                 :class="[
                     'notif-card bg-card rounded-2xl border border-border/60 p-4 sm:p-5 cursor-pointer transition-all duration-300 hover:shadow-md hover:border-primary/20 anim-fade-in-up',
                     !notif.is_read ? 'border-l-4 border-l-primary shadow-sm' : ''
                 ]"
                 :style="{ animationDelay: `${idx * 30}ms` }">
                <div class="flex items-start gap-3 sm:gap-4">
                    <div :class="['w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm', getIconBg(notif.type)]">
                        <Icon :icon="getIcon(notif.type)" class="w-5 h-5 inline-block align-middle" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <h4 :class="['text-sm', !notif.is_read ? 'font-bold' : 'font-medium']">{{ notif.title }}</h4>
                            <div v-if="!notif.is_read" class="w-2.5 h-2.5 rounded-full bg-primary flex-shrink-0 mt-1 animate-pulse-soft"></div>
                        </div>
                        <p class="text-sm text-muted-foreground mt-1 line-clamp-2 leading-relaxed">{{ notif.message }}</p>
                        <p class="text-xs text-muted-foreground/60 mt-2.5">{{ notif.created_at }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <Pagination :links="notifications.links" />
        </div>
    </AdminLayout>
</template>
