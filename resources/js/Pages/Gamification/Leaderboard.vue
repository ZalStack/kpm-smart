<script setup>
import { inject, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
const route = inject('route');

const props = defineProps({
    leaderboard: { type: Array, default: () => [] },
    currentUserRank: { type: Object, default: null },
});

function getRankBadge(rank) {
    if (rank === 1) return { emoji: '🥇', color: 'text-yellow-500', bg: 'bg-yellow-50' };
    if (rank === 2) return { emoji: '🥈', color: 'text-gray-400', bg: 'bg-gray-50' };
    if (rank === 3) return { emoji: '🥉', color: 'text-amber-600', bg: 'bg-amber-50' };
    return { emoji: '', color: 'text-muted-foreground', bg: '' };
}
</script>

<template>
    <UserLayout>
        <Head title="Papan Peringkat - KPM SMART" />
        <template #header-title>🏆 Papan Peringkat</template>
        <template #header-sub>Peringkat berdasarkan rata-rata skor</template>

        <div class="max-w-3xl mx-auto space-y-5">
            <!-- Current user rank card -->
            <div v-if="currentUserRank" class="anim-fade-in-up rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-white p-5 shadow-card-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/70 text-xs font-medium">Peringkatmu</p>
                        <p class="text-3xl font-black mt-1">#{{ currentUserRank.rank }}</p>
                    </div>
                    <div class="text-right">
                        <div class="text-sm"><span class="text-white/70">Rata-rata:</span> <span class="font-bold">{{ currentUserRank.avg_score }}%</span></div>
                        <div class="text-sm"><span class="text-white/70">Terbaik:</span> <span class="font-bold">{{ currentUserRank.best_score }}%</span></div>
                        <div class="text-sm"><span class="text-white/70">Percobaan:</span> <span class="font-bold">{{ currentUserRank.total_attempts }}</span></div>
                    </div>
                </div>
            </div>

            <!-- Leaderboard list -->
            <div class="anim-fade-in-up bg-card border rounded-2xl overflow-hidden shadow-card">
                <div class="divide-y divide-border/50">
                    <div v-for="(entry, idx) in leaderboard" :key="entry.user?.id"
                         :class="['flex items-center gap-4 px-5 py-4 transition', idx < 3 ? 'anim-fade-in-up anim-delay-' + (idx + 1) : '', entry.user?.id === $page.props.auth?.user?.id ? 'bg-primary/5' : 'hover:bg-muted/30']">
                        <div class="w-10 text-center flex-shrink-0">
                            <span v-if="entry.rank <= 3" class="text-xl">{{ getRankBadge(entry.rank).emoji }}</span>
                            <span v-else :class="['text-sm font-bold', getRankBadge(entry.rank).color]">{{ entry.rank }}</span>
                        </div>
                        <div class="w-9 h-9 rounded-full bg-primary/10 flex items-center justify-center text-xs font-bold text-primary flex-shrink-0">
                            {{ (entry.user?.name || 'U').charAt(0).toUpperCase() }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold truncate">{{ entry.user?.name }}</p>
                            <p class="text-xs text-muted-foreground">{{ entry.total_attempts }} percobaan</p>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-lg font-bold text-primary">{{ entry.avg_score }}%</p>
                            <p class="text-[10px] text-muted-foreground">terbaik: {{ entry.best_score }}%</p>
                        </div>
                    </div>

                    <div v-if="leaderboard.length === 0" class="p-10 text-center">
                        <div class="text-5xl mb-3">📊</div>
                        <h3 class="text-lg font-semibold text-foreground mb-1">Belum Ada Data</h3>
                        <p class="text-muted-foreground text-sm mb-4">Belum ada data peringkat. Mulai belajar untuk masuk papan peringkat!</p>
                        <Link :href="route('packages.index')" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-5 py-2.5 rounded-lg text-sm font-medium hover:bg-primary/90 transition">
                            Mulai Belajar
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </Link>
                    </div>
                </div>
            </div>

            <div class="flex justify-center">
                <Link :href="route('user.dashboard')" class="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                    Kembali ke Dasbor
                </Link>
            </div>
        </div>
    </UserLayout>
</template>
