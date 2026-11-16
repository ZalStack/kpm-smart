<script setup>
import { inject, ref, computed } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import UserLayout from '@/Layouts/UserLayout.vue';

const route = inject('route');
const page = usePage();
const user = computed(() => page.props.auth?.user);

const props = defineProps({
    packages: { type: Array, default: () => [] },
    totalAttempts: { type: Number, default: 0 },
    bestScore: { type: Number, default: 0 },
    averageScore: { type: Number, default: 0 },
    gamification: { type: Object, default: () => ({}) },
    teacherNotes: { type: Array, default: () => [] },
    isHomeworkWindowOpen: { type: Boolean, default: true },
});

const startingId = ref(null);

const greetingTime = computed(() => {
    const hour = new Date().getHours();
    if (hour < 11) return 'Selamat Pagi';
    if (hour < 15) return 'Selamat Siang';
    if (hour < 18) return 'Selamat Sore';
    return 'Selamat Malam';
});

const firstName = computed(() => (user.value?.name || 'Siswa').split(' ')[0]);

const currentDate = new Date().toLocaleDateString('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});

const noteText = (note) => note.notes || note.message || '';

const completedPackagesCount = computed(() => {
    return props.packages.filter(p => p.user_attempt?.status === 'completed').length;
});

const pendingPackagesCount = computed(() => {
    return props.packages.filter(p => !p.user_attempt || p.user_attempt?.status === 'in_progress').length;
});

const bidangColors = {
    'MTK':   { bg: 'bg-blue-500/10 text-blue-700 border-blue-200', icon: 'mdi:math-compass', solid: 'bg-blue-600 text-white', accent: 'text-blue-600' },
    'IPA':   { bg: 'bg-emerald-500/10 text-emerald-700 border-emerald-200', icon: 'mdi:flask-outline', solid: 'bg-emerald-600 text-white', accent: 'text-emerald-600' },
    'IPS':   { bg: 'bg-amber-500/10 text-amber-700 border-amber-200', icon: 'mdi:earth', solid: 'bg-amber-600 text-white', accent: 'text-amber-600' },
    'B.IND': { bg: 'bg-rose-500/10 text-rose-700 border-rose-200', icon: 'mdi:book-alphabet', solid: 'bg-rose-600 text-white', accent: 'text-rose-600' },
    'B.ING': { bg: 'bg-violet-500/10 text-violet-700 border-violet-200', icon: 'mdi:translate', solid: 'bg-violet-600 text-white', accent: 'text-violet-600' },
    'default': { bg: 'bg-indigo-500/10 text-indigo-700 border-indigo-200', icon: 'mdi:school-outline', solid: 'bg-indigo-600 text-white', accent: 'text-indigo-600' },
};

function getBidangMeta(bidang) {
    if (!bidang) return bidangColors['default'];
    const b = bidang.toUpperCase();
    for (const key of Object.keys(bidangColors)) {
        if (key !== 'default' && b.includes(key)) {
            return bidangColors[key];
        }
    }
    return bidangColors['default'];
}

function handleOpenPackage(pkg) {
    if (startingId.value) return;
    if (pkg.user_attempt?.id) {
        router.visit(route('user.practice.show', pkg.user_attempt.id));
        return;
    }
    startingId.value = pkg.id;
    router.visit(route('user.packages.show', pkg.id), {
        onFinish: () => { startingId.value = null; },
    });
}
</script>

<template>
    <UserLayout>
        <Head title="Beranda Belajar Siswa - PR KK" />
        <template #header-title>Beranda Belajar</template>
        <template #header-sub>Halo {{ firstName }}, siap mengasah kemampuan matematika dan sains hari ini?</template>

        <div class="space-y-7 pb-10">
            <!-- ====== HERO BANNER SELAMAT DATANG ====== -->
            <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-700 via-indigo-600 to-blue-700 text-white shadow-xl shadow-indigo-600/15 transition-all">
                <!-- Background Geometric Gradients -->
                <div class="pointer-events-none absolute inset-0 overflow-hidden">
                    <div class="absolute -right-16 -top-24 h-80 w-80 rounded-full bg-blue-400/20 blur-3xl"></div>
                    <div class="absolute -left-12 -bottom-20 h-72 w-72 rounded-full bg-purple-500/20 blur-3xl"></div>
                    <div class="absolute right-1/4 bottom-0 h-48 w-48 rounded-full bg-cyan-300/15 blur-2xl"></div>
                    <div class="grid-dots absolute inset-0 opacity-15"></div>
                </div>

                <div class="relative z-10 p-6 sm:p-8 lg:p-10">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                        <div class="max-w-2xl space-y-3.5">
                            <!-- Metadata Siswa Pills -->
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 rounded-full border border-white/20 bg-white/15 px-3 py-1 text-[11px] font-extrabold text-white backdrop-blur-md">
                                    <Icon icon="mdi:calendar-month-outline" class="h-3.5 w-3.5 text-cyan-200" />
                                    {{ currentDate }}
                                </span>
                                <span v-if="user?.student_class || user?.kelas" class="inline-flex items-center gap-1 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-bold text-white/95 backdrop-blur-md">
                                    <Icon icon="mdi:school-outline" class="h-3.5 w-3.5 text-amber-300" />
                                    Kelas {{ user.student_class || user.kelas }}
                                </span>
                                <span v-if="user?.bidang" class="inline-flex items-center gap-1 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-bold text-white/95 backdrop-blur-md">
                                    {{ user.bidang }}
                                </span>
                                <span v-if="user?.level" class="inline-flex items-center gap-1 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-[11px] font-bold text-white/95 backdrop-blur-md">
                                    Level {{ user.level }}
                                </span>
                            </div>

                            <!-- Greeting Title -->
                            <h2 class="text-2xl font-black tracking-tight text-white sm:text-3xl lg:text-4xl">
                                {{ greetingTime }}, {{ firstName }}! <span class="inline-block animate-bounce">👋</span>
                            </h2>
                            <p class="max-w-xl text-sm leading-relaxed text-indigo-100 sm:text-base">
                                Selamat datang di portal belajar PR Kelas Khusus KPM. Selesaikan tugas pekananmu secara mandiri, jujur, dan raih prestasi terbaik!
                            </p>

                            <!-- CTA Buttons -->
                            <div class="flex flex-wrap items-center gap-3 pt-2">
                                <Link
                                    :href="route('user.packages.index')"
                                    class="group inline-flex items-center gap-2.5 rounded-2xl bg-white px-6 py-3.5 text-sm font-black text-indigo-700 shadow-lg shadow-black/10 transition-all duration-200 hover:-translate-y-0.5 hover:bg-indigo-50 active:translate-y-0"
                                >
                                    <Icon icon="mdi:book-open-page-variant" class="h-4 w-4 text-indigo-700 transition-transform group-hover:scale-110" />
                                    <span>Buka PR Pekan Ini</span>
                                    <Icon icon="mdi:arrow-right" class="h-4 w-4 text-indigo-500 transition-transform group-hover:translate-x-0.5" />
                                </Link>

                                <Link
                                    :href="route('user.leaderboard')"
                                    class="inline-flex items-center gap-2 rounded-2xl border border-white/25 bg-white/10 px-5 py-3.5 text-sm font-extrabold text-white backdrop-blur-md transition-all duration-200 hover:bg-white/20 active:translate-y-0"
                                >
                                    <Icon icon="mdi:trophy-outline" class="h-4 w-4 text-amber-300" />
                                    <span>Papan Peringkat</span>
                                </Link>
                            </div>
                        </div>

                        <!-- Right Quick Progress Box (Desktop) -->
                        <div class="hidden shrink-0 lg:block">
                            <div class="w-72 rounded-3xl border border-white/20 bg-white/10 p-5 backdrop-blur-md">
                                <div class="flex items-center justify-between pb-3 border-b border-white/15">
                                    <span class="text-xs font-bold text-indigo-100">Status Tugasmu</span>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-400/20 px-2 py-0.5 text-[10px] font-black text-emerald-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-300 animate-pulse"></span>
                                        Aktif
                                    </span>
                                </div>
                                <div class="mt-4 space-y-3">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-indigo-100">PR Diselesaikan</span>
                                        <span class="font-black text-white">{{ completedPackagesCount }} Paket</span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-indigo-100">Perlu Dikerjakan</span>
                                        <span class="font-black text-amber-300">{{ pendingPackagesCount }} Paket</span>
                                    </div>
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-indigo-100">Nilai Tertinggi</span>
                                        <span class="font-black text-cyan-200">{{ bestScore }}%</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ====== 4 KARTU STATISTIK MODERN ====== -->
            <section class="grid grid-cols-2 gap-3.5 sm:gap-5 lg:grid-cols-4">
                <!-- 1. Total Percobaan -->
                <div class="group relative overflow-hidden rounded-3xl border border-slate-200/80 bg-card p-4 sm:p-5 shadow-xs transition-all duration-300 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-card-hover">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-muted-foreground">Percobaan PR</span>
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 transition-transform group-hover:scale-105">
                            <Icon icon="mdi:check-decagram-outline" class="h-5 w-5" />
                        </span>
                    </div>
                    <div class="mt-2.5">
                        <p class="text-2xl sm:text-3xl font-black tracking-tight text-foreground">{{ totalAttempts }}</p>
                        <p class="mt-1 flex items-center gap-1 text-[11px] font-semibold text-muted-foreground">
                            <span>Sesi tuntas dikerjakan</span>
                        </p>
                    </div>
                    <div class="absolute inset-x-0 bottom-0 h-1 bg-gradient-to-r from-indigo-500 to-indigo-600 opacity-0 transition-opacity group-hover:opacity-100"></div>
                </div>

                <!-- 2. Skor Tertinggi -->
                <div class="group relative overflow-hidden rounded-3xl border border-slate-200/80 bg-card p-4 sm:p-5 shadow-xs transition-all duration-300 hover:-translate-y-1 hover:border-amber-300 hover:shadow-card-hover">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-muted-foreground">Skor Tertinggi</span>
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 transition-transform group-hover:scale-105">
                            <Icon icon="mdi:trophy-outline" class="h-5 w-5" />
                        </span>
                    </div>
                    <div class="mt-2.5">
                        <p class="text-2xl sm:text-3xl font-black tracking-tight text-amber-600">{{ bestScore }}<span class="text-sm font-bold text-amber-500">%</span></p>
                        <p class="mt-1 flex items-center gap-1 text-[11px] font-semibold text-muted-foreground">
                            <Icon icon="mdi:star" class="h-3.5 w-3.5 text-amber-500" />
                            <span>Rekor nilai pribadi</span>
                        </p>
                    </div>
                    <div class="absolute inset-x-0 bottom-0 h-1 bg-gradient-to-r from-amber-400 to-amber-500 opacity-0 transition-opacity group-hover:opacity-100"></div>
                </div>

                <!-- 3. Nilai Rata-rata -->
                <div class="group relative overflow-hidden rounded-3xl border border-slate-200/80 bg-card p-4 sm:p-5 shadow-xs transition-all duration-300 hover:-translate-y-1 hover:border-emerald-300 hover:shadow-card-hover">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-muted-foreground">Rata-rata Nilai</span>
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 transition-transform group-hover:scale-105">
                            <Icon icon="mdi:chart-line-variant" class="h-5 w-5" />
                        </span>
                    </div>
                    <div class="mt-2.5">
                        <p class="text-2xl sm:text-3xl font-black tracking-tight text-emerald-600">{{ averageScore }}<span class="text-sm font-bold text-emerald-500">%</span></p>
                        <p class="mt-1 flex items-center gap-1 text-[11px] font-semibold text-muted-foreground">
                            <span>Performa keseluruhan</span>
                        </p>
                    </div>
                    <div class="absolute inset-x-0 bottom-0 h-1 bg-gradient-to-r from-emerald-400 to-emerald-600 opacity-0 transition-opacity group-hover:opacity-100"></div>
                </div>

                <!-- 4. PR Tersedia -->
                <div class="group relative overflow-hidden rounded-3xl border border-slate-200/80 bg-card p-4 sm:p-5 shadow-xs transition-all duration-300 hover:-translate-y-1 hover:border-blue-300 hover:shadow-card-hover">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-muted-foreground">PR Pekanan</span>
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-600 transition-transform group-hover:scale-105">
                            <Icon icon="mdi:book-open-variant" class="h-5 w-5" />
                        </span>
                    </div>
                    <div class="mt-2.5">
                        <p class="text-2xl sm:text-3xl font-black tracking-tight text-blue-600">{{ packages.length }}</p>
                        <p class="mt-1 flex items-center gap-1 text-[11px] font-semibold text-muted-foreground">
                            <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>Paket aktif untukmu</span>
                        </p>
                    </div>
                    <div class="absolute inset-x-0 bottom-0 h-1 bg-gradient-to-r from-blue-500 to-indigo-600 opacity-0 transition-opacity group-hover:opacity-100"></div>
                </div>
            </section>

            <!-- ====== SEKSI PR SISWA PEKANAN ====== -->
            <section class="space-y-4">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700">
                                <Icon icon="mdi:book-open-page-variant" class="h-4 w-4" />
                            </span>
                            <h3 class="text-lg sm:text-xl font-black tracking-tight text-foreground">PR Siswa Kelas Khusus</h3>
                        </div>
                        <p class="text-xs text-muted-foreground mt-0.5">Daftar paket tugas pekanan yang disiapkan oleh pembimbing</p>
                    </div>

                    <Link
                        :href="route('user.packages.index')"
                        class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:text-indigo-800 transition"
                    >
                        <span>Lihat Semua PR</span>
                        <Icon icon="mdi:chevron-right" class="h-4 w-4" />
                    </Link>
                </div>

                <!-- Grid Kartu PR -->
                <div v-if="packages.length > 0" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <article
                        v-for="pkg in packages"
                        :key="pkg.id"
                        class="group relative flex flex-col justify-between overflow-hidden rounded-3xl border border-slate-200/80 bg-card p-5 sm:p-6 shadow-xs transition-all duration-300 hover:-translate-y-1 hover:border-indigo-300 hover:shadow-card-hover"
                    >
                        <!-- Top Badges -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <!-- Bidang Badge -->
                                    <span :class="['inline-flex items-center gap-1 rounded-xl border px-2.5 py-1 text-[11px] font-black', getBidangMeta(pkg.bidang).bg]">
                                        <Icon :icon="getBidangMeta(pkg.bidang).icon" class="h-3.5 w-3.5" />
                                        {{ pkg.bidang || 'Umum' }}
                                    </span>

                                    <!-- Kelas Badge -->
                                    <span v-if="pkg.kelas" class="rounded-xl border border-slate-200 bg-slate-50 px-2 py-1 text-[10px] font-bold text-slate-700">
                                        Kelas {{ pkg.kelas }}
                                    </span>
                                </div>

                                <!-- Status Pengerjaan Siswa Badge -->
                                <div>
                                    <span
                                        v-if="pkg.user_attempt?.status === 'completed'"
                                        class="inline-flex items-center gap-1 rounded-full border border-emerald-300 bg-emerald-50 px-2.5 py-0.5 text-[10px] font-extrabold text-emerald-800"
                                    >
                                        <Icon icon="mdi:check-circle" class="h-3 w-3 text-emerald-600" />
                                        Nilai: {{ pkg.user_attempt.score }}
                                    </span>
                                    <span
                                        v-else-if="pkg.user_attempt?.status === 'in_progress'"
                                        class="inline-flex items-center gap-1 rounded-full border border-blue-200 bg-blue-50 px-2.5 py-0.5 text-[10px] font-extrabold text-blue-700"
                                    >
                                        <Icon icon="mdi:clock-outline" class="h-3 w-3 text-blue-500 animate-spin" />
                                        Sedang Berjalan
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1 rounded-full border border-indigo-200 bg-indigo-50/80 px-2.5 py-0.5 text-[10px] font-extrabold text-indigo-700"
                                    >
                                        <Icon icon="mdi:pencil-outline" class="h-3 w-3" />
                                        Belum Dikerjakan
                                    </span>
                                </div>
                            </div>

                            <!-- Title & Description -->
                            <div>
                                <h4 class="text-base font-black tracking-tight text-foreground transition-colors group-hover:text-indigo-600 line-clamp-2">
                                    {{ pkg.title }}
                                </h4>
                                <p v-if="pkg.description" class="mt-1 text-xs leading-relaxed text-muted-foreground line-clamp-2">
                                    {{ pkg.description }}
                                </p>
                            </div>

                            <!-- Meta Info (Soal & Jadwal) -->
                            <div class="flex items-center gap-3 pt-1 text-xs font-semibold text-muted-foreground">
                                <span class="flex items-center gap-1">
                                    <Icon icon="mdi:format-list-numbered" class="h-3.5 w-3.5 text-indigo-500" />
                                    {{ pkg.total_questions }} Soal
                                </span>
                                <span v-if="pkg.end_date" class="flex items-center gap-1 text-[11px]">
                                    <Icon icon="mdi:calendar-clock" class="h-3.5 w-3.5 text-amber-500" />
                                    s.d. {{ pkg.end_date }}
                                </span>
                            </div>
                        </div>

                        <!-- CTA Button -->
                        <div class="mt-5 border-t border-slate-100 pt-4">
                            <button
                                v-if="pkg.user_attempt?.status === 'completed'"
                                @click="handleOpenPackage(pkg)"
                                class="inline-flex w-full items-center justify-center gap-1.5 rounded-2xl border-2 border-emerald-300 bg-emerald-50 px-4 py-2.5 text-xs font-black text-emerald-800 transition hover:bg-emerald-100"
                            >
                                <Icon icon="mdi:chart-bar" class="h-4 w-4 text-emerald-600" />
                                <span>Lihat Hasil &amp; Jawaban</span>
                            </button>

                            <button
                                v-else-if="pkg.user_attempt?.status === 'in_progress'"
                                @click="handleOpenPackage(pkg)"
                                class="inline-flex w-full items-center justify-center gap-1.5 rounded-2xl bg-amber-500 px-4 py-2.5 text-xs font-black text-white shadow-sm hover:bg-amber-600 transition"
                            >
                                <Icon icon="mdi:play" class="h-4 w-4" />
                                <span>Lanjutkan Tugas</span>
                            </button>

                            <button
                                v-else
                                @click="handleOpenPackage(pkg)"
                                class="inline-flex w-full items-center justify-center gap-1.5 rounded-2xl bg-indigo-600 px-4 py-2.5 text-xs font-black text-white shadow-sm shadow-indigo-600/20 hover:bg-indigo-700 transition"
                            >
                                <Icon icon="mdi:pencil-outline" class="h-4 w-4" />
                                <span>Mulai Kerjakan</span>
                            </button>
                        </div>
                    </article>
                </div>

                <!-- Empty state jika belum ada PR -->
                <div v-else class="flex flex-col items-center justify-center rounded-3xl border border-dashed border-slate-300 bg-card p-10 text-center shadow-xs">
                    <span class="flex h-16 w-16 items-center justify-center rounded-3xl bg-indigo-50 text-indigo-600">
                        <Icon icon="mdi:check-all" class="h-8 w-8" />
                    </span>
                    <h4 class="mt-3.5 text-base font-extrabold text-foreground">Semua Tugas Sudah Selesai!</h4>
                    <p class="mt-1 max-w-sm text-xs leading-relaxed text-muted-foreground">
                        Belum ada paket PR baru untuk kelasmu saat ini. Silakan nantikan rilis PR berikutnya setiap pekan.
                    </p>
                </div>
            </section>

            <!-- ====== SEKSI CATATAN GURU PEMBIMBING ====== -->
            <section class="space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                            <Icon icon="mdi:message-text-outline" class="h-4 w-4" />
                        </span>
                        <h3 class="text-lg sm:text-xl font-black tracking-tight text-foreground">Catatan Guru Pembimbing</h3>
                    </div>
                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-600">
                        {{ teacherNotes.length }} Catatan
                    </span>
                </div>

                <div v-if="teacherNotes.length > 0" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <article
                        v-for="note in teacherNotes"
                        :key="note.id"
                        class="relative flex flex-col justify-between rounded-3xl border border-slate-200/80 bg-card p-5 sm:p-6 shadow-xs transition-all hover:border-amber-300 hover:shadow-card-hover"
                    >
                        <div class="space-y-3">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-700 font-black text-sm">
                                        {{ (note.teacher_name || 'G')[0] }}
                                    </span>
                                    <div>
                                        <p class="text-xs font-bold text-foreground leading-tight">{{ note.teacher_name || 'Guru Pembimbing' }}</p>
                                        <p class="text-[10px] text-muted-foreground">
                                            <span v-if="note.week_number">Pekan {{ note.week_number }}</span>
                                            <span v-if="note.year"> · {{ note.year }}</span>
                                        </p>
                                    </div>
                                </div>
                                <span
                                    v-if="note.pr_score !== null && note.pr_score !== undefined"
                                    class="rounded-xl border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[11px] font-extrabold text-emerald-800"
                                >
                                    PR: {{ note.pr_score }}
                                </span>
                            </div>

                            <div class="rounded-2xl bg-slate-50/80 p-3.5 border border-slate-100">
                                <p class="text-xs sm:text-sm leading-relaxed text-slate-700">
                                    "{{ noteText(note) }}"
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center gap-1.5 text-[11px] font-semibold text-muted-foreground/80">
                            <Icon icon="mdi:clock-outline" class="h-3.5 w-3.5" />
                            <span>{{ note.created_at }}</span>
                        </div>
                    </article>
                </div>

                <div v-else class="flex flex-col items-center justify-center rounded-3xl border border-slate-200 bg-card p-8 text-center shadow-xs">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                        <Icon icon="mdi:notebook-outline" class="h-6 w-6" />
                    </span>
                    <p class="mt-2 text-sm font-bold text-foreground">Belum ada catatan guru pembimbing</p>
                    <p class="mt-0.5 text-xs text-muted-foreground">Catatan dan evaluasi belajar mingguan akan muncul di sini setelah guru menginput laporan.</p>
                </div>
            </section>

            <!-- ====== INFORMASI JADWAL & KOMITMEN BELAJAR ====== -->
            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="rounded-3xl border border-blue-100 bg-gradient-to-br from-blue-50/70 to-indigo-50/40 p-5 sm:p-6">
                    <div class="flex items-center gap-2.5 text-blue-900">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm">
                            <Icon icon="mdi:clock-fast" class="h-5 w-5" />
                        </span>
                        <div>
                            <h4 class="text-sm font-black">Ketentuan Pengerjaan PR</h4>
                            <p class="text-[11px] text-blue-700/80">Satu kali pengerjaan setiap paket</p>
                        </div>
                    </div>
                    <p class="mt-3 text-xs leading-relaxed text-blue-950/80">
                        Setiap paket PR pekanan hanya dapat dikerjakan 1 kali. Pastikan kamu mempersiapkan alat tulis, kertas coret-coretan, dan konsentrasi penuh sebelum memulai pengerjaan.
                    </p>
                </div>

                <div class="rounded-3xl border border-amber-100 bg-gradient-to-br from-amber-50/70 to-orange-50/40 p-5 sm:p-6">
                    <div class="flex items-center gap-2.5 text-amber-900">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500 text-white shadow-sm">
                            <Icon icon="mdi:shield-check" class="h-5 w-5" />
                        </span>
                        <div>
                            <h4 class="text-sm font-black">Prinsip Kejujuran &amp; Integritas</h4>
                            <p class="text-[11px] text-amber-700/80">Belajar mandiri untuk kemajuan nyata</p>
                        </div>
                    </div>
                    <p class="mt-3 text-xs leading-relaxed text-amber-950/80">
                        Kerjakan setiap soal secara mandiri tanpa bantuan kalkulator atau orang lain. Pembiasaan berpikir analitis dan jujur adalah kunci utama juara sejati.
                    </p>
                </div>
            </section>
        </div>
    </UserLayout>
</template>