<script setup>
import { inject,  ref, computed } from 'vue';
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
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="stat-tile p-4">
                <p class="text-xs text-muted-foreground">Total User</p>
                <p class="text-2xl font-bold mt-1">{{ totalUsers }}</p>
            </div>
            <div class="stat-tile p-4">
                <p class="text-xs text-muted-foreground">Akun Aktif</p>
                <p class="text-2xl font-bold mt-1 text-green-600">{{ activeUsers }}</p>
            </div>
            <div class="stat-tile p-4">
                <p class="text-xs text-muted-foreground">Nonaktif</p>
                <p class="text-2xl font-bold mt-1 text-red-500">{{ inactiveUsers }}</p>
            </div>
            <div class="stat-tile p-4">
                <p class="text-xs text-muted-foreground">Baru Bulan Ini</p>
                <p class="text-2xl font-bold mt-1">{{ users.total }}</p>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="bg-card rounded-lg p-4 shadow-sm border mb-6">
            <form @submit.prevent="applyFilters" class="flex flex-col sm:flex-row gap-3">
                <Input v-model="searchVal" placeholder="Cari nama, email, sekolah..." class="flex-1" />
                <Select v-model="statusVal" class="w-full sm:w-36">
                    <option value="">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </Select>
                <div class="flex gap-2">
                    <Button type="submit" size="sm">Cari</Button>
                    <Button type="button" variant="ghost" size="sm" @click="resetFilters">Reset</Button>
                    <Link :href="route('admin.users.create')" class="inline-flex items-center justify-center bg-fern text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-fern/90 transition ml-2">
                        + Tambah User
                    </Link>
                </div>
            </form>
        </div>

        <!-- Desktop Table -->
        <div class="hidden md:block bg-card rounded-lg shadow-sm border overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="admin-table w-full text-sm">
                    <thead>
                        <tr>
                            <th class="text-left px-4 py-3">User</th>
                            <th class="text-left px-4 py-3">Kelas / Bidang</th>
                            <th class="text-left px-4 py-3">Sekolah</th>
                            <th class="text-left px-4 py-3">Status</th>
                            <th class="text-left px-4 py-3">Terdaftar</th>
                            <th class="text-center px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="users.data.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">Tidak ada user ditemukan</td>
                        </tr>
                        <tr v-for="user in users.data" :key="user.id" class="hover:bg-muted/50 transition-colors cursor-pointer" @click="router.get(route('admin.users.show', user.id))">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full overflow-hidden flex-shrink-0 bg-primary/10 flex items-center justify-center">
                                        <img v-if="user.profile_photo" :src="'/storage/' + user.profile_photo" class="w-full h-full object-cover" />
                                        <span v-else class="text-sm font-bold text-primary">{{ user.name?.charAt(0) }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium truncate">{{ user.name }}</p>
                                        <p class="text-xs text-muted-foreground truncate">{{ user.email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm">{{ user.student_class || '-' }} / {{ user.bidang || '-' }}</td>
                            <td class="px-4 py-3 text-sm truncate max-w-[150px]">{{ user.school_name || '-' }}</td>
                            <td class="px-4 py-3">
                                <Badge :variant="user.is_active ? 'success' : 'destructive'">{{ user.is_active ? 'Aktif' : 'Nonaktif' }}</Badge>
                            </td>
                            <td class="px-4 py-3 text-sm text-muted-foreground">{{ user.created_at ? new Date(user.created_at).toLocaleDateString('id-ID') : '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1" @click.stop>
                                    <Link :href="route('admin.users.show', user.id)" class="p-1.5 rounded-md hover:bg-muted transition text-muted-foreground hover:text-foreground" title="Detail">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </Link>
                                    <button @click="toggleActive(user.id)" class="p-1.5 rounded-md hover:bg-muted transition text-muted-foreground hover:text-foreground" :title="user.is_active ? 'Nonaktifkan' : 'Aktifkan'">
                                        <svg v-if="user.is_active" class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <svg v-else class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
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
            <div v-if="users.data.length === 0" class="text-center py-12 bg-card rounded-lg shadow-sm border">
                <p class="text-muted-foreground">Tidak ada user ditemukan</p>
            </div>
            <div v-for="user in users.data" :key="user.id" class="bg-card rounded-lg shadow-sm border p-4 cursor-pointer" @click="router.get(route('admin.users.show', user.id))">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-full overflow-hidden bg-primary/10 flex items-center justify-center flex-shrink-0">
                        <img v-if="user.profile_photo" :src="'/storage/' + user.profile_photo" class="w-full h-full object-cover" />
                        <span v-else class="text-sm font-bold text-primary">{{ user.name?.charAt(0) }}</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold truncate">{{ user.name }}</p>
                        <p class="text-xs text-muted-foreground truncate">{{ user.email }}</p>
                    </div>
                    <Badge :variant="user.is_active ? 'success' : 'destructive'" class="text-[10px]">{{ user.is_active ? 'Aktif' : 'Nonaktif' }}</Badge>
                </div>
                <div class="flex items-center justify-between text-xs text-muted-foreground">
                    <span>{{ user.school_name || '-' }}</span>
                    <button @click.stop="toggleActive(user.id)" class="text-primary hover:underline">{{ user.is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                </div>
            </div>
        </div>

        <Pagination :links="users.links" />
    </AdminLayout>
</template>
