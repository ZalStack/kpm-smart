<script setup>
import { inject,  ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
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
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6 sm:mb-8">
            <div class="stat-tile group p-4 sm:p-5 anim-fade-in-up anim-delay-1" style="--tile-accent: #769826;">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center flex-shrink-0 group-hover:from-primary/25 group-hover:to-primary/10 transition-all duration-300">
                        <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" /></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] sm:text-xs text-muted-foreground font-medium uppercase tracking-wider">Total</p>
                        <p class="text-2xl sm:text-3xl font-extrabold tracking-tight text-foreground">{{ counts.total || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="stat-tile group p-4 sm:p-5 anim-fade-in-up anim-delay-2" style="--tile-accent: #f59e0b;">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-amber-500/15 to-amber-500/5 flex items-center justify-center flex-shrink-0 group-hover:from-amber-500/25 group-hover:to-amber-500/10 transition-all duration-300">
                        <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] sm:text-xs text-muted-foreground font-medium uppercase tracking-wider">Menunggu</p>
                        <p class="text-2xl sm:text-3xl font-extrabold tracking-tight text-amber-600">{{ counts.pending || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="stat-tile group p-4 sm:p-5 anim-fade-in-up anim-delay-3" style="--tile-accent: #52b788;">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-emerald-500/15 to-emerald-500/5 flex items-center justify-center flex-shrink-0 group-hover:from-emerald-500/25 group-hover:to-emerald-500/10 transition-all duration-300">
                        <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] sm:text-xs text-muted-foreground font-medium uppercase tracking-wider">Dijawab</p>
                        <p class="text-2xl sm:text-3xl font-extrabold tracking-tight text-emerald-600">{{ counts.answered || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="stat-tile group p-4 sm:p-5 anim-fade-in-up anim-delay-4" style="--tile-accent: #6b7280;">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-gray-500/15 to-gray-500/5 flex items-center justify-center flex-shrink-0 group-hover:from-gray-500/25 group-hover:to-gray-500/10 transition-all duration-300">
                        <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] sm:text-xs text-muted-foreground font-medium uppercase tracking-wider">Ditutup</p>
                        <p class="text-2xl sm:text-3xl font-extrabold tracking-tight text-gray-500">{{ counts.closed || 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="bg-card rounded-2xl p-4 sm:p-5 shadow-sm border border-border/60 mb-6 sm:mb-8 anim-fade-in-up anim-delay-3">
            <form @submit.prevent="applyFilters" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    <Input v-model="search" placeholder="Cari pertanyaan, nama, email..." class="pl-11 h-11 rounded-xl bg-muted/50 border-border/60 focus:bg-background transition-colors" />
                </div>
                <Select v-model="status" class="w-full sm:w-44 h-11 rounded-xl bg-muted/50 border-border/60">
                    <option value="">Semua Status</option>
                    <option value="pending">Menunggu</option>
                    <option value="answered">Dijawab</option>
                    <option value="closed">Ditutup</option>
                </Select>
                <div class="flex gap-2 sm:gap-3">
                    <Button type="submit" size="sm" class="gap-1.5 h-11 px-5 rounded-xl font-semibold shadow-sm hover:shadow-md transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                        Filter
                    </Button>
                    <a :href="route('admin.support.export') + (status ? '?status=' + status : '')" class="inline-flex items-center justify-center gap-1.5 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:shadow-lg hover:shadow-emerald-500/25 transition-all duration-300 active:scale-[0.97]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
                        Export
                    </a>
                </div>
            </form>
        </div>

        <!-- Bulk Actions -->
        <div v-if="selected.length > 0" class="bg-primary/5 border border-primary/20 rounded-2xl p-3 mb-4 flex items-center justify-between anim-fade-in-up">
            <span class="text-sm font-medium">{{ selected.length }} dipilih</span>
            <Button variant="destructive" size="sm" class="gap-1.5 rounded-xl hover:shadow-md transition-all" @click="bulkDelete"><Icon icon="mdi:delete-outline" class="w-4 h-4 inline-block align-middle mr-1" /> Hapus Terpilih</Button>
        </div>

        <!-- Desktop Table -->
        <div class="hidden md:block bg-card rounded-2xl shadow-sm border border-border/60 overflow-hidden mb-6 anim-fade-in-up anim-delay-4">
            <div class="overflow-x-auto">
                <table class="admin-table w-full text-sm">
                    <thead>
                        <tr class="border-b border-border/60 bg-gradient-to-r from-muted/40 to-muted/20">
                            <th class="px-5 py-4 w-10"><input type="checkbox" v-model="selectAll" @change="toggleSelectAll" class="rounded" /></th>
                            <th class="text-left px-5 py-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">#</th>
                            <th class="text-left px-5 py-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">User</th>
                            <th class="text-left px-5 py-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Pertanyaan</th>
                            <th class="text-left px-5 py-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Status</th>
                            <th class="text-left px-5 py-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Tanggal</th>
                            <th class="text-center px-5 py-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="tickets.data.length === 0">
                            <td colspan="7" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center gap-4">
                                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-muted to-muted/50 flex items-center justify-center">
                                        <svg class="w-10 h-10 text-muted-foreground/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" /></svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-muted-foreground text-base">Tidak ada tiket</p>
                                        <p class="text-sm text-muted-foreground/60 mt-1.5">Tiket dukungan akan muncul di sini</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="(ticket, index) in tickets.data" :key="ticket.id"
                            class="border-b border-border/40 last:border-b-0 hover:bg-muted/50 transition-all duration-200 cursor-pointer"
                            :style="{ animationDelay: `${index * 30}ms` }">
                            <td class="px-5 py-4"><input type="checkbox" :checked="selected.includes(ticket.id)" @change="toggleSelect(ticket.id)" class="rounded" /></td>
                            <td class="px-5 py-4 text-muted-foreground font-mono text-xs">#{{ ticket.id }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center text-xs font-bold text-primary flex-shrink-0 ring-2 ring-background">{{ (ticket.name || 'A').charAt(0) }}</div>
                                    <div class="min-w-0"><p class="font-medium truncate text-xs">{{ ticket.name || 'Anonim' }}</p><p class="text-[10px] text-muted-foreground truncate">{{ ticket.email || '-' }}</p></div>
                                </div>
                            </td>
                            <td class="px-5 py-4 max-w-[200px]">
                                <p class="truncate text-sm">{{ ticket.question }}</p>
                                <p v-if="ticket.answer" class="text-[10px] text-green-600 mt-0.5 inline-flex items-center gap-1"><Icon icon="mdi:check" class="w-3 h-3 inline-block align-middle" /> Sudah dijawab</p>
                            </td>
                            <td class="px-5 py-4"><Badge :variant="ticket.status === 'pending' ? 'warning' : ticket.status === 'answered' ? 'success' : 'secondary'" class="gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold"><span class="w-1.5 h-1.5 rounded-full" :class="ticket.status === 'pending' ? 'bg-amber-500' : ticket.status === 'answered' ? 'bg-emerald-500' : 'bg-gray-400'"></span>{{ ticket.status === 'pending' ? 'Menunggu' : ticket.status === 'answered' ? 'Dijawab' : 'Ditutup' }}</Badge></td>
                            <td class="px-5 py-4 text-xs text-muted-foreground">{{ formatDate(ticket.created_at) }}</td>
                            <td class="px-5 py-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <Link :href="route('admin.support.show', ticket.id)" class="p-2.5 rounded-xl hover:bg-primary/10 transition-all duration-200 text-muted-foreground hover:text-primary group/btn" title="Detail">
                                        <svg class="w-4 h-4 group-hover/btn:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </Link>
                                    <button @click="confirmDelete(ticket)" class="p-2.5 rounded-xl hover:bg-red-50 transition-all duration-200 text-muted-foreground hover:text-red-500 group/btn" title="Hapus">
                                        <svg class="w-4 h-4 group-hover/btn:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
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
            <div v-if="tickets.data.length === 0" class="text-center py-16 bg-card rounded-2xl shadow-sm border border-border/60">
                <div class="flex flex-col items-center gap-4 px-4">
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-muted to-muted/50 flex items-center justify-center">
                        <svg class="w-10 h-10 text-muted-foreground/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 01-.825-.242m9.345-8.334a2.126 2.126 0 00-.476-.095 48.64 48.64 0 00-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0011.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" /></svg>
                    </div>
                    <div>
                        <p class="text-muted-foreground font-semibold">Tidak ada tiket</p>
                        <p class="text-xs text-muted-foreground/60 mt-1">Tiket dukungan akan muncul di sini</p>
                    </div>
                </div>
            </div>
            <div v-for="(ticket, index) in tickets.data" :key="ticket.id"
                class="bg-card rounded-2xl shadow-sm border border-border/60 p-4 cursor-pointer hover:shadow-md hover:border-primary/20 transition-all duration-300 active:scale-[0.98]"
                :style="{ animationDelay: `${index * 40}ms` }">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center text-xs font-bold text-primary flex-shrink-0 ring-2 ring-background shadow-sm">{{ (ticket.name || 'A').charAt(0) }}</div>
                        <div><p class="text-xs font-medium">{{ ticket.name || 'Anonim' }}</p><p class="text-[10px] text-muted-foreground">#{{ ticket.id }}</p></div>
                    </div>
                    <Badge :variant="ticket.status === 'pending' ? 'warning' : ticket.status === 'answered' ? 'success' : 'secondary'" class="text-[10px] gap-1 flex-shrink-0 px-2 py-0.5 rounded-lg"><span class="w-1.5 h-1.5 rounded-full" :class="ticket.status === 'pending' ? 'bg-amber-500' : ticket.status === 'answered' ? 'bg-emerald-500' : 'bg-gray-400'"></span>{{ ticket.status === 'pending' ? 'Menunggu' : ticket.status === 'answered' ? 'Dijawab' : 'Ditutup' }}</Badge>
                </div>
                <p class="text-sm text-muted-foreground line-clamp-2 mb-3">{{ ticket.question }}</p>
                <div class="flex items-center justify-between text-xs text-muted-foreground bg-muted/30 rounded-xl px-3.5 py-2.5">
                    <span>{{ formatDate(ticket.created_at) }}</span>
                    <Link :href="route('admin.support.show', ticket.id)" class="text-primary hover:underline font-medium">Detail →</Link>
                </div>
            </div>
        </div>

        <Pagination :links="tickets.links" />

        <ConfirmDialog :open="deleteDialog.open" title="Hapus Tiket" :message="'Hapus tiket ' + deleteDialog.label + '?' " confirm-text="Ya, Hapus" @update:open="deleteDialog.open = $event" @confirm="doDelete" />
    </AdminLayout>
</template>
