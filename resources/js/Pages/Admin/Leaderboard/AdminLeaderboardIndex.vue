<script setup>
import { inject, ref, computed, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Select from '@/Components/ui/select/Select.vue';
import Input from '@/Components/ui/input/Input.vue';

const route = inject('route');

const props = defineProps({
    leaderboard: { type: Array, default: () => [] },
    availableKelas: { type: Array, default: () => [] },
    availableLevel: { type: Array, default: () => [] },
    selectedBidang: { type: String, default: '' },
    selectedKelas: { type: String, default: '' },
    selectedLevel: { type: String, default: '' },
    totalStudents: { type: Number, default: 0 },
    attemptedStudents: { type: Number, default: 0 },
    notAttemptedStudents: { type: Number, default: 0 },
    bidangStats: { type: Object, default: () => ({}) },
});

/* ============================ FILTER STATE ============================ */
const bidangVal = ref(props.selectedBidang || '');
const kelasVal = ref(props.selectedKelas || '');
const levelVal = ref(props.selectedLevel || '');
const searchQuery = ref('');

watch(() => props.selectedBidang, (val) => { bidangVal.value = val || ''; });
watch(() => props.selectedKelas, (val) => { kelasVal.value = val || ''; });
watch(() => props.selectedLevel, (val) => { levelVal.value = val || ''; });

const bidangTabs = computed(() => [
    { value: '', label: 'Semua Bidang', count: props.attemptedStudents, icon: 'mdi:view-grid-outline', color: 'text-primary' },
    { value: 'Matematika', label: 'Matematika', count: props.bidangStats?.Matematika || 0, icon: 'mdi:calculator-variant', color: 'text-blue-600' },
    { value: 'IPA', label: 'IPA', count: props.bidangStats?.IPA || 0, icon: 'mdi:flask-outline', color: 'text-emerald-600' },
]);

function applyFilters(overrides = {}) {
    const params = {};
    const b = 'bidang' in overrides ? overrides.bidang : bidangVal.value;
    const k = 'kelas' in overrides ? overrides.kelas : kelasVal.value;
    const l = 'level' in overrides ? overrides.level : levelVal.value;

    bidangVal.value = b;
    kelasVal.value = k;
    levelVal.value = l;

    if (b) params.bidang = b;
    if (k) params.kelas = k;
    if (l) params.level = l;

    router.get(route('admin.leaderboard'), params, {
        preserveState: true,
        replace: true,
        preserveScroll: true,
    });
}

function resetFilters() {
    applyFilters({ bidang: '', kelas: '', level: '' });
}

function onKelasChange(e) {
    applyFilters({ kelas: e.target.value });
}

function onLevelChange(e) {
    applyFilters({ level: e.target.value });
}

const hasFilter = computed(() => !!(bidangVal.value || kelasVal.value || levelVal.value));

const activeBidangLabel = computed(() => bidangVal.value || 'Semua Bidang');

/* ============================ FILTERED LIST & PODIUM ============================ */
const displayedLeaderboard = computed(() => {
    if (!searchQuery.value.trim()) {
        return props.leaderboard;
    }
    const q = searchQuery.value.toLowerCase();
    return props.leaderboard.filter(e => {
        const name = (e.user?.name || '').toLowerCase();
        const email = (e.user?.email || '').toLowerCase();
        const school = (e.user?.school_name || '').toLowerCase();
        return name.includes(q) || email.includes(q) || school.includes(q);
    });
});

const topThree = computed(() => props.leaderboard.slice(0, 3));

/** Susun podium: juara 2 di kiri, juara 1 di tengah, juara 3 di kanan */
const podiumOrder = computed(() => {
    const list = [...topThree.value];
    if (list.length < 3) return list;
    const byRank = (r) => list.find(e => e.rank === r);
    return [byRank(2), byRank(1), byRank(3)].filter(Boolean);
});

/* ============================ THEMES ============================ */
function getRankTheme(rank) {
    if (rank === 1) return {
        medal: 'mdi:crown',
        badgeBg: 'bg-gradient-to-r from-amber-500 to-amber-600 text-white',
        border: 'border-amber-300',
        bg: 'bg-gradient-to-b from-amber-50/80 via-white to-amber-50/20',
        avatarBorder: 'border-amber-400 ring-amber-400/30',
        text: 'text-amber-600',
        height: 'lg:pb-8 lg:-mt-6 shadow-xl',
    };
    if (rank === 2) return {
        medal: 'mdi:medal',
        badgeBg: 'bg-gradient-to-r from-slate-400 to-slate-500 text-white',
        border: 'border-slate-300',
        bg: 'bg-gradient-to-b from-slate-50/80 via-white to-slate-50/20',
        avatarBorder: 'border-slate-400 ring-slate-400/30',
        text: 'text-slate-600',
        height: 'shadow-lg',
    };
    if (rank === 3) return {
        medal: 'mdi:medal',
        badgeBg: 'bg-gradient-to-r from-amber-700 to-amber-800 text-white',
        border: 'border-amber-600/30',
        bg: 'bg-gradient-to-b from-amber-50/50 via-white to-amber-50/10',
        avatarBorder: 'border-amber-600 ring-amber-600/30',
        text: 'text-amber-700',
        height: 'shadow-lg',
    };
    return {
        medal: '',
        badgeBg: 'bg-muted text-muted-foreground',
        border: 'border-border',
        bg: 'bg-card',
        avatarBorder: 'border-border',
        text: 'text-muted-foreground',
        height: '',
    };
}

function bidangBadgeClass(group) {
    if (group === 'Matematika') return 'border-blue-200 bg-blue-50 text-blue-700';
    if (group === 'IPA') return 'border-emerald-200 bg-emerald-50 text-emerald-700';
    return 'border-border bg-muted text-muted-foreground';
}

function bidangBadgeIcon(group) {
    if (group === 'Matematika') return 'mdi:calculator-variant';
    if (group === 'IPA') return 'mdi:flask-outline';
    return 'mdi:school-outline';
}
</script>

<template>
    <AdminLayout>
        <Head title="Papan Peringkat - Admin" />

        <template #header-title>
            <div class="flex items-center gap-2.5">
                <span class="w-9 h-9 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <Icon icon="mdi:trophy-outline" class="w-5 h-5" />
                </span>
                <span>Papan Peringkat</span>
            </div>
        </template>
        <template #header-sub>
            Peringkat prestasi seluruh siswa tersegmen per Kelas, Level, dan Bidang Studi
        </template>

        <div class="space-y-6 pb-12">
            <!-- ============================================================== -->
            <!-- 1. STATISTIK KARTU RINGKASAN                                   -->
            <!-- ============================================================== -->
            <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                <div class="bg-card rounded-2xl border border-border/70 p-4 sm:p-5 shadow-xs hover:shadow-sm transition-all">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                            <Icon icon="mdi:account" class="w-6 h-6" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] sm:text-xs text-muted-foreground font-semibold uppercase tracking-wider">Total Siswa</p>
                            <p class="text-2xl sm:text-3xl font-black tracking-tight text-foreground">{{ totalStudents }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-card rounded-2xl border border-border/70 p-4 sm:p-5 shadow-xs hover:shadow-sm transition-all">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0">
                            <Icon icon="mdi:check-decagram-outline" class="w-6 h-6" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] sm:text-xs text-muted-foreground font-semibold uppercase tracking-wider">Pernah Mengerjakan</p>
                            <p class="text-2xl sm:text-3xl font-black tracking-tight text-emerald-600">{{ attemptedStudents }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-card rounded-2xl border border-border/70 p-4 sm:p-5 shadow-xs hover:shadow-sm transition-all">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center shrink-0">
                            <Icon icon="mdi:clock-outline" class="w-6 h-6" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] sm:text-xs text-muted-foreground font-semibold uppercase tracking-wider">Belum Mengerjakan</p>
                            <p class="text-2xl sm:text-3xl font-black tracking-tight text-amber-600">{{ notAttemptedStudents }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-card rounded-2xl border border-border/70 p-4 sm:p-5 shadow-xs hover:shadow-sm transition-all">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center shrink-0">
                            <Icon icon="mdi:filter-check-outline" class="w-6 h-6" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] sm:text-xs text-muted-foreground font-semibold uppercase tracking-wider">Sedang Ditampilkan</p>
                            <p class="text-2xl sm:text-3xl font-black tracking-tight text-indigo-600">{{ displayedLeaderboard.length }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- 2. FILTER MULTI-DIMENSI: BIDANG, KELAS, LEVEL & SEARCH         -->
            <!-- ============================================================== -->
            <div class="bg-card rounded-3xl border border-border/70 p-5 sm:p-6 shadow-sm space-y-4">
                <!-- Tab Bidang Studi -->
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <button
                            v-for="tab in bidangTabs"
                            :key="tab.value"
                            type="button"
                            class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-xs sm:text-sm font-bold transition-all duration-200"
                            :class="bidangVal === tab.value
                                ? 'border-primary bg-primary text-primary-foreground shadow-sm'
                                : 'border-border/70 bg-background text-muted-foreground hover:border-primary/40 hover:text-foreground'"
                            @click="applyFilters({ bidang: tab.value })"
                        >
                            <Icon :icon="tab.icon" class="h-4 w-4" />
                            <span>{{ tab.label }}</span>
                            <span
                                class="rounded-full px-2 py-0.5 text-[10px] font-black"
                                :class="bidangVal === tab.value ? 'bg-primary-foreground/20 text-primary-foreground' : 'bg-muted text-muted-foreground'"
                            >
                                {{ tab.count }}
                            </span>
                        </button>
                    </div>

                    <button
                        v-if="hasFilter"
                        type="button"
                        class="inline-flex h-9 items-center gap-1.5 rounded-xl border border-border/80 bg-background px-3.5 text-xs font-semibold text-muted-foreground transition hover:border-rose-300 hover:text-rose-600"
                        @click="resetFilters"
                    >
                        <Icon icon="mdi:filter-remove-outline" class="h-4 w-4" />
                        Reset Filter
                    </button>
                </div>

                <!-- Baris Dropdown Kelas, Level & Pencarian -->
                <div class="grid grid-cols-1 gap-3 pt-1 sm:grid-cols-3">
                    <!-- Dropdown Filter Kelas -->
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Filter Kelas</label>
                        <Select v-model="kelasVal" class="h-10 text-xs rounded-xl" @change="onKelasChange">
                            <option value="">Semua Kelas</option>
                            <option v-for="k in availableKelas" :key="k" :value="k">Kelas {{ k }}</option>
                        </Select>
                    </div>

                    <!-- Dropdown Filter Level -->
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Filter Level</label>
                        <Select v-model="levelVal" class="h-10 text-xs rounded-xl" @change="onLevelChange">
                            <option value="">Semua Level</option>
                            <option v-for="lvl in availableLevel" :key="lvl" :value="lvl">Level {{ lvl }}</option>
                        </Select>
                    </div>

                    <!-- Input Live Search Siswa -->
                    <div class="space-y-1">
                        <label class="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Cari Nama / Email</label>
                        <div class="relative">
                            <Icon icon="mdi:magnify" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                v-model="searchQuery"
                                placeholder="Cari nama atau email siswa..."
                                class="h-10 rounded-xl pl-9 text-xs bg-background"
                            />
                        </div>
                    </div>
                </div>

                <!-- Indikator Filter Aktif -->
                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-border/50 pt-3 text-xs">
                    <p class="flex flex-wrap items-center gap-1.5 text-[11px] font-semibold text-muted-foreground">
                        <Icon icon="mdi:school-outline" class="h-3.5 w-3.5 text-amber-500" />
                        Peringkat aktif:
                        <span class="rounded-md bg-muted px-2 py-0.5 text-foreground font-bold">{{ activeBidangLabel }}</span>
                        <span class="rounded-md bg-muted px-2 py-0.5 text-foreground font-bold">{{ kelasVal ? 'Kelas ' + kelasVal : 'Semua Kelas' }}</span>
                        <span class="rounded-md bg-muted px-2 py-0.5 text-foreground font-bold">{{ levelVal ? 'Level ' + levelVal : 'Semua Level' }}</span>
                        <span class="text-muted-foreground">diurutkan dari rata-rata nilai tertinggi.</span>
                    </p>

                    <span class="text-[11px] font-semibold text-muted-foreground">
                        Ditemukan: <strong class="text-foreground">{{ displayedLeaderboard.length }}</strong> siswa
                    </span>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- 3. PODIUM TOP 3                                                -->
            <!-- ============================================================== -->
            <div v-if="leaderboard.length >= 3 && !searchQuery.trim()" class="space-y-3">
                <h3 class="flex items-center gap-1.5 text-sm font-bold uppercase tracking-wider text-foreground">
                    <Icon icon="mdi:podium-gold" class="h-4 w-4 text-amber-500" />
                    Top 3 Siswa Terbaik — {{ activeBidangLabel }}
                </h3>

                <div class="grid grid-cols-1 items-end gap-4 pt-6 sm:grid-cols-3">
                    <div
                        v-for="entry in podiumOrder"
                        :key="entry.user?.id"
                        :class="[
                            'relative rounded-3xl border p-5 text-center transition-all duration-300 hover:-translate-y-1',
                            getRankTheme(entry.rank).bg,
                            getRankTheme(entry.rank).border,
                            getRankTheme(entry.rank).height,
                            entry.rank === 1 ? 'ring-2 ring-amber-300/50' : '',
                        ]"
                    >
                        <span
                            :class="[
                                'absolute -top-3.5 left-1/2 inline-flex -translate-x-1/2 items-center gap-1 rounded-full px-3.5 py-1 text-xs font-black shadow-md ring-2 ring-background',
                                getRankTheme(entry.rank).badgeBg,
                            ]"
                        >
                            <Icon :icon="getRankTheme(entry.rank).medal" class="h-3.5 w-3.5" />
                            #{{ entry.rank }}
                        </span>

                        <!-- Avatar -->
                        <div class="mt-2 mb-3 flex justify-center">
                            <div
                                :class="[
                                    'relative flex h-16 w-16 sm:h-20 sm:w-20 items-center justify-center rounded-2xl border-2 p-1 shadow-lg ring-4 bg-card',
                                    getRankTheme(entry.rank).avatarBorder,
                                ]"
                            >
                                <img
                                    v-if="entry.user?.profile_photo_url && !entry.user?.profile_photo_url.includes('ui-avatars.com')"
                                    :src="entry.user?.profile_photo_url"
                                    :alt="entry.user?.name"
                                    class="h-full w-full rounded-xl object-cover"
                                />
                                <div
                                    v-else
                                    class="flex h-full w-full items-center justify-center rounded-xl bg-gradient-to-br from-primary/10 to-indigo-100 text-xl sm:text-2xl font-black text-primary"
                                >
                                    {{ (entry.user?.name || 'U').charAt(0).toUpperCase() }}
                                </div>
                                <span v-if="entry.rank === 1" class="absolute -right-2.5 -top-3.5 text-2xl drop-shadow select-none">👑</span>
                            </div>
                        </div>

                        <h4 class="truncate px-2 text-sm sm:text-base font-bold text-foreground" :title="entry.user?.name">
                            {{ entry.user?.name }}
                        </h4>
                        <p class="truncate text-xs text-muted-foreground" :title="entry.user?.email">
                            {{ entry.user?.email }}
                        </p>

                        <div class="mt-2 flex flex-wrap items-center justify-center gap-1 text-[11px]">
                            <span :class="['inline-flex items-center gap-1 rounded-md border px-2 py-0.5 font-bold', bidangBadgeClass(entry.bidang_group)]">
                                <Icon :icon="bidangBadgeIcon(entry.bidang_group)" class="h-3 w-3" />
                                {{ entry.bidang_group }}
                            </span>
                            <span v-if="entry.kelas" class="rounded-md bg-slate-100 border border-slate-200 px-2 py-0.5 font-semibold text-slate-700">
                                Kelas {{ entry.kelas }}
                            </span>
                            <span v-if="entry.level" class="rounded-md bg-violet-50 border border-violet-200 px-2 py-0.5 font-semibold text-violet-700">
                                Level {{ entry.level }}
                            </span>
                        </div>

                        <div class="mt-4 flex items-center justify-around border-t border-border/50 pt-3">
                            <div>
                                <p class="text-[10px] font-bold uppercase text-muted-foreground">Rata-rata</p>
                                <p class="text-base sm:text-lg font-black text-primary">{{ entry.avg_score }}%</p>
                            </div>
                            <div class="h-6 w-px bg-border/60"></div>
                            <div>
                                <p class="text-[10px] font-bold uppercase text-muted-foreground">Terbaik</p>
                                <p class="text-base sm:text-lg font-black text-foreground">{{ entry.best_score }}%</p>
                            </div>
                            <div class="h-6 w-px bg-border/60"></div>
                            <div>
                                <p class="text-[10px] font-bold uppercase text-muted-foreground">PR</p>
                                <p class="text-base sm:text-lg font-black text-foreground">{{ entry.total_attempts }}×</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- 4. DAFTAR PERINGKAT SISWA (TABEL & MOBILE CARDS)               -->
            <!-- ============================================================== -->
            <div class="bg-card rounded-3xl shadow-sm border border-border/70 overflow-hidden">
                <div class="p-5 sm:p-6 border-b border-border/50 flex flex-wrap items-center justify-between gap-3 bg-muted/20">
                    <div class="flex items-center gap-2">
                        <Icon icon="mdi:format-list-numbered" class="w-5 h-5 text-primary" />
                        <div>
                            <h3 class="text-base sm:text-lg font-bold text-foreground">
                                Tabel Peringkat Siswa
                            </h3>
                            <p class="text-xs text-muted-foreground">
                                Hasil urutan per Kelas, Level, dan Bidang Studi
                            </p>
                        </div>
                    </div>

                    <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-bold text-primary">
                        {{ displayedLeaderboard.length }} Siswa
                    </span>
                </div>

                <!-- Status Kosong -->
                <div v-if="displayedLeaderboard.length === 0" class="p-12 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-muted/60 flex items-center justify-center mx-auto mb-3 text-muted-foreground">
                        <Icon icon="mdi:trophy-outline" class="w-8 h-8" />
                    </div>
                    <p class="font-bold text-foreground">Belum Ada Data Peringkat</p>
                    <p class="text-xs sm:text-sm text-muted-foreground mt-1 max-w-sm mx-auto">
                        Belum ada siswa pada kriteria filter ini yang menyelesaikan tugas PR latihan.
                    </p>
                    <button
                        v-if="hasFilter"
                        type="button"
                        class="mt-4 inline-flex items-center gap-1.5 rounded-xl border border-border bg-background px-4 py-2 text-xs font-semibold text-muted-foreground transition hover:text-foreground"
                        @click="resetFilters"
                    >
                        <Icon icon="mdi:filter-remove-outline" class="h-4 w-4" />
                        Tampilkan Semua Siswa
                    </button>
                </div>

                <!-- Tabel Versi Desktop (>= md) -->
                <div v-else class="hidden md:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border/60 bg-muted/30">
                                <th class="text-center px-4 py-3.5 font-bold text-muted-foreground text-xs uppercase tracking-wider w-16">Rank</th>
                                <th class="text-left px-5 py-3.5 font-bold text-muted-foreground text-xs uppercase tracking-wider">Siswa</th>
                                <th class="text-left px-4 py-3.5 font-bold text-muted-foreground text-xs uppercase tracking-wider">Bidang</th>
                                <th class="text-center px-4 py-3.5 font-bold text-muted-foreground text-xs uppercase tracking-wider">Kelas</th>
                                <th class="text-center px-4 py-3.5 font-bold text-muted-foreground text-xs uppercase tracking-wider">Level</th>
                                <th class="text-center px-4 py-3.5 font-bold text-muted-foreground text-xs uppercase tracking-wider">PR Selesai</th>
                                <th class="text-right px-5 py-3.5 font-bold text-muted-foreground text-xs uppercase tracking-wider">Rata-rata</th>
                                <th class="text-right px-5 py-3.5 font-bold text-muted-foreground text-xs uppercase tracking-wider">Terbaik</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/50">
                            <tr v-for="entry in displayedLeaderboard" :key="entry.user?.id" class="hover:bg-muted/30 transition-colors">
                                <!-- Rank -->
                                <td class="px-4 py-3.5 text-center">
                                    <span
                                        v-if="entry.rank <= 3"
                                        :class="['inline-flex h-8 w-8 items-center justify-center rounded-xl text-xs font-black text-white shadow-sm', getRankTheme(entry.rank).badgeBg]"
                                    >
                                        {{ entry.rank }}
                                    </span>
                                    <span v-else class="text-xs font-bold text-muted-foreground">#{{ entry.rank }}</span>
                                </td>

                                <!-- Identitas Siswa -->
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-muted/40 flex items-center justify-center shrink-0 border border-border/60 overflow-hidden">
                                            <img
                                                v-if="entry.user?.profile_photo_url && !entry.user?.profile_photo_url.includes('ui-avatars.com')"
                                                :src="entry.user?.profile_photo_url"
                                                :alt="entry.user?.name"
                                                class="w-full h-full object-cover"
                                            />
                                            <span v-else class="text-xs font-black text-primary select-none">
                                                {{ (entry.user?.name || 'U').charAt(0).toUpperCase() }}
                                            </span>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-foreground truncate">{{ entry.user?.name }}</p>
                                            <div class="flex items-center gap-2 text-xs text-muted-foreground truncate">
                                                <span>{{ entry.user?.email }}</span>
                                                <span v-if="entry.user?.school_name" class="text-slate-400">• {{ entry.user?.school_name }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Bidang -->
                                <td class="px-4 py-3.5">
                                    <span :class="['inline-flex items-center gap-1 rounded-md border px-2 py-0.5 text-xs font-bold', bidangBadgeClass(entry.bidang_group)]">
                                        <Icon :icon="bidangBadgeIcon(entry.bidang_group)" class="h-3 w-3" />
                                        {{ entry.bidang_group }}
                                    </span>
                                </td>

                                <!-- Kelas -->
                                <td class="px-4 py-3.5 text-center">
                                    <span v-if="entry.kelas" class="rounded-md bg-slate-100 border border-slate-200 px-2 py-0.5 text-xs font-semibold text-slate-700">
                                        {{ entry.kelas }}
                                    </span>
                                    <span v-else class="text-xs text-muted-foreground">-</span>
                                </td>

                                <!-- Level -->
                                <td class="px-4 py-3.5 text-center">
                                    <span v-if="entry.level" class="rounded-md bg-violet-50 border border-violet-200 px-2 py-0.5 text-xs font-semibold text-violet-700">
                                        {{ entry.level }}
                                    </span>
                                    <span v-else class="text-xs text-muted-foreground">-</span>
                                </td>

                                <!-- Total PR Selesai -->
                                <td class="px-4 py-3.5 text-center">
                                    <span class="text-xs font-bold text-foreground">{{ entry.total_attempts }}×</span>
                                </td>

                                <!-- Rata-rata -->
                                <td class="px-5 py-3.5 text-right">
                                    <span class="text-sm font-black text-primary">{{ entry.avg_score }}%</span>
                                </td>

                                <!-- Terbaik -->
                                <td class="px-5 py-3.5 text-right">
                                    <span class="text-sm font-extrabold text-foreground">{{ entry.best_score }}%</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Kartu Versi Mobile (< md) -->
                <div v-if="displayedLeaderboard.length > 0" class="md:hidden divide-y divide-border/40">
                    <div v-for="entry in displayedLeaderboard" :key="entry.user?.id" class="p-4 space-y-3">
                        <div class="flex items-center gap-3">
                            <span
                                v-if="entry.rank <= 3"
                                :class="['inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-xs font-black text-white shadow-sm', getRankTheme(entry.rank).badgeBg]"
                            >
                                {{ entry.rank }}
                            </span>
                            <span v-else class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-muted text-xs font-bold text-muted-foreground">
                                #{{ entry.rank }}
                            </span>

                            <div class="w-10 h-10 rounded-full bg-muted/40 flex items-center justify-center shrink-0 border border-border/60 overflow-hidden">
                                <img
                                    v-if="entry.user?.profile_photo_url && !entry.user?.profile_photo_url.includes('ui-avatars.com')"
                                    :src="entry.user?.profile_photo_url"
                                    :alt="entry.user?.name"
                                    class="w-full h-full object-cover"
                                />
                                <span v-else class="text-xs font-black text-primary select-none">
                                    {{ (entry.user?.name || 'U').charAt(0).toUpperCase() }}
                                </span>
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-sm text-foreground truncate">{{ entry.user?.name }}</p>
                                <div class="mt-1 flex flex-wrap items-center gap-1.5">
                                    <span :class="['inline-flex items-center gap-1 rounded-md border px-2 py-0.2 text-[10px] font-bold', bidangBadgeClass(entry.bidang_group)]">
                                        <Icon :icon="bidangBadgeIcon(entry.bidang_group)" class="h-3 w-3" />
                                        {{ entry.bidang_group }}
                                    </span>
                                    <span v-if="entry.kelas" class="rounded-md bg-slate-100 border border-slate-200 px-2 py-0.2 text-[10px] font-semibold text-slate-700">
                                        Kelas {{ entry.kelas }}
                                    </span>
                                    <span v-if="entry.level" class="rounded-md bg-violet-50 border border-violet-200 px-2 py-0.2 text-[10px] font-semibold text-violet-700">
                                        Level {{ entry.level }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- 3 Kotak Statistik Mobile -->
                        <div class="grid grid-cols-3 gap-2 text-center bg-muted/40 rounded-2xl p-2.5">
                            <div>
                                <p class="text-[10px] font-bold uppercase text-muted-foreground">Rata-rata</p>
                                <p class="text-sm font-black text-primary">{{ entry.avg_score }}%</p>
                            </div>
                            <div class="border-x border-border/50">
                                <p class="text-[10px] font-bold uppercase text-muted-foreground">Terbaik</p>
                                <p class="text-sm font-extrabold text-foreground">{{ entry.best_score }}%</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase text-muted-foreground">PR Selesai</p>
                                <p class="text-sm font-extrabold text-foreground">{{ entry.total_attempts }}×</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
