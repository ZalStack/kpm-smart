<script setup>
import { inject, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
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
});

const g = computed(() => props.gamification || {
    xp: 0,
    level: 1,
    xp_in_level: 0,
    badge: 'Pemula',
    streak: { current: 0, best: 0 },
    total_attempts: 0,
    avg_score: 0,
    best_score: 0,
});

const xpProgress = computed(() => Math.min(100, Math.max(0, g.value.xp_in_level || 0)));

const greetingTime = computed(() => {
    const hour = new Date().getHours();
    if (hour < 11) return 'Selamat Pagi';
    if (hour < 15) return 'Selamat Siang';
    if (hour < 18) return 'Selamat Sore';
    return 'Selamat Malam';
});

const firstName = computed(() => (user.value?.name || 'Siswa').split(' ')[0]);

/** Kamu menentukan satu "langkah berikutnya" supaya dasbor terasa terarah. */
const nextAction = computed(() => {
    if (props.totalAttempts === 0) {
        return {
            title: 'Kerjakan PR pekanan pertamamu',
            desc: 'Mulai dari paket yang tersedia sesuai bidang belajarmu.',
            href: route('user.packages.index'),
            icon: 'mdi:rocket-launch-outline',
            cta: 'Mulai Sekarang',
        };
    }
    if (xpProgress.value < 100) {
        return {
            title: `Naik ke Level ${(g.value.level || 1) + 1}`,
            desc: `Kumpulkan ${(g.value.xp_to_next || 0)} XP lagi untuk membuka level berikutnya.`,
            href: route('user.packages.index'),
            icon: 'mdi:arrow-up-bold-circle-outline',
            cta: 'Kerjakan PR',
        };
    }
    return {
        title: 'Pertahankan nilaimu!',
        desc: 'Lihat posisimu di papan peringkat kelas.',
        href: route('user.leaderboard'),
        icon: 'mdi:trophy-outline',
        cta: 'Lihat Peringkat',
    };
});
</script>

