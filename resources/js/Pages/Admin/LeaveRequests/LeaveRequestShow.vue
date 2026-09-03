<script setup>
import { inject, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
const route = inject('route');

const props = defineProps({
    leaveRequest: { type: Object, required: true },
});

const form = ref({
    status: props.leaveRequest.status === 'pending' ? '' : props.leaveRequest.status,
    admin_note: props.leaveRequest.admin_note || '',
});
const submitting = ref(false);

function formatDate(d) {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function submitStatus(status) {
    if (!confirm(status === 'approved' ? 'Setujui pengajuan ini?' : 'Tolak pengajuan ini?')) return;
    submitting.value = true;
    router.put(route('admin.leave-requests.update-status', props.leaveRequest.id), {
        status,
        admin_note: form.value.admin_note,
    }, {
        onFinish: () => { submitting.value = false; },
    });
}
</script>

<template>
    <AdminLayout>
        <Head :title="'Detail Izin #' + leaveRequest.id + ' - Admin'" />

        <template #header-title>Detail Pengajuan Izin</template>
        <template #header-sub>Detail pengajuan izin dari {{ leaveRequest.user?.name }}</template>

        <div>
            <div class="bg-card rounded-xl border p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-muted-foreground">ID</p>
                        <p class="text-sm font-medium">#{{ leaveRequest.id }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">Status</p>
                        <span :class="[
                            'text-xs font-medium px-2.5 py-1 rounded-full',
                            leaveRequest.status === 'pending' ? 'bg-yellow-100 text-yellow-700' :
                            leaveRequest.status === 'approved' ? 'bg-green-100 text-green-700' :
                            'bg-red-100 text-red-700'
                        ]">{{ leaveRequest.status === 'pending' ? 'Menunggu' : leaveRequest.status === 'approved' ? 'Disetujui' : 'Ditolak' }}</span>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">Pengguna</p>
                        <p class="text-sm font-medium">{{ leaveRequest.user?.name }}</p>
                        <p class="text-xs text-muted-foreground">{{ leaveRequest.user?.email }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">Tanggal Pengajuan</p>
                        <p class="text-sm">{{ formatDate(leaveRequest.created_at) }}</p>
                    </div>
                </div>

                <div>
                    <p class="text-xs text-muted-foreground mb-1">Alasan Izin</p>
                    <div class="bg-muted rounded-lg p-3">
                        <p class="text-sm">{{ leaveRequest.reason }}</p>
                    </div>
                </div>

                <div v-if="leaveRequest.proof_file">
                    <p class="text-xs text-muted-foreground mb-1">Lampiran</p>
                    <a :href="'/storage/' + leaveRequest.proof_file" target="_blank"
                       class="inline-flex items-center gap-1 text-sm text-primary hover:underline">
                        📎 Lihat Lampiran
                    </a>
                </div>

                <div v-if="leaveRequest.admin_note">
                    <p class="text-xs text-muted-foreground mb-1">Catatan Admin</p>
                    <div class="bg-muted rounded-lg p-3">
                        <p class="text-sm">{{ leaveRequest.admin_note }}</p>
                    </div>
                </div>
            </div>

            <div v-if="leaveRequest.status === 'pending'" class="bg-card rounded-xl border p-6 mt-4 space-y-4">
                <h3 class="text-sm font-semibold">Tindakan</h3>
                <div>
                    <label class="block text-sm font-medium mb-1.5">Catatan Admin (opsional)</label>
                    <textarea v-model="form.admin_note" rows="3"
                              class="w-full rounded-lg border border-input bg-background px-3 py-2 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary"
                              placeholder="Tulis catatan untuk pengguna..."></textarea>
                </div>
                <div class="flex gap-3">
                    <Button @click="submitStatus('approved')" :disabled="submitting" class="bg-green-600 hover:bg-green-700">
                        ✓ Setujui
                    </Button>
                    <Button @click="submitStatus('rejected')" :disabled="submitting" variant="destructive">
                        ✕ Tolak
                    </Button>
                    <Button variant="ghost" @click="router.visit(route('admin.leave-requests.index'))">Kembali</Button>
                </div>
            </div>

            <div v-else class="mt-4">
                <Button variant="ghost" @click="router.visit(route('admin.leave-requests.index'))">← Kembali ke Daftar</Button>
            </div>
        </div>
    </AdminLayout>
</template>
