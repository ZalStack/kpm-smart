<script setup>
import { inject, ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Select from '@/Components/ui/select/Select.vue';

const route = inject('route');

const props = defineProps({
    leaderboard: { type: Array, default: () => [] },
    availableKelas: { type: Array, default: () => [] },
    selectedBidang: { type: String, default: '' },
    selectedKelas: { type: String, default: '' },
    totalStudents: { type: Number, default: 0 },
    attemptedStudents: { type: Number, default: 0 },
    notAttemptedStudents: { type: Number, default: 0 },
    bidangStats: { type: Object, default: () => ({}) },
});

/* ============================ FILTER BIDANG & KELAS ============================ */

const bidangVal = ref(props.selectedBidang || '');
const kelasVal = ref(props.selectedKelas || '');

const bidangTabs = computed(() => [
    { value: '', label: 'Semua Bidang', count: props.attemptedStudents, icon: 'mdi:view-grid-outline', color: 'text-primary' },
    { value: 'Matematika', label: 'Matematika', count: props.bidangStats?.Matematika || 0, icon: 'mdi:calculator-variant', color: 'text-blue-600' },
    { value: 'IPA', label: 'IPA', count: props.bidangStats?.IPA || 0, icon: 'mdi:flask-outline', color: 'text-violet-600' },
]);

function applyFilters(overrides = {}) {
    const params = {};
    const bidang = 'bidang' in overrides ? overrides.bidang : bidangVal.value;
    const kelas = 'kelas' in overrides ? overrides.kelas : kelasVal.value;

    bidangVal.value = bidang;
    kelasVal.value = kelas;

    if (bidang) params.bidang = bidang;
    if (kelas) params.kelas = kelas;

    router.get(route('admin.leaderboard'), params, {
        preserveState: true,
        replace: true,
        preserveScroll: true,
    });
}

function resetFilters() {
    applyFilters({ bidang: '', kelas: '' });
}

/** Nilai dibaca langsung dari elemen agar filter selalu memakai nilai terbaru. */
function onKelasChange(e) {
    kelasVal.value = e.target.value;
    applyFilters();
}

const hasFilter = computed(() => !!(bidangVal.value || kelasVal.value));

/** Nama bidang yang sedang ditampilkan. */
const activeBidangLabel = computed(() => bidangVal.value || 'Semua Bidang');

const topThree = computed(() => props.leaderboard.slice(0, 3));

/** Susun podium: juara 2 di kiri, juara 1 di tengah, juara 3 di kanan. */
const podiumOrder = computed(() => {
    const list = [...topThree.value];
    if (list.length < 3) return list;
    const byRank = (r) => list.find(e => e.rank === r);
    return [byRank(2), byRank(1), byRank(3)].filter(Boolean);
});

/* ============================ TEMA PERINGKAT ============================ */

function getRankTheme(rank) {
    if (rank === 1) return {
        medal: 'mdi:crown',
        badgeBg: 'bg-gradient-to-br from-amber-400 to-amber-500 text-white',
        border: 'border-amber-300',
        bg: 'bg-gradient-to-b from-amber-50 to-card',
        avatarBorder: 'border-amber-400 ring-amber-400/30',
        text: 'text-amber-600',
        height: 'lg:pb-8 lg:-mt-6',
    };
    if (rank === 2) return {
        medal: 'mdi:medal',
        badgeBg: 'bg-gradient-to-br from-slate-400 to-slate-500 text-white',
        border: 'border-slate-300',
        bg: 'bg-gradient-to-b from-slate-50 to-card',
        avatarBorder: 'border-slate-400 ring-slate-400/30',
        text: 'text-slate-500',
        height: '',
    };
    if (rank === 3) return {
        medal: 'mdi:medal',
        badgeBg: 'bg-gradient-to-br from-amber-700 to-amber-800 text-white',
        border: 'border-amber-600/30',
        bg: 'bg-gradient-to-b from-amber-50 to-card',
        avatarBorder: 'border-amber-600 ring-amber-600/30',
        text: 'text-amber-700',
        height: '',
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
    if (group === 'IPA') return 'border-violet-200 bg-violet-50 text-violet-700';
    return 'border-border bg-muted text-muted-foreground';
}

function bidangBadgeIcon(group) {
    if (group === 'Matematika') return 'mdi:calculator-variant';
    if (group === 'IPA') return 'mdi:flask-outline';
    return 'mdi:folder-outline';
}
</script>

<template>
    <AdminLayout>
        <Head title="Papan Peringkat - Admin" />

        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <Icon icon="mdi:trophy-outline" class="w-5 h-5" />
                </span>
                <span>Papan Peringkat</span>
            </div>
        </template>
        <template #header-sub>Peringkat siswa per bidang studi — Matematika dan IPA tidak dicampur</template>

        <div class="space-y-6">
            <!-- Stats -->
            <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                <div class="bg-card rounded-2xl border border-border/60 p-4 sm:p-5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                            <Icon icon="mdi:account-multiple-outline" class="w-6 h-6" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] sm:text-xs text-muted-foreground font-medium uppercase tracking-wider">Total Siswa</p>
                            <p class="text-2xl sm:text-3xl font-black tracking-tight text-foreground">{{ totalStudents }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-card rounded-2xl border border-border/60 p-4 sm:p-5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0">
                            <Icon icon="mdi:check-decagram-outline" class="w-6 h-6" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] sm:text-xs text-muted-foreground font-medium uppercase tracking-wider">Pernah Mengerjakan</p>
                            <p class="text-2xl sm:text-3xl font-black tracking-tight text-emerald-600">{{ attemptedStudents }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-card rounded-2xl border border-border/60 p-4 sm:p-5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center shrink-0">
                            <Icon icon="mdi:clock-outline" class="w-6 h-6" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] sm:text-xs text-muted-foreground font-medium uppercase tracking-wider">Belum Mengerjakan</p>
                            <p class="text-2xl sm:text-3xl font-black tracking-tight text-amber-600">{{ notAttemptedStudents }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-card rounded-2xl border border-border/60 p-4 sm:p-5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center shrink-0">
                            <Icon icon="mdi:filter-check-outline" class="w-6 h-6" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] sm:text-xs text-muted-foreground font-medium uppercase tracking-wider">Sedang Ditampilkan</p>
                            <p class="text-2xl sm:text-3xl font-black tracking-tight text-indigo-600">{{ leaderboard.length }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Bidang & Kelas -->
            <div class="bg-card rounded-3xl border border-border/60 p-5 sm:p-6 shadow-sm space-y-4">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <!-- Tab Bidang: peringkat tidak boleh nyampur -->
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="tab in bidangTabs"
                            :key="tab.value"
                            type="button"
                            class="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-xs sm:text-sm font-bold transition-all duration-200"
                            :class="bidangVal === tab.value
                                ? 'border-primary bg-primary/10 text-primary shadow-sm'
                                : 'border-border/70 bg-background text-muted-foreground hover:border-primary/30 hover:text-foreground'"
                            @click="applyFilters({ bidang: tab.value })"
                        >
                            <Icon :icon="tab.icon" class="h-4 w-4" :class="bidangVal === tab.value ? tab.color : ''" />
                            {{ tab.label }}
                            <span
                                class="rounded-full px-2 py-0.5 text-[10px] font-black"
                                :class="bidangVal === tab.value ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground'"
                            >
                                {{ tab.count }}
                            </span>
                        </button>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <Select v-model="kelasVal" class="h-10 w-full rounded-xl bg-background text-xs sm:w-44" @change="onKelasChange">
                            <option value="">Semua Kelas</option>
                            <option v-for="k in availableKelas" :key="k" :value="k">Kelas {{ k }}</option>
                        </Select>

                        <button
                            v-if="hasFilter"
                            type="button"
                            class="inline-flex h-10 items-center gap-1.5 rounded-xl border border-border/70 bg-background px-4 text-xs font-semibold text-muted-foreground transition hover:border-primary/30 hover:text-foreground"
                            @click="resetFilters"
                        >
                            <Icon icon="mdi:filter-remove-outline" class="h-4 w-4" />
                            Reset
                        </button>
                    </div>
                </div>

                <p class="flex items-center gap-1.5 border-t border-border/50 pt-3 text-[11px] font-semibold text-muted-foreground">
                    <Icon icon="mdi:shield-star-outline" class="h-3.5 w-3.5 text-amber-500" />
                    Peringkat <strong class="text-foreground">{{ activeBidangLabel }}</strong> diurutkan dari rata-rata nilai tertinggi,
                    lalu nilai terbaik sebagai pembanding.
                </p>
            </div>

            <!-- Podium Top 3 -->
            <div v-if="leaderboard.length >= 3" class="space-y-3">
                <h3 class="flex items-center gap-1.5 text-sm font-bold uppercase tracking-wider text-foreground">
                    <Icon icon="mdi:podium-gold" class="h-4 w-4 text-amber-500" />
                    Top 3 — {{ activeBidangLabel }}
                </h3>

                <div class="grid grid-cols-1 items-end gap-4 pt-6 sm:grid-cols-3">
                    <div
                        v-for="entry in podiumOrder"
                        :key="entry.user?.id"
                        :class="[
                            'ui-card relative border p-5 text-center transition-all duration-300 hover:-translate-y-1 hover:shadow-card-hover',
                            getRankTheme(entry.rank).bg,
                            getRankTheme(entry.rank).border,
                            getRankTheme(entry.rank).height,
                            entry.rank === 1 ? 'ring-2 ring-amber-300/40' : '',
                        ]"
                    >
                        <span :class="['absolute -top-3.5 left-1/2 inline-flex -translate-x-1/2 items-center gap-1 rounded-full px-3 py-1 text-xs font-bold shadow-md ring-2 ring-background', getRankTheme(entry.rank).badgeBg]">
                            <Icon :icon="getRankTheme(entry.rank).medal" class="h-3.5 w-3.5" />
                            #{{ entry.rank }}
                        </span>

                        <div class="mt-2 mb-3 flex justify-center">
                            <div :class="['relative flex h-16 w-16 items-center justify-center rounded-2xl border-2 p-1 shadow-lg ring-4 sm:h-20 sm:w-20', getRankTheme(entry.rank).avatarBorder]">
                                <div class="flex h-full w-full items-center justify-center rounded-xl bg-card text-xl font-extrabold text-foreground sm:text-2xl">
                                    {{ (entry.user?.name || 'U').charAt(0).toUpperCase() }}
                                </div>
                                <span v-if="entry.rank === 1" class="absolute -right-2 -top-3 text-2xl drop-shadow">👑</span>
                            </div>
                        </div>

                        <h4 class="truncate px-2 text-sm font-bold text-foreground sm:text-base" :title="entry.user?.name">
                            {{ entry.user?.name }}
                        </h4>
                        <p class="mt-0.5 flex flex-wrap items-center justify-center gap-1.5 text-xs text-muted-foreground">
                            <span :class="['chip border', bidangBadgeClass(entry.bidang_group)]">
                                <Icon :icon="bidangBadgeIcon(entry.bidang_group)" class="h-3 w-3" />
                                {{ entry.bidang_group }}
                            </span>
                            <span v-if="entry.kelas">Kelas {{ entry.kelas }}</span>
                        </p>

                        <div class="mt-4 flex items-center justify-around border-t border-border/50 pt-3">
                            <div>
                                <p class="text-[10px] font-medium uppercase text-muted-foreground">Rata-rata</p>
                                <p class="text-base font-extrabold text-primary">{{ entry.avg_score }}%</p>
                            </div>
                            <div class="h-6 w-px bg-border/60"></div>
                            <div>
                                <p class="text-[10px] font-medium uppercase text-muted-foreground">Terbaik</p>
                                <p class="text-base font-extrabold text-foreground">{{ entry.best_score }}%</p>
                            </div>
                            <div class="h-6 w-px bg-border/60"></div>
                            <div>
                                <p class="text-[10px] font-medium uppercase text-muted-foreground">PR</p>
                                <p class="text-base font-extrabold text-foreground">{{ entry.total_attempts }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daftar Peringkat -->
            <div class="bg-card rounded-3xl shadow-sm border border-border/60 overflow-hidden">
                <div class="p-5 sm:p-6 border-b border-border/50 flex items-center gap-2 bg-muted/20">
                    <Icon icon="mdi:format-list-numbered" class="w-5 h-5 text-primary" />
                    <h3 class="text-base sm:text-lg font-bold text-foreground">Daftar Peringkat {{ activeBidangLabel }}</h3>
                </div>

                <!-- Kosong -->
                <div v-if="leaderboard.length === 0" class="p-12 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-muted/50 flex items-center justify-center mx-auto mb-3">
                        <Icon icon="mdi:trophy-outline" class="w-8 h-8 text-muted-foreground/40" />
                    </div>
                    <p class="font-semibold text-foreground">Belum Ada Data Peringkat</p>
                    <p class="text-xs text-muted-foreground mt-1">
                        Belum ada siswa pada filter ini yang menyelesaikan tugas PR.
                    </p>
                    <button
                        v-if="hasFilter"
                        class="mt-4 inline-flex items-center gap-1.5 rounded-xl border border-border/70 bg-background px-4 py-2 text-xs font-semibold text-muted-foreground transition hover:border-primary/30 hover:text-foreground"
                        @click="resetFilters"
                    >
                        <Icon icon="mdi:filter-remove-outline" class="h-4 w-4" />
                        Tampilkan Semua Bidang
                    </button>
                </div>

                <!-- Tabel Desktop -->
                <div v-else class="hidden md:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border/60 bg-muted/30">
                                <th class="text-center px-4 py-3.5 font-semibold text-muted-foreground text-xs uppercase tracking-wider w-16">Peringkat</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Siswa</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Bidang</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Kelas</th>
                                <th class="text-center px-5 py-3.5 font-semibold text-muted-foreground text-xs uppercase tracking-wider">PR Selesai</th>
                                <th class="text-right px-5 py-3.5 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Rata-rata</th>
                                <th class="text-right px-5 py-3.5 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Terbaik</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/50">
                            <tr v-for="entry in leaderboard" :key="entry.user?.id" class="hover:bg-muted/30 transition-colors">
                                <td class="px-4 py-3.5 text-center">
                                    <span
                                        v-if="entry.rank <= 3"
                                        :class="['inline-flex h-8 w-8 items-center justify-center rounded-xl text-xs font-black text-white shadow-sm', getRankTheme(entry.rank).badgeBg]"
                                    >
                                        {{ entry.rank }}
                                    </span>
                                    <span v-else class="text-xs font-bold text-muted-foreground">#{{ entry.rank }}</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-muted/40 flex items-center justify-center shrink-0 border border-border/60">
                                            <span class="text-xs font-black text-primary select-none">{{ (entry.user?.name || 'U').charAt(0).toUpperCase() }}</span>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-foreground truncate">{{ entry.user?.name }}</p>
                                            <p class="text-xs text-muted-foreground truncate">{{ entry.user?.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span :class="['chip border', bidangBadgeClass(entry.bidang_group)]">
                                        <Icon :icon="bidangBadgeIcon(entry.bidang_group)" class="h-3 w-3" />
                                        {{ entry.bidang_group }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="text-xs font-semibold text-foreground">{{ entry.kelas || '-' }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="text-xs font-bold text-foreground">{{ entry.total_attempts }}×</span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <span class="text-sm font-extrabold text-primary">{{ entry.avg_score }}%</span>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <span class="text-sm font-bold text-foreground">{{ entry.best_score }}%</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Kartu Mobile -->
                <div v-if="leaderboard.length > 0" class="md:hidden divide-y divide-border/40">
                    <div v-for="entry in leaderboard" :key="entry.user?.id" class="p-4 sm:p-5 space-y-3">
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
                            <div class="w-10 h-10 rounded-full bg-muted/40 flex items-center justify-center shrink-0 border border-border/60">
                                <span class="text-xs font-black text-primary select-none">{{ (entry.user?.name || 'U').charAt(0).toUpperCase() }}</span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-bold text-sm text-foreground truncate">{{ entry.user?.name }}</p>
                                <div class="mt-0.5 flex flex-wrap items-center gap-1.5">
                                    <span :class="['chip border', bidangBadgeClass(entry.bidang_group)]">
                                        <Icon :icon="bidangBadgeIcon(entry.bidang_group)" class="h-3 w-3" />
                                        {{ entry.bidang_group }}
                                    </span>
                                    <span v-if="entry.kelas" class="text-[11px] font-semibold text-muted-foreground">Kelas {{ entry.kelas }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-center bg-muted/40 rounded-2xl p-3">
                            <div>
                                <p class="text-[10px] font-semibold uppercase text-muted-foreground">Rata-rata</p>
                                <p class="text-sm font-extrabold text-primary">{{ entry.avg_score }}%</p>
                            </div>
                            <div class="border-x border-border/50">
                                <p class="text-[10px] font-semibold uppercase text-muted-foreground">Terbaik</p>
                                <p class="text-sm font-extrabold text-foreground">{{ entry.best_score }}%</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold uppercase text-muted-foreground">PR Selesai</p>
                                <p class="text-sm font-extrabold text-foreground">{{ entry.total_attempts }}×</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