<template>
    <UserLayout>
        <Head title="Dasbor Siswa - KPM SMART" />

        <!-- ====================== HERO SELAMAT DATANG ====================== -->
        <section class="hero-panel anim-fade-in-up p-6 sm:p-8 lg:p-10">
            <!-- Ornamen latar -->
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="absolute -right-16 -top-20 h-64 w-64 rounded-full bg-white/15 blur-3xl"></div>
                <div class="absolute -bottom-24 -left-16 h-72 w-72 rounded-full bg-cyan-400/25 blur-3xl"></div>
                <div class="grid-dots absolute inset-0 opacity-40"></div>
            </div>

            <div class="relative z-10 flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-xl">
                    <div class="mb-4 inline-flex flex-wrap items-center gap-2 rounded-full border border-white/25 bg-white/15 px-3 py-1.5 text-[11px] font-bold text-blue-50 backdrop-blur">
                        <Icon icon="mdi:school-outline" class="h-3.5 w-3.5 text-cyan-200" />
                        <span>{{ user?.student_class ? 'Kelas ' + user.student_class : (user?.kelas ? 'Kelas ' + user.kelas : 'Siswa KPM SMART') }}</span>
                        <span v-if="user?.bidang" class="text-white/70">· {{ user.bidang }}</span>
                        <span v-if="user?.level" class="text-white/60">· Level {{ user.level }}</span>
                    </div>

                    <h2 class="text-[1.6rem] font-extrabold leading-tight tracking-tight text-white sm:text-3xl lg:text-4xl">
                        {{ greetingTime }}, {{ firstName }}! <span class="inline-block">👋</span>
                    </h2>
                    <p class="mt-2.5 max-w-md text-sm leading-relaxed text-blue-100/90">
                        Lanjutkan latihanmu hari ini. Kerjakan PR pekanan terstruktur dan pahami setiap konsep lebih dalam.
                    </p>

                    <div class="mt-6 flex flex-wrap items-center gap-3">
                        <Link
                            :href="route('user.packages.index')"
                            class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-bold text-indigo-900 shadow-lg shadow-indigo-950/20 transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-50 active:translate-y-0"
                        >
                            <Icon icon="mdi:book-open-page-variant" class="h-4 w-4 text-indigo-700" />
                            Mulai Kerjakan PR Pekanan
                        </Link>
                        <Link
                            :href="route('user.leaderboard')"
                            class="inline-flex items-center gap-2 rounded-xl border border-white/25 bg-white/10 px-4 py-3 text-sm font-semibold text-white backdrop-blur transition-all duration-200 hover:bg-white/20"
                        >
                            <Icon icon="mdi:trophy-outline" class="h-4 w-4 text-amber-300" />
                            Lihat Peringkat
                        </Link>
                    </div>
                </div>

                <!-- Lencana Level (glass) -->
                <div class="hero-glass flex shrink-0 items-center gap-4 p-5 text-left sm:p-6 lg:min-w-[240px]">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 text-2xl font-black text-white shadow-lg shadow-amber-900/20">
                        {{ g.level || 1 }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-amber-200">Level Saat Ini</p>
                        <p class="truncate text-lg font-extrabold text-white">{{ g.badge || 'Pemula' }}</p>
                        <p class="text-xs text-blue-100/80">{{ g.xp || 0 }} XP Terkumpul</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ====================== LANGKAH BERIKUTNYA ====================== -->
        <section class="anim-fade-in-up anim-delay-1 mt-5 sm:mt-6">
            <Link
                :href="nextAction.href"
                class="ui-card ui-card-hover group flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-start gap-4">
                    <span class="icon-box shrink-0 bg-primary/10 text-primary transition-colors duration-300 group-hover:bg-primary group-hover:text-primary-foreground">
                        <Icon :icon="nextAction.icon" class="h-6 w-6" />
                    </span>
                    <div class="min-w-0">
                        <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-primary">Langkah Berikutnya</p>
                        <h3 class="mt-1 text-base font-bold tracking-tight text-foreground sm:text-lg">{{ nextAction.title }}</h3>
                        <p class="mt-0.5 text-sm text-muted-foreground">{{ nextAction.desc }}</p>
                    </div>
                </div>
                <span class="btn-primary shrink-0 self-start sm:self-auto">
                    {{ nextAction.cta }}
                    <Icon icon="mdi:arrow-right" class="h-4 w-4 transition-transform duration-200 group-hover:translate-x-1" />
                </span>
            </Link>
        </section>

        <!-- ====================== RINGKASAN PERFORMA ====================== -->
        <section class="anim-fade-in-up anim-delay-2 mt-5 sm:mt-6">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="section-title">Ringkasan Belajar</h3>
                <Link :href="route('user.practice.statistics')" class="text-xs font-bold text-primary transition hover:text-primary/80">
                    Lihat Statistik →
                </Link>
            </div>

            <div class="grid-stats">
                <!-- Total Percobaan -->
                <div class="stat-tile-v2">
                    <div class="flex items-center justify-between">
                        <span class="stat-label">Percobaan</span>
                        <span class="icon-box-sm bg-primary/10 text-primary">
                            <Icon icon="mdi:check-circle-outline" class="h-5 w-5" />
                        </span>
                    </div>
                    <p class="stat-value mt-3">{{ totalAttempts }}</p>
                    <p class="mt-1 text-xs text-muted-foreground">kali mengerjakan</p>
                </div>

                <!-- Skor Tertinggi -->
                <div class="stat-tile-v2">
                    <div class="flex items-center justify-between">
                        <span class="stat-label">Tertinggi</span>
                        <span class="icon-box-sm bg-amber-50 text-amber-600">
                            <Icon icon="mdi:trophy-outline" class="h-5 w-5" />
                        </span>
                    </div>
                    <p class="stat-value mt-3 text-amber-600">{{ bestScore }}%</p>
                    <p class="mt-1 text-xs text-muted-foreground">rekor pribadi</p>
                </div>

                <!-- Rata-rata -->
                <div class="stat-tile-v2">
                    <div class="flex items-center justify-between">
                        <span class="stat-label">Rata-rata</span>
                        <span class="icon-box-sm bg-indigo-50 text-indigo-600">
                            <Icon icon="mdi:chart-bar" class="h-5 w-5" />
                        </span>
                    </div>
                    <p class="stat-value mt-3 text-indigo-600">{{ averageScore }}%</p>
                    <p class="mt-1 text-xs text-muted-foreground">nilai rata-rata</p>
                </div>

                <!-- PR Tersedia -->
                <div class="stat-tile-v2">
                    <div class="flex items-center justify-between">
                        <span class="stat-label">Tersedia</span>
                        <span class="icon-box-sm bg-emerald-50 text-emerald-600">
                            <Icon icon="mdi:book-open-variant" class="h-5 w-5" />
                        </span>
                    </div>
                    <p class="stat-value mt-3 text-emerald-600">{{ packages.length }}</p>
                    <p class="mt-1 text-xs text-muted-foreground">paket PR pekanan</p>
                </div>
            </div>
        </section>

        <!-- ====================== PROGRES & KEAKTIFAN ====================== -->
        <section class="anim-fade-in-up anim-delay-3 mt-5 grid grid-cols-1 gap-4 sm:mt-6 lg:grid-cols-3">
            <!-- Progres Level -->
            <div class="ui-card p-5 sm:p-6">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="icon-box bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-sm">
                            <Icon icon="mdi:star-four-points-outline" class="h-6 w-6" />
                        </span>
                        <div>
                            <p class="stat-label">Level Belajar</p>
                            <p class="text-sm font-extrabold text-foreground">{{ g.badge || 'Pemula' }}</p>
                        </div>
                    </div>
                    <span class="chip bg-amber-100 text-amber-700">{{ g.xp || 0 }} XP</span>
                </div>

                <div class="flex items-center justify-between text-xs font-semibold text-muted-foreground">
                    <span>Menuju Level {{ (g.level || 1) + 1 }}</span>
                    <span class="text-primary">{{ xpProgress }}%</span>
                </div>
                <div class="progress-track mt-2">
                    <div class="progress-fill" :style="{ width: xpProgress + '%' }"></div>
                </div>
            </div>

            <!-- Streak -->
            <div class="ui-card p-5 sm:p-6">
                <div class="mb-4 flex items-center gap-3">
                    <span class="icon-box bg-orange-50 text-orange-500">
                        <Icon icon="mdi:fire" class="h-6 w-6" />
                    </span>
                    <div>
                        <p class="stat-label">Keaktifan</p>
                        <p class="text-sm font-extrabold text-foreground">Streak Belajar</p>
                    </div>
                </div>

                <div class="flex items-baseline gap-1.5">
                    <span class="text-3xl font-extrabold tracking-tight text-orange-500">{{ g.streak?.current || 0 }}</span>
                    <span class="text-xs font-semibold text-muted-foreground">hari berturut-turut</span>
                </div>

                <div class="mt-4 flex items-center justify-between border-t border-border/60 pt-3 text-xs">
                    <span class="text-muted-foreground">Rekor terbaik</span>
                    <span class="font-bold text-orange-600">{{ g.streak?.best || 0 }} hari</span>
                </div>
            </div>

            <!-- Performa Tugas -->
            <div class="ui-card p-5 sm:p-6">
                <div class="mb-4 flex items-center gap-3">
                    <span class="icon-box bg-indigo-50 text-indigo-600">
                        <Icon icon="mdi:target" class="h-6 w-6" />
                    </span>
                    <div>
                        <p class="stat-label">Performa Tugas</p>
                        <p class="text-sm font-extrabold text-foreground">{{ totalAttempts }} Pengerjaan</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 text-center">
                    <div class="rounded-xl border border-border/60 bg-muted/30 p-3">
                        <span class="text-[11px] font-semibold text-muted-foreground">Nilai Tertinggi</span>
                        <p class="mt-0.5 text-lg font-extrabold text-primary">{{ bestScore }}%</p>
                    </div>
                    <div class="rounded-xl border border-border/60 bg-muted/30 p-3">
                        <span class="text-[11px] font-semibold text-muted-foreground">Rata-rata</span>
                        <p class="mt-0.5 text-lg font-extrabold text-foreground">{{ averageScore }}%</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ====================== AKSES CEPAT ====================== -->
        <section class="anim-fade-in-up anim-delay-4 mt-8 sm:mt-10">
            <div class="mb-4 flex items-center gap-3">
                <span class="h-6 w-1 rounded-full bg-primary"></span>
                <h3 class="section-title">Akses Cepat</h3>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    :href="route('user.packages.index')"
                    class="ui-card ui-card-hover group p-5 sm:p-6"
                >
                    <span class="icon-box mb-4 border border-blue-100 bg-blue-50 text-blue-600 transition-all duration-300 group-hover:bg-blue-600 group-hover:text-white">
                        <Icon icon="mdi:book-open-page-variant" class="h-6 w-6" />
                    </span>
                    <h4 class="font-bold text-foreground transition-colors group-hover:text-primary">PR Pekanan</h4>
                    <p class="mt-1.5 text-sm leading-relaxed text-muted-foreground">Jelajahi dan kerjakan paket tugas PR pekanan sesuai bidang belajarmu.</p>
                    <span class="mt-4 inline-flex items-center gap-1.5 text-xs font-bold text-primary">
                        Buka <Icon icon="mdi:arrow-right" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-1" />
                    </span>
                </Link>

                <Link
                    :href="route('user.practice.history')"
                    class="ui-card ui-card-hover group p-5 sm:p-6"
                >
                    <span class="icon-box mb-4 border border-indigo-100 bg-indigo-50 text-indigo-600 transition-all duration-300 group-hover:bg-indigo-600 group-hover:text-white">
                        <Icon icon="mdi:history" class="h-6 w-6" />
                    </span>
                    <h4 class="font-bold text-foreground transition-colors group-hover:text-primary">Riwayat & Pembahasan</h4>
                    <p class="mt-1.5 text-sm leading-relaxed text-muted-foreground">Tinjau kembali nilai, kunci jawaban, dan pembahasan soal yang pernah dikerjakan.</p>
                    <span class="mt-4 inline-flex items-center gap-1.5 text-xs font-bold text-primary">
                        Buka <Icon icon="mdi:arrow-right" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-1" />
                    </span>
                </Link>

                <Link
                    :href="route('user.leaderboard')"
                    class="ui-card ui-card-hover group p-5 sm:p-6"
                >
                    <span class="icon-box mb-4 border border-amber-100 bg-amber-50 text-amber-600 transition-all duration-300 group-hover:bg-amber-500 group-hover:text-white">
                        <Icon icon="mdi:trophy" class="h-6 w-6" />
                    </span>
                    <h4 class="font-bold text-foreground transition-colors group-hover:text-primary">Papan Peringkat</h4>
                    <p class="mt-1.5 text-sm leading-relaxed text-muted-foreground">Pantau posisimu dan bersaing secara sportif dengan teman sekelas.</p>
                    <span class="mt-4 inline-flex items-center gap-1.5 text-xs font-bold text-primary">
                        Buka <Icon icon="mdi:arrow-right" class="h-3.5 w-3.5 transition-transform group-hover:translate-x-1" />
                    </span>
                </Link>
            </div>
        </section>
    </UserLayout>
</template>
