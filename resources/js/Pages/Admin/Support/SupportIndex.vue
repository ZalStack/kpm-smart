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
    tickets: { type: Object, required: true },
    counts: { type: Object, default: () => ({}) },
});

const search = ref('');
const status = ref('');
const selected = ref([]);
const selectAll = ref(false);
const deleteDialog = ref({ open: false, url: null, label: '' });

function applyFilters() {
    router.get(route('admin.support.index'), { search: search.value, status: status.value }, { preserveState: true, replace: true });
}

function toggleSelectAll() {
    if (selectAll.value) {
        selected.value = props.tickets.data.map(t => t.id);
    } else {
        selected.value = [];
    }
}

function toggleSelect(id) {
    const idx = selected.value.indexOf(id);
    if (idx > -1) { selected.value.splice(idx, 1); } else { selected.value.push(id); }
    selectAll.value = selected.value.length === props.tickets.data.length;
}

function bulkDelete() {
    if (selected.value.length === 0) return;
    if (!confirm(`Hapus ${selected.value.length} tiket?`)) return;
    router.post(route('admin.support.bulk-delete'), { ids: selected.value });
    selected.value = [];
    selectAll.value = false;
}

function confirmDelete(ticket) {
    deleteDialog.value = { open: true, url: route('admin.support.delete', ticket.id), label: 'Tiket #' + ticket.id };
}

function doDelete() {
    if (deleteDialog.value.url) {
        router.delete(deleteDialog.value.url);
        deleteDialog.value = { open: false, url: null, label: '' };
    }
}

