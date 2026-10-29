<script setup>
import { inject, ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import Pagination from '@/Components/shared/Pagination.vue';
import { timeAgo } from '@/lib/utils';
import { Icon } from '@iconify/vue/offline';

const route = inject('route');

const props = defineProps({
    notifications: { type: Object, required: true },
    unreadCount: { type: Number, default: 0 },
});

const localNotifications = ref(props.notifications.data || []);
const processing = ref(false);
const activeTab = ref('all'); // all, unread, announcements

function getIcon(type) {
    const icons = {
        announcement: 'mdi:bullhorn-outline',
        sps_reminder: 'mdi:calendar-clock-outline',
        practice_result: 'mdi:clipboard-check-outline'
    };
    return icons[type] || 'mdi:bell-outline';
}

function getIconTheme(type) {
    const themes = {
        announcement: 'border-amber-500/20 bg-amber-500/10 text-amber-600',
        sps_reminder: 'border-blue-500/20 bg-blue-500/10 text-blue-600',
        practice_result: 'border-emerald-500/20 bg-emerald-500/10 text-emerald-600',
    };
    return themes[type] || 'border-primary/20 bg-primary/10 text-primary';
}

const filteredList = computed(() => {
    if (activeTab.value === 'unread') {
        return localNotifications.value.filter(n => !n.is_read);
    }
    if (activeTab.value === 'announcements') {
        return localNotifications.value.filter(n => n.type === 'announcement');
    }
    return localNotifications.value;
});

const remainingUnread = computed(() => localNotifications.value.filter(n => !n.is_read).length);

function markAsRead(id, index) {
    const notif = localNotifications.value[index];
    if (notif && !notif.is_read) {
        fetch(route('user.notifications.mark-read', id), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
            },
        }).then(r => r.json()).then(() => {
            localNotifications.value[index].is_read = true;
        }).catch(() => {});
    }
}

function markAllRead() {
    if (remainingUnread.value === 0 || processing.value) return;
    processing.value = true;
    fetch(route('user.notifications.mark-all-read'), {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'Accept': 'application/json'
        },
    }).then(r => r.json()).then(() => {
        localNotifications.value.forEach(n => n.is_read = true);
        processing.value = false;
    }).catch(() => {
        processing.value = false;
    });
}

const tabs = [
    { key: 'all', label: 'Semua' },
    { key: 'unread', label: 'Belum Dibaca' },
    { key: 'announcements', label: 'Pengumuman' },
];
</script>

<template>
    <UserLayout>
        <Head title="Pusat Notifikasi - PR KK" />

        <template #header-title>Pusat Notifikasi</template>
        <template #header-sub>Informasi penting seputar jadwal tugas, pengumuman, dan evaluasi hasil belajarmu.</template>
        <template #header-actions>
            <button
                v-if="remainingUnread > 0"
                class="btn-outline px-4 py-2.5 text-xs"
                :disabled="processing"
                @click="markAllRead"
            >
                <Icon icon="mdi:check-all" class="h-4 w-4 text-primary" />
                {{ processing ? 'Memproses...' : 'Tandai Semua Dibaca' }}
            </button>
        </template>

        <div class="mx-auto max-w-5xl space-y-6">
            <!-- Tab Filter -->
            <div class="seg-tabs anim-fade-in-up">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    @click="activeTab = tab.key"
                    :class="['seg-tab flex items-center gap-1.5', activeTab === tab.key ? 'seg-tab-active' : '', tab.key === 'unread' && activeTab === tab.key ? '!text-primary' : '', tab.key === 'announcements' && activeTab === tab.key ? '!text-amber-600' : '']"
                >
                    <span>{{ tab.label }}</span>
                    <span
                        v-if="tab.key === 'unread' && remainingUnread > 0"
                        class="rounded-full px-1.5 py-0.5 text-[10px] font-bold"
                        :class="activeTab === tab.key ? 'bg-primary text-primary-foreground' : 'bg-primary/10 text-primary'"
                    >
                        {{ remainingUnread }}
                    </span>
                </button>
            </div>

            <!-- Kosong -->
            <div v-if="filteredList.length === 0" class="empty-state anim-fade-in">
                <div class="empty-state-icon">
                    <Icon icon="mdi:bell-check-outline" class="h-8 w-8" />
                </div>
                <h3 class="text-base font-bold text-foreground">
                    {{ activeTab === 'unread' ? 'Semua Notifikasi Sudah Dibaca' : 'Tidak Ada Notifikasi' }}
                </h3>
                <p class="mt-1.5 max-w-sm text-sm text-muted-foreground">
                    {{ activeTab === 'unread' ? 'Kamu telah membaca seluruh informasi penting terbaru.' : 'Belum ada notifikasi baru untuk saat ini.' }}
                </p>
            </div>

            <!-- Feed -->
            <div v-else class="space-y-3">
                <article
                    v-for="(notif, idx) in filteredList"
                    :key="notif.id"
                    @click="markAsRead(notif.id, idx)"
                    :class="[
                        'ui-card group relative cursor-pointer p-4 transition-all duration-200 hover:shadow-card-hover sm:p-5',
                        !notif.is_read ? 'border-primary/40 bg-gradient-to-r from-primary/[0.04] to-card' : 'opacity-90 hover:opacity-100'
                    ]"
                >
                    <span v-if="!notif.is_read" class="absolute right-4 top-4 flex items-center gap-1.5">
                        <span class="h-2.5 w-2.5 animate-pulse rounded-full bg-primary"></span>
                    </span>

                    <div class="flex items-start gap-3.5 sm:gap-4">
                        <span :class="['icon-box shrink-0 border shadow-sm', getIconTheme(notif.type)]">
                            <Icon :icon="getIcon(notif.type)" class="h-5 w-5 sm:h-6 sm:w-6" />
                        </span>

                        <div class="min-w-0 flex-1 pr-4">
                            <div class="mb-1 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                <h4 :class="['text-sm tracking-tight', !notif.is_read ? 'font-bold text-foreground' : 'font-semibold text-foreground/90']">
                                    {{ notif.title }}
                                </h4>
                                <span class="flex shrink-0 items-center gap-1 text-[11px] text-muted-foreground">
                                    <Icon icon="mdi:clock-outline" class="h-3 w-3" />
                                    {{ timeAgo(notif.created_at) }}
                                </span>
                            </div>

                            <p class="line-clamp-3 text-sm leading-relaxed text-muted-foreground">
                                {{ notif.message }}
                            </p>

                            <div v-if="notif.action_url" class="mt-2.5">
                                <Link
                                    :href="notif.action_url"
                                    class="inline-flex items-center gap-1 text-xs font-semibold text-primary transition hover:text-primary/80"
                                    @click.stop
                                >
                                    <span>Buka Tautan</span>
                                    <Icon icon="mdi:arrow-right" class="h-3.5 w-3.5" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </article>
            </div>

            <Pagination :links="notifications.links" class="mt-6" />
        </div>
    </UserLayout>
</template>
