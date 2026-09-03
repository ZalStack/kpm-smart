<script setup>
import { inject,  ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Input from '@/Components/ui/input/Input.vue';
import Select from '@/Components/ui/select/Select.vue';
import Badge from '@/Components/ui/badge/Badge.vue';
import Pagination from '@/Components/shared/Pagination.vue';
import ConfirmDialog from '@/Components/shared/ConfirmDialog.vue';
const route = inject('route');

const props = defineProps({
    packages: { type: Object, required: true },
    allKelas: { type: Array, default: () => [] },
    allBidang: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
    stats: { type: Object, default: () => ({}) },
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const kelas = ref(props.filters?.kelas || '');
const bidang = ref(props.filters?.bidang || '');
const deleteDialog = ref({ open: false, package: null });

const deleteDialogMessage = computed(() => `Apakah Anda yakin ingin menghapus soal '${deleteDialog.value.package?.title || ''}'?`);

function applyFilters() {
    router.get(route('admin.packages.index'), {
        search: search.value, status: status.value, kelas: kelas.value, bidang: bidang.value,
    }, { preserveState: true, replace: true });
}

function resetFilters() {
    search.value = ''; status.value = ''; kelas.value = ''; bidang.value = '';
    applyFilters();
}

function confirmDelete(pkg) {
    deleteDialog.value = { open: true, package: pkg };
}

function doDelete() {
    if (deleteDialog.value.package) {
        router.delete(route('admin.packages.destroy', deleteDialog.value.package.id), {
            onSuccess: () => {
                deleteDialog.value = { open: false, package: null };
            },
        });
    }
}
</script>

<template>
    <AdminLayout>
        <Head title="Kelola Soal - Admin" />

        <template #header-title>Soal Tugas</template>
        <template #header-sub>Kelola soal tugas dengan mudah</template>

        <!-- Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="stat-tile p-4">
                <p class="text-xs text-muted-foreground">Total Paket</p>
                <p class="text-2xl font-bold mt-1">{{ stats.total || 0 }}</p>
            </div>
            <div class="stat-tile p-4">
                <p class="text-xs text-muted-foreground">Aktif</p>
                <p class="text-2xl font-bold mt-1 text-green-600">{{ stats.active || 0 }}</p>
            </div>
            <div class="stat-tile p-4">
                <p class="text-xs text-muted-foreground">Nonaktif</p>
                <p class="text-2xl font-bold mt-1 text-red-500">{{ stats.inactive || 0 }}</p>
            </div>
            <div class="stat-tile p-4">
                <p class="text-xs text-muted-foreground">Total Soal</p>
                <p class="text-2xl font-bold mt-1">{{ stats.totalQuestions || 0 }}</p>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="bg-card rounded-lg p-4 shadow-sm border mb-6">
            <form @submit.prevent="applyFilters" class="flex flex-col sm:flex-row gap-3">
                <Input v-model="search" placeholder="Cari soal..." class="flex-1" />
                <Select v-model="status" class="w-full sm:w-36">
                    <option value="">Semua Status</option>
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </Select>
                <Select v-model="bidang" class="w-full sm:w-36">
                    <option value="">Semua Bidang</option>
                    <option v-for="b in allBidang" :key="b" :value="b">{{ b }}</option>
                </Select>
                <Select v-model="kelas" class="w-full sm:w-36">
                    <option value="">Semua Kelas</option>
                    <option v-for="k in allKelas" :key="k" :value="k">{{ k }}</option>
                </Select>
                <div class="flex gap-2">
                    <Button type="submit" size="sm">Cari</Button>
                    <Button type="button" variant="ghost" size="sm" @click="resetFilters">Reset</Button>
                    <Link :href="route('admin.packages.create')" class="inline-flex items-center justify-center bg-fern text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-fern/90 transition ml-2">
                        + Tambah Soal
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
                            <th class="text-left px-4 py-3">#</th>
                            <th class="text-left px-4 py-3">Soal</th>
                            <th class="text-left px-4 py-3">Bidang</th>
                            <th class="text-left px-4 py-3">Level</th>
                            <th class="text-left px-4 py-3">Status</th>
                            <th class="text-center px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="packages.data.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">Belum ada soal</td>
                        </tr>
                        <tr v-for="(pkg, idx) in packages.data" :key="pkg.id" class="hover:bg-muted/50 transition-colors">
                            <td class="px-4 py-3 text-muted-foreground">{{ packages.from + idx }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-sm flex-shrink-0 overflow-hidden">
                                        <img v-if="pkg.thumbnail" :src="'/storage/' + pkg.thumbnail" class="w-full h-full object-cover" />
                                        <span v-else>📦</span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium truncate max-w-[200px]">{{ pkg.title }}</p>
                                        <p class="text-xs text-muted-foreground truncate max-w-[200px]">{{ pkg.description }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">{{ pkg.bidang || '-' }}</td>
                            <td class="px-4 py-3">{{ pkg.level || '-' }}</td>
                            <td class="px-4 py-3">
                                <Badge :variant="pkg.is_active ? 'success' : 'destructive'">{{ pkg.is_active ? 'Aktif' : 'Nonaktif' }}</Badge>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <Link :href="route('admin.packages.detail', pkg.id)" class="p-1.5 rounded-md hover:bg-muted transition text-muted-foreground hover:text-foreground" title="Detail">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </Link>
                                    <Link :href="route('admin.packages.edit.informasi', pkg.id)" class="p-1.5 rounded-md hover:bg-muted transition text-muted-foreground hover:text-foreground" title="Edit">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                                    </Link>
                                    <button @click="confirmDelete(pkg)" class="p-1.5 rounded-md hover:bg-red-50 transition text-muted-foreground hover:text-red-500" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Cards -->
        <div class="md:hidden space-y-4 mb-6">
            <div v-if="packages.data.length === 0" class="text-center py-12 bg-card rounded-lg shadow-sm border">
                <p class="text-muted-foreground">Belum ada soal</p>
            </div>
            <div v-for="(pkg, idx) in packages.data" :key="pkg.id" class="bg-card rounded-lg shadow-sm border p-4">
                <div class="flex items-start gap-3 mb-3">
                    <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0 overflow-hidden">
                        <img v-if="pkg.thumbnail" :src="'/storage/' + pkg.thumbnail" class="w-full h-full object-cover" />
                        <span v-else class="text-xl">📦</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold truncate">{{ pkg.title }}</p>
                        <p class="text-xs text-muted-foreground truncate">{{ pkg.description }}</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-1.5 mb-3">
                    <Badge v-if="pkg.bidang" variant="outline" class="text-[10px]">{{ pkg.bidang }}</Badge>
                    <Badge :variant="pkg.is_active ? 'success' : 'destructive'" class="text-[10px]">{{ pkg.is_active ? 'Aktif' : 'Nonaktif' }}</Badge>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('admin.packages.detail', pkg.id)" class="flex-1 text-center bg-muted text-foreground py-2 rounded-md text-xs font-medium hover:bg-muted/80 transition">Detail</Link>
                    <Link :href="route('admin.packages.edit.informasi', pkg.id)" class="flex-1 text-center bg-primary text-primary-foreground py-2 rounded-md text-xs font-medium hover:bg-primary/90 transition">Edit</Link>
                    <button @click="confirmDelete(pkg)" class="px-3 py-2 rounded-md bg-red-50 text-red-600 text-xs font-medium hover:bg-red-100 transition">Hapus</button>
                </div>
            </div>
        </div>

        <Pagination :links="packages.links" />

        <ConfirmDialog :open="deleteDialog.open" title="Hapus Soal" :message="deleteDialogMessage" confirm-text="Ya, Hapus" @update:open="deleteDialog.open = $event" @confirm="doDelete" />
    </AdminLayout>
</template>
