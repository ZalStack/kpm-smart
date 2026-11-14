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
    isHomeworkWindowOpen: { type: Boolean, default: true },
    filters: { type: Object, default: () => ({}) },
});

const startingId = ref(null);

function attemptInfo(pkg) {
    return props.packageStatuses?.[pkg.id] || { status: 'none', session_id: null };
}

function attemptStatus(pkg) {
    return attemptInfo(pkg).status || 'none';
}

function isLocked(pkg) {
    if (!props.isHomeworkWindowOpen) return true;
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
    if (!props.isHomeworkWindowOpen) return 'Jadwal Istirahat';
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
    if (isLocked(pkg)) return 'bg-muted text-muted-foreground cursor-not-allowed border border-border';
    const s = attemptStatus(pkg);
    if (s === 'completed') return 'bg-emerald-50 border border-emerald-200 text-emerald-700 hover:bg-emerald-100';
    if (s === 'in_progress') return 'bg-blue-50 border border-blue-200 text-brand-blue hover:bg-blue-100';
    return 'bg-primary text-primary-foreground hover:bg-primary/90 shadow-sm hover:shadow-primary/25';
}

const statusMeta = {
    active:   { label: 'Aktif',        cls: 'bg-emerald-50 text-emerald-700 border-emerald-200', dot: 'bg-emerald-500 animate-pulse' },
    upcoming: { label: 'Akan Datang',  cls: 'bg-amber-50 text-amber-700 border-amber-200',       dot: 'bg-amber-500' },
    expired:  { label: 'Berakhir',     cls: 'bg-slate-50 text-slate-600 border-slate-200',        dot: 'bg-slate-400' },
    no_limit: { label: 'Tanpa Batas',  cls: 'bg-indigo-50 text-indigo-700 border-indigo-200',     dot: 'bg-indigo-500' },
};

function attemptBadge(pkg) {
    const s = attemptStatus(pkg);
    if (s === 'completed')   return { label: 'Selesai',  cls: 'bg-emerald-50 text-emerald-700 border-emerald-200', icon: 'mdi:check-circle' };
    if (s === 'in_progress') return { label: 'Berjalan', cls: 'bg-blue-50 text-brand-blue border-blue-200',             icon: 'mdi:clock-outline' };
    return null;
}

const bidangColors = {
    'MTK':      { bg: 'bg-blue-50', solid: 'bg-brand-blue text-white shadow-xs', light: 'bg-blue-50', text: 'text-brand-blue', border: 'border-blue-200', icon: 'mdi:math-compass' },
    'IPA':      { bg: 'bg-emerald-50', solid: 'bg-brand-emerald text-white shadow-xs', light: 'bg-emerald-50', text: 'text-brand-emerald', border: 'border-emerald-200', icon: 'mdi:flask-outline' },
    'IPS':      { bg: 'bg-amber-50', solid: 'bg-brand-amber text-white shadow-xs', light: 'bg-amber-50', text: 'text-brand-amber', border: 'border-amber-200', icon: 'mdi:earth' },
    'B.IND':    { bg: 'bg-rose-50', solid: 'bg-rose-600 text-white shadow-xs', light: 'bg-rose-50', text: 'text-rose-700', border: 'border-rose-200', icon: 'mdi:book-alphabet' },
    'B.ING':    { bg: 'bg-violet-50', solid: 'bg-brand-violet text-white shadow-xs', light: 'bg-violet-50', text: 'text-brand-violet', border: 'border-violet-200', icon: 'mdi:translate' },
    'default':  { bg: 'bg-slate-50', solid: 'bg-slate-700 text-white shadow-xs', light: 'bg-slate-50', text: 'text-slate-700', border: 'border-slate-200', icon: 'mdi:folder-outline' },
};

function getBidangColor(bidang) {
    if (!bidang) return bidangColors['default'];
    const key = Object.keys(bidangColors).find(k => k !== 'default' && bidang.toUpperCase().includes(k));
    return bidangColors[key] || bidangColors['default'];
}

const openFolders = ref({});

function toggleFolder(key) {
    openFolders.value[key] = !openFolders.value[key];
}

function isFolderOpen(key) {
    return openFolders.value[key] !== false;
}

