<script setup>
import { inject, ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import UserLayout from '@/Layouts/UserLayout.vue';
import Pagination from '@/Components/shared/Pagination.vue';
import { timeAgo } from '@/lib/utils';

const route = inject('route');

const props = defineProps({
    leaveRequests: { type: Object, required: true },
});

const statusFilter = ref('all');

function statusConfig(s) {
    if (s === 'approved') return { label: 'Disetujui', color: 'border-emerald-200 bg-emerald-50 text-emerald-700', icon: 'mdi:check-circle', bar: 'bg-emerald-500' };
    if (s === 'rejected') return { label: 'Ditolak', color: 'border-rose-200 bg-rose-50 text-rose-700', icon: 'mdi:close-circle', bar: 'bg-rose-500' };
    return { label: 'Menunggu Review', color: 'border-amber-200 bg-amber-50 text-amber-700', icon: 'mdi:clock-outline', bar: 'bg-amber-500' };
}

const filteredRequests = computed(() => {
    if (statusFilter.value === 'all') return props.leaveRequests.data || [];
    return (props.leaveRequests.data || []).filter(item => item.status === statusFilter.value);
});

const tabs = [
    { key: 'all', label: 'Semua' },
    { key: 'pending', label: 'Menunggu' },
    { key: 'approved', label: 'Disetujui' },
    { key: 'rejected', label: 'Ditolak' },
];

function countBy(status) {
    return (props.leaveRequests.data || []).filter(i => i.status === status).length;
}
</script>

<template>
    <UserLayout>
        <Head title="Pengajuan Izin - KPM SMART" />

        <template #header-title>Pengajuan Izin Belajar</template>
        <template #header-sub>Kelola dan pantau status permohonan izin ketidakhadiran kegiatan belajar.</template>
        <template #header-actions>
            <Link :href="route('user.leave-requests.create')" class="btn-primary px-5 py-2.5 text-xs">
                <Icon icon="mdi:plus" class="h-4 w-4" />
                Ajukan Izin Baru
            </Link>
        </template>

        <div class="mx-auto max-w-5xl space-y-6">
            <!-- Filter Status -->
            <div class="seg-tabs anim-fade-in-up flex-wrap">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    @click="statusFilter = tab.key"
                    :class="['seg-tab flex items-center gap-1.5', statusFilter === tab.key ? 'seg-tab-active' : '']"
                >
                    <span>{{ tab.label }}</span>
                    <span
                        v-if="tab.key !== 'all' && countBy(tab.key) > 0"
                        class="rounded-full px-1.5 py-0.5 text-[10px] font-bold"
                        :class="statusFilter === tab.key ? 'bg-primary/10 text-primary' : 'bg-muted text-muted-foreground'"
                    >
                        {{ countBy(tab.key) }}
                    </span>
                </button>
            </div>

            <!-- Kosong -->
            <div v-if="filteredRequests.length === 0" class="empty-state anim-fade-in">
                <div class="empty-state-icon">
                    <Icon icon="mdi:calendar-check-outline" class="h-8 w-8" />
                </div>
                <h3 class="text-base font-bold text-foreground">
                    {{ statusFilter === 'all' ? 'Belum Ada Pengajuan Izin' : 'Tidak Ada Izin Berstatus Tersebut' }}
                </h3>
                <p class="mt-1.5 max-w-sm text-sm text-muted-foreground">
                    {{ statusFilter === 'all' ? 'Jika kamu berhalangan hadir pada jadwal sesi tertentu, ajukan permohonan izin di sini.' : 'Coba ubah filter di atas untuk melihat status lainnya.' }}
                </p>
                <Link v-if="statusFilter === 'all'" :href="route('user.leave-requests.create')" class="btn-primary mt-5 px-5 py-2.5 text-xs">
                    <Icon icon="mdi:plus" class="h-4 w-4" />
                    Ajukan Izin Sekarang
                </Link>
            </div>

            <!-- Kartu Permohonan -->
            <div v-else class="space-y-3.5">
                <article
                    v-for="(item, idx) in filteredRequests"
                    :key="item.id"
                    :style="{ animationDelay: (idx * 50) + 'ms' }"
                    class="ui-card anim-fade-in-up overflow-hidden"
                >
                    <div class="h-1 w-full" :class="statusConfig(item.status).bar"></div>
                    <div class="p-5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0 flex-1 space-y-2">
                                <span class="flex items-center gap-1.5 text-xs font-medium text-muted-foreground">
                                    <Icon icon="mdi:clock-outline" class="h-3.5 w-3.5" />
                                    Diajukan {{ timeAgo(item.created_at) }}
                                </span>
                                <p class="whitespace-pre-line text-sm leading-relaxed text-foreground">{{ item.reason }}</p>
                            </div>

                            <span :class="['chip shrink-0 self-start border', statusConfig(item.status).color]">
                                <Icon :icon="statusConfig(item.status).icon" class="h-3.5 w-3.5" />
                                {{ statusConfig(item.status).label }}
                            </span>
                        </div>

                        <!-- Lampiran -->
                        <div v-if="item.proof_file" class="mt-4 border-t border-border/60 pt-3">
                            <a
                                :href="'/storage/' + item.proof_file"
                                target="_blank"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-primary/20 bg-primary/5 px-3 py-1.5 text-xs font-semibold text-primary transition hover:bg-primary/10"
                            >
                                <Icon icon="mdi:paperclip" class="h-3.5 w-3.5" />
                                Lihat Lampiran Berkas
                            </a>
                        </div>

                        <!-- Catatan Admin -->
                        <div v-if="item.admin_note" class="mt-3.5 rounded-xl border border-border/60 bg-muted/40 p-3.5">
                            <div class="mb-1 flex items-center gap-1.5 text-xs font-bold text-foreground">
                                <Icon icon="mdi:account-tie" class="h-3.5 w-3.5 text-primary" />
                                Catatan dari Admin
                            </div>
                            <p class="text-sm leading-relaxed text-muted-foreground">{{ item.admin_note }}</p>
                        </div>
                    </div>
                </article>
            </div>

            <Pagination :links="leaveRequests.links" class="mt-6" />
        </div>
    </UserLayout>
</template>
