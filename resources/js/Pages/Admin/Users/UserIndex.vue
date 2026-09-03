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
});

const searchVal = ref(props.search);
const statusVal = ref(props.status);

const inactiveUsers = computed(() => props.totalUsers - props.activeUsers);

function applyFilters() {
    router.get(route('admin.users.index'), {
        search: searchVal.value,
        status: statusVal.value,
    }, { preserveState: true, replace: true });
}

function resetFilters() {
    searchVal.value = '';
    statusVal.value = '';
    applyFilters();
}

function toggleActive(userId) {
    if (confirm('Yakin ingin mengubah status akun ini?')) {
        router.post(route('admin.users.toggle-active', userId));
    }
}
</script>

<template>
    <AdminLayout>
        <Head title="Kelola User - Admin" />

        <template #header-title>Kelola User</template>
        <template #header-sub>Manajemen data pengguna platform</template>

        <!-- Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
            <!-- Total Users -->
            <div class="stat-tile group p-4 sm:p-5 anim-fade-in-up anim-delay-1">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-primary/10 flex items-center justify-center flex-shrink-0 group-hover:bg-primary/20 transition-colors">
                        <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128H5.228A2 2 0 013 17.208V5.792a2 2 0 012.228-1.986h13.544A2 2 0 0121 5.792v11.416a2 2 0 01-2.228 1.986M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-muted-foreground font-medium">Total User</p>
                        <p class="text-2xl font-bold tracking-tight">{{ totalUsers }}</p>
                    </div>
                </div>
            </div>

            <!-- Active Users -->
            <div class="stat-tile group p-4 sm:p-5 anim-fade-in-up anim-delay-2">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-success-500/10 flex items-center justify-center flex-shrink-0 group-hover:bg-success-500/20 transition-colors">
                        <svg class="w-5 h-5 text-success-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-muted-foreground font-medium">Akun Aktif</p>
                        <p class="text-2xl font-bold tracking-tight text-success-600">{{ activeUsers }}</p>
                    </div>
                </div>
            </div>

            <!-- Inactive -->
            <div class="stat-tile group p-4 sm:p-5 anim-fade-in-up anim-delay-3">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-danger-500/10 flex items-center justify-center flex-shrink-0 group-hover:bg-danger-500/20 transition-colors">
                        <svg class="w-5 h-5 text-danger-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-muted-foreground font-medium">Nonaktif</p>
                        <p class="text-2xl font-bold tracking-tight text-danger-500">{{ inactiveUsers }}</p>
                    </div>
                </div>
            </div>

            <!-- New This Month -->
            <div class="stat-tile group p-4 sm:p-5 anim-fade-in-up anim-delay-4">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-warning-500/10 flex items-center justify-center flex-shrink-0 group-hover:bg-warning-500/20 transition-colors">
                        <svg class="w-5 h-5 text-warning-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-muted-foreground font-medium">Baru Bulan Ini</p>
                        <p class="text-2xl font-bold tracking-tight">{{ users.total }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="bg-card rounded-xl p-4 shadow-sm border mb-6 anim-fade-in-up anim-delay-3">
            <form @submit.prevent="applyFilters" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <Input v-model="searchVal" placeholder="Cari nama, email, sekolah..." class="pl-10" />
                </div>
                <Select v-model="statusVal" class="w-full sm:w-40">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </Select>
                <div class="flex gap-2">
                    <Button type="submit" size="sm" class="gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        Cari
                    </Button>
                    <Button type="button" variant="ghost" size="sm" @click="resetFilters">Reset</Button>
                    <Link :href="route('admin.users.create')" class="inline-flex items-center gap-1.5 bg-fern text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-fern/90 transition-all duration-200 hover:shadow-md active:scale-[0.98] ml-auto sm:ml-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Tambah User
                    </Link>
                </div>
            </form>
        </div>

        <!-- Desktop Table -->
        <div class="hidden md:block bg-card rounded-xl shadow-sm border overflow-hidden mb-6 anim-fade-in-up anim-delay-4">
            <div class="overflow-x-auto">
                <table class="admin-table w-full text-sm">
                    <thead>
                        <tr class="border-b bg-muted/30">
                            <th class="text-left px-5 py-3.5 font-semibold text-muted-foreground">User</th>
                            <th class="text-left px-5 py-3.5 font-semibold text-muted-foreground">Kelas / Bidang</th>
                            <th class="text-left px-5 py-3.5 font-semibold text-muted-foreground">Sekolah</th>
                            <th class="text-left px-5 py-3.5 font-semibold text-muted-foreground">Status</th>
                            <th class="text-left px-5 py-3.5 font-semibold text-muted-foreground">Terdaftar</th>
                            <th class="text-center px-5 py-3.5 font-semibold text-muted-foreground">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="users.data.length === 0">
                            <td colspan="6" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-14 h-14 rounded-full bg-muted flex items-center justify-center">
                                        <svg class="w-7 h-7 text-muted-foreground/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128H5.228A2 2 0 013 17.208V5.792a2 2 0 012.228-1.986h13.544A2 2 0 0121 5.792v11.416a2 2 0 01-2.228 1.986M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-muted-foreground">Tidak ada user ditemukan</p>
                                        <p class="text-xs text-muted-foreground/70 mt-1">Coba ubah filter pencarian Anda</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="user in users.data" :key="user.id" class="border-b last:border-b-0 hover:bg-muted/40 transition-colors cursor-pointer group" @click="router.get(route('admin.users.show', user.id))">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl overflow-hidden flex-shrink-0 bg-primary/10 flex items-center justify-center ring-2 ring-background group-hover:ring-primary/20 transition-all">
                                        <img v-if="user.profile_photo" :src="'/storage/' + user.profile_photo" class="w-full h-full object-cover" />
                                        <span v-else class="text-sm font-bold text-primary">{{ user.name?.charAt(0) }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold truncate group-hover:text-primary transition-colors">{{ user.name }}</p>
                                        <p class="text-xs text-muted-foreground truncate">{{ user.email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-sm">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-muted/60 text-foreground">
                                    {{ user.student_class || '-' }} / {{ user.bidang || '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-sm truncate max-w-[180px] text-muted-foreground">{{ user.school_name || '-' }}</td>
                            <td class="px-5 py-3.5">
                                <Badge :variant="user.is_active ? 'success' : 'destructive'" class="gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="user.is_active ? 'bg-success-600' : 'bg-danger-500'"></span>
                                    {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                                </Badge>
                            </td>
                            <td class="px-5 py-3.5 text-sm text-muted-foreground">{{ user.created_at ? new Date(user.created_at).toLocaleDateString('id-ID') : '-' }}</td>
                            <td class="px-5 py-3.5 text-center">
                                <div class="flex items-center justify-center gap-1" @click.stop>
                                    <Link :href="route('admin.users.show', user.id)" class="p-2 rounded-lg hover:bg-muted transition text-muted-foreground hover:text-foreground" title="Detail">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </Link>
                                    <button @click="toggleActive(user.id)" class="p-2 rounded-lg hover:bg-muted transition text-muted-foreground hover:text-foreground" :title="user.is_active ? 'Nonaktifkan' : 'Aktifkan'">
                                        <svg v-if="user.is_active" class="w-4 h-4 text-success-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <svg v-else class="w-4 h-4 text-danger-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
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
            <div v-if="users.data.length === 0" class="text-center py-16 bg-card rounded-xl shadow-sm border">
                <div class="flex flex-col items-center gap-3">
                    <div class="w-14 h-14 rounded-full bg-muted flex items-center justify-center">
                        <svg class="w-7 h-7 text-muted-foreground/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128H5.228A2 2 0 013 17.208V5.792a2 2 0 012.228-1.986h13.544A2 2 0 0121 5.792v11.416a2 2 0 01-2.228 1.986M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                    <p class="text-muted-foreground font-medium">Tidak ada user ditemukan</p>
                </div>
            </div>
            <div v-for="user in users.data" :key="user.id" class="bg-card rounded-xl shadow-sm border p-4 cursor-pointer hover:shadow-md transition-all duration-200 active:scale-[0.98]" @click="router.get(route('admin.users.show', user.id))">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-11 h-11 rounded-xl overflow-hidden bg-primary/10 flex items-center justify-center flex-shrink-0 ring-2 ring-background">
                        <img v-if="user.profile_photo" :src="'/storage/' + user.profile_photo" class="w-full h-full object-cover" />
                        <span v-else class="text-sm font-bold text-primary">{{ user.name?.charAt(0) }}</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold truncate">{{ user.name }}</p>
                        <p class="text-xs text-muted-foreground truncate">{{ user.email }}</p>
                    </div>
                    <Badge :variant="user.is_active ? 'success' : 'destructive'" class="text-[10px] gap-1 flex-shrink-0">
                        <span class="w-1.5 h-1.5 rounded-full" :class="user.is_active ? 'bg-success-600' : 'bg-danger-500'"></span>
                        {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                    </Badge>
                </div>
                <div class="flex items-center justify-between text-xs text-muted-foreground">
                    <span class="inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" />
                        </svg>
                        {{ user.school_name || '-' }}
                    </span>
                    <button @click.stop="toggleActive(user.id)" class="inline-flex items-center gap-1 text-primary font-medium hover:underline">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182" />
                        </svg>
                        {{ user.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                    </button>
                </div>
            </div>
        </div>

        <Pagination :links="users.links" />
    </AdminLayout>
</template>
