Agent pid 3729262
<script setup>
import { inject, ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
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

const levelOptions = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

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

const syncing = ref(false);
function syncFromParent() {
    if (confirm('Sinkronkan seluruh data user dari Web Induk sekarang?')) {
        syncing.value = true;
        router.post(route('admin.users.sync-parent'), {}, {
            onFinish: () => {
                syncing.value = false;
            },
        });
    }
}
</script>

<template>
    <AdminLayout>
        <Head title="Manajemen Siswa - Admin" />

        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <Icon icon="mdi:account-group-outline" class="w-5 h-5" />
                </span>
                <span>Manajemen Siswa</span>
            </div>
        </template>
        <template #header-sub>Manajemen data akun dan profil siswa platform</template>

        <div class="space-y-6">
            <!-- Stats -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <!-- Total Users -->
                <div class="bg-card rounded-2xl border border-border/60 p-4 sm:p-5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-primary/10 text-primary flex items-center justify-center shrink-0">
                            <Icon icon="mdi:account-multiple-outline" class="w-6 h-6" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] sm:text-xs text-muted-foreground font-medium uppercase tracking-wider">Total Siswa</p>
                            <p class="text-2xl sm:text-3xl font-black tracking-tight text-foreground">{{ totalUsers }}</p>
                        </div>
                    </div>
                </div>

                <!-- Active Users -->
                <div class="bg-card rounded-2xl border border-border/60 p-4 sm:p-5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center shrink-0">
                            <Icon icon="mdi:account-check-outline" class="w-6 h-6" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] sm:text-xs text-muted-foreground font-medium uppercase tracking-wider">Akun Aktif</p>
                            <p class="text-2xl sm:text-3xl font-black tracking-tight text-emerald-600">{{ activeUsers }}</p>
                        </div>
                    </div>
                </div>

                <!-- Inactive -->
                <div class="bg-card rounded-2xl border border-border/60 p-4 sm:p-5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-rose-500/10 text-rose-600 flex items-center justify-center shrink-0">
                            <Icon icon="mdi:account-off-outline" class="w-6 h-6" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] sm:text-xs text-muted-foreground font-medium uppercase tracking-wider">Nonaktif</p>
                            <p class="text-2xl sm:text-3xl font-black tracking-tight text-rose-600">{{ inactiveUsers }}</p>
                        </div>
                    </div>
                </div>

                <!-- Total Filtered -->
                <div class="bg-card rounded-2xl border border-border/60 p-4 sm:p-5 shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center shrink-0">
                            <Icon icon="mdi:filter-check-outline" class="w-6 h-6" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] sm:text-xs text-muted-foreground font-medium uppercase tracking-wider">Hasil Filter</p>
                            <p class="text-2xl sm:text-3xl font-black tracking-tight text-amber-600">{{ users.total }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Toolbar & Filter -->
            <div class="bg-card rounded-3xl p-5 sm:p-6 shadow-sm border border-border/60 space-y-4">
                <form @submit.prevent="applyFilters" class="space-y-3">
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="relative flex-1">
                            <Icon icon="mdi:magnify" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground pointer-events-none" />
                            <Input v-model="searchVal" placeholder="Cari nama, email, sekolah..." class="pl-10 h-11 rounded-xl bg-background" />
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <Select v-model="statusVal" class="w-full sm:w-36 h-11 rounded-xl bg-background">
                                <option value="">Semua Status</option>
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </Select>

                            <Button type="submit" class="h-11 px-5 rounded-xl font-semibold gap-1.5 shadow-sm">
                                <Icon icon="mdi:magnify" class="w-4 h-4" />
                                <span>Cari</span>
                            </Button>

                            <Button type="button" variant="outline" @click="resetFilters" class="h-11 px-4 rounded-xl font-semibold">
                                Reset
                            </Button>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 pt-3 border-t border-border/50">
                        <div class="flex flex-wrap gap-2.5 flex-1">
                            <Select v-model="bidangVal" class="w-full sm:w-40 h-10 rounded-xl bg-background text-xs">
                                <option value="">Semua Bidang</option>
                                <option v-for="b in allBidang" :key="b" :value="b">{{ b }}</option>
                            </Select>

                            <Select v-model="levelVal" class="w-full sm:w-36 h-10 rounded-xl bg-background text-xs">
                                <option value="">Semua Level</option>
                                <option v-for="l in levelOptions" :key="l" :value="l">{{ l }}</option>
                            </Select>

                            <Select v-model="kelasVal" class="w-full sm:w-36 h-10 rounded-xl bg-background text-xs">
                                <option value="">Semua Kelas</option>
                                <option v-for="k in allKelas" :key="k" :value="k">{{ k }}</option>
                            </Select>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <Button type="button" variant="outline" :disabled="syncing" @click="syncFromParent"
                                    class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl border border-sky-500/30 bg-sky-500/10 text-sky-700 hover:bg-sky-500/20 text-xs sm:text-sm font-semibold transition">
                                <Icon :icon="syncing ? 'mdi:loading' : 'mdi:sync'" :class="['w-4 h-4 text-sky-600', syncing ? 'animate-spin' : '']" />
                                <span>{{ syncing ? 'Menyinkronkan...' : 'Sinkron Web Induk' }}</span>
                            </Button>
                            <Link :href="route('admin.users.import-excel')"
                                  class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl border border-amber-500/30 bg-amber-500/10 text-amber-700 hover:bg-amber-500/20 text-xs sm:text-sm font-semibold transition">
                                <Icon icon="mdi:file-excel-box" class="w-4 h-4 text-amber-600" />
                                <span>Import Excel</span>
                            </Link>
                            <Link :href="route('admin.users.create')"
                                  class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-primary text-primary-foreground hover:bg-primary/90 text-xs sm:text-sm font-semibold shadow-sm transition">
                                <Icon icon="mdi:account-plus" class="w-4 h-4" />
                                <span>Tambah Siswa</span>
                            </Link>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Desktop Table -->
            <div class="hidden md:block bg-card rounded-3xl shadow-sm border border-border/60 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border/60 bg-muted/30">
                                <th class="text-left px-5 py-3.5 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Pengguna</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Username</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Kelas</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Bidang</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Asal Sekolah</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Level</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Status</th>
                                <th class="text-center px-5 py-3.5 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/50">
                            <tr v-if="users.data.length === 0">
                                <td colspan="8" class="px-6 py-16 text-center text-muted-foreground">
                                    <div class="w-16 h-16 rounded-2xl bg-muted/50 flex items-center justify-center mx-auto mb-3">
                                        <Icon icon="mdi:account-search-outline" class="w-8 h-8 text-muted-foreground/40" />
                                    </div>
                                    <p class="font-semibold text-foreground">Tidak ada siswa yang ditemukan</p>
                                    <p class="text-xs text-muted-foreground mt-1">Coba ubah filter pencarian Anda atau tambah pengguna baru</p>
                                </td>
                            </tr>
                            <tr v-for="user in users.data" :key="user.id"
                                class="hover:bg-muted/30 transition-colors cursor-pointer"
                                @click="router.get(route('admin.users.show', user.id))">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div class="relative w-10 h-10 rounded-full overflow-hidden bg-muted/40 flex items-center justify-center shrink-0 border border-border/60">
                                            <span class="text-xs font-black text-primary select-none">{{ user.name?.charAt(0)?.toUpperCase() }}</span>
                                            <img v-if="user.profile_photo" :src="user.profile_photo.startsWith('http') ? user.profile_photo : '/storage/' + user.profile_photo" class="absolute inset-0 w-full h-full object-cover" @error="$event.target.style.display='none'" />
                                            
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-foreground truncate hover:text-primary transition-colors">{{ user.name }}</p>
                                            <p class="text-xs text-muted-foreground truncate">{{ user.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="text-xs font-mono text-muted-foreground">{{ user.username || user.email?.split('@')[0] || '-' }}</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="text-xs text-foreground font-medium">{{ user.student_class || '-' }}</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="text-xs text-foreground font-medium">{{ user.bidang || '-' }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-xs text-muted-foreground truncate max-w-[180px]" :title="user.school_name || ''">
                                    {{ user.school_name || '-' }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <span v-if="user.level" class="text-xs text-foreground font-medium">
                                        {{ user.level }}
                                    </span>
                                    <span v-else class="text-xs text-muted-foreground">-</span>
                                </td>
                                <td class="px-5 py-3.5">
                                    <Badge :variant="user.is_active ? 'success' : 'destructive'" class="gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="user.is_active ? 'bg-emerald-500' : 'bg-red-500'"></span>
                                        {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </Badge>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <div class="flex items-center justify-center gap-1.5" @click.stop>
                                        <Link :href="route('admin.users.show', user.id)" class="p-2 rounded-xl hover:bg-muted text-muted-foreground hover:text-foreground transition" title="Detail">
                                            <Icon icon="mdi:eye-outline" class="w-4 h-4" />
                                        </Link>
                                        <Link :href="route('admin.users.edit', user.id)" class="p-2 rounded-xl hover:bg-muted text-muted-foreground hover:text-foreground transition" title="Edit">
                                            <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                                        </Link>
                                        <button @click="toggleActive(user.id)" class="p-2 rounded-xl transition" :class="user.is_active ? 'hover:bg-rose-500/10 text-rose-600' : 'hover:bg-emerald-500/10 text-emerald-600'" :title="user.is_active ? 'Nonaktifkan' : 'Aktifkan'">
                                            <Icon :icon="user.is_active ? 'mdi:account-off-outline' : 'mdi:account-check-outline'" class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile Cards -->
            <div class="md:hidden space-y-3">
                <div v-if="users.data.length === 0" class="text-center py-16 bg-card rounded-3xl shadow-sm border border-border/60 p-6">
                    <div class="w-16 h-16 rounded-2xl bg-muted/50 flex items-center justify-center mx-auto mb-3">
                        <Icon icon="mdi:account-search-outline" class="w-8 h-8 text-muted-foreground/40" />
                    </div>
                    <p class="font-semibold text-foreground">Tidak ada siswa ditemukan</p>
                    <p class="text-xs text-muted-foreground mt-1">Coba ubah filter pencarian Anda</p>
                </div>

                <div v-for="user in users.data" :key="user.id"
                     class="bg-card rounded-3xl shadow-sm border border-border/60 p-5 space-y-3.5 cursor-pointer hover:border-primary/30 transition"
                     @click="router.get(route('admin.users.show', user.id))">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="relative w-11 h-11 rounded-full overflow-hidden bg-muted/40 flex items-center justify-center shrink-0 border border-border/60">
                                <span class="text-sm font-black text-primary select-none">{{ user.name?.charAt(0)?.toUpperCase() }}</span>
                                <img v-if="user.profile_photo" :src="user.profile_photo.startsWith('http') ? user.profile_photo : '/storage/' + user.profile_photo" class="absolute inset-0 w-full h-full object-cover" @error="$event.target.style.display='none'" />
                                
                            </div>
                            <div class="min-w-0">
                                <p class="font-bold text-sm text-foreground truncate">{{ user.name }}</p>
                                <p class="text-xs text-muted-foreground truncate">{{ user.email }}</p>
                            </div>
                        </div>
                        <Badge :variant="user.is_active ? 'success' : 'destructive'" class="text-[10px] gap-1 shrink-0 px-2 py-0.5 rounded-full font-bold">
                            <span class="w-1.5 h-1.5 rounded-full" :class="user.is_active ? 'bg-emerald-500' : 'bg-red-500'"></span>
                            {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                        </Badge>
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-xs bg-muted/40 rounded-2xl p-3">
                        <div>
                            <span class="block text-[10px] text-muted-foreground font-semibold uppercase">Username</span>
                            <span class="font-mono text-foreground font-medium">{{ user.username || user.email?.split('@')[0] || '-' }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] text-muted-foreground font-semibold uppercase">Kelas</span>
                            <span class="font-semibold text-foreground">{{ user.student_class || '-' }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] text-muted-foreground font-semibold uppercase">Bidang</span>
                            <span class="text-foreground">{{ user.bidang || '-' }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] text-muted-foreground font-semibold uppercase">Level</span>
                            <span class="font-medium text-foreground">{{ user.level || '-' }}</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-border/50 text-xs">
                        <span class="text-muted-foreground truncate max-w-[150px]">{{ user.school_name || '-' }}</span>
                        <div class="flex items-center gap-2" @click.stop>
                            <Link :href="route('admin.users.show', user.id)" class="text-muted-foreground hover:text-foreground font-bold">Detail</Link>
                            <span class="text-muted-foreground">&bull;</span>
                            <Link :href="route('admin.users.edit', user.id)" class="text-primary hover:underline font-bold">Edit</Link>
                            <span class="text-muted-foreground">&bull;</span>
                            <button @click="toggleActive(user.id)" :class="user.is_active ? 'text-rose-600' : 'text-emerald-600'" class="font-bold">
                                {{ user.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <Pagination :links="users.links" />
        </div>
    </AdminLayout>
</template>
