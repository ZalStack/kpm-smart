<script setup>
import { inject, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Pagination from '@/Components/shared/Pagination.vue';

const route = inject('route');

const props = defineProps({
    notifications: { type: Object, required: true },
    unreadCount: { type: Number, default: 0 },
});

const localNotifications = ref(props.notifications.data || []);
const processing = ref(false);

function getTypeConfig(type) {
    const configs = {
        order:       { icon: 'mdi:cart-outline', color: 'bg-yellow-500/10 text-yellow-600', label: 'Pesanan' },
        testimonial: { icon: 'mdi:chat-outline', color: 'bg-primary/10 text-primary', label: 'Ulasan' },
        support:     { icon: 'mdi:lifebuoy', color: 'bg-red-500/10 text-red-600', label: 'Dukungan' },
        enroll:      { icon: 'mdi:graduation-cap', color: 'bg-emerald-500/10 text-emerald-600', label: 'Pendaftaran' },
        video:       { icon: 'mdi:video-outline', color: 'bg-pink-500/10 text-pink-600', label: 'Video' },
        announcement:{ icon: 'mdi:bullhorn-outline', color: 'bg-amber-500/10 text-amber-600', label: 'Pengumuman' },
        leave:       { icon: 'mdi:file-document-outline', color: 'bg-violet-500/10 text-violet-600', label: 'Izin' },
    };
    return configs[type] || { icon: 'mdi:bell-outline', color: 'bg-muted text-muted-foreground', label: 'Notifikasi' };
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
    const now = new Date();
    const diffMs = now - dt;
    const diffMins = Math.floor(diffMs / 60000);
    if (diffMins < 1) return 'Baru saja';
    if (diffMins < 60) return diffMins + ' menit lalu';
    if (diffMins < 1440) return Math.floor(diffMins / 60) + ' jam lalu';
    return dt.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <AdminLayout>
        <Head title="Semua Notifikasi - Admin" />

        <template #header-title>Semua Notifikasi</template>
        <template #header-sub>{{ notifications.total || localNotifications.length }} notifikasi</template>

        <div class="space-y-5">
            <!-- Top Actions Bar -->
            <div class="flex items-center justify-between anim-fade-in-up">
                <div class="flex items-center gap-2">
                    <div v-if="unreadCount > 0"
                         class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary/10 border border-primary/20 rounded-full text-xs font-bold text-primary">
                        <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                        {{ unreadCount }} belum dibaca
                    </div>
                    <div v-else class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-500/10 border border-emerald-500/20 rounded-full text-xs font-bold text-emerald-600">
                        <Icon icon="mdi:check-all" class="w-3.5 h-3.5" />
                        Semua sudah dibaca
                    </div>
                </div>

                <Button v-if="unreadCount > 0" variant="outline" size="sm"
                        class="gap-1.5 rounded-xl font-semibold text-xs hover:bg-primary/5 transition-all"
                        @click="markAllRead" :disabled="processing">
                    <Icon v-if="processing" icon="mdi:loading" class="w-3.5 h-3.5 animate-spin" />
                    <Icon v-else icon="mdi:check-all" class="w-3.5 h-3.5" />
                    {{ processing ? 'Memproses...' : 'Tandai Semua Dibaca' }}
                </Button>
            </div>

            <!-- Notifications List -->
            <div class="space-y-2">
                <!-- Empty State -->
                <div v-if="localNotifications.length === 0"
                     class="bg-card border border-border/60 rounded-2xl p-16 text-center shadow-sm anim-fade-in-up">
                    <div class="flex flex-col items-center gap-4">
                        <div class="w-20 h-20 rounded-2xl bg-muted flex items-center justify-center">
                            <Icon icon="mdi:bell-off-outline" class="w-10 h-10 text-muted-foreground/40" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-foreground">Kotak Masuk Kosong</h3>
                            <p class="text-sm text-muted-foreground mt-1">Belum ada notifikasi baru untuk saat ini.</p>
                        </div>
                    </div>
                </div>

                <!-- Notification Items -->
                <div v-for="(notif, idx) in localNotifications" :key="notif.id"
                     @click="markAsRead(notif.id, idx)"
                     :class="[
                         'group bg-card rounded-2xl border transition-all duration-200 cursor-pointer anim-fade-in-up',
                         !notif.is_read
                             ? 'border-primary/30 shadow-sm hover:shadow-md hover:border-primary/50 border-l-4 border-l-primary'
                             : 'border-border/60 hover:border-border hover:shadow-sm'
                     ]"
                     :style="{ animationDelay: `${idx * 25}ms` }">

                    <div class="flex items-start gap-3 sm:gap-4 p-4 sm:p-5">
                        <!-- Icon -->
                        <div :class="['w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm', getTypeConfig(notif.type).color]">
                            <Icon :icon="getTypeConfig(notif.type).icon" class="w-5 h-5" />
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h4 :class="['text-sm truncate', !notif.is_read ? 'font-bold text-foreground' : 'font-semibold text-foreground/90']">
                                            {{ notif.title }}
                                        </h4>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-muted text-muted-foreground whitespace-nowrap">
                                            {{ getTypeConfig(notif.type).label }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-muted-foreground mt-1 line-clamp-2 leading-relaxed">{{ notif.message }}</p>
                                </div>

                                <!-- Unread indicator -->
                                <div v-if="!notif.is_read"
                                     class="w-2.5 h-2.5 rounded-full bg-primary flex-shrink-0 mt-1 shadow-sm shadow-primary/50">
                                </div>
                            </div>

                            <div class="flex items-center gap-2 mt-2">
                                <Icon icon="mdi:clock-outline" class="w-3 h-3 text-muted-foreground/60" />
                                <p class="text-[11px] text-muted-foreground/70">{{ formatDate(notif.created_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <Pagination :links="notifications.links" />
        </div>
    </AdminLayout>
</template>
