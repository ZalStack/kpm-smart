<script setup>
import { inject, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
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

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6 sm:mb-8">
            <div class="stat-tile group p-4 sm:p-5 anim-fade-in-up anim-delay-1" style="--tile-accent: #769826;">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center flex-shrink-0 group-hover:from-primary/25 group-hover:to-primary/10 transition-all duration-300">
                        <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
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
                        <p class="text-[11px] sm:text-xs text-muted-foreground font-medium uppercase tracking-wider">Disetujui</p>
                        <p class="text-2xl sm:text-3xl font-extrabold tracking-tight text-emerald-600">{{ counts.approved || 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="stat-tile group p-4 sm:p-5 anim-fade-in-up anim-delay-4" style="--tile-accent: #ef4444;">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-xl bg-gradient-to-br from-red-500/15 to-red-500/5 flex items-center justify-center flex-shrink-0 group-hover:from-red-500/25 group-hover:to-red-500/10 transition-all duration-300">
                        <svg class="w-5 h-5 sm:w-5.5 sm:h-5.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] sm:text-xs text-muted-foreground font-medium uppercase tracking-wider">Ditolak</p>
                        <p class="text-2xl sm:text-3xl font-extrabold tracking-tight text-red-500">{{ counts.rejected || 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="hidden md:block bg-card rounded-2xl shadow-sm border border-border/60 overflow-hidden mb-6 anim-fade-in-up anim-delay-4">
            <div class="overflow-x-auto">
                <table class="admin-table w-full text-sm">
                    <thead>
                        <tr class="border-b border-border/60 bg-gradient-to-r from-muted/40 to-muted/20">
                            <th class="text-left px-5 py-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">#</th>
                            <th class="text-left px-5 py-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Pengguna</th>
                            <th class="text-left px-5 py-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Alasan</th>
                            <th class="text-left px-5 py-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Status</th>
                            <th class="text-left px-5 py-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Tanggal</th>
                            <th class="text-center px-5 py-4 font-semibold text-muted-foreground text-xs uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="leaveRequests.data.length === 0">
                            <td colspan="6" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center gap-4">
                                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-muted to-muted/50 flex items-center justify-center">
                                        <svg class="w-10 h-10 text-muted-foreground/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-muted-foreground text-base">Tidak ada pengajuan izin</p>
                                        <p class="text-sm text-muted-foreground/60 mt-1.5">Pengajuan izin akan muncul di sini</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr v-for="(item, index) in leaveRequests.data" :key="item.id"
                            class="border-b border-border/40 last:border-b-0 hover:bg-muted/50 transition-all duration-200 cursor-pointer"
                            :style="{ animationDelay: `${index * 30}ms` }">
                            <td class="px-5 py-4 text-muted-foreground font-mono text-xs">#{{ item.id }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center text-xs font-bold text-primary flex-shrink-0 ring-2 ring-background shadow-sm">{{ (item.user?.name || 'U').charAt(0) }}</div>
                                    <div class="min-w-0"><p class="font-semibold truncate text-xs">{{ item.user?.name }}</p><p class="text-[10px] text-muted-foreground truncate">{{ item.user?.email }}</p></div>
                                </div>
                            </td>
                            <td class="px-5 py-4 max-w-[200px]">
                                <p class="truncate text-sm">{{ item.reason }}</p>
                                <a v-if="item.proof_file" :href="'/storage/' + item.proof_file" target="_blank" class="text-[10px] text-primary hover:underline inline-flex items-center gap-1 mt-0.5"><Icon icon="mdi:paperclip" class="w-3 h-3 inline-block align-middle" /> Lampiran</a>
                            </td>
                            <td class="px-5 py-4">
                                <span :class="[
                                    'text-xs font-semibold px-2.5 py-1 rounded-lg',
                                    item.status === 'pending' ? 'bg-amber-100 text-amber-700' :
                                    item.status === 'approved' ? 'bg-emerald-100 text-emerald-700' :
                                    'bg-red-100 text-red-700'
                                ]">{{ item.status === 'pending' ? 'Menunggu' : item.status === 'approved' ? 'Disetujui' : 'Ditolak' }}</span>
                            </td>
                            <td class="px-5 py-4 text-xs text-muted-foreground">{{ formatDate(item.created_at) }}</td>
                            <td class="px-5 py-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <Link :href="route('admin.leave-requests.show', item.id)" class="p-2.5 rounded-xl hover:bg-primary/10 transition-all duration-200 text-muted-foreground hover:text-primary group/btn" title="Detail">
                                        <svg class="w-4 h-4 group-hover/btn:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </Link>
                                    <button @click="confirmDelete(item)" class="p-2.5 rounded-xl hover:bg-red-50 transition-all duration-200 text-muted-foreground hover:text-red-500 group/btn" title="Hapus">
                                        <svg class="w-4 h-4 group-hover/btn:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="md:hidden space-y-3 mb-6">
            <div v-if="leaveRequests.data.length === 0" class="text-center py-16 bg-card rounded-2xl shadow-sm border border-border/60">
                <div class="flex flex-col items-center gap-4 px-4">
                    <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-muted to-muted/50 flex items-center justify-center">
                        <svg class="w-10 h-10 text-muted-foreground/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                    </div>
                    <div>
                        <p class="text-muted-foreground font-semibold">Tidak ada pengajuan izin</p>
                        <p class="text-xs text-muted-foreground/60 mt-1">Pengajuan izin akan muncul di sini</p>
                    </div>
                </div>
            </div>
            <div v-for="(item, index) in leaveRequests.data" :key="item.id"
                class="bg-card rounded-2xl shadow-sm border border-border/60 p-4 hover:shadow-md hover:border-primary/20 transition-all duration-300 active:scale-[0.98]"
                :style="{ animationDelay: `${index * 40}ms` }">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center text-xs font-bold text-primary flex-shrink-0 ring-2 ring-background shadow-sm">{{ (item.user?.name || 'U').charAt(0) }}</div>
                        <p class="text-xs font-semibold">{{ item.user?.name }}</p>
                    </div>
                    <span :class="[
                        'text-[10px] font-semibold px-2 py-0.5 rounded-lg',
                        item.status === 'pending' ? 'bg-amber-100 text-amber-700' :
                        item.status === 'approved' ? 'bg-emerald-100 text-emerald-700' :
                        'bg-red-100 text-red-700'
                    ]">{{ item.status === 'pending' ? 'Menunggu' : item.status === 'approved' ? 'Disetujui' : 'Ditolak' }}</span>
                </div>
                <p class="text-sm text-muted-foreground line-clamp-2 mb-3">{{ item.reason }}</p>
                <div class="flex items-center justify-between text-xs text-muted-foreground bg-muted/30 rounded-xl px-3.5 py-2.5">
                    <span>{{ formatDate(item.created_at) }}</span>
                    <Link :href="route('admin.leave-requests.show', item.id)" class="text-primary hover:underline font-medium">Detail</Link>
                </div>
            </div>
        </div>

        <Pagination :links="leaveRequests.links" />

        <ConfirmDialog :open="deleteDialog.open" title="Hapus Pengajuan Izin" :message="'Hapus pengajuan izin dari ' + deleteDialog.label + '?' " confirm-text="Ya, Hapus" @update:open="deleteDialog.open = $event" @confirm="doDelete" />
    </AdminLayout>
</template>
