<script setup>
import { inject, computed, ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import { Icon } from '@iconify/vue/offline';
import Input from '@/Components/ui/input/Input.vue';
import Select from '@/Components/ui/select/Select.vue';

const route = inject('route');
const page = usePage();

const props = defineProps({
    leaderboard: { type: Array, default: () => [] },
    currentUserRank: { type: Object, default: null },
    bidangLabel: { type: String, default: 'Semua Bidang' },
    selectedBidang: { type: String, default: '' },
    selectedKelas: { type: String, default: '' },
    selectedLevel: { type: String, default: '' },
    availableKelas: { type: Array, default: () => [] },
    availableLevel: { type: Array, default: () => [] },
    myScope: { type: Object, default: () => ({ bidang: '', kelas: '', level: '' }) },
    isMyScope: { type: Boolean, default: false },
    totalParticipants: { type: Number, default: 0 },
});

/* ============================ FILTER STATE ============================ */
const selectedBidangVal = ref(props.selectedBidang || '');
const selectedKelasVal = ref(props.selectedKelas || '');
const selectedLevelVal = ref(props.selectedLevel || '');
const searchQuery = ref('');

// Sinkronkan ref saat props berubah
watch(() => props.selectedBidang, (val) => { selectedBidangVal.value = val || ''; });
watch(() => props.selectedKelas, (val) => { selectedKelasVal.value = val || ''; });
watch(() => props.selectedLevel, (val) => { selectedLevelVal.value = val || ''; });

const bidangTabs = computed(() => [
    { value: '', label: 'Semua Bidang', icon: 'mdi:view-grid-outline' },
    { value: 'Matematika', label: 'Matematika', icon: 'mdi:calculator-variant' },
    { value: 'IPA', label: 'IPA', icon: 'mdi:flask-outline' },
]);

function applyFilters(overrides = {}) {
    const params = {};
    const b = 'bidang' in overrides ? overrides.bidang : selectedBidangVal.value;
    const k = 'kelas' in overrides ? overrides.kelas : selectedKelasVal.value;
    const l = 'level' in overrides ? overrides.level : selectedLevelVal.value;

    selectedBidangVal.value = b;
    selectedKelasVal.value = k;
    selectedLevelVal.value = l;

    if (b !== undefined && b !== null) params.bidang = b === '' ? 'all' : b;
    if (k !== undefined && k !== null) params.kelas = k === '' ? 'all' : k;
    if (l !== undefined && l !== null) params.level = l === '' ? 'all' : l;

    router.get(route('user.leaderboard'), params, {
        preserveState: true,
        replace: true,
        preserveScroll: true,
    });
}

function selectBidang(val) {
    applyFilters({ bidang: val });
}

function onKelasChange(e) {
    applyFilters({ kelas: e.target.value });
}

function onLevelChange(e) {
    applyFilters({ level: e.target.value });
}

function resetToMyCohort() {
    applyFilters({
        bidang: props.myScope?.bidang || '',
        kelas: props.myScope?.kelas || '',
        level: props.myScope?.level || '',
    });
}

function resetAllFilters() {
    applyFilters({
        bidang: '',
        kelas: '',
        level: '',
    });
}

const hasActiveFilters = computed(() => {
    return Boolean(selectedBidangVal.value || selectedKelasVal.value || selectedLevelVal.value);
});

const isViewingMyCohort = computed(() => {
    const myB = props.myScope?.bidang || '';
    const myK = props.myScope?.kelas || '';
    const myL = props.myScope?.level || '';

    return (
        (!myB || selectedBidangVal.value === myB) &&
        (!myK || selectedKelasVal.value === myK) &&
        (!myL || selectedLevelVal.value === myL)
    );
});

/* ============================ PODIUM & LIST DATA ============================ */
const filteredLeaderboard = computed(() => {
    if (!searchQuery.value.trim()) {
        return props.leaderboard;
    }
    const q = searchQuery.value.toLowerCase();
    return props.leaderboard.filter(e => {
        const name = (e.user?.name || '').toLowerCase();
        const school = (e.user?.school_name || '').toLowerCase();
        return name.includes(q) || school.includes(q);
    });
});

const topThree = computed(() => {
    return props.leaderboard.slice(0, 3);
});

/** Susun podium 3 kolom: Juara 2 di kiri, Juara 1 di tengah, Juara 3 di kanan */
const podiumOrder = computed(() => {
    const list = [...topThree.value];
    if (list.length < 3) return list;
    const byRank = (r) => list.find(e => e.rank === r);
    return [byRank(2), byRank(1), byRank(3)].filter(Boolean);
});

/* ============================ THEME & STYLING ============================ */
function getRankTheme(rank) {
    if (rank === 1) return {
        medal: 'mdi:crown',
        badgeBg: 'bg-gradient-to-r from-amber-500 to-amber-600 text-white shadow-amber-500/25',
        border: 'border-amber-300/80 shadow-amber-500/10',
        ring: 'ring-amber-400/40',
        bg: 'bg-gradient-to-b from-amber-50/80 via-white to-amber-50/30',
        avatarBorder: 'border-amber-400 ring-amber-300/40',
        text: 'text-amber-600',
        height: 'lg:-mt-6 lg:pb-8 shadow-xl',
        podiumPedestal: 'bg-gradient-to-t from-amber-200 to-amber-100 border-amber-300 text-amber-900',
    };
    if (rank === 2) return {
        medal: 'mdi:medal',
        badgeBg: 'bg-gradient-to-r from-slate-400 to-slate-500 text-white shadow-slate-500/25',
        border: 'border-slate-300 shadow-slate-500/5',
        ring: 'ring-slate-300/40',
        bg: 'bg-gradient-to-b from-slate-50/80 via-white to-slate-50/30',
        avatarBorder: 'border-slate-400 ring-slate-300/40',
        text: 'text-slate-600',
        height: 'shadow-lg',
        podiumPedestal: 'bg-gradient-to-t from-slate-200 to-slate-100 border-slate-300 text-slate-800',
    };
    if (rank === 3) return {
        medal: 'mdi:medal',
        badgeBg: 'bg-gradient-to-r from-amber-700 to-amber-800 text-white shadow-amber-900/25',
        border: 'border-amber-600/30 shadow-amber-800/5',
        ring: 'ring-amber-600/30',
        bg: 'bg-gradient-to-b from-amber-50/60 via-white to-amber-50/20',
        avatarBorder: 'border-amber-700 ring-amber-600/30',
        text: 'text-amber-800',
        height: 'shadow-lg',
        podiumPedestal: 'bg-gradient-to-t from-amber-300/60 to-amber-200/50 border-amber-400/50 text-amber-950',
    };
    return {
        medal: '',
        badgeBg: 'bg-muted text-muted-foreground',
        border: 'border-border',
        ring: '',
        bg: 'bg-card',
        avatarBorder: 'border-border',
        text: 'text-muted-foreground',
        height: '',
        podiumPedestal: '',
    };
}

function getBidangColor(bidang) {
    if (!bidang) return 'border-slate-200 bg-slate-50 text-slate-700';
    const b = bidang.toUpperCase();
    if (b.includes('MTK') || b.includes('MAT')) return 'border-blue-200 bg-blue-50 text-blue-700';
    if (b.includes('IPA') || b.includes('SAINS')) return 'border-emerald-200 bg-emerald-50 text-emerald-700';
    return 'border-violet-200 bg-violet-50 text-violet-700';
}

function getBidangIcon(bidang) {
    if (!bidang) return 'mdi:school-outline';
    const b = bidang.toUpperCase();
    if (b.includes('MTK') || b.includes('MAT')) return 'mdi:calculator-variant';
    if (b.includes('IPA') || b.includes('SAINS')) return 'mdi:flask-outline';
    return 'mdi:school-outline';
}

const isCurrentUser = (id) => id === page.props.auth?.user?.id;
</script>

<template>
    <UserLayout>
        <Head title="Papan Peringkat Siswa - PR KK" />

        <template #header-title>Papan Peringkat</template>
        <template #header-sub>
            Peringkat prestasi siswa dihitung berdasarkan nilai rata-rata PR pekanan dan nilai terbaik sebagai penentu.
        </template>
        <template #header-actions>
            <div class="flex flex-wrap items-center gap-2">
                <!-- Tombol Shortcut Kembali ke Cohort Saya -->
                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 rounded-2xl border px-3.5 py-2 text-xs font-bold transition-all shadow-xs"
                    :class="isViewingMyCohort
                        ? 'border-primary/40 bg-primary/10 text-primary'
                        : 'border-border bg-card text-muted-foreground hover:border-primary/40 hover:text-foreground'"
                    @click="resetToMyCohort"
                    title="Tampilkan papan peringkat sesuai kelas, level & bidang saya"
                >
                    <Icon icon="mdi:star" class="h-3.5 w-3.5 text-amber-500" />
                    <span>Peringkat Saya</span>
                    <span v-if="isViewingMyCohort" class="ml-1 inline-block h-2 w-2 rounded-full bg-primary"></span>
                </button>
            </div>
        </template>

        <div class="mx-auto max-w-5xl space-y-6 pb-12">
            <!-- ============================================================== -->
            <!-- CARD 1: BANNER STATUS PERINGKAT SAYA                          -->
            <!-- ============================================================== -->
            <section
                v-if="currentUserRank"
                class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-700 via-indigo-600 to-violet-700 p-6 sm:p-7 text-white shadow-xl shadow-indigo-600/15"
            >
                <div class="pointer-events-none absolute -bottom-10 -right-10 h-44 w-44 rounded-full bg-white/10 blur-2xl"></div>
                <div class="pointer-events-none absolute -left-8 -top-8 h-36 w-36 rounded-full bg-cyan-400/20 blur-xl"></div>
                <div class="pointer-events-none absolute right-1/3 top-0 h-24 w-24 rounded-full bg-purple-400/15 blur-lg"></div>

                <div class="relative z-10 flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex h-16 w-16 sm:h-20 sm:w-20 shrink-0 flex-col items-center justify-center rounded-2xl border border-white/25 bg-white/15 backdrop-blur-md shadow-inner">
                            <span class="text-xs font-semibold text-white/80 uppercase">Peringkat</span>
                            <span class="text-2xl sm:text-3xl font-black text-amber-300 drop-shadow-sm">#{{ currentUserRank.rank }}</span>
                        </div>

                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-1.5 mb-1">
                                <span class="inline-flex items-center gap-1 rounded-full bg-white/20 px-2.5 py-0.5 text-[11px] font-bold text-white backdrop-blur-md">
                                    <Icon icon="mdi:account" class="h-3 w-3" />
                                    Peringkatmu
                                </span>
                                <span v-if="page.props.auth?.user?.student_class || page.props.auth?.user?.kelas" class="rounded-full bg-white/15 px-2.5 py-0.5 text-[11px] font-semibold text-white/90">
                                    Kelas {{ page.props.auth?.user?.student_class || page.props.auth?.user?.kelas }}
                                </span>
                                <span v-if="page.props.auth?.user?.level" class="rounded-full bg-white/15 px-2.5 py-0.5 text-[11px] font-semibold text-white/90">
                                    Level {{ page.props.auth?.user?.level }}
                                </span>
                                <span v-if="page.props.auth?.user?.bidang" class="rounded-full bg-white/15 px-2.5 py-0.5 text-[11px] font-semibold text-white/90">
                                    {{ page.props.auth?.user?.bidang }}
                                </span>
                            </div>

                            <h2 class="truncate text-lg sm:text-xl font-bold tracking-tight text-white">{{ page.props.auth?.user?.name }}</h2>
                            <p class="mt-0.5 text-xs text-white/80">
                                Berada di peringkat <strong class="text-amber-300">#{{ currentUserRank.rank }}</strong> dari total {{ totalParticipants }} siswa yang terdaftar di filter ini
                            </p>
                        </div>
                    </div>

                    <!-- Statistik Nilai Siswa -->
                    <div class="grid grid-cols-3 gap-2.5 rounded-2xl border border-white/15 bg-black/20 p-3 sm:p-4 text-center backdrop-blur-md sm:gap-4 shrink-0">
                        <div class="px-1.5 sm:px-2">
                            <p class="text-[10px] sm:text-[11px] font-semibold text-white/70 uppercase">Rata-rata</p>
                            <p class="mt-0.5 text-base sm:text-xl font-black text-amber-300">{{ currentUserRank.avg_score }}%</p>
                        </div>
                        <div class="border-x border-white/15 px-1.5 sm:px-2">
                            <p class="text-[10px] sm:text-[11px] font-semibold text-white/70 uppercase">Terbaik</p>
                            <p class="mt-0.5 text-base sm:text-xl font-black text-white">{{ currentUserRank.best_score }}%</p>
                        </div>
                        <div class="px-1.5 sm:px-2">
                            <p class="text-[10px] sm:text-[11px] font-semibold text-white/70 uppercase">PR Selesai</p>
                            <p class="mt-0.5 text-base sm:text-xl font-black text-white">{{ currentUserRank.total_attempts }}×</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Banner Belum Masuk Peringkat -->
            <section
                v-else
                class="relative overflow-hidden rounded-3xl border border-dashed border-border/90 bg-card p-6 shadow-sm"
            >
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-muted/70 text-muted-foreground">
                            <Icon icon="mdi:trophy-outline" class="h-7 w-7" />
                        </span>
                        <div class="min-w-0">
                            <h2 class="text-base font-bold text-foreground sm:text-lg">Belum Masuk Peringkat Ini</h2>
                            <p class="mt-0.5 text-xs sm:text-sm leading-relaxed text-muted-foreground">
                                <span v-if="isViewingMyCohort">
                                    Kamu belum memiliki nilai PR yang selesai. Kerjakan minimal 1 PR pekanan untuk muncul di papan peringkat!
                                </span>
                                <span v-else>
                                    Kamu sedang melihat filter yang berbeda dari kelas/levelmu.
                                    <button type="button" class="text-primary font-bold underline ml-1 hover:text-primary/80" @click="resetToMyCohort">
                                        Lihat peringkat kelas & level saya
                                    </button>
                                </span>
                            </p>
                        </div>
                    </div>

                    <Link :href="route('user.packages.index')" class="btn-primary shrink-0 px-5 py-2.5 text-xs">
                        <Icon icon="mdi:book-open-page-variant" class="h-4 w-4" />
                        Kerjakan PR Sekarang
                    </Link>
                </div>
            </section>

            <!-- ============================================================== -->
            <!-- CARD 2: FILTER PANEL PER KELAS, LEVEL, DAN BIDANG               -->
            <!-- ============================================================== -->
            <div class="rounded-3xl border border-border/80 bg-card p-4 sm:p-5 shadow-sm space-y-4">
                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <Icon icon="mdi:filter-variant" class="h-4 w-4 text-primary" />
                            <h3 class="text-sm font-bold text-foreground">Saring Papan Peringkat</h3>
                        </div>

                        <div class="flex items-center gap-2">
                            <button
                                v-if="hasActiveFilters"
                                type="button"
                                class="inline-flex items-center gap-1 text-xs font-semibold text-muted-foreground transition hover:text-foreground"
                                @click="resetAllFilters"
                            >
                                <Icon icon="mdi:filter-remove-outline" class="h-3.5 w-3.5" />
                                Reset Semua Filter
                            </button>
                        </div>
                    </div>

                    <!-- Pilihan Bidang (Pills / Tabs) -->
                    <div class="flex flex-wrap items-center gap-2">
                        <button
                            v-for="tab in bidangTabs"
                            :key="tab.value"
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-xl border px-3.5 py-2 text-xs font-bold transition-all duration-200"
                            :class="selectedBidangVal === tab.value
                                ? 'border-primary bg-primary text-primary-foreground shadow-sm'
                                : 'border-border/70 bg-background text-muted-foreground hover:border-primary/40 hover:text-foreground'"
                            @click="selectBidang(tab.value)"
                        >
                            <Icon :icon="tab.icon" class="h-3.5 w-3.5" />
                            <span>{{ tab.label }}</span>
                        </button>
                    </div>

                    <!-- Dropdown Kelas & Level + Search Bar -->
                    <div class="grid grid-cols-1 gap-2.5 pt-1 sm:grid-cols-3">
                        <!-- Dropdown Kelas -->
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Kelas</label>
                            <Select v-model="selectedKelasVal" class="h-10 text-xs rounded-xl" @change="onKelasChange">
                                <option value="">Semua Kelas</option>
                                <option v-for="k in availableKelas" :key="k" :value="k">Kelas {{ k }}</option>
                            </Select>
                        </div>

                        <!-- Dropdown Level -->
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Level</label>
                            <Select v-model="selectedLevelVal" class="h-10 text-xs rounded-xl" @change="onLevelChange">
                                <option value="">Semua Level</option>
                                <option v-for="lvl in availableLevel" :key="lvl" :value="lvl">Level {{ lvl }}</option>
                            </Select>
                        </div>

                        <!-- Search Box Nama Siswa -->
                        <div class="space-y-1">
                            <label class="text-[11px] font-bold text-muted-foreground uppercase tracking-wider">Cari Siswa</label>
                            <div class="relative">
                                <Icon icon="mdi:magnify" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    v-model="searchQuery"
                                    placeholder="Ketik nama siswa..."
                                    class="h-10 rounded-xl pl-9 text-xs bg-background"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Filter Indicator Bar -->
                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-border/50 pt-3 text-xs">
                    <div class="flex flex-wrap items-center gap-1.5 text-muted-foreground">
                        <span class="font-semibold text-foreground">Menampilkan:</span>
                        <span class="inline-flex items-center gap-1 rounded-md bg-muted px-2 py-0.5 text-[11px] font-bold text-foreground">
                            <Icon :icon="getBidangIcon(selectedBidangVal)" class="h-3 w-3 text-primary" />
                            {{ selectedBidangVal || 'Semua Bidang' }}
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-md bg-muted px-2 py-0.5 text-[11px] font-bold text-foreground">
                            <Icon icon="mdi:school-outline" class="h-3 w-3 text-amber-500" />
                            {{ selectedKelasVal ? 'Kelas ' + selectedKelasVal : 'Semua Kelas' }}
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-md bg-muted px-2 py-0.5 text-[11px] font-bold text-foreground">
                            <Icon icon="mdi:layers-outline" class="h-3 w-3 text-violet-500" />
                            {{ selectedLevelVal ? 'Level ' + selectedLevelVal : 'Semua Level' }}
                        </span>
                        <span v-if="isViewingMyCohort" class="inline-flex items-center gap-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 px-2.5 py-0.5 text-[10px] font-extrabold">
                            <Icon icon="mdi:star" class="h-3 w-3 text-emerald-600" />
                            Sesuai Kelas & Level Saya
                        </span>
                    </div>

                    <div class="text-[11px] font-semibold text-muted-foreground">
                        Total Peserta: <strong class="text-foreground">{{ totalParticipants }}</strong> siswa
                    </div>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- CARD 3: PODIUM TOP 3 (PRESTASI TERBAIK)                        -->
            <!-- ============================================================== -->
            <div v-if="leaderboard.length >= 3 && !searchQuery.trim()" class="space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="flex items-center gap-2 text-sm font-bold uppercase tracking-wider text-foreground">
                        <Icon icon="mdi:podium-gold" class="h-4 w-4 text-amber-500" />
                        Top 3 Prestasi Terbaik
                    </h3>
                </div>

                <div class="grid grid-cols-1 items-end gap-4 pt-6 sm:grid-cols-3">
                    <div
                        v-for="entry in podiumOrder"
                        :key="entry.user?.id"
                        :class="[
                            'relative rounded-3xl border p-5 text-center transition-all duration-300 hover:-translate-y-1',
                            getRankTheme(entry.rank).bg,
                            getRankTheme(entry.rank).border,
                            getRankTheme(entry.rank).height,
                            entry.rank === 1 ? 'ring-2 ' + getRankTheme(entry.rank).ring : '',
                            isCurrentUser(entry.user?.id) ? 'ring-2 ring-primary' : '',
                        ]"
                    >
                        <!-- Badge Nomor Rank di Atas Kartu -->
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

                        <!-- Info Siswa -->
                        <h4 class="truncate px-2 text-sm sm:text-base font-bold text-foreground" :title="entry.user?.name">
                            {{ entry.user?.name }}
                            <span v-if="isCurrentUser(entry.user?.id)" class="ml-1 inline-block rounded-full bg-primary px-1.5 py-0.2 text-[9px] font-bold text-primary-foreground">
                                Kamu
                            </span>
                        </h4>

                        <!-- Metadata Kelas, Level & Bidang -->
                        <div class="mt-1 flex flex-wrap items-center justify-center gap-1 text-[11px]">
                            <span v-if="entry.bidang_group" :class="['inline-flex items-center gap-1 rounded-full border px-2 py-0.5 font-bold', getBidangColor(entry.bidang_group)]">
                                <Icon :icon="getBidangIcon(entry.bidang_group)" class="h-3 w-3" />
                                {{ entry.bidang_group }}
                            </span>
                            <span v-if="entry.kelas" class="rounded-full bg-slate-100 border border-slate-200 px-2 py-0.5 font-semibold text-slate-700">
                                Kelas {{ entry.kelas }}
                            </span>
                            <span v-if="entry.level" class="rounded-full bg-violet-50 border border-violet-200 px-2 py-0.5 font-semibold text-violet-700">
                                Level {{ entry.level }}
                            </span>
                        </div>

                        <!-- Skor -->
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
                                <p class="text-[10px] font-bold uppercase text-muted-foreground">PR Selesai</p>
                                <p class="text-base sm:text-lg font-black text-foreground">{{ entry.total_attempts }}×</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- CARD 4: DAFTAR PERINGKAT LENGKAP                               -->
            <!-- ============================================================== -->
            <div class="rounded-3xl border border-border/80 bg-card shadow-sm overflow-hidden">
                <div class="flex flex-col gap-3 border-b border-border/60 bg-muted/20 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                    <div class="flex items-center gap-2">
                        <Icon icon="mdi:format-list-numbered" class="h-5 w-5 text-primary" />
                        <div>
                            <h3 class="text-base font-bold text-foreground">Daftar Peringkat Lengkap</h3>
                            <p class="text-xs text-muted-foreground">
                                Diurutkan berdasarkan nilai rata-rata PR tertinggi
                            </p>
                        </div>
                    </div>
                    <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-bold text-primary">
                        {{ filteredLeaderboard.length }} Siswa Terdaftar
                    </span>
                </div>

                <!-- Status Kosong -->
                <div v-if="filteredLeaderboard.length === 0" class="p-12 text-center">
                    <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-2xl bg-muted/60 text-muted-foreground">
                        <Icon icon="mdi:trophy-outline" class="h-8 w-8" />
                    </div>
                    <h4 class="text-base font-bold text-foreground">Belum Ada Data Peringkat</h4>
                    <p class="mx-auto mt-1 max-w-sm text-xs sm:text-sm text-muted-foreground">
                        Belum ada siswa pada filter ini yang menyelesaikan tugas PR latihan.
                    </p>
                    <div class="mt-4 flex flex-wrap items-center justify-center gap-2">
                        <button
                            v-if="hasActiveFilters"
                            type="button"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-border bg-background px-4 py-2 text-xs font-semibold text-muted-foreground hover:text-foreground"
                            @click="resetAllFilters"
                        >
                            <Icon icon="mdi:filter-remove-outline" class="h-4 w-4" />
                            Tampilkan Semua Siswa
                        </button>
                        <Link :href="route('user.packages.index')" class="btn-primary px-4 py-2 text-xs">
                            <Icon icon="mdi:play" class="h-4 w-4" />
                            Mulai Kerjakan PR
                        </Link>
                    </div>
                </div>

                <!-- Baris Peringkat -->
                <div v-else class="divide-y divide-border/40">
                    <div
                        v-for="entry in filteredLeaderboard"
                        :key="entry.user?.id"
                        :class="[
                            'flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 sm:px-6 sm:py-3.5 transition-colors',
                            isCurrentUser(entry.user?.id)
                                ? 'bg-primary/10 border-l-4 border-l-primary font-semibold hover:bg-primary/15'
                                : 'hover:bg-muted/30'
                        ]"
                    >
                        <!-- Kiri: Nomor, Avatar, Identitas Siswa -->
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <!-- Nomor Peringkat -->
                            <div class="w-8 shrink-0 text-center sm:w-10">
                                <span
                                    v-if="entry.rank <= 3"
                                    :class="[
                                        'inline-flex h-7 w-7 sm:h-8 sm:w-8 items-center justify-center rounded-xl text-xs font-black text-white shadow-sm',
                                        getRankTheme(entry.rank).badgeBg,
                                    ]"
                                >
                                    {{ entry.rank }}
                                </span>
                                <span v-else class="text-xs sm:text-sm font-bold text-muted-foreground">
                                    #{{ entry.rank }}
                                </span>
                            </div>

                            <!-- Avatar -->
                            <div
                                :class="[
                                    'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-xs font-bold shadow-xs border',
                                    isCurrentUser(entry.user?.id)
                                        ? 'bg-primary text-primary-foreground border-primary'
                                        : 'bg-muted/60 text-muted-foreground border-border/80',
                                ]"
                            >
                                <img
                                    v-if="entry.user?.profile_photo_url && !entry.user?.profile_photo_url.includes('ui-avatars.com')"
                                    :src="entry.user?.profile_photo_url"
                                    :alt="entry.user?.name"
                                    class="h-full w-full rounded-xl object-cover"
                                />
                                <span v-else class="font-black text-sm">
                                    {{ (entry.user?.name || 'U').charAt(0).toUpperCase() }}
                                </span>
                            </div>

                            <!-- Nama & Info Siswa -->
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="truncate text-sm font-bold text-foreground">
                                        {{ entry.user?.name }}
                                    </p>
                                    <span v-if="isCurrentUser(entry.user?.id)" class="shrink-0 rounded-full bg-primary px-2 py-0.5 text-[10px] font-bold text-primary-foreground">
                                        Kamu
                                    </span>
                                </div>

                                <div class="mt-0.5 flex flex-wrap items-center gap-1.5 text-xs">
                                    <!-- Bidang Badge -->
                                    <span :class="['inline-flex items-center gap-1 rounded-md border px-2 py-0.2 text-[10px] font-bold', getBidangColor(entry.bidang_group)]">
                                        <Icon :icon="getBidangIcon(entry.bidang_group)" class="h-3 w-3" />
                                        {{ entry.bidang_group || entry.bidang || '-' }}
                                    </span>

                                    <!-- Kelas Badge -->
                                    <span v-if="entry.kelas" class="rounded-md bg-slate-100 border border-slate-200 px-2 py-0.2 text-[10px] font-semibold text-slate-700">
                                        Kelas {{ entry.kelas }}
                                    </span>

                                    <!-- Level Badge -->
                                    <span v-if="entry.level" class="rounded-md bg-violet-50 border border-violet-200 px-2 py-0.2 text-[10px] font-semibold text-violet-700">
                                        Level {{ entry.level }}
                                    </span>

                                    <span class="text-[11px] text-muted-foreground">
                                        • {{ entry.total_attempts }} PR selesai
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Kanan: Nilai Rata-rata & Terbaik -->
                        <div class="flex items-center justify-between sm:justify-end gap-4 pl-11 sm:pl-0 shrink-0">
                            <div class="text-left sm:text-right">
                                <p class="text-[10px] uppercase font-bold text-muted-foreground">Rata-rata</p>
                                <p class="text-base sm:text-lg font-black text-primary">{{ entry.avg_score }}%</p>
                            </div>
                            <div class="h-7 w-px bg-border/50"></div>
                            <div class="text-right">
                                <p class="text-[10px] uppercase font-bold text-muted-foreground">Terbaik</p>
                                <p class="text-sm sm:text-base font-extrabold text-foreground">{{ entry.best_score }}%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigasi Bawah -->
            <div class="flex items-center justify-between pt-2">
                <button
                    type="button"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground transition"
                    @click="resetToMyCohort"
                >
                    <Icon icon="mdi:star" class="h-3.5 w-3.5 text-amber-500" />
                    Peringkat Sesuai Profil Saya
                </button>

                <Link :href="route('user.dashboard')" class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground transition">
                    <Icon icon="mdi:arrow-left" class="h-4 w-4" />
                    Kembali ke Beranda
                </Link>
            </div>
        </div>
    </UserLayout>
</template>
