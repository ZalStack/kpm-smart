<script setup>
import { inject, computed, ref } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import { Icon } from '@iconify/vue/offline';
import Input from '@/Components/ui/input/Input.vue';

const route = inject('route');
const page = usePage();

const props = defineProps({
    leaderboard: { type: Array, default: () => [] },
    currentUserRank: { type: Object, default: null },
});

const searchQuery = ref('');

const topThree = computed(() => props.leaderboard.slice(0, 3));

/** Susun podium: juara 2 di kiri, juara 1 di tengah, juara 3 di kanan. */
const podiumOrder = computed(() => {
    const list = [...topThree.value];
    if (list.length < 3) return list;
    const byRank = (r) => list.find(e => e.rank === r);
    return [byRank(2), byRank(1), byRank(3)].filter(Boolean);
});

const remainingList = computed(() => {
    let list = props.leaderboard.slice(3);
    if (searchQuery.value.trim()) {
        const q = searchQuery.value.toLowerCase();
        list = props.leaderboard.filter(e => (e.user?.name || '').toLowerCase().includes(q));
    }
    return list;
});

function getRankTheme(rank) {
    if (rank === 1) return {
        medal: 'mdi:crown',
        badgeBg: 'bg-gradient-to-br from-amber-400 to-amber-500 text-white',
        border: 'border-amber-300',
        ring: 'ring-amber-300/40',
        bg: 'bg-gradient-to-b from-amber-50 to-card',
        avatarBorder: 'border-amber-400 ring-amber-400/30',
        text: 'text-amber-500',
        height: 'lg:pb-8 lg:-mt-6',
    };
    if (rank === 2) return {
        medal: 'mdi:medal',
        badgeBg: 'bg-gradient-to-br from-slate-400 to-slate-500 text-white',
        border: 'border-slate-300',
        ring: 'ring-slate-300/40',
        bg: 'bg-gradient-to-b from-slate-50 to-card',
        avatarBorder: 'border-slate-400 ring-slate-400/30',
        text: 'text-slate-400',
        height: '',
    };
    if (rank === 3) return {
        medal: 'mdi:medal',
        badgeBg: 'bg-gradient-to-br from-amber-700 to-amber-800 text-white',
        border: 'border-amber-600/30',
        ring: 'ring-amber-600/30',
        bg: 'bg-gradient-to-b from-amber-50 to-card',
        avatarBorder: 'border-amber-600 ring-amber-600/30',
        text: 'text-amber-700',
        height: '',
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
    };
}

const isCurrentUser = (id) => id === page.props.auth?.user?.id;
</script>

