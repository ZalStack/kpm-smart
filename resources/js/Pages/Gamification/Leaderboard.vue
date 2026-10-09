<script setup>
import { inject, computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import { Icon } from '@iconify/vue/offline';
import Input from '@/Components/ui/input/Input.vue';

const route = inject('route');

const props = defineProps({
    leaderboard: { type: Array, default: () => [] },
    currentUserRank: { type: Object, default: null },
});

const searchQuery = ref('');

const topThree = computed(() => {
    return props.leaderboard.slice(0, 3);
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
        title: 'Juara 1',
        medal: 'mdi:crown',
        medalColor: 'text-amber-400',
        badgeBg: 'bg-gradient-to-br from-amber-400 to-amber-500 text-white',
        border: 'border-amber-300 dark:border-amber-600/50',
        ring: 'ring-amber-300/40',
        bg: 'from-amber-500/10 via-amber-500/5 to-card',
        avatarBorder: 'border-amber-400 ring-amber-400/30',
        height: 'sm:order-2',
    };
    if (rank === 2) return {
        title: 'Juara 2',
        medal: 'mdi:medal',
        medalColor: 'text-slate-400',
        badgeBg: 'bg-gradient-to-br from-slate-400 to-slate-500 text-white',
        border: 'border-slate-300 dark:border-slate-600/50',
        ring: 'ring-slate-300/40',
        bg: 'from-slate-500/10 via-slate-500/5 to-card',
        avatarBorder: 'border-slate-400 ring-slate-400/30',
        height: 'sm:order-1',
    };
    if (rank === 3) return {
        title: 'Juara 3',
        medal: 'mdi:medal',
        medalColor: 'text-amber-700',
        badgeBg: 'bg-gradient-to-br from-amber-700 to-amber-800 text-white',
        border: 'border-amber-700/30 dark:border-amber-700/50',
        ring: 'ring-amber-700/30',
        bg: 'from-amber-700/10 via-amber-700/5 to-card',
        avatarBorder: 'border-amber-700 ring-amber-700/30',
        height: 'sm:order-3',
    };
    return {
        title: `#${rank}`,
        medal: '',
        medalColor: 'text-muted-foreground',
        badgeBg: 'bg-muted text-muted-foreground',
        border: 'border-border',
        ring: '',
        bg: 'bg-card',
        avatarBorder: 'border-border',
        height: '',
    };
}
</script>

<template>
    <UserLayout>
        <Head title="Papan Peringkat - KPM SMART" />

        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                    <Icon icon="mdi:trophy" class="w-5 h-5" />
                </span>
                <span>Papan Peringkat</span>
            </div>
        </template>
        <template #header-sub>
            Peringkat siswa terbaik berdasarkan rata-rata nilai latihan & ujian
        </template>

        <div class="max-w-4xl mx-auto space-y-6">

            <!-- Current user rank hero card -->
            <div v-if="currentUserRank"
                 class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-primary p-6 text-white shadow-xl shadow-primary/10">
                <div class="absolute -right-8 -bottom-8 w-40 h-40 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
                <div class="absolute -left-8 -top-8 w-32 h-32 rounded-full bg-emerald-400/20 blur-xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-5">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-white/15 backdrop-blur-md border border-white/20 flex items-center justify-center font-black text-2xl shadow-inner text-amber-300">
                            #{{ currentUserRank.rank }}
                        </div>
                        <div>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-white/20 backdrop-blur-sm mb-1 text-white/95">
                                <Icon icon="mdi:account" class="w-3.5 h-3.5" /> Peringkatmu Saat Ini
                            </span>
                            <h2 class="text-xl font-bold tracking-tight">{{ $page.props.auth?.user?.name }}</h2>
                            <p class="text-xs text-white/80 mt-0.5">{{ currentUserRank.total_attempts }} sesi tugas telah diselesaikan</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3 sm:gap-4 bg-black/15 backdrop-blur-sm rounded-xl p-3 border border-white/10 text-center">
                        <div class="px-2">
                            <p class="text-[11px] text-white/75 font-medium">Rata-rata</p>
                            <p class="text-base sm:text-lg font-extrabold text-amber-300 mt-0.5">{{ currentUserRank.avg_score }}%</p>
                        </div>
                        <div class="px-2 border-x border-white/10">
                            <p class="text-[11px] text-white/75 font-medium">Tertinggi</p>
                            <p class="text-base sm:text-lg font-extrabold text-white mt-0.5">{{ currentUserRank.best_score }}%</p>
                        </div>
                        <div class="px-2">
                            <p class="text-[11px] text-white/75 font-medium">Selesai</p>
                            <p class="text-base sm:text-lg font-extrabold text-white mt-0.5">{{ currentUserRank.total_attempts }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top 3 Podium (Desktop & Tablet) -->
            <div v-if="props.leaderboard.length >= 3 && !searchQuery.trim()" class="space-y-3">
                <div class="flex items-center justify-between px-1">
                    <h3 class="text-sm font-bold text-foreground flex items-center gap-1.5 uppercase tracking-wider">
                        <Icon icon="mdi:podium-gold" class="w-4 h-4 text-amber-500" />
                        Top 3 Prestasi Terbaik
                    </h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end pt-4">
                    <div v-for="entry in topThree" :key="entry.user?.id"
                         :class="[
                             'relative rounded-2xl border p-5 transition-all duration-300 bg-gradient-to-b shadow-sm hover:shadow-xl hover:-translate-y-1 text-center',
                             getRankTheme(entry.rank).bg,
                             getRankTheme(entry.rank).border,
                             getRankTheme(entry.rank).height,
                             entry.rank === 1 ? 'sm:-mt-4 sm:pb-7 ring-2 ring-amber-400/30 shadow-amber-500/10' : ''
                         ]">

                        <!-- Rank Badge Float -->
                        <div class="absolute -top-3.5 left-1/2 -translate-x-1/2">
                            <span :class="['inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold shadow-md ring-2 ring-background', getRankTheme(entry.rank).badgeBg]">
                                <Icon :icon="getRankTheme(entry.rank).medal" class="w-3.5 h-3.5" />
                                <span>#{{ entry.rank }}</span>
                            </span>
                        </div>

                        <!-- User Avatar -->
                        <div class="mt-2 mb-3 flex justify-center">
                            <div :class="['relative w-16 h-16 sm:w-20 sm:h-20 rounded-2xl p-1 border-2 ring-4 flex items-center justify-center shadow-lg', getRankTheme(entry.rank).avatarBorder]">
                                <div class="w-full h-full rounded-xl bg-card flex items-center justify-center font-extrabold text-xl sm:text-2xl text-foreground">
                                    {{ (entry.user?.name || 'U').charAt(0).toUpperCase() }}
                                </div>
                                <span v-if="entry.rank === 1" class="absolute -top-3 -right-2 text-2xl drop-shadow">👑</span>
                            </div>
                        </div>

                        <h4 class="font-bold text-sm sm:text-base text-foreground truncate px-2" :title="entry.user?.name">
                            {{ entry.user?.name }}
                        </h4>
                        <p class="text-xs text-muted-foreground mt-0.5">{{ entry.total_attempts }} tugas</p>

                        <div class="mt-4 pt-3 border-t border-border/50 flex items-center justify-around">
                            <div>
                                <p class="text-[10px] text-muted-foreground font-medium uppercase">Rata-rata</p>
                                <p class="text-base font-extrabold text-primary">{{ entry.avg_score }}%</p>
                            </div>
                            <div class="w-px h-6 bg-border/60"></div>
                            <div>
                                <p class="text-[10px] text-muted-foreground font-medium uppercase">Terbaik</p>
                                <p class="text-base font-extrabold text-foreground">{{ entry.best_score }}%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search & Leaderboard Table -->
            <div class="bg-card border border-border/60 rounded-2xl overflow-hidden shadow-sm">
                <div class="p-4 sm:p-5 border-b border-border/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-muted/20">
                    <div class="flex items-center gap-2">
                        <Icon icon="mdi:format-list-numbered" class="w-5 h-5 text-primary" />
                        <h3 class="font-bold text-base text-foreground">Daftar Peringkat Siswa</h3>
                    </div>
                    <div class="w-full sm:w-64">
                        <div class="relative">
                            <Icon icon="mdi:magnify" class="w-4 h-4 text-muted-foreground absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" />
                            <Input v-model="searchQuery" placeholder="Cari nama siswa..." class="pl-9 h-9 text-xs rounded-xl" />
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="leaderboard.length === 0" class="py-16 px-6 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-muted flex items-center justify-center mx-auto mb-4 text-muted-foreground">
                        <Icon icon="mdi:trophy-outline" class="w-8 h-8" />
                    </div>
                    <h3 class="text-base font-bold text-foreground mb-1">Belum Ada Data Peringkat</h3>
                    <p class="text-muted-foreground text-xs mb-5 max-w-xs mx-auto">Mulai selesaikan tugas latihan untuk mencatat skor dan masuk ke papan peringkat!</p>
                    <Link :href="route('user.packages.index')"
                          class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-5 py-2.5 rounded-xl text-xs font-semibold hover:bg-primary/90 shadow-sm transition">
                        <Icon icon="mdi:play" class="w-4 h-4" /> Mulai Kerjakan Tugas
                    </Link>
                </div>

                <!-- List of Ranks -->
                <div v-else class="divide-y divide-border/40">
                    <div v-for="entry in (searchQuery.trim() ? remainingList : leaderboard)" :key="entry.user?.id"
                         :class="[
                             'flex items-center gap-3 sm:gap-4 px-4 sm:px-6 py-3.5 transition-colors',
                             entry.user?.id === $page.props.auth?.user?.id
                                 ? 'bg-primary/10 hover:bg-primary/15 font-semibold'
                                 : 'hover:bg-muted/30'
                         ]">

                        <!-- Rank number -->
                        <div class="w-8 sm:w-10 text-center flex-shrink-0">
                            <span v-if="entry.rank === 1" class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-amber-400 text-white text-xs font-black shadow-sm">1</span>
                            <span v-else-if="entry.rank === 2" class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-slate-400 text-white text-xs font-black shadow-sm">2</span>
                            <span v-else-if="entry.rank === 3" class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-amber-700 text-white text-xs font-black shadow-sm">3</span>
                            <span v-else class="text-xs sm:text-sm font-bold text-muted-foreground">#{{ entry.rank }}</span>
                        </div>

                        <!-- Avatar -->
                        <div :class="[
                            'w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center text-xs sm:text-sm font-bold flex-shrink-0 shadow-sm',
                            entry.user?.id === $page.props.auth?.user?.id
                                ? 'bg-primary text-primary-foreground'
                                : 'bg-muted text-muted-foreground'
                        ]">
                            {{ (entry.user?.name || 'U').charAt(0).toUpperCase() }}
                        </div>

                        <!-- User name & attempts -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-semibold text-foreground truncate">{{ entry.user?.name }}</p>
                                <span v-if="entry.user?.id === $page.props.auth?.user?.id"
                                      class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-primary text-primary-foreground shrink-0">
                                    Kamu
                                </span>
                            </div>
                            <p class="text-xs text-muted-foreground mt-0.5">{{ entry.total_attempts }} kali tugas dikerjakan</p>
                        </div>

                        <!-- Scores -->
                        <div class="text-right flex-shrink-0 pl-2">
                            <p class="text-sm sm:text-base font-extrabold text-primary">{{ entry.avg_score }}%</p>
                            <p class="text-[11px] text-muted-foreground">terbaik: <strong class="text-foreground">{{ entry.best_score }}%</strong></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer navigation -->
            <div class="flex items-center justify-between pt-2">
                <Link :href="route('user.practice.statistics')"
                      class="inline-flex items-center gap-1.5 text-xs sm:text-sm text-primary hover:text-primary/80 font-medium transition">
                    <Icon icon="mdi:chart-bar" class="w-4 h-4" /> Lihat Statistik Lengkap Saya
                </Link>
                <Link :href="route('user.dashboard')"
                      class="inline-flex items-center gap-1.5 text-xs sm:text-sm text-muted-foreground hover:text-foreground transition">
                    <Icon icon="mdi:arrow-left" class="w-4 h-4" /> Dasbor
                </Link>
            </div>
        </div>
    </UserLayout>
</template>
