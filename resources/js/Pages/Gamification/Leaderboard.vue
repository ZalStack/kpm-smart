<script setup>
import { inject, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import { Icon } from '@iconify/vue';
const route = inject('route');

const props = defineProps({
    leaderboard: { type: Array, default: () => [] },
    currentUserRank: { type: Object, default: null },
});

function getRankBadge(rank) {
    if (rank === 1) return { icon: 'mdi:medal', color: 'text-yellow-500', bg: 'bg-yellow-50' };
    if (rank === 2) return { icon: 'mdi:medal', color: 'text-gray-400', bg: 'bg-gray-50' };
    if (rank === 3) return { icon: 'mdi:medal', color: 'text-amber-600', bg: 'bg-amber-50' };
    return { icon: '', color: 'text-muted-foreground', bg: '' };
}
</script>

<template>
    <UserLayout>
        <Head title="Papan Peringkat - KPM SMART" />
        <template #header-title><Icon icon="mdi:trophy" class="w-5 h-5 inline-block mr-1.5 align-middle" /> Papan Peringkat</template>
        <template #header-sub>Peringkat berdasarkan rata-rata skor</template>

        <div class="max-w-3xl mx-auto space-y-5">
            <!-- Current user rank card -->
            <div v-if="currentUserRank" class="anim-fade-in-up rounded-2xl bg-gradient-to-br from-emerald-500 via-emerald-600 to-emerald-700 text-white p-5 sm:p-6 shadow-lg hover:shadow-xl transition-all duration-300">
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
            <div class="anim-fade-in-up bg-card border rounded-2xl overflow-hidden shadow-card hover:shadow-lg transition-shadow duration-300">
                <div class="divide-y divide-border/50">
                    <div v-for="(entry, idx) in leaderboard" :key="entry.user?.id"
                         :class="['flex items-center gap-4 px-5 py-4 transition-all duration-200', idx < 3 ? 'anim-fade-in-up anim-delay-' + (idx + 1) : '', entry.user?.id === $page.props.auth?.user?.id ? 'bg-primary/5' : 'hover:bg-muted/30 hover:shadow-sm']">
                        <div class="w-10 text-center flex-shrink-0">
                            <span v-if="entry.rank <= 3" class="text-xl"><Icon :icon="getRankBadge(entry.rank).icon" class="w-5 h-5" /></span>
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

                    <div v-if="leaderboard.length === 0" class="py-16 px-6 text-center">
                        <div class="w-20 h-20 rounded-full bg-muted flex items-center justify-center mx-auto mb-4 animate-pulse"><Icon icon="mdi:chart-bar" class="w-10 h-10 text-muted-foreground" /></div>
                        <h3 class="text-lg font-semibold text-foreground mb-1">Belum Ada Data</h3>
                        <p class="text-muted-foreground text-sm mb-5 max-w-xs mx-auto">Belum ada data peringkat. Mulai belajar untuk masuk papan peringkat!</p>
                        <Link :href="route('packages.index')" class="inline-flex items-center gap-2 bg-primary text-primary-foreground px-6 py-2.5 rounded-xl text-sm font-medium hover:bg-primary/90 hover:shadow-md active:scale-95 transition-all duration-200">
                            Mulai Belajar
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </Link>
                    </div>
                </div>
            </div>

            <div class="flex justify-center">
                <Link :href="route('user.dashboard')" class="inline-flex items-center gap-2 text-sm text-muted-foreground hover:text-foreground hover:shadow-sm transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                    Kembali ke Dasbor
                </Link>
            </div>
        </div>
    </UserLayout>
</template>
