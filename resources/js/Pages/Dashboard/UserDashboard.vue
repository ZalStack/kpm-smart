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
    teacherNotes: { type: Array, default: () => [] },
});

const greetingTime = computed(() => {
    const hour = new Date().getHours();
    if (hour < 11) return 'Selamat Pagi';
    if (hour < 15) return 'Selamat Siang';
    if (hour < 18) return 'Selamat Sore';
    return 'Selamat Malam';
});

const firstName = computed(() => (user.value?.name || 'Siswa').split(' ')[0]);

const noteText = (note) => note.notes || note.message || '';
</script>

<template>
    <UserLayout>
        <Head title="Beranda - PR KK" />
        <template #header-title>Beranda</template>
        <template #header-sub>Ringkasan belajar dan catatan guru terbarumu.</template>

        <!-- HERO SELAMAT DATANG -->
        <section class="hero-panel anim-fade-in-up p-6 sm:p-8 lg:p-10 mb-6 sm:mb-8">
            <div class="relative z-10 max-w-2xl">
                <div class="mb-4 inline-flex flex-wrap items-center gap-2 rounded-full border border-white/25 bg-white/15 px-3.5 py-1.5 text-[11px] font-extrabold text-blue-100 backdrop-blur-sm">
                    <Icon icon="mdi:school-outline" class="h-3.5 w-3.5 text-cyan-200" />
                    <span>{{ user?.student_class ? 'Kelas ' + user.student_class : (user?.kelas ? 'Kelas ' + user.kelas : 'Siswa PR KK') }}</span>
                    <span v-if="user?.bidang" class="text-white/80">· {{ user.bidang }}</span>
                    <span v-if="user?.level" class="text-white/80">· Level {{ user.level }}</span>
                </div>

                <h2 class="text-[1.75rem] font-extrabold leading-tight tracking-tight text-white sm:text-4xl lg:text-5xl">
                    {{ greetingTime }}, {{ firstName }}! <span class="inline-block">👋</span>
                </h2>
                <p class="mt-3 max-w-md text-sm leading-relaxed text-blue-100/90">
                    Lanjutkan latihanmu hari ini. Kerjakan PR pekanan terstruktur dan pahami setiap konsep lebih dalam.
                </p>

                <div class="mt-7 flex flex-wrap items-center gap-3">
                    <Link
                        :href="route('user.packages.index')"
                        class="inline-flex items-center gap-2 rounded-2xl bg-white px-6 py-3.5 text-sm font-extrabold text-brand-indigo shadow-lg shadow-indigo-200/40 transition-all duration-200 hover:-translate-y-0.5 hover:bg-blue-50 active:translate-y-0"
                    >
                        <Icon icon="mdi:book-open-page-variant" class="h-4 w-4 text-brand-indigo" />
                        Mulai Kerjakan PR
                    </Link>
                    <Link
                        :href="route('user.leaderboard')"
                        class="inline-flex items-center gap-2 rounded-2xl border border-white/25 bg-white/10 px-5 py-3.5 text-sm font-extrabold text-white backdrop-blur-sm transition-all duration-200 hover:bg-white/20"
                    >
                        <Icon icon="mdi:trophy-outline" class="h-4 w-4 text-amber-300" />
                        Lihat Peringkat
                    </Link>
                </div>
            </div>
        </section>

        <!-- RINGKASAN BELAJAR -->
        <section class="anim-fade-in-up anim-delay-1 mt-4">
            <div class="mb-3 flex items-center justify-between gap-3">
                <div>
                    <h3 class="section-title">Ringkasan Belajar</h3>
                    <p class="text-xs text-muted-foreground">Ketercapaian latihan dan performa nilai</p>
                </div>
                <Link
                    :href="route('user.practice.history')"
                    class="back-link"
                >
                    Lihat Semua Riwayat <Icon icon="mdi:arrow-right" class="h-3 w-3 ml-0.5" />
                </Link>
            </div>

            <div class="grid-stats mb-8">
                <!-- Total Percobaan -->
                <div class="stat-tile-v2">
                    <div class="flex items-center justify-between mb-4">
                        <span class="stat-label">Percobaan</span>
                        <span class="icon-box-sm bg-blue-50 text-brand-blue">
                            <Icon icon="mdi:check-circle-outline" class="h-5 w-5" />
                        </span>
                    </div>
                    <p class="stat-value">{{ totalAttempts }}</p>
                    <p class="text-xs text-muted-foreground">kali mengerjakan</p>
                </div>

                <!-- Skor Tertinggi -->
                <div class="stat-tile-v2">
                    <div class="flex items-center justify-between mb-4">
                        <span class="stat-label">Tertinggi</span>
                        <span class="icon-box-sm bg-amber-50 text-brand-amber">
                            <Icon icon="mdi:trophy-outline" class="h-5 w-5" />
                        </span>
                    </div>
                    <p class="stat-value text-brand-amber">{{ bestScore }}%</p>
                    <p class="text-xs text-muted-foreground">rekor pribadi</p>
                </div>

                <!-- Rata-rata -->
                <div class="stat-tile-v2">
                    <div class="flex items-center justify-between mb-4">
                        <span class="stat-label">Rata-rata</span>
                        <span class="icon-box-sm bg-indigo-50 text-brand-indigo">
                            <Icon icon="mdi:chart-bar" class="h-5 w-5" />
                        </span>
                    </div>
                    <p class="stat-value text-brand-indigo">{{ averageScore }}%</p>
                    <p class="text-xs text-muted-foreground">nilai rata-rata</p>
                </div>

                <!-- PR Tersedia -->
                <div class="stat-tile-v2">
                    <div class="flex items-center justify-between mb-4">
                        <span class="stat-label">Tersedia</span>
                        <span class="icon-box-sm bg-emerald-50 text-brand-emerald">
                            <Icon icon="mdi:book-open-variant" class="h-5 w-5" />
                        </span>
                    </div>
                    <p class="stat-value text-brand-emerald">{{ packages.length }}</p>
                    <p class="text-xs text-muted-foreground">paket PR pekanan</p>
                </div>
            </div>
        </section>

        <!-- CATATAN GURU -->
        <section class="anim-fade-in-up anim-delay-2 mt-4">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h3 class="section-title">Catatan Guru</h3>
                    <p class="text-xs text-muted-foreground">Pesan dari pembimbing belajarmu</p>
                </div>
                <span class="chip bg-muted text-muted-foreground">{{ teacherNotes.length }} catatan</span>
            </div>

            <div v-if="teacherNotes.length > 0" class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <article
                    v-for="(note, i) in teacherNotes"
                    :key="note.id"
                    class="ui-card-hover p-5 sm:p-6"
                    :style="{ animationDelay: (i * 80) + 'ms' }"
                >
                    <div class="mb-4 flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-start gap-3">
                            <span class="icon-box shrink-0 border border-amber-100 bg-amber-50 text-amber-600">
                                <Icon icon="mdi:notebook-edit-outline" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-extrabold text-foreground">{{ note.title }}</p>
                                <p class="mt-0.5 text-xs font-extrabold text-muted-foreground">
                                    {{ note.teacher_name || 'Guru Pembimbing' }}
                                    <span v-if="note.week_number"> · Pekan {{ note.week_number }}</span>
                                </p>
                            </div>
                        </div>
                        <span
                            v-if="note.pr_score !== null && note.pr_score !== undefined"
                            class="chip shrink-0 border border-emerald-200 bg-emerald-50 text-emerald-700"
                        >
                            PR {{ note.pr_score }}
                        </span>
                    </div>

                    <p class="text-sm leading-relaxed text-muted-foreground mb-5">{{ noteText(note) }}</p>

                    <div class="flex items-center gap-1.5 border-t border-border/60 pt-4 text-[11px] font-extrabold text-muted-foreground/70">
                        <Icon icon="mdi:clock-outline" class="h-3.5 w-3.5" />
                        <span>{{ note.created_at }}</span>
                    </div>
                </article>
            </div>

            <!-- Empty state -->
            <div v-else class="empty-state">
                <span class="empty-state-icon">
                    <Icon icon="mdi:notebook-outline" class="h-8 w-8" />
                </span>
                <p class="text-sm font-extrabold text-foreground">Belum ada catatan guru</p>
                <p class="mt-2 max-w-sm text-xs leading-relaxed text-muted-foreground">
                    Catatan dari guru pembimbing akan muncul di sini setiap kali dikirimkan untukmu.
                </p>
            </div>
        </section>
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