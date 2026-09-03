<script setup>
import { inject, ref } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Pagination from '@/Components/shared/Pagination.vue';
const route = inject('route');

const props = defineProps({
    leaveRequests: { type: Object, required: true },
});

function formatDate(d) {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

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

        <div class="max-w-4xl mx-auto px-4 py-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-xl font-bold">Riwayat Pengajuan Izin</h1>
                    <p class="text-sm text-muted-foreground mt-1">Lihat status pengajuan izin anda</p>
                </div>
                <Link :href="route('leave-requests.create')">
                    <Button size="sm">+ Ajukan Izin</Button>
                </Link>
            </div>

            <div v-if="leaveRequests.data.length === 0" class="text-center py-12 bg-card rounded-xl border">
                <p class="text-muted-foreground">Belum ada pengajuan izin</p>
                <Link :href="route('leave-requests.create')" class="mt-3 inline-block">
                    <Button size="sm" variant="outline">Ajukan Izin Sekarang</Button>
                </Link>
            </div>

            <div v-else class="space-y-3">
                <div v-for="item in leaveRequests.data" :key="item.id"
                     class="bg-card rounded-xl border p-4 hover:shadow-sm transition">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium line-clamp-2">{{ item.reason }}</p>
                            <p class="text-xs text-muted-foreground mt-1">{{ formatDate(item.created_at) }}</p>
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
                           class="text-xs text-primary hover:underline">📎 Lampiran</a>
                    </div>
                    <div v-if="item.admin_note" class="mt-2 bg-muted rounded-lg p-2">
                        <p class="text-xs text-muted-foreground"><strong>Catatan Admin:</strong> {{ item.admin_note }}</p>
                    </div>
                </div>
            </div>

            <Pagination :links="leaveRequests.links" class="mt-6" />
        </div>
    </UserLayout>
</template>
