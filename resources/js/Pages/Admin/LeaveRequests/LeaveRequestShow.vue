<script setup>
import { inject, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
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
            <div class="bg-card rounded-2xl border border-border/60 shadow-sm p-5 sm:p-6 space-y-4 anim-fade-in-up overflow-hidden">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-muted-foreground font-medium uppercase tracking-wider">ID</p>
                        <p class="text-sm font-semibold">#{{ leaveRequest.id }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground font-medium uppercase tracking-wider">Status</p>
                        <span :class="[
                            'text-xs font-semibold px-2.5 py-1 rounded-lg',
                            leaveRequest.status === 'pending' ? 'bg-amber-100 text-amber-700' :
                            leaveRequest.status === 'approved' ? 'bg-emerald-100 text-emerald-700' :
                            'bg-red-100 text-red-700'
                        ]">{{ leaveRequest.status === 'pending' ? 'Menunggu' : leaveRequest.status === 'approved' ? 'Disetujui' : 'Ditolak' }}</span>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground font-medium uppercase tracking-wider">Pengguna</p>
                        <p class="text-sm font-semibold">{{ leaveRequest.user?.name }}</p>
                        <p class="text-xs text-muted-foreground">{{ leaveRequest.user?.email }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground font-medium uppercase tracking-wider">Tanggal Pengajuan</p>
                        <p class="text-sm">{{ formatDate(leaveRequest.created_at) }}</p>
                    </div>
                </div>

                <div>
                    <p class="text-xs text-muted-foreground font-medium uppercase tracking-wider mb-2">Alasan Izin</p>
                    <div class="bg-muted/50 rounded-xl p-4 border border-border/30">
                        <p class="text-sm leading-relaxed">{{ leaveRequest.reason }}</p>
                    </div>
                </div>

                <div v-if="leaveRequest.proof_file">
                    <p class="text-xs text-muted-foreground font-medium uppercase tracking-wider mb-2">Lampiran</p>
                    <a :href="'/storage/' + leaveRequest.proof_file" target="_blank"
                       class="inline-flex items-center gap-1.5 text-sm text-primary hover:text-primary/80 font-medium hover:underline transition">
                        <Icon icon="mdi:paperclip" class="w-4 h-4 inline-block align-middle" /> Lihat Lampiran
                    </a>
                </div>

                <div v-if="leaveRequest.admin_note">
                    <p class="text-xs text-muted-foreground font-medium uppercase tracking-wider mb-2">Catatan Admin</p>
                    <div class="bg-muted/50 rounded-xl p-4 border border-border/30">
                        <p class="text-sm leading-relaxed">{{ leaveRequest.admin_note }}</p>
                    </div>
                </div>
            </div>

            <div v-if="leaveRequest.status === 'pending'" class="bg-card rounded-2xl border border-border/60 shadow-sm p-5 sm:p-6 mt-4 space-y-4 anim-fade-in-up anim-delay-1">
                <h3 class="text-sm font-bold inline-flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center"><svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg></div>
                    Tindakan
                </h3>
                <div>
                    <label class="block text-sm font-semibold mb-1.5">Catatan Admin (opsional)</label>
                    <textarea v-model="form.admin_note" rows="3"
                              class="w-full rounded-xl border border-input bg-background px-3 py-2.5 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all duration-200"
                              placeholder="Tulis catatan untuk pengguna..."></textarea>
                </div>
                <div class="flex gap-3">
                    <Button @click="submitStatus('approved')" :disabled="submitting" class="gap-1.5 px-5 rounded-xl font-semibold bg-emerald-600 hover:bg-emerald-700 hover:shadow-md transition-all">
                        <Icon icon="mdi:check" class="w-4 h-4" /> Setujui
                    </Button>
                    <Button @click="submitStatus('rejected')" :disabled="submitting" variant="destructive" class="gap-1.5 px-5 rounded-xl font-semibold hover:shadow-md transition-all">
                        <Icon icon="mdi:close" class="w-4 h-4" /> Tolak
                    </Button>
                    <Button variant="ghost" @click="router.visit(route('admin.leave-requests.index'))" class="rounded-xl">Kembali</Button>
                </div>
            </div>

            <div v-else class="mt-4 anim-fade-in-up">
                <Button variant="ghost" @click="router.visit(route('admin.leave-requests.index'))" class="gap-1.5 rounded-xl font-semibold">← Kembali ke Daftar</Button>
            </div>
        </div>
    </AdminLayout>
</template>
