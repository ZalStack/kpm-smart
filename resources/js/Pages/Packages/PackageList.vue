<script setup>
import { inject, ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import UserLayout from '@/Layouts/UserLayout.vue';

const route = inject('route');

const props = defineProps({
    packages: { type: Object, required: true },
    packageStatuses: { type: Object, default: () => ({}) },
    allKelas: { type: Array, default: () => [] },
    allBidang: { type: Array, default: () => [] },
    allLevel: { type: Array, default: () => [] },
    userBidangGroup: { type: String, default: null },
    userBidangRaw: { type: String, default: '' },
    currentSort: { type: String, default: 'latest' },
    filters: { type: Object, default: () => ({}) },
});

/* ── Status pengerjaan ── */
const startingId = ref(null);

function attemptInfo(pkg) {
    return props.packageStatuses?.[pkg.id] || { status: 'none', session_id: null };
}

function attemptStatus(pkg) {
    return attemptInfo(pkg).status || 'none';
}

function isLocked(pkg) {
    return pkg.schedule_status === 'expired' || pkg.schedule_status === 'upcoming';
}

function openPackage(pkg) {
    if (isLocked(pkg) || startingId.value) return;
    const info = attemptInfo(pkg);
    if ((info.status === 'completed' || info.status === 'in_progress') && info.session_id) {
        router.visit(route('user.practice.show', info.session_id));
        return;
    }
    startingId.value = pkg.id;
    router.post(route('user.practice.start', pkg.id), {}, {
        onFinish: () => { startingId.value = null; },
    });
}

function ctaLabel(pkg) {
    if (pkg.schedule_status === 'expired') return 'Berakhir';
    if (pkg.schedule_status === 'upcoming') return 'Belum Dimulai';
    const s = attemptStatus(pkg);
    if (s === 'completed') return 'Lihat Hasil';
    if (s === 'in_progress') return 'Lanjutkan';
    if (startingId.value === pkg.id) return 'Membuka...';
    return 'Kerjakan';
}

function ctaIcon(pkg) {
    if (pkg.schedule_status === 'expired') return 'mdi:clock-remove-outline';
    if (pkg.schedule_status === 'upcoming') return 'mdi:clock-outline';
    const s = attemptStatus(pkg);
    if (s === 'completed') return 'mdi:chart-bar';
    if (s === 'in_progress') return 'mdi:play';
    if (startingId.value === pkg.id) return 'mdi:loading';
    return 'mdi:pencil-outline';
}

function ctaClass(pkg) {
    if (isLocked(pkg)) return 'bg-muted text-muted-foreground cursor-not-allowed';
    const s = attemptStatus(pkg);
    if (s === 'completed') return 'bg-emerald-50 border border-emerald-200 text-emerald-700 hover:bg-emerald-100';
    if (s === 'in_progress') return 'bg-sky-50 border border-sky-200 text-sky-700 hover:bg-sky-100';
    return 'bg-primary text-primary-foreground hover:bg-primary/90';
}

/* ── Status jadwal badge ── */
const statusMeta = {
    active:   { label: 'Aktif',        cls: 'bg-emerald-100 text-emerald-700 border-emerald-200', dot: 'bg-emerald-500 animate-pulse' },
    upcoming: { label: 'Akan Datang',  cls: 'bg-amber-100 text-amber-700 border-amber-200',       dot: 'bg-amber-500' },
    expired:  { label: 'Berakhir',     cls: 'bg-slate-100 text-slate-600 border-slate-200',        dot: 'bg-slate-400' },
    no_limit: { label: 'Tanpa Batas',  cls: 'bg-indigo-100 text-indigo-700 border-indigo-200',     dot: 'bg-indigo-500' },
};

/* ── Attempt status badge ── */
function attemptBadge(pkg) {
    const s = attemptStatus(pkg);
    if (s === 'completed')   return { label: 'Selesai',  cls: 'bg-emerald-100 text-emerald-700 border-emerald-200', icon: 'mdi:check-circle' };
    if (s === 'in_progress') return { label: 'Berjalan', cls: 'bg-sky-100 text-sky-700 border-sky-200',             icon: 'mdi:clock-outline' };
    return null;
}

/* ── Folder warna bidang ── */
const bidangColors = {
    'MTK':      { bg: 'bg-blue-500', light: 'bg-blue-50', text: 'text-blue-700', border: 'border-blue-200', icon: 'mdi:math-compass' },
    'IPA':      { bg: 'bg-emerald-500', light: 'bg-emerald-50', text: 'text-emerald-700', border: 'border-emerald-200', icon: 'mdi:flask-outline' },
    'IPS':      { bg: 'bg-amber-500', light: 'bg-amber-50', text: 'text-amber-700', border: 'border-amber-200', icon: 'mdi:earth' },
    'B.IND':    { bg: 'bg-rose-500', light: 'bg-rose-50', text: 'text-rose-700', border: 'border-rose-200', icon: 'mdi:book-alphabet' },
    'B.ING':    { bg: 'bg-purple-500', light: 'bg-purple-50', text: 'text-purple-700', border: 'border-purple-200', icon: 'mdi:translate' },
    'default':  { bg: 'bg-slate-500', light: 'bg-slate-50', text: 'text-slate-700', border: 'border-slate-200', icon: 'mdi:folder-outline' },
};

function getBidangColor(bidang) {
    if (!bidang) return bidangColors['default'];
    const key = Object.keys(bidangColors).find(k => k !== 'default' && bidang.toUpperCase().includes(k));
    return bidangColors[key] || bidangColors['default'];
}

/* ── Folder state ── */
const openFolders = ref({});

function toggleFolder(key) {
    openFolders.value[key] = !openFolders.value[key];
}

function isFolderOpen(key) {
    return openFolders.value[key] !== false; // default terbuka
}

/* ── Grup: Kelas → Bidang → Packages ── */
const grouped = computed(() => {
    const all = props.packages?.data || [];

    // Group by kelas
    const byKelas = {};
    for (const pkg of all) {
        const kelas = pkg.kelas || 'Tanpa Kelas';
        if (!byKelas[kelas]) byKelas[kelas] = {};
        const bidang = pkg.bidang || 'Umum';
        if (!byKelas[kelas][bidang]) byKelas[kelas][bidang] = [];
        byKelas[kelas][bidang].push(pkg);
    }

    // Urutkan kelas secara ascending, bidang secara ascending
    return Object.entries(byKelas)
        .sort(([a], [b]) => a.localeCompare(b))
        .map(([kelas, bidangMap]) => ({
            kelas,
            bidangs: Object.entries(bidangMap)
                .sort(([a], [b]) => a.localeCompare(b))
                .map(([bidang, pkgs]) => ({ bidang, pkgs })),
        }));
});

const totalPackages = computed(() => props.packages?.total || 0);
const totalActive = computed(() =>
    (props.packages?.data || []).filter(p => p.schedule_status === 'active').length
);
</script>

<template>
    <UserLayout>
        <Head title="PR Siswa Kelas Khusus - PR KK" />

        <template #header-title>PR Siswa Kelas Khusus</template>
        <template #header-sub>Pilih folder kelas dan bidang untuk melihat tugas PR yang tersedia.</template>

        <template #header-actions>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 rounded-xl border border-border/60 bg-card px-3.5 py-2 text-xs font-semibold text-foreground shadow-sm">
                    <Icon icon="mdi:book-open-variant" class="h-3.5 w-3.5 text-primary" />
                    <span class="font-extrabold text-primary">{{ totalPackages }}</span>
                    <span class="text-muted-foreground">PR Tersedia</span>
                </span>
                <span v-if="totalActive > 0" class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-xs font-semibold text-emerald-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    {{ totalActive }} Sedang Aktif
                </span>
            </div>
        </template>

        <!-- Empty state -->
        <div v-if="grouped.length === 0" class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-border bg-card px-6 py-20 text-center">
            <span class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-muted/60 text-muted-foreground/50">
                <Icon icon="mdi:folder-open-outline" class="h-8 w-8" />
            </span>
            <h3 class="text-base font-bold text-foreground">Belum Ada PR Tersedia</h3>
            <p class="mt-1.5 max-w-sm text-sm text-muted-foreground">
                <template v-if="userBidangGroup">
                    Saat ini belum ada tugas PR {{ userBidangGroup }} yang tersedia untukmu.
                </template>
                <template v-else>
                    Saat ini belum ada tugas PR yang tersedia. Silakan kembali sesuai jadwal.
                </template>
            </p>
            <Link :href="route('user.dashboard')" class="mt-6 inline-flex items-center gap-2 rounded-xl border border-border px-5 py-2.5 text-xs font-semibold text-foreground transition hover:bg-muted">
                <Icon icon="mdi:arrow-left" class="h-4 w-4" />
                Kembali ke Beranda
            </Link>
        </div>

        <!-- Foldering view: Kelas → Bidang → Packages -->
        <div v-else class="space-y-6">
            <!-- Kelas folder -->
            <section
                v-for="kelasGroup in grouped"
                :key="kelasGroup.kelas"
                class="overflow-hidden rounded-2xl border border-border/60 bg-card shadow-sm"
            >
                <!-- Kelas header -->
                <button
                    type="button"
                    class="flex w-full items-center justify-between gap-3 bg-gradient-to-r from-slate-800 to-slate-700 px-5 py-4 text-left transition hover:from-slate-700 hover:to-slate-600"
                    @click="toggleFolder('kelas-' + kelasGroup.kelas)"
                >
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-white/15 text-white">
                            <Icon icon="mdi:school-outline" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="text-base font-bold text-white">{{ kelasGroup.kelas }}</p>
                            <p class="text-xs text-white/60">
                                {{ kelasGroup.bidangs.length }} Bidang ·
                                {{ kelasGroup.bidangs.reduce((acc, b) => acc + b.pkgs.length, 0) }} PR
                            </p>
                        </div>
                    </div>
                    <Icon
                        :icon="isFolderOpen('kelas-' + kelasGroup.kelas) ? 'mdi:chevron-up' : 'mdi:chevron-down'"
                        class="h-5 w-5 text-white/70 transition-transform duration-200"
                    />
                </button>

                <!-- Bidang folders inside kelas -->
                <div v-if="isFolderOpen('kelas-' + kelasGroup.kelas)" class="divide-y divide-border/50 p-4 space-y-3">
                    <div
                        v-for="bidangGroup in kelasGroup.bidangs"
                        :key="bidangGroup.bidang"
                        class="overflow-hidden rounded-xl border border-border/50"
                    >
                        <!-- Bidang header -->
                        <button
                            type="button"
                            class="flex w-full items-center justify-between gap-3 px-4 py-3 text-left transition"
                            :class="[getBidangColor(bidangGroup.bidang).light, 'hover:opacity-90']"
                            @click="toggleFolder('bidang-' + kelasGroup.kelas + '-' + bidangGroup.bidang)"
                        >
                            <div class="flex items-center gap-2.5">
                                <span
                                    class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg text-white"
                                    :class="getBidangColor(bidangGroup.bidang).bg"
                                >
                                    <Icon :icon="getBidangColor(bidangGroup.bidang).icon" class="h-4 w-4" />
                                </span>
                                <div>
                                    <p
                                        class="text-sm font-bold"
                                        :class="getBidangColor(bidangGroup.bidang).text"
                                    >{{ bidangGroup.bidang }}</p>
                                    <p class="text-[11px] text-muted-foreground">{{ bidangGroup.pkgs.length }} tugas PR</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <!-- Badge aktif -->
                                <span
                                    v-if="bidangGroup.pkgs.some(p => p.schedule_status === 'active')"
                                    class="hidden sm:inline-flex items-center gap-1 rounded-full border bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700 border-emerald-200"
                                >
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Ada yang Aktif
                                </span>
                                <Icon
                                    :icon="isFolderOpen('bidang-' + kelasGroup.kelas + '-' + bidangGroup.bidang) ? 'mdi:folder-open-outline' : 'mdi:folder-outline'"
                                    class="h-4.5 w-4.5"
                                    :class="getBidangColor(bidangGroup.bidang).text"
                                />
                                <Icon
                                    :icon="isFolderOpen('bidang-' + kelasGroup.kelas + '-' + bidangGroup.bidang) ? 'mdi:chevron-up' : 'mdi:chevron-down'"
                                    class="h-4 w-4 text-muted-foreground"
                                />
                            </div>
                        </button>

                        <!-- Package list inside bidang -->
                        <div
                            v-if="isFolderOpen('bidang-' + kelasGroup.kelas + '-' + bidangGroup.bidang)"
                            class="divide-y divide-border/30 bg-background"
                        >
                            <div
                                v-for="pkg in bidangGroup.pkgs"
                                :key="pkg.id"
                                class="group flex items-center justify-between gap-4 px-4 py-3.5 transition-colors hover:bg-muted/40"
                                :class="{ 'opacity-70': isLocked(pkg) }"
                            >
                                <!-- Kiri: info PR -->
                                <div class="flex min-w-0 flex-1 items-center gap-3">
                                    <!-- Icon PR -->
                                    <div
                                        class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl border"
                                        :class="[getBidangColor(pkg.bidang).light, getBidangColor(pkg.bidang).border]"
                                    >
                                        <img
                                            v-if="pkg.thumbnail"
                                            :src="'/storage/' + pkg.thumbnail"
                                            :alt="pkg.title"
                                            class="h-full w-full rounded-xl object-cover"
                                        />
                                        <Icon
                                            v-else
                                            :icon="getBidangColor(pkg.bidang).icon"
                                            class="h-4.5 w-4.5"
                                            :class="getBidangColor(pkg.bidang).text"
                                        />
                                    </div>

                                    <!-- Info teks -->
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-bold text-foreground transition-colors group-hover:text-primary">
                                            {{ pkg.title }}
                                        </p>
                                        <div class="mt-1 flex flex-wrap items-center gap-2">
                                            <!-- Status jadwal badge -->
                                            <span
                                                v-if="statusMeta[pkg.schedule_status]"
                                                class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] font-bold"
                                                :class="statusMeta[pkg.schedule_status].cls"
                                            >
                                                <span class="h-1.5 w-1.5 rounded-full" :class="statusMeta[pkg.schedule_status].dot"></span>
                                                {{ statusMeta[pkg.schedule_status].label }}
                                            </span>

                                            <!-- Attempt badge -->
                                            <span
                                                v-if="attemptBadge(pkg)"
                                                class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[10px] font-bold"
                                                :class="attemptBadge(pkg).cls"
                                            >
                                                <Icon :icon="attemptBadge(pkg).icon" class="h-2.5 w-2.5" />
                                                {{ attemptBadge(pkg).label }}
                                            </span>

                                            <!-- Jumlah soal -->
                                            <span class="text-[11px] text-muted-foreground">
                                                <Icon icon="mdi:help-circle-outline" class="inline h-3 w-3" />
                                                {{ pkg.total_questions }} Soal
                                            </span>

                                            <!-- Jadwal -->
                                            <span v-if="pkg.schedule_label" class="hidden sm:inline text-[11px] text-muted-foreground">
                                                · {{ pkg.schedule_label }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Kanan: tombol aksi -->
                                <button
                                    type="button"
                                    class="flex-shrink-0 inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2 text-xs font-bold transition-all duration-200"
                                    :class="ctaClass(pkg)"
                                    :disabled="isLocked(pkg) || startingId === pkg.id"
                                    @click="openPackage(pkg)"
                                >
                                    <Icon
                                        :icon="ctaIcon(pkg)"
                                        class="h-3.5 w-3.5"
                                        :class="{ 'animate-spin': startingId === pkg.id }"
                                    />
                                    <span class="hidden sm:inline">{{ ctaLabel(pkg) }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </UserLayout>
</template>
