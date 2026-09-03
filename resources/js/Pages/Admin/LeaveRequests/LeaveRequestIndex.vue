<script setup>
import { inject, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Pagination from '@/Components/shared/Pagination.vue';
import ConfirmDialog from '@/Components/shared/ConfirmDialog.vue';
const route = inject('route');

const props = defineProps({
    leaveRequests: { type: Object, required: true },
    counts: { type: Object, default: () => ({}) },
});

const deleteDialog = ref({ open: false, id: null, label: '' });

function formatDate(d) {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function confirmDelete(item) {
    deleteDialog.value = { open: true, id: item.id, label: item.user?.name || 'User #' + item.user_id };
}

function doDelete() {
    if (deleteDialog.value.id) {
        router.delete(route('admin.leave-requests.delete', deleteDialog.value.id));
        deleteDialog.value = { open: false, id: null, label: '' };
    }
}
</script>

<template>
    <AdminLayout>
        <Head title="Pengajuan Izin - Admin" />

        <template #header-title>Pengajuan Izin</template>
        <template #header-sub>Kelola pengajuan izin dari pengguna</template>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="stat-tile p-4"><p class="text-xs text-muted-foreground">Total</p><p class="text-2xl font-bold mt-1">{{ counts.total || 0 }}</p></div>
            <div class="stat-tile p-4"><p class="text-xs text-muted-foreground">Menunggu</p><p class="text-2xl font-bold mt-1 text-yellow-600">{{ counts.pending || 0 }}</p></div>
            <div class="stat-tile p-4"><p class="text-xs text-muted-foreground">Disetujui</p><p class="text-2xl font-bold mt-1 text-green-600">{{ counts.approved || 0 }}</p></div>
            <div class="stat-tile p-4"><p class="text-xs text-muted-foreground">Ditolak</p><p class="text-2xl font-bold mt-1 text-red-600">{{ counts.rejected || 0 }}</p></div>
        </div>

        <div class="hidden md:block bg-card rounded-lg shadow-sm border overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="admin-table w-full text-sm">
                    <thead>
                        <tr>
                            <th class="text-left px-4 py-3">#</th>
                            <th class="text-left px-4 py-3">Pengguna</th>
                            <th class="text-left px-4 py-3">Alasan</th>
                            <th class="text-left px-4 py-3">Status</th>
                            <th class="text-left px-4 py-3">Tanggal</th>
                            <th class="text-center px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="leaveRequests.data.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">Tidak ada pengajuan izin</td>
                        </tr>
                        <tr v-for="item in leaveRequests.data" :key="item.id" class="hover:bg-muted/50 transition-colors">
                            <td class="px-4 py-3 text-muted-foreground">#{{ item.id }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-xs font-bold text-primary flex-shrink-0">{{ (item.user?.name || 'U').charAt(0) }}</div>
                                    <div class="min-w-0"><p class="font-medium truncate text-xs">{{ item.user?.name }}</p><p class="text-[10px] text-muted-foreground truncate">{{ item.user?.email }}</p></div>
                                </div>
                            </td>
                            <td class="px-4 py-3 max-w-[200px]">
                                <p class="truncate text-sm">{{ item.reason }}</p>
                                <a v-if="item.proof_file" :href="'/storage/' + item.proof_file" target="_blank" class="text-[10px] text-primary hover:underline">📎 Lampiran</a>
                            </td>
                            <td class="px-4 py-3">
                                <span :class="[
                                    'text-xs font-medium px-2.5 py-1 rounded-full',
                                    item.status === 'pending' ? 'bg-yellow-100 text-yellow-700' :
                                    item.status === 'approved' ? 'bg-green-100 text-green-700' :
                                    'bg-red-100 text-red-700'
                                ]">{{ item.status === 'pending' ? 'Menunggu' : item.status === 'approved' ? 'Disetujui' : 'Ditolak' }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs text-muted-foreground">{{ formatDate(item.created_at) }}</td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <Link :href="route('admin.leave-requests.show', item.id)" class="p-1.5 rounded-md hover:bg-muted transition text-muted-foreground hover:text-foreground" title="Detail">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </Link>
                                    <button @click="confirmDelete(item)" class="p-1.5 rounded-md hover:bg-red-50 transition text-muted-foreground hover:text-red-500" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="md:hidden space-y-3 mb-6">
            <div v-if="leaveRequests.data.length === 0" class="text-center py-12 bg-card rounded-lg shadow-sm border"><p class="text-muted-foreground">Tidak ada pengajuan izin</p></div>
            <div v-for="item in leaveRequests.data" :key="item.id" class="bg-card rounded-lg shadow-sm border p-4">
                <div class="flex items-start justify-between mb-2">
                    <p class="text-xs font-medium">{{ item.user?.name }}</p>
                    <span :class="[
                        'text-[10px] font-medium px-2 py-0.5 rounded-full',
                        item.status === 'pending' ? 'bg-yellow-100 text-yellow-700' :
                        item.status === 'approved' ? 'bg-green-100 text-green-700' :
                        'bg-red-100 text-red-700'
                    ]">{{ item.status === 'pending' ? 'Menunggu' : item.status === 'approved' ? 'Disetujui' : 'Ditolak' }}</span>
                </div>
                <p class="text-sm text-muted-foreground line-clamp-2 mb-2">{{ item.reason }}</p>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-muted-foreground">{{ formatDate(item.created_at) }}</span>
                    <Link :href="route('admin.leave-requests.show', item.id)" class="text-primary hover:underline">Detail</Link>
                </div>
            </div>
        </div>

        <Pagination :links="leaveRequests.links" />

        <ConfirmDialog :open="deleteDialog.open" title="Hapus Pengajuan Izin" :message="'Hapus pengajuan izin dari ' + deleteDialog.label + '?' " confirm-text="Ya, Hapus" @update:open="deleteDialog.open = $event" @confirm="doDelete" />
    </AdminLayout>
</template>
