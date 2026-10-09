<script setup>
import { inject, ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
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
        announcement: 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
        sps_reminder: 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
        practice_result: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
    };
    return themes[type] || 'bg-primary/10 text-primary border-primary/20';
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

const remainingUnread = computed(() => {
    return localNotifications.value.filter(n => !n.is_read).length;
});

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
</script>

<template>
    <UserLayout>
        <Head title="Pusat Notifikasi - KPM SMART" />

        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <Icon icon="mdi:bell-badge-outline" class="w-5 h-5" />
                </span>
                <span>Pusat Notifikasi</span>
            </div>
        </template>
        <template #header-sub>
            Informasi penting seputar jadwal tugas, pengumuman, dan evaluasi hasil belajarmu
        </template>

        <div class="max-w-4xl mx-auto space-y-6">

            <!-- Filter & Mark All Read Toolbar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-1.5 p-1 bg-muted/60 rounded-xl w-fit">
                    <button @click="activeTab = 'all'"
                            :class="['px-3.5 py-1.5 rounded-lg text-xs font-semibold transition', activeTab === 'all' ? 'bg-card text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground']">
                        Semua
                    </button>
                    <button @click="activeTab = 'unread'"
                            :class="['px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5', activeTab === 'unread' ? 'bg-card text-primary shadow-sm' : 'text-muted-foreground hover:text-foreground']">
                        <span>Belum Dibaca</span>
                        <span v-if="remainingUnread > 0" class="px-1.5 py-0.2 rounded-full text-[10px] bg-primary text-primary-foreground font-bold">
                            {{ remainingUnread }}
                        </span>
                    </button>
                    <button @click="activeTab = 'announcements'"
                            :class="['px-3.5 py-1.5 rounded-lg text-xs font-semibold transition', activeTab === 'announcements' ? 'bg-card text-amber-600 shadow-sm' : 'text-muted-foreground hover:text-foreground']">
                        Pengumuman
                    </button>
                </div>

                <Button v-if="remainingUnread > 0"
                        variant="outline"
                        size="sm"
                        @click="markAllRead"
                        :disabled="processing"
                        class="gap-1.5 text-xs font-semibold shadow-sm">
                    <Icon icon="mdi:check-all" class="w-4 h-4 text-primary" />
                    <span>{{ processing ? 'Memproses...' : 'Tandai Semua Dibaca' }}</span>
                </Button>
            </div>

            <!-- Notification Feed List -->
            <div v-if="filteredList.length === 0"
                 class="text-center py-20 bg-card rounded-2xl border border-border/60 shadow-sm p-6">
                <div class="w-16 h-16 rounded-2xl bg-muted/70 flex items-center justify-center mx-auto mb-4 text-muted-foreground">
                    <Icon icon="mdi:bell-check-outline" class="w-8 h-8" />
                </div>
                <h3 class="text-base font-bold text-foreground mb-1">
                    {{ activeTab === 'unread' ? 'Semua Notifikasi Sudah Dibaca' : 'Tidak Ada Notifikasi' }}
                </h3>
                <p class="text-xs text-muted-foreground max-w-sm mx-auto">
                    {{ activeTab === 'unread' ? 'Kamu telah membaca seluruh informasi penting terbaru.' : 'Belum ada notifikasi baru untuk saat ini.' }}
                </p>
            </div>

            <div v-else class="space-y-3">
                <div v-for="(notif, idx) in filteredList" :key="notif.id"
                     @click="markAsRead(notif.id, idx)"
                     :class="[
                         'group relative bg-card rounded-2xl border p-4 sm:p-5 transition-all duration-200 cursor-pointer shadow-sm hover:shadow-md',
                         !notif.is_read
                             ? 'border-primary/40 bg-gradient-to-r from-primary/[0.04] to-card'
                             : 'border-border/60 opacity-90 hover:opacity-100'
                     ]">

                    <!-- Unread pulse dot -->
                    <div v-if="!notif.is_read" class="absolute top-4 right-4 flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-primary animate-pulse"></span>
                    </div>

                    <div class="flex items-start gap-3.5 sm:gap-4">
                        <div :class="['w-10 h-10 sm:w-11 sm:h-11 rounded-xl border flex items-center justify-center flex-shrink-0 shadow-sm', getIconTheme(notif.type)]">
                            <Icon :icon="getIcon(notif.type)" class="w-5 h-5 sm:w-6 sm:h-6" />
                        </div>

                        <div class="flex-1 min-w-0 pr-4">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-1">
                                <h4 :class="['text-sm tracking-tight', !notif.is_read ? 'font-bold text-foreground' : 'font-semibold text-foreground/90']">
                                    {{ notif.title }}
                                </h4>
                                <span class="text-[11px] text-muted-foreground flex items-center gap-1 shrink-0">
                                    <Icon icon="mdi:clock-outline" class="w-3 h-3" />
                                    {{ timeAgo(notif.created_at) }}
                                </span>
                            </div>

                            <p class="text-xs sm:text-sm text-muted-foreground leading-relaxed line-clamp-3">
                                {{ notif.message }}
                            </p>

                            <!-- Link to target if available -->
                            <div v-if="notif.action_url" class="mt-2.5">
                                <Link :href="notif.action_url"
                                      class="inline-flex items-center gap-1 text-xs font-semibold text-primary hover:text-primary/80 transition">
                                    <span>Buka Tautan</span>
                                    <Icon icon="mdi:arrow-right" class="w-3.5 h-3.5" />
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <Pagination :links="notifications.links" class="mt-6" />
        </div>
    </UserLayout>
</template>
