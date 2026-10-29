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

/** Isi catatan guru: utamakan teks notes, fallback ke teks message. */
const noteText = (note) => note.notes || note.message || '';
</script>

<template>
    <UserLayout>
        <Head title="Beranda - PR KK" />
        <template #header-title>Beranda</template>
        <template #header-sub>Ringkasan belajar dan catatan guru terbarumu.</template>

        <!-- ====================== HERO SELAMAT DATANG ====================== -->
        <section class="hero-panel anim-fade-in-up p-6 sm:p-8 lg:p-10">
            <!-- Ornamen latar -->
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="absolute -right-16 -top-20 h-64 w-64 rounded-full bg-white/15 blur-3xl"></div>
                <div class="absolute -bottom-24 -left-16 h-72 w-72 rounded-full bg-cyan-400/25 blur-3xl"></div>
                <div class="grid-dots absolute inset-0 opacity-40"></div>
            </div>

            <div class="relative z-10 max-w-2xl">
                <div class="mb-4 inline-flex flex-wrap items-center gap-2 rounded-full border border-white/25 bg-white/15 px-3 py-1.5 text-[11px] font-bold text-blue-50 backdrop-blur">
                    <Icon icon="mdi:school-outline" class="h-3.5 w-3.5 text-cyan-200" />
                    <span>{{ user?.student_class ? 'Kelas ' + user.student_class : (user?.kelas ? 'Kelas ' + user.kelas : 'Siswa PR KK') }}</span>
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
                        Mulai Kerjakan PR Siswa Kelas Khusus
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
        </section>

        <!-- ====================== RINGKASAN BELAJAR ====================== -->
        <section class="anim-fade-in-up anim-delay-1 mt-5 sm:mt-6">
            <div class="mb-3">
                <h3 class="section-title">Ringkasan Belajar</h3>
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

        <!-- ====================== CATATAN GURU ====================== -->
        <section class="anim-fade-in-up anim-delay-2 mt-6 sm:mt-8">
            <div class="mb-3 flex items-center justify-between gap-3">
                <h3 class="section-title">Catatan Guru</h3>
                <span class="chip bg-muted text-muted-foreground">{{ teacherNotes.length }} catatan</span>
            </div>

            <div v-if="teacherNotes.length > 0" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <article
                    v-for="(note, i) in teacherNotes"
                    :key="note.id"
                    class="ui-card p-5"
                    :style="{ animationDelay: (i * 60) + 'ms' }"
                >
                    <div class="mb-3 flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-start gap-3">
                            <span class="icon-box shrink-0 border border-amber-100 bg-amber-50 text-amber-600">
                                <Icon icon="mdi:notebook-edit-outline" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-foreground">{{ note.title }}</p>
                                <p class="mt-0.5 text-xs font-semibold text-muted-foreground">
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

                    <p class="text-sm leading-relaxed text-muted-foreground">{{ noteText(note) }}</p>

                    <p class="mt-3 flex items-center gap-1.5 border-t border-border/60 pt-3 text-[11px] font-semibold text-muted-foreground/70">
                        <Icon icon="mdi:clock-outline" class="h-3.5 w-3.5" />
                        {{ note.created_at }}
                    </p>
                </article>
            </div>

            <!-- Empty state -->
            <div v-else class="ui-card flex flex-col items-center px-6 py-10 text-center">
                <span class="icon-box mb-3 bg-muted/60 text-muted-foreground/60">
                    <Icon icon="mdi:notebook-outline" class="h-6 w-6" />
                </span>
                <p class="text-sm font-bold text-foreground">Belum ada catatan guru</p>
                <p class="mt-1 max-w-sm text-xs leading-relaxed text-muted-foreground">
                    Catatan dari guru pembimbing akan muncul di sini setiap kali dikirimkan untukmu.
                </p>
            </div>
        </section>
    </UserLayout>
</template>