<template>
    <UserLayout>
        <Head title="Papan Peringkat - KPM SMART" />

        <template #header-title>Papan Peringkat</template>
        <template #header-sub>Peringkat siswa terbaik berdasarkan rata-rata nilai latihan dan ujian.</template>

        <div class="mx-auto max-w-5xl space-y-6">
            <!-- Peringkatmu -->
            <section
                v-if="currentUserRank"
                class="anim-fade-in-up relative overflow-hidden rounded-3xl bg-gradient-to-r from-primary via-indigo-600 to-violet-700 p-6 text-white shadow-lg shadow-primary/10"
            >
                <div class="pointer-events-none absolute -bottom-10 -right-10 h-40 w-40 rounded-full bg-white/10 blur-2xl"></div>
                <div class="pointer-events-none absolute -left-8 -top-8 h-32 w-32 rounded-full bg-emerald-400/20 blur-xl"></div>

                <div class="relative z-10 flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl border border-white/20 bg-white/15 text-2xl font-black text-amber-300 shadow-inner backdrop-blur">
                            #{{ currentUserRank.rank }}
                        </span>
                        <div class="min-w-0">
                            <span class="mb-1 inline-flex items-center gap-1.5 rounded-full bg-white/20 px-2.5 py-0.5 text-xs font-semibold backdrop-blur-sm">
                                <Icon icon="mdi:account" class="h-3.5 w-3.5" />
                                Peringkatmu Saat Ini
                            </span>
                            <h2 class="truncate text-xl font-bold tracking-tight">{{ page.props.auth?.user?.name }}</h2>
                            <p class="mt-0.5 text-xs text-white/80">{{ currentUserRank.total_attempts }} sesi tugas telah diselesaikan</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3 rounded-xl border border-white/10 bg-black/15 p-3 text-center backdrop-blur-sm sm:gap-4">
                        <div class="px-2">
                            <p class="text-[11px] font-medium text-white/75">Rata-rata</p>
                            <p class="mt-0.5 text-base font-extrabold text-amber-300 sm:text-lg">{{ currentUserRank.avg_score }}%</p>
                        </div>
                        <div class="border-x border-white/10 px-2">
                            <p class="text-[11px] font-medium text-white/75">Tertinggi</p>
                            <p class="mt-0.5 text-base font-extrabold sm:text-lg">{{ currentUserRank.best_score }}%</p>
                        </div>
                        <div class="px-2">
                            <p class="text-[11px] font-medium text-white/75">Selesai</p>
                            <p class="mt-0.5 text-base font-extrabold sm:text-lg">{{ currentUserRank.total_attempts }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Podium Top 3 -->
            <div v-if="leaderboard.length >= 3 && !searchQuery.trim()" class="space-y-3">
                <h3 class="flex items-center gap-1.5 text-sm font-bold uppercase tracking-wider text-foreground">
                    <Icon icon="mdi:podium-gold" class="h-4 w-4 text-amber-500" />
                    Top 3 Prestasi Terbaik
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
                            getRankTheme(entry.rank).ring,
                            entry.rank === 1 ? 'ring-2' : ''
                        ]"
                    >
                        <!-- Lencana peringkat -->
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
                        <p class="mt-0.5 text-xs text-muted-foreground">{{ entry.total_attempts }} tugas</p>

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
                        </div>
                    </div>
                </div>
            </div>

            <!-- Daftar Peringkat -->
            <div class="ui-card overflow-hidden">
                <div class="flex flex-col gap-3 border-b border-border/60 bg-muted/20 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                    <div class="flex items-center gap-2">
                        <Icon icon="mdi:format-list-numbered" class="h-5 w-5 text-primary" />
                        <h3 class="text-base font-bold text-foreground">Daftar Peringkat Siswa</h3>
                    </div>
                    <div class="relative w-full sm:w-64">
                        <Icon icon="mdi:magnify" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input v-model="searchQuery" placeholder="Cari nama siswa..." class="h-9 rounded-xl pl-9 text-xs" />
                    </div>
                </div>

                <!-- Kosong -->
                <div v-if="leaderboard.length === 0" class="empty-state !border-0 !rounded-none">
                    <div class="empty-state-icon">
                        <Icon icon="mdi:trophy-outline" class="h-8 w-8" />
                    </div>
                    <h3 class="text-base font-bold text-foreground">Belum Ada Data Peringkat</h3>
                    <p class="mt-1.5 max-w-xs text-sm text-muted-foreground">
                        Mulai selesaikan tugas latihan untuk mencatat skor dan masuk ke papan peringkat!
                    </p>
                    <Link :href="route('user.packages.index')" class="btn-primary mt-5 px-5 py-2.5 text-xs">
                        <Icon icon="mdi:play" class="h-4 w-4" />
                        Mulai Kerjakan Tugas
                    </Link>
                </div>

                <!-- Baris -->
                <div v-else class="divide-y divide-border/40">
                    <div
                        v-for="entry in (searchQuery.trim() ? remainingList : leaderboard)"
                        :key="entry.user?.id"
                        :class="[
                            'flex items-center gap-3 px-4 py-3.5 transition-colors sm:gap-4 sm:px-6',
                            isCurrentUser(entry.user?.id) ? 'bg-primary/10 font-semibold hover:bg-primary/15' : 'hover:bg-muted/30'
                        ]"
                    >
                        <!-- Nomor -->
                        <div class="w-8 shrink-0 text-center sm:w-10">
                            <span v-if="entry.rank <= 3" :class="['inline-flex h-7 w-7 items-center justify-center rounded-xl text-xs font-black text-white shadow-sm', getRankTheme(entry.rank).badgeBg]">
                                {{ entry.rank }}
                            </span>
                            <span v-else class="text-xs font-bold text-muted-foreground sm:text-sm">#{{ entry.rank }}</span>
                        </div>

                        <!-- Avatar -->
                        <span :class="[
                            'flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-xs font-bold shadow-sm sm:h-10 sm:w-10 sm:text-sm',
                            isCurrentUser(entry.user?.id) ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground'
                        ]">
                            {{ (entry.user?.name || 'U').charAt(0).toUpperCase() }}
                        </span>

                        <!-- Nama -->
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="truncate text-sm font-semibold text-foreground">{{ entry.user?.name }}</p>
                                <span v-if="isCurrentUser(entry.user?.id)" class="shrink-0 rounded-full bg-primary px-2 py-0.5 text-[10px] font-bold text-primary-foreground">
                                    Kamu
                                </span>
                            </div>
                            <p class="mt-0.5 text-xs text-muted-foreground">{{ entry.total_attempts }} kali tugas dikerjakan</p>
                        </div>

                        <!-- Skor -->
                        <div class="shrink-0 pl-2 text-right">
                            <p class="text-sm font-extrabold text-primary sm:text-base">{{ entry.avg_score }}%</p>
                            <p class="text-[11px] text-muted-foreground">terbaik: <strong class="text-foreground">{{ entry.best_score }}%</strong></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigasi -->
            <div class="flex items-center justify-between pt-2">
                <Link :href="route('user.practice.statistics')" class="inline-flex items-center gap-1.5 text-sm font-medium text-primary transition hover:text-primary/80">
                    <Icon icon="mdi:chart-box-outline" class="h-4 w-4" />
                    Lihat Statistik Lengkap Saya
                </Link>
                <Link :href="route('user.dashboard')" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground transition hover:text-foreground">
                    <Icon icon="mdi:arrow-left" class="h-4 w-4" />
                    Dasbor
                </Link>
            </div>
        </div>
    </UserLayout>
</template>