function formatDate(d) {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <AdminLayout>
        <Head title="Tiket Dukungan - Admin" />

        <template #header-title>Tiket Dukungan</template>
        <template #header-sub>Kelola pertanyaan dan masukan dari pengguna</template>

        <!-- Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="stat-tile p-4"><p class="text-xs text-muted-foreground">Total</p><p class="text-2xl font-bold mt-1">{{ counts.total || 0 }}</p></div>
            <div class="stat-tile p-4"><p class="text-xs text-muted-foreground">Menunggu</p><p class="text-2xl font-bold mt-1 text-yellow-600">{{ counts.pending || 0 }}</p></div>
            <div class="stat-tile p-4"><p class="text-xs text-muted-foreground">Dijawab</p><p class="text-2xl font-bold mt-1 text-green-600">{{ counts.answered || 0 }}</p></div>
            <div class="stat-tile p-4"><p class="text-xs text-muted-foreground">Ditutup</p><p class="text-2xl font-bold mt-1 text-gray-500">{{ counts.closed || 0 }}</p></div>
        </div>

        <!-- Toolbar -->
        <div class="bg-card rounded-lg p-4 shadow-sm border mb-6">
            <form @submit.prevent="applyFilters" class="flex flex-col sm:flex-row gap-3">
                <Input v-model="search" placeholder="Cari pertanyaan, nama, email..." class="flex-1" />
                <Select v-model="status" class="w-full sm:w-36">
                    <option value="">Semua Status</option>
                    <option value="pending">Menunggu</option>
                    <option value="answered">Dijawab</option>
                    <option value="closed">Ditutup</option>
                </Select>
                <div class="flex gap-2">
                    <Button type="submit" size="sm">Filter</Button>
                    <a :href="route('admin.support.export') + (status ? '?status=' + status : '')" class="inline-flex items-center justify-center bg-green-600 text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-green-700 transition">📥 Export</a>
                </div>
            </form>
        </div>

        <!-- Bulk Actions -->
        <div v-if="selected.length > 0" class="bg-primary/5 border border-primary/20 rounded-lg p-3 mb-4 flex items-center justify-between">
            <span class="text-sm font-medium">{{ selected.length }} dipilih</span>
            <Button variant="destructive" size="sm" @click="bulkDelete">🗑️ Hapus Terpilih</Button>
        </div>

        <!-- Desktop Table -->
        <div class="hidden md:block bg-card rounded-lg shadow-sm border overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="admin-table w-full text-sm">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 w-10"><input type="checkbox" v-model="selectAll" @change="toggleSelectAll" class="rounded" /></th>
                            <th class="text-left px-4 py-3">#</th>
                            <th class="text-left px-4 py-3">User</th>
                            <th class="text-left px-4 py-3">Pertanyaan</th>
                            <th class="text-left px-4 py-3">Status</th>
                            <th class="text-left px-4 py-3">Tanggal</th>
                            <th class="text-center px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="tickets.data.length === 0">
                            <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">Tidak ada tiket</td>
                        </tr>
                        <tr v-for="ticket in tickets.data" :key="ticket.id" class="hover:bg-muted/50 transition-colors">
                            <td class="px-4 py-3"><input type="checkbox" :checked="selected.includes(ticket.id)" @change="toggleSelect(ticket.id)" class="rounded" /></td>
                            <td class="px-4 py-3 text-muted-foreground">#{{ ticket.id }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-xs font-bold text-primary flex-shrink-0">{{ (ticket.name || 'A').charAt(0) }}</div>
                                    <div class="min-w-0"><p class="font-medium truncate text-xs">{{ ticket.name || 'Anonim' }}</p><p class="text-[10px] text-muted-foreground truncate">{{ ticket.email || '-' }}</p></div>
                                </div>
                            </td>
                            <td class="px-4 py-3 max-w-[200px]">
                                <p class="truncate text-sm">{{ ticket.question }}</p>
                                <p v-if="ticket.answer" class="text-[10px] text-green-600 mt-0.5">✓ Sudah dijawab</p>
                            </td>
                            <td class="px-4 py-3"><Badge :variant="ticket.status === 'pending' ? 'warning' : ticket.status === 'answered' ? 'success' : 'secondary'" class="text-[10px]">{{ ticket.status === 'pending' ? 'Menunggu' : ticket.status === 'answered' ? 'Dijawab' : 'Ditutup' }}</Badge></td>
                            <td class="px-4 py-3 text-xs text-muted-foreground">{{ formatDate(ticket.created_at) }}</td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <Link :href="route('admin.support.show', ticket.id)" class="p-1.5 rounded-md hover:bg-muted transition text-muted-foreground hover:text-foreground" title="Detail">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </Link>
                                    <button @click="confirmDelete(ticket)" class="p-1.5 rounded-md hover:bg-red-50 transition text-muted-foreground hover:text-red-500" title="Hapus">
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
        <div class="md:hidden space-y-3 mb-6">
            <div v-if="tickets.data.length === 0" class="text-center py-12 bg-card rounded-lg shadow-sm border"><p class="text-muted-foreground">Tidak ada tiket</p></div>
            <div v-for="ticket in tickets.data" :key="ticket.id" class="bg-card rounded-lg shadow-sm border p-4">
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center text-xs font-bold text-primary flex-shrink-0">{{ (ticket.name || 'A').charAt(0) }}</div>
                        <div><p class="text-xs font-medium">{{ ticket.name || 'Anonim' }}</p><p class="text-[10px] text-muted-foreground">#{{ ticket.id }}</p></div>
                    </div>
                    <Badge :variant="ticket.status === 'pending' ? 'warning' : ticket.status === 'answered' ? 'success' : 'secondary'" class="text-[10px]">{{ ticket.status }}</Badge>
                </div>
                <p class="text-sm text-muted-foreground line-clamp-2 mb-2">{{ ticket.question }}</p>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-muted-foreground">{{ formatDate(ticket.created_at) }}</span>
                    <Link :href="route('admin.support.show', ticket.id)" class="text-primary hover:underline">Detail →</Link>
                </div>
            </div>
        </div>

        <Pagination :links="tickets.links" />

        <ConfirmDialog :open="deleteDialog.open" title="Hapus Tiket" :message="'Hapus tiket ' + deleteDialog.label + '?' " confirm-text="Ya, Hapus" @update:open="deleteDialog.open = $event" @confirm="doDelete" />
    </AdminLayout>
</template>
