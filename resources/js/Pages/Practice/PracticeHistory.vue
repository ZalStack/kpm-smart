<script setup>
import { inject, ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import Input from '@/Components/ui/input/Input.vue';
import Select from '@/Components/ui/select/Select.vue';
import { Icon } from '@iconify/vue/offline';
import { timeAgo } from '@/lib/utils';
const route = inject('route');

const props = defineProps({
    sessions: { type: Array, default: () => [] },
});

const search = ref('');
const sortBy = ref('newest');

function formatDuration(seconds) {
    if (!seconds) return '0 mnt';
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    if (m >= 60) {
        const h = Math.floor(m / 60);
        const rm = m % 60;
        return `${h} jam ${rm} mnt`;
    }
    return m > 0 ? `${m} mnt ${s} dtk` : `${s} dtk`;
}

function scoreGrade(score) {
    const s = Number(score);
    if (s >= 90) return { label: 'Sangat Baik', color: 'text-emerald-600', bg: 'bg-emerald-50', ring: 'border-emerald-200', icon: 'mdi:trophy' };
    if (s >= 75) return { label: 'Baik', color: 'text-blue-600', bg: 'bg-blue-50', ring: 'border-blue-200', icon: 'mdi:check-circle' };
    if (s >= 60) return { label: 'Cukup', color: 'text-yellow-600', bg: 'bg-yellow-50', ring: 'border-yellow-200', icon: 'mdi:chart-bar' };
    return { label: 'Perlu Latihan', color: 'text-red-500', bg: 'bg-red-50', ring: 'border-red-200', icon: 'mdi:book-open-variant' };
}

function scoreBarColor(score) {
    const s = Number(score);
    if (s >= 90) return 'bg-emerald-500';
    if (s >= 75) return 'bg-blue-500';
    if (s >= 60) return 'bg-yellow-500';
    return 'bg-red-500';
}

const filteredSessions = computed(() => {
    let list = [...props.sessions];
    if (search.value.trim()) {
        const q = search.value.toLowerCase();
        list = list.filter(s => (s.package?.title || '').toLowerCase().includes(q));
    }
    if (sortBy.value === 'newest') list.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    else if (sortBy.value === 'oldest') list.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
    else if (sortBy.value === 'highest') list.sort((a, b) => (b.total_score || 0) - (a.total_score || 0));
    else if (sortBy.value === 'lowest') list.sort((a, b) => (a.total_score || 0) - (b.total_score || 0));
    return list;
});

const avgScore = computed(() => {
    if (!props.sessions.length) return 0;
    return props.sessions.reduce((s, e) => s + Number(e.total_score || 0), 0) / props.sessions.length;
});

const bestScore = computed(() => {
    if (!props.sessions.length) return 0;
    return Math.max(...props.sessions.map(s => Number(s.total_score || 0)));
});
</script>

<template>
    <UserLayout>
        <Head title="Riwayat Tugas - KPM SMART" />

        <template #header-title>Riwayat Pengerjaan</template>
        <template #header-sub>Semua tugas yang sudah kamu kerjakan, lengkap dengan nilai dan pembahasannya.</template>

        <!-- Ringkasan -->
        <div v-if="sessions.length > 0" class="anim-fade-in-up grid-stats mb-6">
            <div class="stat-tile-v2">
                <div class="flex items-center justify-between">
                    <span class="stat-label">Total Tugas</span>
                    <span class="icon-box-sm bg-primary/10 text-primary">
                        <Icon icon="mdi:clipboard-text-outline" class="h-5 w-5" />
                    </span>
                </div>
                <p class="stat-value mt-3">{{ sessions.length }}</p>
                <p class="mt-1 text-xs text-muted-foreground">sesi dikerjakan</p>
            </div>

            <div class="stat-tile-v2">
                <div class="flex items-center justify-between">
                    <span class="stat-label">Tertinggi</span>
                    <span class="icon-box-sm bg-amber-50 text-amber-600">
                        <Icon icon="mdi:trophy" class="h-5 w-5" />
                    </span>
                </div>
                <p class="stat-value mt-3 text-amber-600">{{ bestScore.toFixed(0) }}</p>
                <p class="mt-1 text-xs text-muted-foreground">nilai terbaik</p>
            </div>

            <div class="stat-tile-v2">
                <div class="flex items-center justify-between">
                    <span class="stat-label">Rata-rata</span>
                    <span class="icon-box-sm bg-emerald-50 text-emerald-600">
                        <Icon icon="mdi:chart-line" class="h-5 w-5" />
                    </span>
                </div>
                <p class="stat-value mt-3 text-emerald-600">{{ avgScore.toFixed(0) }}</p>
                <p class="mt-1 text-xs text-muted-foreground">nilai rata-rata</p>
            </div>

            <Link
                :href="route('user.practice.statistics')"
                class="stat-tile-v2 group flex flex-col justify-between border-primary/30"
            >
                <div class="flex items-center justify-between">
                    <span class="stat-label">Analitik</span>
                    <span class="icon-box-sm bg-indigo-50 text-indigo-600 transition-colors group-hover:bg-indigo-600 group-hover:text-white">
                        <Icon icon="mdi:chart-bar" class="h-5 w-5" />
                    </span>
                </div>
                <p class="mt-3 text-sm font-bold text-foreground">Lihat Statistik <span class="text-primary">→</span></p>
                <p class="mt-1 text-xs text-muted-foreground">analitik belajar lengkap</p>
            </Link>
        </div>

        <!-- Pencarian & Urutan -->
        <div v-if="sessions.length > 0" class="anim-fade-in mb-5 flex flex-col gap-3 sm:flex-row">
            <div class="relative flex-1">
                <Icon icon="mdi:magnify" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <Input v-model="search" type="text" placeholder="Cari judul tugas..." class="h-11 rounded-xl border-border/60 pl-10" />
            </div>
            <Select v-model="sortBy" class="h-11 rounded-xl border-border/60 sm:min-w-[180px]">
                <option value="newest">Terbaru</option>
                <option value="oldest">Terlama</option>
                <option value="highest">Nilai Tertinggi</option>
                <option value="lowest">Nilai Terendah</option>
            </Select>
        </div>

        <!-- Keadaan Kosong -->
        <div v-if="sessions.length === 0" class="empty-state anim-fade-in">
            <div class="empty-state-icon">
                <Icon icon="mdi:clipboard-text-outline" class="h-8 w-8" />
            </div>
            <h3 class="text-lg font-bold text-foreground">Belum Ada Riwayat</h3>
            <p class="mt-2 max-w-xs text-sm leading-relaxed text-muted-foreground">
                Kamu belum mengerjakan PR Pekanan apapun. Yuk mulai sekarang!
            </p>
            <Link :href="route('user.packages.index')" class="btn-primary mt-6 px-7 py-3">
                <Icon icon="mdi:package-variant" class="h-4 w-4" />
                Lihat PR Pekanan
            </Link>
        </div>

        <!-- Hasil Pencarian Kosong -->
        <div v-else-if="filteredSessions.length === 0" class="empty-state anim-fade-in">
            <div class="empty-state-icon">
                <Icon icon="mdi:magnify" class="h-8 w-8" />
            </div>
            <p class="text-base font-semibold text-foreground">Tidak Ditemukan</p>
            <p class="mt-1 text-sm text-muted-foreground">Coba kata kunci lain</p>
            <button @click="search = ''" class="btn-outline mt-5 px-5 py-2.5 text-xs">Reset pencarian</button>
        </div>

        <!-- Kartu Sesi -->
        <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <article
                v-for="(session, idx) in filteredSessions"
                :key="session.id"
                :style="{ animationDelay: (idx * 50) + 'ms' }"
                class="ui-card anim-fade-in-up ui-card-hover group overflow-hidden opacity-0 [animation-fill-mode:forwards]"
            >
                <!-- Bilah Skor -->
                <div class="h-1.5 w-full bg-muted/50">
                    <div
                        :class="['h-full rounded-full transition-all duration-700 ease-out', scoreBarColor(Number(session.total_score || 0))]"
                        :style="{ width: Math.min(Number(session.total_score || 0), 100) + '%' }"
                    ></div>
                </div>

                <div class="p-5">
                    <div class="mb-4">
                        <h3 class="line-clamp-2 text-sm font-semibold leading-tight text-foreground transition-colors group-hover:text-primary">
                            {{ session.package?.title || 'Tugas' }}
                        </h3>
                        <p class="mt-1.5 flex items-center gap-1.5 text-xs text-muted-foreground">
                            <Icon icon="mdi:calendar-blank-outline" class="h-3.5 w-3.5" />
                            {{ timeAgo(session.created_at) }}
                        </p>
                    </div>

                    <span
                        class="score-pill ring-1 mb-4 shadow-sm"
                        :class="[scoreGrade(Number(session.total_score || 0)).bg, scoreGrade(Number(session.total_score || 0)).color, scoreGrade(Number(session.total_score || 0)).ring]"
                    >
                        <Icon :icon="scoreGrade(Number(session.total_score || 0)).icon" class="h-4 w-4" />
                        Nilai: {{ Number(session.total_score || 0).toFixed(0) }}
                        <span class="opacity-70">· {{ scoreGrade(Number(session.total_score || 0)).label }}</span>
                    </span>

                    <div class="mb-4 grid grid-cols-3 gap-2">
                        <div class="rounded-xl border border-emerald-100/60 bg-emerald-50/80 p-2.5 text-center">
                            <p class="text-sm font-bold text-emerald-600">{{ session.correct_answer || 0 }}</p>
                            <p class="mt-0.5 text-[10px] text-emerald-700/60">Benar</p>
                        </div>
                        <div class="rounded-xl border border-red-100/60 bg-red-50/80 p-2.5 text-center">
                            <p class="text-sm font-bold text-red-500">{{ session.wrong_answer || 0 }}</p>
                            <p class="mt-0.5 text-[10px] text-red-700/60">Salah</p>
                        </div>
                        <div class="rounded-xl border border-border/40 bg-muted/50 p-2.5 text-center">
                            <p class="text-sm font-bold text-muted-foreground">{{ session.unanswered || 0 }}</p>
                            <p class="mt-0.5 text-[10px] text-muted-foreground/60">Kosong</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-t border-border/60 pt-3">
                        <span class="flex items-center gap-1.5 text-xs text-muted-foreground">
                            <Icon icon="mdi:clock-outline" class="h-3.5 w-3.5" />
                            {{ formatDuration(session.duration_seconds) }}
                        </span>
                        <Link
                            :href="route('user.practice.show', session.id)"
                            class="inline-flex items-center gap-1.5 text-xs font-bold text-primary transition-all duration-300 group-hover:gap-2.5"
                        >
                            Lihat Detail
                            <Icon icon="mdi:arrow-right" class="h-3.5 w-3.5 transition-transform duration-300 group-hover:translate-x-1" />
                        </Link>
                    </div>
                </div>
            </article>
        </div>

        <!-- Tautan Statistik -->
        <div v-if="sessions.length > 0" class="mt-8 text-center">
            <Link :href="route('user.practice.statistics')" class="inline-flex items-center gap-2 text-sm font-semibold text-primary transition hover:gap-3">
                <Icon icon="mdi:chart-box-outline" class="h-4 w-4" />
                Lihat Statistik Lengkap
                <Icon icon="mdi:arrow-right" class="h-4 w-4" />
            </Link>
        </div>
    </UserLayout>
</template>
