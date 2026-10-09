<script setup>
import { inject, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import UserLayout from '@/Layouts/UserLayout.vue';
import StatCard from '@/Components/shared/StatCard.vue';

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
</script>

<template>
    <UserLayout>
        <Head title="Dasbor Belajar - KPM SMART" />

        <!-- Hero Welcome Banner -->
        <div class="anim-fade-in-up rounded-3xl bg-gradient-to-br from-emerald-800 via-emerald-700 to-teal-800 p-6 sm:p-8 lg:p-10 text-white mb-8 relative overflow-hidden shadow-xl shadow-emerald-950/15">
            <div class="absolute inset-0 opacity-15 pointer-events-none">
                <div class="absolute -top-12 -right-12 w-64 h-64 rounded-full bg-white blur-3xl"></div>
                <div class="absolute -bottom-16 -left-16 w-72 h-72 rounded-full bg-amber-400 blur-3xl"></div>
                <div class="grid-dots absolute inset-0"></div>
            </div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="max-w-xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur border border-white/20 text-xs font-semibold text-emerald-100 mb-4">
                        <Icon icon="mdi:school-outline" class="w-4 h-4 text-emerald-300" />
                        <span>{{ user?.kelas ? 'Kelas ' + user.kelas : 'Siswa KPM SMART' }}</span>
                        <span v-if="user?.level" class="text-white/60">• Level {{ user.level }}</span>
                    </div>

                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight mb-2 text-white">
                        {{ greetingTime }}, {{ user?.name }}! <span class="inline-block animate-bounce">👋</span>
                    </h2>
                    <p class="text-emerald-100/85 text-xs sm:text-sm leading-relaxed">
                        Terus asah kemampuanmu dengan mengerjakan latihan tugas harian dan raih peringkat terbaik di kelasmu.
                    </p>

                    <div class="flex flex-wrap items-center gap-3 mt-6">
                        <Link
                            :href="route('user.packages.index')"
                            class="inline-flex items-center gap-2 bg-white text-emerald-900 hover:bg-emerald-50 px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold shadow-md hover:shadow-lg transition-all duration-200 active:scale-95"
                        >
                            <Icon icon="mdi:book-open-page-variant" class="w-4 h-4 text-emerald-700" />
                            Mulai Kerjakan Tugas
                        </Link>
                        <Link
                            :href="route('user.leaderboard')"
                            class="inline-flex items-center gap-2 bg-white/15 hover:bg-white/25 backdrop-blur border border-white/20 text-white px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all duration-200"
                        >
                            <Icon icon="mdi:trophy-outline" class="w-4 h-4 text-amber-300" />
                            Lihat Peringkat
                        </Link>
                    </div>
                </div>

                <!-- Gamification Quick Badge Circle on Desktop -->
                <div class="hidden lg:flex flex-col items-center justify-center p-5 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 text-center min-w-[200px] shadow-sm">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-white text-xl font-black shadow-md mb-2">
                        {{ g.level || 1 }}
                    </div>
                    <p class="text-xs font-bold text-amber-200 tracking-wide uppercase">{{ g.badge || 'Pemula' }}</p>
                    <p class="text-[11px] text-white/70 mt-0.5">{{ g.xp || 0 }} XP Terkumpul</p>
                </div>
            </div>
        </div>

        <!-- Gamification Widgets Section -->
        <div class="anim-fade-in-up anim-delay-1 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-8">
            <!-- Level & XP Card -->
            <div class="rounded-2xl border border-amber-200/80 bg-gradient-to-br from-amber-50/40 via-card to-card p-5 sm:p-6 shadow-xs hover:shadow-md transition-all duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-white text-lg font-black shadow-sm">
                            {{ g.level || 1 }}
                        </div>
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground">Level Belajar</p>
                            <p class="text-sm font-extrabold text-foreground">{{ g.badge || 'Pemula' }}</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-amber-700 bg-amber-100/80 px-2.5 py-1 rounded-full border border-amber-200">
                        {{ g.xp || 0 }} XP
                    </span>
                </div>
                <div class="space-y-1.5">
                    <div class="flex justify-between text-xs font-semibold text-muted-foreground">
                        <span>Progress Level {{ g.level || 1 }}</span>
                        <span>{{ xpProgress }}%</span>
                    </div>
                    <div class="h-2.5 bg-muted rounded-full overflow-hidden p-0.5">
                        <div
                            class="h-full bg-gradient-to-r from-amber-400 to-amber-500 rounded-full transition-all duration-700"
                            :style="{ width: xpProgress + '%' }"
                        ></div>
                    </div>
                </div>
            </div>

            <!-- Streak Card -->
            <div class="rounded-2xl border border-orange-200/80 bg-gradient-to-br from-orange-50/40 via-card to-card p-5 sm:p-6 shadow-xs hover:shadow-md transition-all duration-300">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-orange-100 text-orange-600 flex items-center justify-center text-2xl shadow-xs border border-orange-200/60">
                        <Icon icon="mdi:fire" class="w-6 h-6 text-orange-500" />
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground">Keaktifan (Streak)</p>
                        <p class="text-xl sm:text-2xl font-black text-foreground">
                            {{ g.streak?.current || 0 }} <span class="text-xs font-medium text-muted-foreground">Hari Berturut</span>
                        </p>
                    </div>
                </div>
                <p class="text-xs text-muted-foreground flex items-center justify-between border-t border-border/60 pt-3">
                    <span>Rekor Streak Terbaik:</span>
                    <span class="font-bold text-orange-700">{{ g.streak?.best || 0 }} Hari</span>
                </p>
            </div>

            <!-- Performance Card -->
            <div class="rounded-2xl border border-emerald-200/80 bg-gradient-to-br from-emerald-50/40 via-card to-card p-5 sm:p-6 shadow-xs hover:shadow-md transition-all duration-300 sm:col-span-2 lg:col-span-1">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-2xl shadow-xs border border-emerald-200/60">
                        <Icon icon="mdi:target" class="w-6 h-6 text-emerald-600" />
                    </div>
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground">Performa Tugas</p>
                        <p class="text-base font-extrabold text-foreground">{{ totalAttempts }} Kali Pengerjaan</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="bg-card rounded-xl p-2.5 border border-border/60 text-center">
                        <span class="text-muted-foreground text-[11px] block">Nilai Tertinggi</span>
                        <p class="font-black text-sm text-emerald-600 mt-0.5">{{ bestScore }}%</p>
                    </div>
                    <div class="bg-card rounded-xl p-2.5 border border-border/60 text-center">
                        <span class="text-muted-foreground text-[11px] block">Rata-rata</span>
                        <p class="font-black text-sm text-foreground mt-0.5">{{ averageScore }}%</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="anim-fade-in-up anim-delay-2 grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5 mb-8">
            <StatCard
                title="Total Percobaan"
                :value="totalAttempts"
                color="pine-teal"
                icon="<svg class='w-5 h-5' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2'><path stroke-linecap='round' stroke-linejoin='round' d='M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'/></svg>"
            />
            <StatCard
                title="Skor Tertinggi"
                :value="bestScore + '%'"
                color="fern"
                icon="<svg class='w-5 h-5' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2'><path stroke-linecap='round' stroke-linejoin='round' d='M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 01-.982-3.172M9.497 14.25a7.454 7.454 0 00.981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 007.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M18.75 4.236c.982.143 1.954.317 2.916.52A6.003 6.003 0 0116.27 9.728M18.75 4.236V4.5c0 2.108-.966 3.99-2.48 5.228m0 0a6.015 6.015 0 01-2.77.665 6.015 6.015 0 01-2.77-.665'/></svg>"
            />
            <StatCard
                title="Rata-rata Skor"
                :value="averageScore + '%'"
                color="dry-sage"
                icon="<svg class='w-5 h-5' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2'><path stroke-linecap='round' stroke-linejoin='round' d='M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z'/></svg>"
            />
            <StatCard
                title="Soal Tersedia"
                :value="packages.length"
                color="hunter-green"
                icon="<svg class='w-5 h-5' fill='none' viewBox='0 0 24 24' stroke='currentColor' stroke-width='2'><path stroke-linecap='round' stroke-linejoin='round' d='M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'/></svg>"
            />
        </div>

        <!-- Quick Navigation Cards -->
        <div class="anim-fade-in-up anim-delay-3">
            <h3 class="text-sm font-bold uppercase tracking-wider text-muted-foreground mb-4">Akses Cepat</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
                <Link
                    :href="route('user.packages.index')"
                    class="group rounded-2xl border border-border/80 bg-card p-5 sm:p-6 shadow-xs hover:shadow-md hover:border-primary/40 transition-all duration-300 hover:-translate-y-1"
                >
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center mb-4 group-hover:scale-105 group-hover:bg-primary group-hover:text-white transition-all duration-300 shadow-2xs">
                        <Icon icon="mdi:book-open-page-variant" class="w-6 h-6" />
                    </div>
                    <h4 class="font-bold text-sm sm:text-base text-foreground mb-1 group-hover:text-primary transition-colors">Bank Soal Tugas PR</h4>
                    <p class="text-xs text-muted-foreground leading-relaxed">Jelajahi dan kerjakan paket soal tugas yang tersedia untuk kelasmu.</p>
                </Link>

                <Link
                    :href="route('user.practice.history')"
                    class="group rounded-2xl border border-border/80 bg-card p-5 sm:p-6 shadow-xs hover:shadow-md hover:border-teal-500/40 transition-all duration-300 hover:-translate-y-1"
                >
                    <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 border border-teal-100 flex items-center justify-center mb-4 group-hover:scale-105 group-hover:bg-teal-600 group-hover:text-white transition-all duration-300 shadow-2xs">
                        <Icon icon="mdi:history" class="w-6 h-6" />
                    </div>
                    <h4 class="font-bold text-sm sm:text-base text-foreground mb-1 group-hover:text-teal-600 transition-colors">Riwayat & Pembahasan</h4>
                    <p class="text-xs text-muted-foreground leading-relaxed">Tinjau kembali nilai, kunci jawaban, dan pembahasan soal yang pernah kamu kerjakan.</p>
                </Link>

                <Link
                    :href="route('user.leaderboard')"
                    class="group rounded-2xl border border-border/80 bg-card p-5 sm:p-6 shadow-xs hover:shadow-md hover:border-amber-500/40 transition-all duration-300 hover:-translate-y-1"
                >
                    <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center mb-4 group-hover:scale-105 group-hover:bg-amber-500 group-hover:text-white transition-all duration-300 shadow-2xs">
                        <Icon icon="mdi:trophy" class="w-6 h-6" />
                    </div>
                    <h4 class="font-bold text-sm sm:text-base text-foreground mb-1 group-hover:text-amber-600 transition-colors">Papan Peringkat</h4>
                    <p class="text-xs text-muted-foreground leading-relaxed">Pantau posisimu dan bersaing secara sportif dengan teman sekelas.</p>
                </Link>
            </div>
        </div>
    </UserLayout>
</template>