function normalizeKelas(raw) {
    if (!raw) return 'Tanpa Kelas';
    const s = String(raw).trim().replace(/['"]/g, '');
    if (!s) return 'Tanpa Kelas';
    if (/^\d+$/.test(s)) return `Kelas ${s}`;
    const m = s.match(/^kelas\s*(\d+)$/i);
    if (m) return `Kelas ${m[1]}`;
    return s;
}

function resolveBidang(pkg) {
    const title = (pkg.title || '').toUpperCase();
    const rawBidang = (pkg.bidang || '').toUpperCase();
    const isTitleMtk = /(MTK|MATEMATIKA|MATH)/.test(title);
    const isTitleIpa = /(IPA|SAINS|SCIENCE)/.test(title);
    if (isTitleMtk && !isTitleIpa) return 'MTK';
    if (isTitleIpa && !isTitleMtk) return 'IPA';
    if (/(MTK|MATEMATIKA|MATH)/.test(rawBidang)) return 'MTK';
    if (/(IPA|SAINS|SCIENCE)/.test(rawBidang)) return 'IPA';
    return pkg.bidang || 'Umum';
}

const grouped = computed(() => {
    const all = props.packages?.data || [];
    const byKelas = {};
    for (const pkg of all) {
        const kelas = normalizeKelas(pkg.kelas);
        if (!byKelas[kelas]) byKelas[kelas] = {};
        const bidang = resolveBidang(pkg);
        if (!byKelas[kelas][bidang]) byKelas[kelas][bidang] = [];
        byKelas[kelas][bidang].push(pkg);
    }
    return Object.entries(byKelas)
        .sort(([a], [b]) => a.localeCompare(b, undefined, { numeric: true }))
        .map(([kelas, bidangMap]) => ({
            kelas,
            bidangs: Object.entries(bidangMap)
                .sort(([a], [b]) => {
                    const order = { 'MTK': 1, 'IPA': 2, 'IPS': 3, 'B.IND': 4, 'B.ING': 5 };
                    const orderA = order[a] || 99;
                    const orderB = order[b] || 99;
                    if (orderA !== orderB) return orderA - orderB;
                    return a.localeCompare(b);
                })
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
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-2 rounded-xl border border-brand-blue/20 bg-blue-50 px-3.5 py-2 text-xs font-extrabold text-brand-blue shadow-sm">
                    <Icon icon="mdi:book-open-variant" class="h-3.5 w-3.5" />
                    <span class="font-extrabold text-brand-blue">{{ totalPackages }}</span>
                    <span class="text-muted-foreground/80">PR Tersedia</span>
                </span>
                <span v-if="totalActive > 0" class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-xs font-extrabold text-emerald-700">
                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    {{ totalActive }} Sedang Aktif
                </span>
            </div>
        </template>

        <!-- Banner Jadwal PR Istirahat -->
        <div
            v-if="!isHomeworkWindowOpen"
            class="anim-fade-in-up mb-6 flex flex-col gap-3 rounded-3xl border border-amber-200 bg-gradient-to-r from-amber-100/80 via-amber-50/60 to-transparent p-5 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="flex items-start gap-3 sm:items-center">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-amber-500 text-white shadow-sm">
                    <Icon icon="mdi:clock-alert-outline" class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-sm font-extrabold text-amber-900 dark:text-amber-200">
                        Jadwal Pengerjaan PR Sedang Istirahat
                    </p>
                    <p class="mt-0.5 text-xs leading-relaxed text-amber-800/90 dark:text-amber-300/90">
                        Tugas PR dibuka setiap hari <strong>Sabtu pukul 19:00 WIB</strong> hingga <strong>Jumat pukul 19:00 WIB</strong>. Pengerjaan baru dapat dimulai saat jam aktif dibuka kembali.
                    </p>
                </div>
            </div>
            <span class="shrink-0 self-start rounded-full border border-amber-300 bg-amber-100 px-3 py-1 text-xs font-extrabold text-amber-900 sm:self-auto">
                Sedang Ditutup
            </span>
        </div>

        <!-- Empty state -->
        <div v-if="grouped.length === 0" class="flex flex-col items-center justify-center rounded-4xl border-2 border-dashed border-border bg-card px-8 py-24 text-center">
            <span class="mb-6 flex h-24 w-24 items-center justify-center rounded-3xl bg-muted/40 text-muted-foreground/40">
                <Icon icon="mdi:folder-open-outline" class="h-10 w-10" />
            </span>
            <h3 class="text-base font-extrabold text-foreground">Belum Ada PR Tersedia</h3>
            <p class="mt-2 max-w-sm text-sm text-muted-foreground leading-relaxed">
                <template v-if="userBidangGroup">
                    Saat ini belum ada tugas PR {{ userBidangGroup }} yang tersedia untukmu.
                </template>
                <template v-else>
                    Saat ini belum ada tugas PR yang tersedia. Silakan kembali sesuai jadwal.
                </template>
            </p>
            <Link :href="route('user.dashboard')" class="mt-7 inline-flex items-center gap-2 rounded-xl border border-border px-6 py-3 text-xs font-extrabold text-foreground transition hover:bg-muted">
                <Icon icon="mdi:arrow-left" class="h-4 w-4" />
                Kembali ke Beranda
            </Link>
        </div>

        <!-- Foldering view: Kelas → Bidang → Packages -->
        <div v-else class="space-y-7">
            <section
                v-for="kelasGroup in grouped"
                :key="kelasGroup.kelas"
                class="overflow-hidden rounded-3xl border border-border bg-card shadow-sm"
            >
                <!-- Kelas header -->
                <button
                    type="button"
                    class="flex w-full items-center justify-between gap-3 bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 px-5 py-4 text-left transition hover:from-slate-850 hover:to-slate-800"
                    @click="toggleFolder('kelas-' + kelasGroup.kelas)"
                >
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-2xl bg-white/10 text-white">
                            <Icon icon="mdi:school-outline" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="text-base font-extrabold text-white">{{ kelasGroup.kelas }}</p>
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
                <div v-if="isFolderOpen('kelas-' + kelasGroup.kelas)" class="divide-y divide-border/50 p-5 space-y-4">
                    <div
                        v-for="bidangGroup in kelasGroup.bidangs"
                        :key="bidangGroup.bidang"
                        class="overflow-hidden rounded-2xl border border-border/50"
                    >
                        <!-- Bidang header -->
                        <button
                            type="button"
                            class="flex w-full items-center justify-between gap-3 px-4 py-3.5 text-left transition"
                            :class="[getBidangColor(bidangGroup.bidang).bg, 'hover:opacity-90']"
                            @click="toggleFolder('bidang-' + kelasGroup.kelas + '-' + bidangGroup.bidang)"
                        >
                            <div class="flex items-center gap-2.5">
                                <span
                                    class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl"
                                    :class="getBidangColor(bidangGroup.bidang).solid"
                                >
                                    <Icon :icon="getBidangColor(bidangGroup.bidang).icon" class="h-4 w-4" />
                                </span>
                                <div>
                                    <p
                                        class="text-sm font-extrabold"
                                        :class="getBidangColor(bidangGroup.bidang).text"
                                    >{{ bidangGroup.bidang }}</p>
                                    <p class="text-[11px] text-muted-foreground">{{ bidangGroup.pkgs.length }} tugas PR</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span
                                    v-if="bidangGroup.pkgs.some(p => p.schedule_status === 'active')"
                                    class="hidden sm:inline-flex items-center gap-1.5 rounded-full border bg-emerald-100 px-2.5 py-0.5 text-[10px] font-extrabold text-emerald-700 border-emerald-200"
                                >
                                    <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Ada yang Aktif
                                </span>
                                <Icon
                                    :icon="isFolderOpen('bidang-' + kelasGroup.kelas + '-' + bidangGroup.bidang) ? 'mdi:folder-open-outline' : 'mdi:folder-outline'"
                                    class="h-5 w-5"
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
                                class="group flex items-center justify-between gap-4 px-4 py-4 transition-colors hover:bg-muted/50 rounded-xl"
                                :class="{ 'opacity-60': isLocked(pkg), 'ring-2 ring-primary/20': pkg.id === startingId }"
                            >
                                <div class="flex min-w-0 flex-1 items-center gap-3.5">
                                    <div
                                        class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-2xl border"
                                        :class="[getBidangColor(pkg.bidang).light, getBidangColor(pkg.bidang).border]"
                                    >
                                        <img
                                            v-if="pkg.thumbnail"
                                            :src="'/storage/' + pkg.thumbnail"
                                            :alt="pkg.title"
                                            class="h-full w-full rounded-2xl object-cover"
                                        />
                                        <Icon
                                            v-else
                                            :icon="getBidangColor(pkg.bidang).icon"
                                            class="h-5 w-5"
                                            :class="getBidangColor(pkg.bidang).text"
                                        />
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-extrabold text-foreground transition-colors group-hover:text-primary">
                                            {{ pkg.title }}
                                        </p>
                                        <div class="mt-1.5 flex flex-wrap items-center gap-2">
                                            <span
                                                v-if="statusMeta[pkg.schedule_status]"
                                                class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-[10px] font-extrabold"
                                                :class="statusMeta[pkg.schedule_status].cls"
                                            >
                                                <span class="h-1.5 w-1.5 rounded-full" :class="statusMeta[pkg.schedule_status].dot"></span>
                                                {{ statusMeta[pkg.schedule_status].label }}
                                            </span>

                                            <span
                                                v-if="attemptBadge(pkg)"
                                                class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-[10px] font-extrabold"
                                                :class="attemptBadge(pkg).cls"
                                            >
                                                <Icon :icon="attemptBadge(pkg).icon" class="h-3 w-3" />
                                                {{ attemptBadge(pkg).label }}
                                            </span>

                                            <span class="text-[11px] text-muted-foreground">
                                                <Icon icon="mdi:help-circle-outline" class="inline h-3 w-3" />
                                                {{ pkg.total_questions }} Soal
                                            </span>

                                            <span v-if="pkg.schedule_label" class="hidden sm:inline text-[11px] text-muted-foreground">
                                                · {{ pkg.schedule_label }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="flex-shrink-0 inline-flex items-center gap-1.5 rounded-2xl px-4 py-2.5 text-xs font-extrabold transition-all duration-200"
                                    :class="ctaClass(pkg)"
                                    :disabled="isLocked(pkg) || startingId === pkg.id"
                                    @click="openPackage(pkg)"
                                >
                                    <Icon
                                        :icon="ctaIcon(pkg)"
                                        class="h-4 w-4"
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

<style scoped>
.dropdown-enter-active {
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.dropdown-leave-active {
    transition: all 0.15s ease-in;
}
.dropdown-enter-from {
    opacity: 0;
    transform: scale(0.96) translateY(-6px);
}
.dropdown-leave-to {
    opacity: 0;
    transform: scale(0.96) translateY(-6px);
}
</style>