<script setup>
import { inject, ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import UserLayout from '@/Layouts/UserLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Pagination from '@/Components/shared/Pagination.vue';
import { timeAgo } from '@/lib/utils';

const route = inject('route');

const props = defineProps({
    leaveRequests: { type: Object, required: true },
});

const statusFilter = ref('all');

function statusConfig(s) {
    if (s === 'approved') return { label: 'Disetujui', color: 'text-emerald-700 bg-emerald-50 dark:bg-emerald-950/40 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800/40', icon: 'mdi:check-circle' };
    if (s === 'rejected') return { label: 'Ditolak', color: 'text-rose-700 bg-rose-50 dark:bg-rose-950/40 dark:text-rose-400 border-rose-200 dark:border-rose-800/40', icon: 'mdi:close-circle' };
    return { label: 'Menunggu Review', color: 'text-amber-700 bg-amber-50 dark:bg-amber-950/40 dark:text-amber-400 border-amber-200 dark:border-amber-800/40', icon: 'mdi:clock-outline' };
}

const filteredRequests = computed(() => {
    if (statusFilter.value === 'all') return props.leaveRequests.data || [];
    return (props.leaveRequests.data || []).filter(item => item.status === statusFilter.value);
});
</script>

<template>
    <UserLayout>
        <Head title="Pengajuan Izin - KPM SMART" />

        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <Icon icon="mdi:calendar-remove-outline" class="w-5 h-5" />
                </span>
                <span>Pengajuan Izin Belajar</span>
            </div>
        </template>
        <template #header-sub>
            Kelola dan pantau status permohonan izin ketidakhadiran kegiatan belajar
        </template>

        <div class="max-w-4xl mx-auto space-y-6">

            <!-- Action Bar & Filter -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-1.5 p-1 bg-muted/60 rounded-xl w-fit">
                    <button @click="statusFilter = 'all'"
                            :class="['px-3.5 py-1.5 rounded-lg text-xs font-semibold transition', statusFilter === 'all' ? 'bg-card text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground']">
                        Semua
                    </button>
                    <button @click="statusFilter = 'pending'"
                            :class="['px-3.5 py-1.5 rounded-lg text-xs font-semibold transition', statusFilter === 'pending' ? 'bg-card text-amber-600 shadow-sm' : 'text-muted-foreground hover:text-foreground']">
                        Menunggu
                    </button>
                    <button @click="statusFilter = 'approved'"
                            :class="['px-3.5 py-1.5 rounded-lg text-xs font-semibold transition', statusFilter === 'approved' ? 'bg-card text-emerald-600 shadow-sm' : 'text-muted-foreground hover:text-foreground']">
                        Disetujui
                    </button>
                    <button @click="statusFilter = 'rejected'"
                            :class="['px-3.5 py-1.5 rounded-lg text-xs font-semibold transition', statusFilter === 'rejected' ? 'bg-card text-rose-600 shadow-sm' : 'text-muted-foreground hover:text-foreground']">
                        Ditolak
                    </button>
                </div>

                <Link :href="route('user.leave-requests.create')">
                    <Button class="w-full sm:w-auto shadow-sm gap-2 font-semibold">
                        <Icon icon="mdi:plus" class="w-4 h-4" /> Ajukan Izin Baru
                    </Button>
                </Link>
            </div>

            <!-- Empty State -->
            <div v-if="filteredRequests.length === 0"
                 class="text-center py-16 bg-card rounded-2xl border border-border/60 shadow-sm p-6">
                <div class="mx-auto w-16 h-16 rounded-2xl bg-muted/80 flex items-center justify-center mb-4 text-muted-foreground">
                    <Icon icon="mdi:calendar-check-outline" class="w-8 h-8" />
                </div>
                <h3 class="text-base font-bold text-foreground mb-1">
                    {{ statusFilter === 'all' ? 'Belum Ada Pengajuan Izin' : 'Tidak Ada Izin Berstatus Tersebut' }}
                </h3>
                <p class="text-xs text-muted-foreground mb-5 max-w-sm mx-auto">
                    {{ statusFilter === 'all' ? 'Jika kamu berhalangan hadir pada jadwal sesi tertentu, ajukan permohonan izin di sini.' : 'Coba ubah filter di atas untuk melihat status lainnya.' }}
                </p>
                <Link v-if="statusFilter === 'all'" :href="route('user.leave-requests.create')">
                    <Button size="sm" class="gap-1.5 font-semibold">
                        <Icon icon="mdi:plus" class="w-4 h-4" /> Ajukan Izin Sekarang
                    </Button>
                </Link>
            </div>

            <!-- Request Cards Grid -->
            <div v-else class="space-y-3.5">
                <div v-for="item in filteredRequests" :key="item.id"
                     class="bg-card rounded-2xl border border-border/60 p-5 shadow-sm hover:shadow-md transition-all duration-200">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                        <div class="space-y-1.5 flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-muted-foreground flex items-center gap-1 font-medium">
                                    <Icon icon="mdi:clock-outline" class="w-3.5 h-3.5" /> Diajukan {{ timeAgo(item.created_at) }}
                                </span>
                            </div>
                            <p class="text-sm font-medium text-foreground leading-relaxed whitespace-pre-line">{{ item.reason }}</p>
                        </div>

                        <!-- Status Badge -->
                        <span :class="['inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border self-start shrink-0', statusConfig(item.status).color]">
                            <Icon :icon="statusConfig(item.status).icon" class="w-3.5 h-3.5" />
                            {{ statusConfig(item.status).label }}
                        </span>
                    </div>

                    <!-- Attachment -->
                    <div v-if="item.proof_file" class="mt-4 pt-3 border-t border-border/40 flex items-center justify-between">
                        <a :href="'/storage/' + item.proof_file" target="_blank"
                           class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary hover:text-primary/80 transition bg-primary/5 px-3 py-1.5 rounded-lg border border-primary/20">
                            <Icon icon="mdi:paperclip" class="w-3.5 h-3.5" /> Lihat Lampiran Berkas
                        </a>
                    </div>

                    <!-- Admin Note Box -->
                    <div v-if="item.admin_note" class="mt-3.5 bg-muted/40 rounded-xl p-3.5 border border-border/60">
                        <div class="flex items-center gap-1.5 text-xs font-bold text-foreground mb-1">
                            <Icon icon="mdi:account-tie" class="w-3.5 h-3.5 text-primary" />
                            Catatan dari Admin:
                        </div>
                        <p class="text-xs text-muted-foreground leading-relaxed">{{ item.admin_note }}</p>
                    </div>
                </div>
            </div>

            <Pagination :links="leaveRequests.links" class="mt-6" />
        </div>
    </UserLayout>
</template>
