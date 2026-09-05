<script setup>
import { inject, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/ui/badge/Badge.vue';
import Pagination from '@/Components/shared/Pagination.vue';
const route = inject('route');

const props = defineProps({
    sessions: { type: Object, required: true },
    packages: { type: Array, default: () => [] },
    startDate: { type: String, default: '' },
    endDate: { type: String, default: '' },
    stats: { type: Object, default: () => ({}) },
    allBidang: { type: Array, default: () => [] },
    allLevel: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const start = ref(props.startDate);
const end = ref(props.endDate);
const packageId = ref(props.filters?.package_id || '');
const status = ref(props.filters?.status || '');
const bidang = ref(props.filters?.bidang || '');
const level = ref(props.filters?.level || '');

function applyFilters() {
    router.get(route('admin.practice-statistics.index'), {
        start_date: start.value,
        end_date: end.value,
        package_id: packageId.value,
        status: status.value,
        bidang: bidang.value,
        level: level.value,
    }, { preserveState: true, replace: true });
}

function resetFilters() {
    start.value = '';
    end.value = '';
    packageId.value = '';
    status.value = '';
    bidang.value = '';
    level.value = '';
    applyFilters();
}

function formatDate(d) {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function scoreColor(score) {
    if (!score) return 'text-muted-foreground';
    if (score >= 90) return 'text-emerald-600';
    if (score >= 75) return 'text-blue-600';
    if (score >= 60) return 'text-yellow-600';
    return 'text-red-500';
}

function scoreBg(score) {
    if (!score) return '';
    if (score >= 90) return 'bg-emerald-50';
    if (score >= 75) return 'bg-blue-50';
    if (score >= 60) return 'bg-yellow-50';
    return 'bg-red-50';
}

const accuracyPct = () => {
    const s = props.stats;
    if (!s.totalQuestions || s.totalQuestions === 0) return '0.0';
    return ((s.totalCorrect / s.totalQuestions) * 100).toFixed(1);
};
</script>

<template>
    <AdminLayout>
        <Head title="Statistik Aktivitas - Admin" />

        <template #header-title><Icon icon="mdi:chart-bar" class="w-5 h-5 inline-block align-middle mr-2" /> Statistik Aktivitas Praktik</template>
        <template #header-sub>Monitoring aktivitas pengerjaan soal seluruh pengguna</template>

        <!-- Stats Overview -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6 sm:mb-8">
            <div class="bg-card border border-border/60 rounded-2xl p-5 hover:shadow-md transition-all duration-300 anim-fade-in-up anim-delay-1 group">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center group-hover:from-primary/25 group-hover:to-primary/10 transition-all duration-300"><Icon icon="mdi:pencil-outline" class="w-5 h-5 text-primary" /></div>
                    <span class="text-[11px] text-muted-foreground font-medium uppercase tracking-wider">Total Sesi</span>
                </div>
                <p class="text-2xl sm:text-3xl font-extrabold tracking-tight">{{ stats.totalSessions || 0 }}</p>
            </div>
            <div class="bg-card border border-border/60 rounded-2xl p-5 hover:shadow-md transition-all duration-300 anim-fade-in-up anim-delay-2 group">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-blue-500/15 to-blue-500/5 flex items-center justify-center group-hover:from-blue-500/25 group-hover:to-blue-500/10 transition-all duration-300"><Icon icon="mdi:account-group" class="w-5 h-5 text-blue-600" /></div>
                    <span class="text-[11px] text-muted-foreground font-medium uppercase tracking-wider">User Aktif</span>
                </div>
                <p class="text-2xl sm:text-3xl font-extrabold tracking-tight text-blue-600">{{ stats.totalUsers || 0 }}</p>
            </div>
            <div class="bg-card border border-border/60 rounded-2xl p-5 hover:shadow-md transition-all duration-300 anim-fade-in-up anim-delay-3 group">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-fern/15 to-fern/5 flex items-center justify-center group-hover:from-fern/25 group-hover:to-fern/10 transition-all duration-300"><Icon icon="mdi:trending-up" class="w-5 h-5 text-fern" /></div>
                    <span class="text-[11px] text-muted-foreground font-medium uppercase tracking-wider">Rata-rata Skor</span>
                </div>
                <p class="text-2xl sm:text-3xl font-extrabold tracking-tight text-fern">{{ stats.avgScore || '0.0' }}</p>
            </div>
            <div class="bg-card border border-border/60 rounded-2xl p-5 hover:shadow-md transition-all duration-300 anim-fade-in-up anim-delay-4 group">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-emerald-500/15 to-emerald-500/5 flex items-center justify-center group-hover:from-emerald-500/25 group-hover:to-emerald-500/10 transition-all duration-300"><Icon icon="mdi:target" class="w-5 h-5 text-emerald-600" /></div>
                    <span class="text-[11px] text-muted-foreground font-medium uppercase tracking-wider">Akurasi</span>
                </div>
                <p class="text-2xl sm:text-3xl font-extrabold tracking-tight text-emerald-600">{{ accuracyPct() }}%</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-card rounded-2xl border border-border/60 p-5 mb-6 shadow-sm anim-fade-in-up anim-delay-3">
            <h3 class="text-sm font-bold mb-4 inline-flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center"><Icon icon="mdi:magnify" class="w-4 h-4 text-primary" /></div>
                Filter & Export
            </h3>
            <div class="flex flex-col sm:flex-row flex-wrap gap-3">
                <div class="flex flex-col gap-1">
                    <label class="text-xs text-muted-foreground font-medium">Tanggal Mulai</label>
                    <input v-model="start" type="date" class="px-3 py-2.5 text-sm border border-input rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-ring w-full sm:w-40 transition-all duration-200" />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs text-muted-foreground font-medium">Tanggal Akhir</label>
                    <input v-model="end" type="date" class="px-3 py-2.5 text-sm border border-input rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-ring w-full sm:w-40 transition-all duration-200" />
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs text-muted-foreground font-medium">Soal</label>
                    <select v-model="packageId" class="px-3 py-2.5 text-sm border border-input rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-ring w-full sm:w-48 transition-all duration-200">
                        <option value="">Semua Soal</option>
                        <option v-for="pkg in packages" :key="pkg.id" :value="pkg.id">{{ pkg.title }}</option>
                    </select>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs text-muted-foreground font-medium">Bidang</label>
                    <select v-model="bidang" class="px-3 py-2.5 text-sm border border-input rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-ring w-full sm:w-40 transition-all duration-200">
                        <option value="">Semua Bidang</option>
                        <option v-for="b in allBidang" :key="b" :value="b">{{ b }}</option>
                    </select>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs text-muted-foreground font-medium">Level</label>
                    <select v-model="level" class="px-3 py-2.5 text-sm border border-input rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-ring w-full sm:w-40 transition-all duration-200">
                        <option value="">Semua Level</option>
                        <option v-for="l in allLevel" :key="l" :value="l">{{ l }}</option>
                    </select>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs text-muted-foreground font-medium">Status</label>
                    <select v-model="status" class="px-3 py-2.5 text-sm border border-input rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-ring w-full sm:w-36 transition-all duration-200">
                        <option value="">Semua Status</option>
                        <option value="completed">Selesai</option>
                        <option value="in_progress">Berlangsung</option>
                    </select>
                </div>
                <div class="flex items-end gap-2 flex-wrap">
                    <button @click="applyFilters" class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-primary text-primary-foreground text-sm font-semibold rounded-xl hover:shadow-md hover:shadow-primary/20 transition-all duration-300 active:scale-[0.97]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        Filter
                    </button>
                    <button @click="resetFilters" class="inline-flex items-center gap-1.5 px-5 py-2.5 border border-border text-sm font-semibold rounded-xl hover:bg-muted transition-all duration-200 active:scale-[0.97]">
                        Reset
                    </button>
                    <a :href="route('admin.practice-statistics.export-excel') + '?start_date=' + start + '&end_date=' + end + '&package_id=' + packageId + '&status=' + status + '&bidang=' + bidang + '&level=' + level"
                       class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:shadow-lg hover:shadow-emerald-500/25 transition-all duration-300 active:scale-[0.97]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        Excel
                    </a>
                    <a :href="route('admin.practice-statistics.export-pdf') + '?start_date=' + start + '&end_date=' + end + '&package_id=' + packageId + '&status=' + status + '&bidang=' + bidang + '&level=' + level"
                       class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-red-600 text-white text-sm font-semibold rounded-xl hover:shadow-lg hover:shadow-red-500/25 transition-all duration-300 active:scale-[0.97]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        PDF
                    </a>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-card rounded-2xl border border-border/60 shadow-sm overflow-hidden mb-6 anim-fade-in-up anim-delay-4">
            <div class="px-5 py-4 border-b border-border/60 flex items-center justify-between bg-gradient-to-r from-muted/40 to-muted/20">
                <h3 class="font-bold text-sm">Data Sesi Pengerjaan</h3>
                <span class="text-xs text-muted-foreground bg-muted/50 px-2.5 py-1 rounded-lg font-medium">{{ sessions.total || 0 }} data</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border/60 bg-muted/20">
                            <th class="text-left px-5 py-4 text-xs font-semibold text-muted-foreground whitespace-nowrap uppercase tracking-wider">#</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold text-muted-foreground whitespace-nowrap uppercase tracking-wider">Pengguna</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold text-muted-foreground whitespace-nowrap uppercase tracking-wider">Soal</th>
                            <th class="text-center px-5 py-4 text-xs font-semibold text-muted-foreground whitespace-nowrap uppercase tracking-wider">Soal</th>
                            <th class="text-center px-5 py-4 text-xs font-semibold text-muted-foreground whitespace-nowrap uppercase tracking-wider">Benar</th>
                            <th class="text-center px-5 py-4 text-xs font-semibold text-muted-foreground whitespace-nowrap uppercase tracking-wider">Nilai</th>
                            <th class="text-center px-5 py-4 text-xs font-semibold text-muted-foreground whitespace-nowrap uppercase tracking-wider">Status</th>
                            <th class="text-left px-5 py-4 text-xs font-semibold text-muted-foreground whitespace-nowrap uppercase tracking-wider">Tanggal</th>
                            <th class="text-center px-5 py-4 text-xs font-semibold text-muted-foreground whitespace-nowrap uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/50">
                        <tr v-if="sessions.data.length === 0">
                            <td colspan="9" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center gap-4">
                                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-muted to-muted/50 flex items-center justify-center">
                                        <Icon icon="mdi:magnify" class="w-10 h-10 text-muted-foreground/40" />
                                    </div>
                                    <div>
                                        <p class="font-semibold text-muted-foreground text-base">Tidak ada data yang ditemukan</p>
                                        <p class="text-sm text-muted-foreground/60 mt-1.5">Coba ubah filter pencarian Anda</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="(s, idx) in sessions.data" :key="s.id"
                            class="border-b border-border/40 last:border-b-0 hover:bg-muted/50 transition-all duration-200 cursor-pointer"
                            :style="{ animationDelay: `${idx * 30}ms` }">
                            <td class="px-5 py-4 text-xs text-muted-foreground font-mono">{{ (sessions.from || 0) + idx }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center text-xs font-bold text-primary flex-shrink-0 ring-2 ring-background shadow-sm">
                                        {{ (s.user?.name || '?').charAt(0).toUpperCase() }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-xs truncate max-w-[120px]">{{ s.user?.name || '-' }}</p>
                                        <p class="text-[10px] text-muted-foreground truncate max-w-[120px]">{{ s.user?.email || '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <p class="text-xs font-semibold truncate max-w-[150px]">{{ s.package?.title || '-' }}</p>
                                <p v-if="s.card_id" class="text-[10px] text-muted-foreground mt-0.5 truncate max-w-[150px]">Card: {{ s.card_id }}</p>
                            </td>
                            <td class="px-5 py-4 text-center text-xs font-medium">{{ s.total_question || 0 }}</td>
                            <td class="px-5 py-4 text-center">
                                <span class="text-xs font-bold text-emerald-600">{{ s.correct_answer || 0 }}</span>
                                <span class="text-[10px] text-muted-foreground">/{{ s.total_question || 0 }}</span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span :class="['text-sm font-bold px-2.5 py-1 rounded-lg', scoreColor(s.total_score), scoreBg(s.total_score)]">
                                    {{ s.total_score != null ? Number(s.total_score).toFixed(1) : '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span :class="[
                                    'inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-semibold',
                                    s.status === 'completed' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-200'
                                ]">
                                    <template v-if="s.status === 'completed'"><Icon icon="mdi:check-circle" class="w-3 h-3 inline-block align-middle mr-1" /> Selesai</template><template v-else><Icon icon="mdi:clock-outline" class="w-3 h-3 inline-block align-middle mr-1" /> Berlangsung</template>
                                </span>
                            </td>
                            <td class="px-5 py-4 text-xs text-muted-foreground whitespace-nowrap">{{ formatDate(s.created_at) }}</td>
                            <td class="px-5 py-4 text-center">
                                <Link :href="route('admin.practice-statistics.show', s.id)"
                                      class="inline-flex items-center gap-1 text-xs font-semibold text-primary hover:text-primary/80 hover:underline transition">
                                    Detail →
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <Pagination :links="sessions.links" />
    </AdminLayout>
</template>
