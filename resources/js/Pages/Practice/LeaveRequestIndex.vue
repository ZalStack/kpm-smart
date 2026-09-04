<script setup>
import { inject, ref } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import UserLayout from '@/Layouts/UserLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Pagination from '@/Components/shared/Pagination.vue';
import { timeAgo } from '@/lib/utils';
const route = inject('route');

const props = defineProps({
    leaveRequests: { type: Object, required: true },
});

function statusLabel(s) {
    return s === 'pending' ? 'Menunggu' : s === 'approved' ? 'Disetujui' : 'Ditolak';
}
function statusVariant(s) {
    return s === 'pending' ? 'warning' : s === 'approved' ? 'success' : 'destructive';
}
</script>

<template>
    <UserLayout>
        <Head title="Riwayat Izin" />

        <template #header-title>Riwayat Pengajuan Izin</template>
        <template #header-sub>Lihat status pengajuan izin anda</template>

        <div class="flex items-center justify-end mb-6">
            <Link :href="route('leave-requests.create')">
                <Button size="sm" class="hover:shadow-md active:scale-95 transition-all duration-200 min-h-10">+ Ajukan Izin</Button>
            </Link>
        </div>

        <div v-if="leaveRequests.data.length === 0" class="text-center py-16 bg-card rounded-2xl border anim-fade-in-up hover:shadow-lg transition-shadow duration-300">
            <div class="mx-auto w-16 h-16 rounded-full bg-muted flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
            </div>
            <h3 class="text-base font-semibold mb-1">Belum ada pengajuan izin</h3>
            <p class="text-sm text-muted-foreground mb-5 max-w-xs mx-auto">Ajukan izin pertamamu sekarang</p>
            <Link :href="route('leave-requests.create')">
                <Button size="sm" class="hover:shadow-md active:scale-95 transition-all duration-200 min-h-10">Ajukan Izin Sekarang</Button>
            </Link>
        </div>

        <div v-else class="space-y-3">
            <div v-for="(item, idx) in leaveRequests.data" :key="item.id"
                 class="bg-card rounded-2xl border p-4 shadow-card hover:shadow-card-hover hover:-translate-y-0.5 transition-all duration-200 anim-fade-in-up" :style="{ animationDelay: (idx * 60) + 'ms' }">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium line-clamp-2">{{ item.reason }}</p>
                        <p class="text-xs text-muted-foreground mt-1">{{ timeAgo(item.created_at) }}</p>
                    </div>
                    <span :class="[
                        'flex-shrink-0 text-xs font-medium px-2.5 py-1 rounded-full',
                        item.status === 'pending' ? 'bg-yellow-100 text-yellow-700' :
                        item.status === 'approved' ? 'bg-green-100 text-green-700' :
                        'bg-red-100 text-red-700'
                    ]">{{ statusLabel(item.status) }}</span>
                </div>
                <div v-if="item.proof_file" class="mt-2">
                    <a :href="'/storage/' + item.proof_file" target="_blank"
                       class="text-xs text-primary hover:underline inline-flex items-center gap-1"><Icon icon="mdi:paperclip" class="w-3.5 h-3.5 inline-block align-middle" /> Lampiran</a>
                </div>
                <div v-if="item.admin_note" class="mt-2 bg-muted rounded-lg p-2">
                    <p class="text-xs text-muted-foreground"><strong>Catatan Admin:</strong> {{ item.admin_note }}</p>
                </div>
            </div>
        </div>

        <Pagination :links="leaveRequests.links" class="mt-6" />
    </UserLayout>
</template>
