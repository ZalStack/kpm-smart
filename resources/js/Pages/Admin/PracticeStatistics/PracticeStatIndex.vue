<script setup>
import { inject, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Badge from '@/Components/ui/badge/Badge.vue';
import Button from '@/Components/ui/button/Button.vue';
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
    if (score == null) return 'text-muted-foreground';
    if (score >= 90) return 'text-emerald-600 bg-emerald-500/10 border-emerald-500/20';
    if (score >= 75) return 'text-blue-600 bg-blue-500/10 border-blue-500/20';
    if (score >= 60) return 'text-amber-600 bg-amber-500/10 border-amber-500/20';
    return 'text-rose-600 bg-rose-500/10 border-rose-500/20';
}

const accuracyPct = () => {
    const s = props.stats;
    if (!s.totalQuestions || s.totalQuestions === 0) return '0.0';
    return ((s.totalCorrect / s.totalQuestions) * 100).toFixed(1);
};
</script>

<template>
    <AdminLayout>
        <Head title="Statistik Aktivitas Praktik - Admin" />

        <template #header-title>Statistik Aktivitas Praktik</template>
        <template #header-sub>Monitoring dan analitik hasil pengerjaan soal seluruh pengguna</template>

        <div class="space-y-6">
            <!-- Stats Overview -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
                <div class="bg-card border border-border/60 rounded-2xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-all duration-300 anim-fade-in-up anim-delay-1 group">
                    <div class="flex items-center gap-3 mb-2.5">
                        <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary group-hover:scale-105 transition-transform">
                            <Icon icon="mdi:book-open-variant-outline" class="w-5 h-5" />
                        </div>
                        <span class="text-[11px] text-muted-foreground font-bold uppercase tracking-wider">Total Sesi</span>
                    </div>
                    <p class="text-2xl sm:text-3xl font-black tracking-tight text-foreground">{{ stats.totalSessions || 0 }}</p>
                    <p class="text-[11px] text-muted-foreground mt-1">Sesi latihan dikerjakan</p>
                </div>

                <div class="bg-card border border-border/60 rounded-2xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-all duration-300 anim-fade-in-up anim-delay-2 group">
                    <div class="flex items-center gap-3 mb-2.5">
                        <div class="w-10 h-10 rounded-xl bg-blue-500/10 flex items-center justify-center text-blue-600 group-hover:scale-105 transition-transform">
                            <Icon icon="mdi:account-group-outline" class="w-5 h-5" />
                        </div>
                        <span class="text-[11px] text-muted-foreground font-bold uppercase tracking-wider">User Aktif</span>
                    </div>
                    <p class="text-2xl sm:text-3xl font-black tracking-tight text-blue-600">{{ stats.totalUsers || 0 }}</p>
                    <p class="text-[11px] text-muted-foreground mt-1">Siswa telah berpartisipasi</p>
                </div>

                <div class="bg-card border border-border/60 rounded-2xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-all duration-300 anim-fade-in-up anim-delay-3 group">
                    <div class="flex items-center gap-3 mb-2.5">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-600 group-hover:scale-105 transition-transform">
                            <Icon icon="mdi:chart-line" class="w-5 h-5" />
                        </div>
                        <span class="text-[11px] text-muted-foreground font-bold uppercase tracking-wider">Rata-rata Skor</span>
                    </div>
                    <p class="text-2xl sm:text-3xl font-black tracking-tight text-amber-600">{{ stats.avgScore || '0.0' }}</p>
                    <p class="text-[11px] text-muted-foreground mt-1">Dari skala maksimal 100</p>
                </div>

                <div class="bg-card border border-border/60 rounded-2xl p-4 sm:p-5 shadow-sm hover:shadow-md transition-all duration-300 anim-fade-in-up anim-delay-4 group">
                    <div class="flex items-center gap-3 mb-2.5">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center text-emerald-600 group-hover:scale-105 transition-transform">
                            <Icon icon="mdi:target-variant" class="w-5 h-5" />
                        </div>
                        <span class="text-[11px] text-muted-foreground font-bold uppercase tracking-wider">Akurasi</span>
                    </div>
                    <p class="text-2xl sm:text-3xl font-black tracking-tight text-emerald-600">{{ accuracyPct() }}%</p>
                    <p class="text-[11px] text-muted-foreground mt-1">Persentase jawaban tepat</p>
                </div>
            </div>

            <!-- Filter & Export Card -->
            <div class="bg-card rounded-2xl border border-border/60 p-5 shadow-sm anim-fade-in-up anim-delay-3 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                            <Icon icon="mdi:filter-outline" class="w-4 h-4" />
                        </div>
                        <h3 class="text-sm font-bold text-foreground">Filter & Export Laporan</h3>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                    <div class="space-y-1">
                        <label class="text-[11px] text-muted-foreground font-semibold">Mulai Dari</label>
                        <input v-model="start" type="date" class="w-full px-3 py-2 text-xs border border-border rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-[11px] text-muted-foreground font-semibold">Sampai Dengan</label>
                        <input v-model="end" type="date" class="w-full px-3 py-2 text-xs border border-border rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition" />
                    </div>
                    <div class="space-y-1">
                        <label class="text-[11px] text-muted-foreground font-semibold">Paket Soal</label>
                        <select v-model="packageId" class="w-full px-3 py-2 text-xs border border-border rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                            <option value="">Semua Paket</option>
                            <option v-for="pkg in packages" :key="pkg.id" :value="pkg.id">{{ pkg.title }}</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[11px] text-muted-foreground font-semibold">Bidang</label>
                        <select v-model="bidang" class="w-full px-3 py-2 text-xs border border-border rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                            <option value="">Semua Bidang</option>
                            <option v-for="b in allBidang" :key="b" :value="b">{{ b }}</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[11px] text-muted-foreground font-semibold">Level</label>
                        <select v-model="level" class="w-full px-3 py-2 text-xs border border-border rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                            <option value="">Semua Level</option>
                            <option v-for="l in allLevel" :key="l" :value="l">{{ l }}</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label class="text-[11px] text-muted-foreground font-semibold">Status</label>
                        <select v-model="status" class="w-full px-3 py-2 text-xs border border-border rounded-xl bg-background focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition">
                            <option value="">Semua Status</option>
                            <option value="completed">Selesai</option>
                            <option value="in_progress">Berlangsung</option>
                        </select>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-border/50">
                    <div class="flex items-center gap-2">
                        <Button @click="applyFilters" size="sm" class="gap-1.5 px-4 rounded-xl text-xs font-bold shadow-sm">
                            <Icon icon="mdi:magnify" class="w-3.5 h-3.5" /> Terapkan Filter
                        </Button>
                        <Button @click="resetFilters" variant="ghost" size="sm" class="rounded-xl text-xs">
                            Reset
                        </Button>
                    </div>

                    <div class="flex items-center gap-2">
                        <a :href="route('admin.practice-statistics.export-excel') + '?start_date=' + start + '&end_date=' + end + '&package_id=' + packageId + '&status=' + status + '&bidang=' + bidang + '&level=' + level"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all active:scale-[0.97]">
                            <Icon icon="mdi:file-excel-box" class="w-4 h-4" /> Export Excel
                        </a>
                        <a :href="route('admin.practice-statistics.export-pdf') + '?start_date=' + start + '&end_date=' + end + '&package_id=' + packageId + '&status=' + status + '&bidang=' + bidang + '&level=' + level"
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-sm transition-all active:scale-[0.97]">
                            <Icon icon="mdi:file-pdf-box" class="w-4 h-4" /> Export PDF
                        </a>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-card rounded-2xl border border-border/60 shadow-sm overflow-hidden anim-fade-in-up anim-delay-4">
                <div class="px-5 py-4 border-b border-border/60 flex items-center justify-between bg-gradient-to-r from-muted/40 to-muted/20">
                    <div class="flex items-center gap-2">
                        <Icon icon="mdi:format-list-checks" class="w-4 h-4 text-primary" />
                        <h3 class="font-bold text-sm text-foreground">Data Sesi Pengerjaan</h3>
                    </div>
                    <span class="text-xs text-muted-foreground bg-muted px-2.5 py-1 rounded-lg font-semibold">{{ sessions.total || 0 }} sesi ditemukan</span>
                </div>

                <!-- Desktop Table View -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="border-b border-border/60 bg-muted/20 text-muted-foreground">
                                <th class="text-left px-5 py-3.5 font-bold uppercase tracking-wider">#</th>
                                <th class="text-left px-5 py-3.5 font-bold uppercase tracking-wider">Siswa</th>
                                <th class="text-left px-5 py-3.5 font-bold uppercase tracking-wider">Paket Latihan</th>
                                <th class="text-center px-5 py-3.5 font-bold uppercase tracking-wider">Soal</th>
                                <th class="text-center px-5 py-3.5 font-bold uppercase tracking-wider">Jawaban Benar</th>
                                <th class="text-center px-5 py-3.5 font-bold uppercase tracking-wider">Nilai</th>
                                <th class="text-center px-5 py-3.5 font-bold uppercase tracking-wider">Status</th>
                                <th class="text-left px-5 py-3.5 font-bold uppercase tracking-wider">Waktu Sesi</th>
                                <th class="text-center px-5 py-3.5 font-bold uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/40">
                            <tr v-if="sessions.data.length === 0">
                                <td colspan="9" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-16 h-16 rounded-2xl bg-muted flex items-center justify-center text-muted-foreground/40">
                                            <Icon icon="mdi:clipboard-text-search-outline" class="w-8 h-8" />
                                        </div>
                                        <div>
                                            <p class="font-bold text-muted-foreground text-sm">Tidak ada sesi pengerjaan</p>
                                            <p class="text-xs text-muted-foreground/70 mt-1">Coba sesuaikan filter atau rentang tanggal pencarian</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="(s, idx) in sessions.data" :key="s.id"
                                class="hover:bg-muted/30 transition-colors">
                                <td class="px-5 py-3.5 text-muted-foreground font-mono font-medium">{{ (sessions.from || 0) + idx }}</td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center text-xs font-bold text-primary flex-shrink-0 ring-1 ring-border shadow-2xs">
                                            {{ (s.user?.name || '?').charAt(0).toUpperCase() }}
                                        </div>
                                        <div class="min-w-0 max-w-[140px]">
                                            <p class="font-bold text-foreground truncate">{{ s.user?.name || '-' }}</p>
                                            <p class="text-[10px] text-muted-foreground truncate">{{ s.user?.email || '' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <p class="font-bold text-foreground truncate max-w-[180px]">{{ s.package?.title || '-' }}</p>
                                    <p v-if="s.card_id" class="text-[10px] text-muted-foreground mt-0.5">Card ID: <code class="font-mono">{{ s.card_id }}</code></p>
                                </td>
                                <td class="px-5 py-3.5 text-center font-semibold text-foreground">{{ s.total_question || 0 }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="font-bold text-emerald-600">{{ s.correct_answer || 0 }}</span>
                                    <span class="text-muted-foreground font-medium"> / {{ s.total_question || 0 }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span :class="['inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-black border', scoreColor(s.total_score)]">
                                        {{ s.total_score != null ? Number(s.total_score).toFixed(1) : '-' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span :class="[
                                        'inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold border',
                                        s.status === 'completed' ? 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20' : 'bg-amber-500/10 text-amber-600 border-amber-500/20'
                                    ]">
                                        <Icon :icon="s.status === 'completed' ? 'mdi:check-circle-outline' : 'mdi:clock-outline'" class="w-3 h-3" />
                                        {{ s.status === 'completed' ? 'Selesai' : 'Berlangsung' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-muted-foreground whitespace-nowrap">{{ formatDate(s.created_at) }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <Link :href="route('admin.practice-statistics.show', s.id)"
                                          class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-primary/10 hover:bg-primary/20 text-primary font-bold text-xs transition-colors">
                                        <span>Detail</span>
                                        <Icon icon="mdi:chevron-right" class="w-3.5 h-3.5" />
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card List View -->
                <div class="md:hidden divide-y divide-border/40">
                    <div v-if="sessions.data.length === 0" class="p-8 text-center">
                        <p class="text-xs text-muted-foreground">Tidak ada data sesi pengerjaan</p>
                    </div>
                    <div v-for="s in sessions.data" :key="s.id" class="p-4 space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center text-xs font-bold text-primary flex-shrink-0">
                                    {{ (s.user?.name || '?').charAt(0).toUpperCase() }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-xs text-foreground truncate">{{ s.user?.name || '-' }}</p>
                                    <p class="text-[10px] text-muted-foreground truncate">{{ s.package?.title || '-' }}</p>
                                </div>
                            </div>
                            <span :class="['px-2 py-0.5 rounded-lg text-xs font-black border', scoreColor(s.total_score)]">
                                {{ s.total_score != null ? Number(s.total_score).toFixed(1) : '-' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-[11px] text-muted-foreground bg-muted/40 p-2.5 rounded-xl">
                            <span>Benar: <strong class="text-emerald-600">{{ s.correct_answer || 0 }}</strong>/{{ s.total_question || 0 }}</span>
                            <span>{{ formatDate(s.created_at) }}</span>
                            <Link :href="route('admin.practice-statistics.show', s.id)" class="text-primary font-bold">
                                Detail →
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <Pagination :links="sessions.links" />
        </div>
    </AdminLayout>
</template>
