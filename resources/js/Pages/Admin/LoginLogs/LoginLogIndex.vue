<script setup>
import { inject,  ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Input from '@/Components/ui/input/Input.vue';
import Badge from '@/Components/ui/badge/Badge.vue';
import Pagination from '@/Components/shared/Pagination.vue';
const route = inject('route');

const props = defineProps({
    logs: { type: Object, required: true },
    todayCount: { type: Number, default: 0 },
    weekCount: { type: Number, default: 0 },
});

const search = ref('');
const dateFrom = ref('');
const dateTo = ref('');

function applyFilters() {
    router.get(route('admin.login-logs.index'), {
        search: search.value,
        date_from: dateFrom.value,
        date_to: dateTo.value,
    }, { preserveState: true, replace: true });
}

function resetFilters() {
    search.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    applyFilters();
}

function formatDateTime(d) {
    if (!d) return '-';
    const dt = new Date(d);
    return dt.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) + ' ' + dt.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <AdminLayout>
        <Head title="Log Login - Admin" />

        <template #header-title>Log Aktivitas Login</template>
        <template #header-sub>Riwayat login pengguna ke platform</template>

        <!-- Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6 sm:mb-8">
            <div class="stat-tile group p-4 sm:p-5 anim-fade-in-up anim-delay-1" style="--tile-accent: #769826;">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center flex-shrink-0 group-hover:from-primary/25 group-hover:to-primary/10 transition-all duration-300">
                        <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] sm:text-xs text-muted-foreground font-medium uppercase tracking-wider">Total Login</p>
                        <p class="text-2xl sm:text-3xl font-extrabold tracking-tight text-foreground">{{ logs.total }}</p>
                    </div>
                </div>
            </div>
            <div class="stat-tile group p-4 sm:p-5 anim-fade-in-up anim-delay-2" style="--tile-accent: #52b788;">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-emerald-500/15 to-emerald-500/5 flex items-center justify-center flex-shrink-0 group-hover:from-emerald-500/25 group-hover:to-emerald-500/10 transition-all duration-300">
                        <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] sm:text-xs text-muted-foreground font-medium uppercase tracking-wider">Hari Ini</p>
                        <p class="text-2xl sm:text-3xl font-extrabold tracking-tight text-emerald-600">{{ todayCount }}</p>
                    </div>
                </div>
            </div>
            <div class="stat-tile group p-4 sm:p-5 anim-fade-in-up anim-delay-3" style="--tile-accent: #3b82f6;">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-blue-500/15 to-blue-500/5 flex items-center justify-center flex-shrink-0 group-hover:from-blue-500/25 group-hover:to-blue-500/10 transition-all duration-300">
                        <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] sm:text-xs text-muted-foreground font-medium uppercase tracking-wider">Minggu Ini</p>
                        <p class="text-2xl sm:text-3xl font-extrabold tracking-tight text-blue-600">{{ weekCount }}</p>
                    </div>
                </div>
            </div>
            <div class="stat-tile group p-4 sm:p-5 anim-fade-in-up anim-delay-4" style="--tile-accent: #8b5cf6;">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-violet-500/15 to-violet-500/5 flex items-center justify-center flex-shrink-0 group-hover:from-violet-500/25 group-hover:to-violet-500/10 transition-all duration-300">
                        <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128H5.228A2 2 0 013 17.208V5.792a2 2 0 012.228-1.986h13.544A2 2 0 0121 5.792v11.416a2 2 0 01-2.228 1.986M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] sm:text-xs text-muted-foreground font-medium uppercase tracking-wider">User Unik</p>
                        <p class="text-2xl sm:text-3xl font-extrabold tracking-tight text-violet-600">{{ logs.total }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="bg-card rounded-2xl p-4 sm:p-5 shadow-sm border border-border/60 mb-6 sm:mb-8 anim-fade-in-up anim-delay-3">
            <form @submit.prevent="applyFilters" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    <Input v-model="search" placeholder="Cari nama, email..." class="pl-11 h-11 rounded-xl bg-muted/50 border-border/60 focus:bg-background transition-colors" />
                </div>
                <Input v-model="dateFrom" type="date" class="w-full sm:w-44 h-11 rounded-xl bg-muted/50 border-border/60" />
                <Input v-model="dateTo" type="date" class="w-full sm:w-44 h-11 rounded-xl bg-muted/50 border-border/60" />
                <div class="flex gap-2 sm:gap-3">
                    <Button type="submit" size="sm" class="gap-1.5 h-11 px-5 rounded-xl font-semibold shadow-sm hover:shadow-md transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                        Filter
                    </Button>
                    <Button type="button" variant="ghost" size="sm" @click="resetFilters" class="h-11 px-4 rounded-xl">Reset</Button>
                </div>
            </form>
        </div>

        <!-- Desktop Table -->
        <div class="hidden md:block bg-card rounded-2xl shadow-sm border border-border/60 overflow-hidden mb-6 anim-fade-in-up anim-delay-4">
            <div class="overflow-x-auto">
                <table class="admin-table w-full text-sm">
                    <thead>
                        <tr class="border-b border-border/60 bg-gradient-to-r from-muted/40 to-muted/20">
                            <th class="text-left px-5 py-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">User</th>
                            <th class="text-left px-5 py-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Waktu Login</th>
                            <th class="text-left px-5 py-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">IP Address</th>
                            <th class="text-left px-5 py-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Lokasi</th>
                            <th class="text-left px-5 py-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Perangkat</th>
                            <th class="text-left px-5 py-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="logs.data.length === 0">
                            <td colspan="6" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center gap-4">
                                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-muted to-muted/50 flex items-center justify-center">
                                        <svg class="w-10 h-10 text-muted-foreground/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-muted-foreground text-base">Tidak ada data login</p>
                                        <p class="text-sm text-muted-foreground/60 mt-1.5">Data login akan muncul di sini</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="(log, index) in logs.data" :key="log.id"
                            class="border-b border-border/40 last:border-b-0 hover:bg-muted/50 transition-all duration-200 cursor-pointer"
                            :style="{ animationDelay: `${index * 30}ms` }">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl overflow-hidden bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center flex-shrink-0 ring-2 ring-background shadow-sm">
                                        <img v-if="log.user?.profile_photo" :src="'/storage/' + log.user.profile_photo" class="w-full h-full object-cover" />
                                        <span v-else class="text-xs font-bold text-primary">{{ log.user?.name?.charAt(0) || '?' }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold truncate text-xs">{{ log.user?.name || 'Unknown' }}</p>
                                        <p class="text-[10px] text-muted-foreground truncate">{{ log.user?.email || '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-sm">{{ formatDateTime(log.login_at) }}</td>
                            <td class="px-5 py-4 text-sm font-mono text-xs">{{ log.ip_address || '-' }}</td>
                            <td class="px-5 py-4 text-sm">{{ log.location || '-' }}</td>
                            <td class="px-5 py-4 text-sm truncate max-w-[150px]" :title="log.user_agent">{{ log.user_agent ? log.user_agent.substring(0, 30) + '...' : '-' }}</td>
                            <td class="px-5 py-4"><Badge variant="success" class="gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Berhasil</Badge></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden space-y-3 mb-6">
            <div v-if="logs.data.length === 0" class="text-center py-16 bg-card rounded-2xl shadow-sm border border-border/60">
                <div class="flex flex-col items-center gap-4 px-4">
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-muted to-muted/50 flex items-center justify-center">
                        <svg class="w-10 h-10 text-muted-foreground/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" /></svg>
                    </div>
                    <div>
                        <p class="text-muted-foreground font-semibold">Tidak ada data login</p>
                        <p class="text-xs text-muted-foreground/60 mt-1">Data login akan muncul di sini</p>
                    </div>
                </div>
            </div>
            <div v-for="(log, index) in logs.data" :key="log.id"
                class="bg-card rounded-2xl shadow-sm border border-border/60 p-4 hover:shadow-md hover:border-primary/20 transition-all duration-300 active:scale-[0.98]"
                :style="{ animationDelay: `${index * 40}ms` }">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-11 h-11 rounded-xl overflow-hidden bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center flex-shrink-0 ring-2 ring-background shadow-sm">
                        <img v-if="log.user?.profile_photo" :src="'/storage/' + log.user.profile_photo" class="w-full h-full object-cover" />
                        <span v-else class="text-sm font-bold text-primary">{{ log.user?.name?.charAt(0) || '?' }}</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold truncate text-sm">{{ log.user?.name || 'Unknown' }}</p>
                        <p class="text-xs text-muted-foreground">{{ formatDateTime(log.login_at) }}</p>
                    </div>
                    <Badge variant="success" class="text-[10px] gap-1 flex-shrink-0 px-2 py-0.5 rounded-lg"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Berhasil</Badge>
                </div>
                <div class="flex items-center gap-3 text-xs text-muted-foreground bg-muted/30 rounded-xl px-3.5 py-2.5">
                    <span class="inline-flex items-center gap-1"><Icon icon="mdi:web" class="w-3 h-3 inline-block align-middle" /> {{ log.ip_address || '-' }}</span>
                    <span class="inline-flex items-center gap-1"><Icon icon="mdi:map-marker-outline" class="w-3 h-3 inline-block align-middle" /> {{ log.location || '-' }}</span>
                </div>
            </div>
        </div>

        <Pagination :links="logs.links" />
    </AdminLayout>
</template>
