<script setup>
import { inject, ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Input from '@/Components/ui/input/Input.vue';
import Select from '@/Components/ui/select/Select.vue';
import Badge from '@/Components/ui/badge/Badge.vue';
import Pagination from '@/Components/shared/Pagination.vue';
const route = inject('route');

const props = defineProps({
    users: { type: Object, required: true },
    search: { type: String, default: '' },
    status: { type: String, default: '' },
    totalUsers: { type: Number, default: 0 },
    activeUsers: { type: Number, default: 0 },
    allBidang: { type: Array, default: () => [] },
    allLevel: { type: Array, default: () => [] },
    allKelas: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const searchVal = ref(props.filters?.search || props.search || '');
const statusVal = ref(props.filters?.status || props.status || '');
const bidangVal = ref(props.filters?.bidang || '');
const levelVal = ref(props.filters?.level || '');
const kelasVal = ref(props.filters?.kelas || '');

const inactiveUsers = computed(() => props.totalUsers - props.activeUsers);

const updatingLevelId = ref(null);

function applyFilters() {
    router.get(route('admin.users.index'), {
        search: searchVal.value,
        status: statusVal.value,
        bidang: bidangVal.value,
        level: levelVal.value,
        kelas: kelasVal.value,
    }, { preserveState: true, replace: true });
}

function resetFilters() {
    searchVal.value = '';
    statusVal.value = '';
    bidangVal.value = '';
    levelVal.value = '';
    kelasVal.value = '';
    applyFilters();
}

function toggleActive(userId) {
    if (confirm('Yakin ingin mengubah status akun ini?')) {
        router.post(route('admin.users.toggle-active', userId));
    }
}

function updateLevel(user, newLevel) {
    if (user.level === newLevel) return;
    updatingLevelId.value = user.id;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
        || decodeURIComponent(document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] || '');
    fetch(route('admin.users.update-level', user.id), {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-XSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({ level: newLevel }),
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            user.level = data.level;
        }
    })
    .catch(() => {})
    .finally(() => {
        updatingLevelId.value = null;
    });
}
</script>

<template>
    <AdminLayout>
        <Head title="Kelola User - Admin" />

        <template #header-title>Kelola User</template>
        <template #header-sub>Manajemen data pengguna platform</template>

        <!-- Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6 sm:mb-8">
            <!-- Total Users -->
            <div class="stat-tile group p-4 sm:p-5 anim-fade-in-up anim-delay-1" style="--tile-accent: #769826;">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center flex-shrink-0 group-hover:from-primary/25 group-hover:to-primary/10 transition-all duration-300">
                        <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128H5.228A2 2 0 013 17.208V5.792a2 2 0 012.228-1.986h13.544A2 2 0 0121 5.792v11.416a2 2 0 01-2.228 1.986M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] sm:text-xs text-muted-foreground font-medium uppercase tracking-wider">Total User</p>
                        <p class="text-2xl sm:text-3xl font-extrabold tracking-tight text-foreground">{{ totalUsers }}</p>
                    </div>
                </div>
            </div>

            <!-- Active Users -->
            <div class="stat-tile group p-4 sm:p-5 anim-fade-in-up anim-delay-2" style="--tile-accent: #52b788;">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-emerald-500/15 to-emerald-500/5 flex items-center justify-center flex-shrink-0 group-hover:from-emerald-500/25 group-hover:to-emerald-500/10 transition-all duration-300">
                        <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] sm:text-xs text-muted-foreground font-medium uppercase tracking-wider">Akun Aktif</p>
                        <p class="text-2xl sm:text-3xl font-extrabold tracking-tight text-emerald-600">{{ activeUsers }}</p>
                    </div>
                </div>
            </div>

            <!-- Inactive -->
            <div class="stat-tile group p-4 sm:p-5 anim-fade-in-up anim-delay-3" style="--tile-accent: #ef4444;">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-red-500/15 to-red-500/5 flex items-center justify-center flex-shrink-0 group-hover:from-red-500/25 group-hover:to-red-500/10 transition-all duration-300">
                        <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] sm:text-xs text-muted-foreground font-medium uppercase tracking-wider">Nonaktif</p>
                        <p class="text-2xl sm:text-3xl font-extrabold tracking-tight text-red-500">{{ inactiveUsers }}</p>
                    </div>
                </div>
            </div>

            <!-- New This Month -->
            <div class="stat-tile group p-4 sm:p-5 anim-fade-in-up anim-delay-4" style="--tile-accent: #f59e0b;">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-amber-500/15 to-amber-500/5 flex items-center justify-center flex-shrink-0 group-hover:from-amber-500/25 group-hover:to-amber-500/10 transition-all duration-300">
                        <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] sm:text-xs text-muted-foreground font-medium uppercase tracking-wider">Baru Bulan Ini</p>
                        <p class="text-2xl sm:text-3xl font-extrabold tracking-tight text-amber-600">{{ users.total }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="bg-card rounded-2xl p-4 sm:p-5 shadow-sm border border-border/60 mb-6 sm:mb-8 anim-fade-in-up anim-delay-3">
            <form @submit.prevent="applyFilters" class="flex flex-col gap-3">
                <!-- Search + Status Row -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <Input v-model="searchVal" placeholder="Cari nama, email, sekolah..." class="pl-11 h-11 rounded-xl bg-muted/50 border-border/60 focus:bg-background transition-colors" />
                    </div>
                    <div class="flex gap-2 sm:gap-3">
                        <Select v-model="statusVal" class="w-full sm:w-44 h-11 rounded-xl bg-muted/50 border-border/60">
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </Select>
                        <Button type="submit" size="sm" class="gap-1.5 h-11 px-5 rounded-xl font-semibold shadow-sm hover:shadow-md transition-all">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                            </svg>
                            Cari
                        </Button>
                        <Button type="button" variant="ghost" size="sm" @click="resetFilters" class="h-11 px-4 rounded-xl">
                            Reset
                        </Button>
                    </div>
                </div>
                <!-- Bidang + Level + Kelas Row -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <Select v-model="bidangVal" class="w-full sm:w-44 h-11 rounded-xl bg-muted/50 border-border/60">
                        <option value="">Semua Bidang</option>
                        <option v-for="b in allBidang" :key="b" :value="b">{{ b }}</option>
                    </Select>
                    <Select v-model="levelVal" class="w-full sm:w-44 h-11 rounded-xl bg-muted/50 border-border/60">
                        <option value="">Semua Level</option>
                        <option v-for="l in allLevel" :key="l" :value="l">{{ l }}</option>
                    </Select>
                    <Select v-model="kelasVal" class="w-full sm:w-44 h-11 rounded-xl bg-muted/50 border-border/60">
                        <option value="">Semua Kelas</option>
                        <option v-for="k in allKelas" :key="k" :value="k">{{ k }}</option>
                    </Select>
                    <div class="hidden sm:block"></div>
                    <div class="flex gap-2 sm:hidden">
                        <Link :href="route('admin.users.import-excel')" class="flex-1 inline-flex items-center justify-center gap-1.5 bg-gradient-to-r from-amber-500 to-amber-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold hover:shadow-lg hover:shadow-amber-500/25 transition-all duration-300 active:scale-[0.97]">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                            Import
                        </Link>
                    </div>
                </div>
                <!-- Action Buttons (Desktop) -->
                <div class="hidden sm:flex gap-2 sm:gap-3">
                    <Link :href="route('admin.users.import-excel')" class="inline-flex items-center gap-1.5 bg-gradient-to-r from-amber-500 to-amber-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:shadow-lg hover:shadow-amber-500/25 transition-all duration-300 active:scale-[0.97]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                        </svg>
                        Import Excel
                    </Link>
                    <Link :href="route('admin.users.create')" class="inline-flex items-center gap-1.5 bg-gradient-to-r from-fern to-hunter-green text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:shadow-lg hover:shadow-fern/25 transition-all duration-300 active:scale-[0.97]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Tambah User
                    </Link>
                </div>
            </form>
        </div>

        <!-- Desktop Table -->
        <div class="hidden md:block bg-card rounded-2xl shadow-sm border border-border/60 overflow-hidden mb-6 anim-fade-in-up anim-delay-4">
            <div class="overflow-x-auto">
                <table class="admin-table w-full text-sm">
                    <thead>
                        <tr class="border-b border-border/60 bg-muted/20">
                            <th class="text-left px-6 py-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">User</th>
                            <th class="text-left px-6 py-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Kelas / Bidang</th>
                            <th class="text-left px-6 py-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Level</th>
                            <th class="text-left px-6 py-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Sekolah</th>
                            <th class="text-left px-6 py-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Status</th>
                            <th class="text-left px-6 py-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Terdaftar</th>
                            <th class="text-center px-6 py-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="users.data.length === 0">
                            <td colspan="7" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center gap-4">
                                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-muted to-muted/50 flex items-center justify-center">
                                        <svg class="w-10 h-10 text-muted-foreground/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128H5.228A2 2 0 013 17.208V5.792a2 2 0 012.228-1.986h13.544A2 2 0 0121 5.792v11.416a2 2 0 01-2.228 1.986M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-muted-foreground text-base">Tidak ada user ditemukan</p>
                                        <p class="text-sm text-muted-foreground/60 mt-1.5">Coba ubah filter pencarian Anda atau tambah user baru</p>
                                    </div>
                                    <Link :href="route('admin.users.create')" class="inline-flex items-center gap-1.5 bg-primary text-primary-foreground px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-primary/90 transition-all hover:shadow-md active:scale-[0.97]">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                        Tambah User
                                    </Link>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="(user, index) in users.data" :key="user.id"
                            class="border-b border-border/40 last:border-b-0 hover:bg-muted/30 transition-all duration-200 cursor-pointer group"
                            :style="{ animationDelay: `${index * 30}ms` }"
                            @click="router.get(route('admin.users.show', user.id))">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3.5">
                                    <div class="w-11 h-11 rounded-xl overflow-hidden flex-shrink-0 bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center ring-2 ring-background group-hover:ring-primary/20 group-hover:shadow-md transition-all duration-300">
                                        <img v-if="user.profile_photo" :src="'/storage/' + user.profile_photo" class="w-full h-full object-cover" />
                                        <span v-else class="text-sm font-bold text-primary">{{ user.name?.charAt(0)?.toUpperCase() }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold truncate group-hover:text-primary transition-colors duration-200">{{ user.name }}</p>
                                        <p class="text-xs text-muted-foreground truncate mt-0.5">{{ user.email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-muted/60 text-foreground font-medium text-xs">
                                    <svg class="w-3 h-3 text-muted-foreground/60" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" />
                                    </svg>
                                    {{ user.student_class || '-' }} / {{ user.bidang || '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm" @click.stop>
                                <div class="relative inline-flex items-center">
                                    <select
                                        :value="user.level || ''"
                                        @change="updateLevel(user, $event.target.value)"
                                        :disabled="updatingLevelId === user.id"
                                        class="appearance-none cursor-pointer pl-2.5 pr-7 py-1.5 rounded-lg text-xs font-medium border transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                                        :class="user.level ? 'bg-primary/10 text-primary border-primary/20 hover:bg-primary/15' : 'bg-muted/60 text-muted-foreground border-border/60 hover:bg-muted'"
                                    >
                                        <option value="">Pilih Level</option>
                                        <option v-for="l in allLevel" :key="l" :value="l">{{ l }}</option>
                                    </select>
                                    <svg class="absolute right-2 w-3 h-3 pointer-events-none" :class="user.level ? 'text-primary/60' : 'text-muted-foreground/60'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                    <svg v-if="updatingLevelId === user.id" class="absolute -right-5 w-3.5 h-3.5 text-primary animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm truncate max-w-[200px] text-muted-foreground">{{ user.school_name || '-' }}</td>
                            <td class="px-6 py-4">
                                <Badge :variant="user.is_active ? 'success' : 'destructive'" class="gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold">
                                    <span class="w-2 h-2 rounded-full animate-pulse-soft" :class="user.is_active ? 'bg-emerald-500' : 'bg-red-500'"></span>
                                    {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                                </Badge>
                            </td>
                            <td class="px-6 py-4 text-sm text-muted-foreground">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-muted-foreground/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                    </svg>
                                    {{ user.created_at ? new Date(user.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-1" @click.stop>
                                    <Link :href="route('admin.users.show', user.id)" class="p-2.5 rounded-xl hover:bg-primary/10 transition-all duration-200 text-muted-foreground hover:text-primary group/btn" title="Detail">
                                        <svg class="w-4 h-4 group-hover/btn:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </Link>
                                    <button @click="toggleActive(user.id)" class="p-2.5 rounded-xl transition-all duration-200 group/btn" :class="user.is_active ? 'hover:bg-emerald-50 text-emerald-600' : 'hover:bg-red-50 text-red-500'" :title="user.is_active ? 'Nonaktifkan' : 'Aktifkan'">
                                        <svg v-if="user.is_active" class="w-4 h-4 group-hover/btn:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <svg v-else class="w-4 h-4 group-hover/btn:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden space-y-3 mb-6">
            <div v-if="users.data.length === 0" class="text-center py-16 bg-card rounded-2xl shadow-sm border border-border/60">
                <div class="flex flex-col items-center gap-4 px-4">
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-muted to-muted/50 flex items-center justify-center">
                        <svg class="w-10 h-10 text-muted-foreground/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128H5.228A2 2 0 013 17.208V5.792a2 2 0 012.228-1.986h13.544A2 2 0 0121 5.792v11.416a2 2 0 01-2.228 1.986M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-muted-foreground font-semibold">Tidak ada user ditemukan</p>
                        <p class="text-xs text-muted-foreground/60 mt-1">Coba ubah filter pencarian Anda</p>
                    </div>
                </div>
            </div>
            <div v-for="(user, index) in users.data" :key="user.id"
                class="bg-card rounded-2xl shadow-sm border border-border/60 p-4 cursor-pointer hover:shadow-md hover:border-primary/20 transition-all duration-300 active:scale-[0.98]"
                :style="{ animationDelay: `${index * 40}ms` }"
                @click="router.get(route('admin.users.show', user.id))">
                <!-- Mobile Card Header -->
                <div class="flex items-center gap-3.5 mb-3.5">
                    <div class="w-12 h-12 rounded-xl overflow-hidden bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center flex-shrink-0 ring-2 ring-background shadow-sm">
                        <img v-if="user.profile_photo" :src="'/storage/' + user.profile_photo" class="w-full h-full object-cover" />
                        <span v-else class="text-base font-bold text-primary">{{ user.name?.charAt(0)?.toUpperCase() }}</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold truncate text-[15px]">{{ user.name }}</p>
                        <p class="text-xs text-muted-foreground truncate mt-0.5">{{ user.email }}</p>
                    </div>
                    <Badge :variant="user.is_active ? 'success' : 'destructive'" class="text-[10px] gap-1 flex-shrink-0 px-2 py-0.5 rounded-lg">
                        <span class="w-1.5 h-1.5 rounded-full" :class="user.is_active ? 'bg-emerald-500' : 'bg-red-500'"></span>
                        {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                    </Badge>
                </div>
                <!-- Mobile Card Body -->
                <div class="flex items-center justify-between text-xs text-muted-foreground bg-muted/30 rounded-xl px-3.5 py-2.5">
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-muted-foreground/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" />
                        </svg>
                        {{ user.student_class || '-' }} / {{ user.bidang || '-' }}
                    </span>
                    <div class="relative" @click.stop>
                        <select
                            :value="user.level || ''"
                            @change="updateLevel(user, $event.target.value)"
                            :disabled="updatingLevelId === user.id"
                            class="appearance-none cursor-pointer pl-2 pr-6 py-1 rounded-lg text-xs font-medium border transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary"
                            :class="user.level ? 'bg-primary/10 text-primary border-primary/20' : 'bg-muted/60 text-muted-foreground border-border/60'"
                        >
                            <option value="">Level</option>
                            <option v-for="l in allLevel" :key="l" :value="l">{{ l }}</option>
                        </select>
                        <svg class="absolute right-1.5 top-1/2 -translate-y-1/2 w-3 h-3 pointer-events-none" :class="user.level ? 'text-primary/60' : 'text-muted-foreground/60'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                        </svg>
                    </div>
                </div>
                <!-- Mobile Card Actions -->
                <div class="flex items-center justify-between mt-3 pt-3 border-t border-border/40">
                    <span class="text-[11px] text-muted-foreground/60">
                        {{ user.created_at ? new Date(user.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-' }}
                    </span>
                    <button @click.stop="toggleActive(user.id)" class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg transition-all duration-200" :class="user.is_active ? 'text-emerald-600 hover:bg-emerald-50' : 'text-red-500 hover:bg-red-50'">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182" />
                        </svg>
                        {{ user.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile FAB -->
        <Link :href="route('admin.users.create')" class="sm:hidden fixed bottom-20 right-4 z-20 w-14 h-14 bg-gradient-to-r from-fern to-hunter-green text-white rounded-2xl shadow-lg shadow-fern/30 flex items-center justify-center active:scale-95 transition-all duration-200 hover:shadow-xl hover:shadow-fern/40">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
        </Link>

        <Pagination :links="users.links" />
    </AdminLayout>
</template>
