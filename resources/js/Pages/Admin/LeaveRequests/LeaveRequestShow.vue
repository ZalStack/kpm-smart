<script setup>
import { inject, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
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
const showConfirmModal = ref(false);
const pendingAction = ref('');

function formatDate(d) {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function triggerAction(action) {
    pendingAction.value = action;
    showConfirmModal.value = true;
}

function submitStatus() {
    submitting.value = true;
    router.put(route('admin.leave-requests.update-status', props.leaveRequest.id), {
        status: pendingAction.value,
        admin_note: form.value.admin_note,
    }, {
        onFinish: () => {
            submitting.value = false;
            showConfirmModal.value = false;
        },
    });
}

function getStatusConfig(s) {
    const map = {
        pending: { label: 'Menunggu', color: 'bg-amber-500/10 text-amber-700 border-amber-500/20', icon: 'mdi:clock-outline' },
        approved: { label: 'Disetujui', color: 'bg-emerald-500/10 text-emerald-700 border-emerald-500/20', icon: 'mdi:check-circle-outline' },
        rejected: { label: 'Ditolak', color: 'bg-rose-500/10 text-rose-700 border-rose-500/20', icon: 'mdi:close-circle-outline' },
    };
    return map[s] || map.pending;
}
</script>

<template>
    <AdminLayout>
        <Head :title="'Detail Izin #' + leaveRequest.id + ' - Admin'" />

        <template #header-title>Detail Pengajuan Izin</template>
        <template #header-sub>Pengajuan dari {{ leaveRequest.user?.name }}</template>

        <div class="w-full max-w-3xl mx-auto space-y-5">
            <!-- Back Link -->
            <Button variant="ghost" @click="router.visit(route('admin.leave-requests.index'))"
                    class="gap-2 rounded-xl font-semibold text-sm anim-fade-in-up">
                <Icon icon="mdi:arrow-left" class="w-4 h-4" /> Kembali ke Daftar
            </Button>

            <!-- Detail Card -->
            <div class="bg-card border border-border/60 rounded-2xl shadow-sm overflow-hidden anim-fade-in-up anim-delay-1">
                <!-- Card Header -->
                <div class="p-5 sm:p-6 border-b border-border/60 bg-gradient-to-r from-primary/5 to-transparent flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-primary/10 flex items-center justify-center text-primary flex-shrink-0">
                            <Icon icon="mdi:file-document-outline" class="w-5 h-5" />
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground font-medium">Pengajuan Izin #{{ leaveRequest.id }}</p>
                            <p class="text-sm font-bold text-foreground">{{ formatDate(leaveRequest.created_at) }}</p>
                        </div>
                    </div>
                    <span :class="['inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border text-xs font-bold', getStatusConfig(leaveRequest.status).color]">
                        <Icon :icon="getStatusConfig(leaveRequest.status).icon" class="w-3.5 h-3.5" />
                        {{ getStatusConfig(leaveRequest.status).label }}
                    </span>
                </div>

                <!-- User Info -->
                <div class="p-5 sm:p-6 border-b border-border/60">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center text-xl font-black text-primary flex-shrink-0 ring-2 ring-background shadow-sm">
                            {{ (leaveRequest.user?.name || 'U').charAt(0).toUpperCase() }}
                        </div>
                        <div>
                            <p class="font-bold text-base text-foreground">{{ leaveRequest.user?.name || '-' }}</p>
                            <p class="text-sm text-muted-foreground">{{ leaveRequest.user?.email || '-' }}</p>
                            <p v-if="leaveRequest.user?.kelas || leaveRequest.user?.bidang" class="text-xs text-muted-foreground mt-0.5">
                                {{ [leaveRequest.user?.kelas, leaveRequest.user?.bidang].filter(Boolean).join(' · ') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Details Body -->
                <div class="p-5 sm:p-6 space-y-5">
                    <!-- Reason -->
                    <div class="space-y-2">
                        <p class="text-xs font-bold text-muted-foreground uppercase tracking-wider flex items-center gap-1.5">
                            <Icon icon="mdi:text-box-outline" class="w-4 h-4" /> Alasan Pengajuan Izin
                        </p>
                        <div class="bg-muted/40 border border-border/50 rounded-xl p-4">
                            <p class="text-sm text-foreground leading-relaxed">{{ leaveRequest.reason || 'Tidak ada keterangan.' }}</p>
                        </div>
                    </div>

                    <!-- Attachment -->
                    <div v-if="leaveRequest.proof_file" class="space-y-2">
                        <p class="text-xs font-bold text-muted-foreground uppercase tracking-wider flex items-center gap-1.5">
                            <Icon icon="mdi:paperclip" class="w-4 h-4" /> Lampiran / Bukti
                        </p>
                        <a :href="'/storage/' + leaveRequest.proof_file" target="_blank"
                           class="inline-flex items-center gap-2 px-4 py-2.5 bg-card border border-border/60 rounded-xl text-sm font-semibold text-primary hover:bg-primary/5 hover:border-primary/30 transition-colors">
                            <Icon icon="mdi:file-eye-outline" class="w-4 h-4" /> Lihat Lampiran
                        </a>
                    </div>

                    <!-- Admin Note (if already processed) -->
                    <div v-if="leaveRequest.admin_note && leaveRequest.status !== 'pending'" class="space-y-2">
                        <p class="text-xs font-bold text-muted-foreground uppercase tracking-wider flex items-center gap-1.5">
                            <Icon icon="mdi:shield-account-outline" class="w-4 h-4" /> Catatan Admin
                        </p>
                        <div class="bg-blue-50/60 border border-blue-200/60 rounded-xl p-4">
                            <p class="text-sm text-blue-900 leading-relaxed">{{ leaveRequest.admin_note }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Panel (only for pending requests) -->
            <div v-if="leaveRequest.status === 'pending'"
                 class="bg-card border border-border/60 rounded-2xl shadow-sm overflow-hidden anim-fade-in-up anim-delay-2">
                <div class="p-5 sm:p-6 border-b border-border/60 bg-gradient-to-r from-amber-500/5 to-transparent flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-amber-500/10 flex items-center justify-center text-amber-600">
                        <Icon icon="mdi:gavel" class="w-4 h-4" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-foreground">Keputusan Admin</h3>
                        <p class="text-xs text-muted-foreground">Setujui atau tolak pengajuan izin ini</p>
                    </div>
                </div>

                <div class="p-5 sm:p-6 space-y-4">
                    <div class="space-y-1.5">
                        <label class="text-sm font-semibold text-foreground block">
                            Catatan Admin
                            <span class="text-muted-foreground font-normal text-xs ml-1">(Opsional)</span>
                        </label>
                        <textarea v-model="form.admin_note" rows="3"
                                  class="w-full rounded-xl border border-input bg-background px-4 py-3 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all resize-none"
                                  placeholder="Tulis catatan untuk pengguna... (opsional)"></textarea>
                    </div>

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <Button @click="triggerAction('approved')" :disabled="submitting"
                                class="flex-1 gap-2 py-2.5 rounded-xl font-bold bg-emerald-600 hover:bg-emerald-700 hover:shadow-md text-white">
                            <Icon icon="mdi:check-circle-outline" class="w-4 h-4" />
                            Setujui Pengajuan
                        </Button>
                        <Button @click="triggerAction('rejected')" :disabled="submitting"
                                variant="destructive"
                                class="flex-1 gap-2 py-2.5 rounded-xl font-bold hover:shadow-md">
                            <Icon icon="mdi:close-circle-outline" class="w-4 h-4" />
                            Tolak Pengajuan
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirm Modal -->
        <Teleport to="body">
            <div v-if="showConfirmModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showConfirmModal = false"></div>
                <div class="relative bg-card border border-border/80 rounded-2xl shadow-2xl w-full max-w-sm p-6 space-y-4 anim-fade-in-up">
                    <div class="flex items-center gap-3">
                        <div :class="['w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0',
                            pendingAction === 'approved' ? 'bg-emerald-500/10 text-emerald-600' : 'bg-rose-500/10 text-rose-600']">
                            <Icon :icon="pendingAction === 'approved' ? 'mdi:check-circle-outline' : 'mdi:close-circle-outline'" class="w-6 h-6" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-foreground">
                                {{ pendingAction === 'approved' ? 'Setujui Pengajuan?' : 'Tolak Pengajuan?' }}
                            </h3>
                            <p class="text-xs text-muted-foreground">Tindakan ini akan mengubah status pengajuan.</p>
                        </div>
                    </div>
                    <p class="text-xs text-muted-foreground bg-muted/40 p-3.5 rounded-xl border border-border/40 leading-relaxed">
                        Status pengajuan izin dari <strong>{{ leaveRequest.user?.name }}</strong> akan diubah menjadi
                        <strong>{{ pendingAction === 'approved' ? 'Disetujui' : 'Ditolak' }}</strong>.
                        Pengguna akan mendapatkan notifikasi hasilnya.
                    </p>
                    <div class="flex justify-end gap-2.5">
                        <Button variant="ghost" @click="showConfirmModal = false" class="rounded-xl text-xs">
                            Batal
                        </Button>
                        <Button @click="submitStatus" :disabled="submitting"
                                :class="['rounded-xl text-xs font-bold gap-1.5',
                                    pendingAction === 'approved' ? 'bg-emerald-600 hover:bg-emerald-700 text-white' : 'bg-rose-600 hover:bg-rose-700 text-white']">
                            <Icon v-if="submitting" icon="mdi:loading" class="w-3.5 h-3.5 animate-spin" />
                            <Icon v-else :icon="pendingAction === 'approved' ? 'mdi:check' : 'mdi:close'" class="w-3.5 h-3.5" />
                            {{ submitting ? 'Memproses...' : (pendingAction === 'approved' ? 'Ya, Setujui' : 'Ya, Tolak') }}
                        </Button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AdminLayout>
</template>
