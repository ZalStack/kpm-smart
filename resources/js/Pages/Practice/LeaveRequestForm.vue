<script setup>
import { inject, ref } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import UserLayout from '@/Layouts/UserLayout.vue';
import Textarea from '@/Components/ui/textarea/Textarea.vue';
import Label from '@/Components/ui/label/Label.vue';

const route = inject('route');

const form = ref({
    reason: '',
    proof_file: null,
});

const fileInput = ref(null);
const fileName = ref('');
const fileSize = ref('');
const submitting = ref(false);
const errorMsg = ref('');

function handleFile(e) {
    const file = e.target.files[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) {
        errorMsg.value = 'Ukuran file terlalu besar, maksimal 2MB';
        fileInput.value.value = '';
        return;
    }
    errorMsg.value = '';
    form.value.proof_file = file;
    fileName.value = file.name;
    fileSize.value = (file.size / 1024).toFixed(0) + ' KB';
}

function removeFile() {
    form.value.proof_file = null;
    fileName.value = '';
    fileSize.value = '';
    if (fileInput.value) fileInput.value.value = '';
}

function submit() {
    errorMsg.value = '';
    if (!form.value.reason.trim()) {
        errorMsg.value = 'Mohon isi alasan pengajuan izin secara lengkap.';
        return;
    }
    submitting.value = true;

    const formData = new FormData();
    formData.append('reason', form.value.reason);
    if (form.value.proof_file) {
        formData.append('proof_file', form.value.proof_file);
    }

    router.post(route('user.leave-requests.store'), formData, {
        onFinish: () => { submitting.value = false; },
    });
}
</script>

<template>
    <UserLayout>
        <Head title="Ajukan Izin - PR KK" />

        <template #header-title>Formulir Pengajuan Izin</template>
        <template #header-sub>Lengkapi data di bawah ini untuk mengajukan izin ketidakhadiran.</template>

        <div class="mx-auto max-w-2xl space-y-5">
            <Link :href="route('user.leave-requests.index')" class="back-link group">
                <Icon icon="mdi:arrow-left" class="h-4 w-4 transition-transform group-hover:-translate-x-0.5" />
                Kembali ke Daftar Izin
            </Link>

            <!-- Error -->
            <div v-if="errorMsg" class="anim-fade-in flex items-center gap-2.5 rounded-2xl border border-destructive/20 bg-destructive/10 p-4 text-destructive">
                <Icon icon="mdi:alert-circle-outline" class="h-5 w-5 shrink-0" />
                <p class="text-sm font-medium">{{ errorMsg }}</p>
            </div>

            <!-- Form -->
            <div class="ui-card anim-fade-in-up space-y-5 p-5 sm:p-7">
                <!-- Alasan -->
                <div class="space-y-2">
                    <Label for="reason" class="flex items-center justify-between">
                        <span>Alasan Pengajuan Izin <span class="text-destructive">*</span></span>
                        <span class="text-xs font-normal text-muted-foreground">{{ (form.reason || '').length }}/500</span>
                    </Label>
                    <Textarea
                        id="reason"
                        v-model="form.reason"
                        rows="5"
                        maxlength="500"
                        class="resize-none rounded-xl border-border/60 text-sm"
                        placeholder="Contoh: Mengikuti olimpiade sains tingkat kota pada tanggal..., atau sedang sakit..."
                    />
                </div>

                <!-- Lampiran -->
                <div class="space-y-2">
                    <Label class="text-sm font-semibold">Lampiran Bukti (Opsional)</Label>
                    <p class="text-xs text-muted-foreground">Upload surat dokter, surat tugas, atau dokumen pendukung (JPG, PNG, PDF maks. 2MB)</p>

                    <!-- Berkas terpilih -->
                    <div v-if="fileName" class="flex items-center justify-between rounded-xl border border-border/60 bg-muted/40 p-3.5">
                        <div class="flex min-w-0 items-center gap-2.5">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                <Icon icon="mdi:file-document-outline" class="h-4 w-4" />
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-xs font-semibold text-foreground">{{ fileName }}</p>
                                <p class="text-[10px] text-muted-foreground">{{ fileSize }}</p>
                            </div>
                        </div>
                        <button type="button" @click="removeFile" class="shrink-0 px-2 py-1 text-xs font-semibold text-destructive hover:underline">
                            Hapus
                        </button>
                    </div>

                    <!-- Dropzone -->
                    <div
                        v-else
                        @click="$refs.fileInput.click()"
                        class="cursor-pointer rounded-2xl border-2 border-dashed border-border/70 p-6 text-center transition-all duration-200 hover:border-primary/60 hover:bg-muted/20"
                    >
                        <span class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-2xl bg-muted/70 text-muted-foreground">
                            <Icon icon="mdi:cloud-upload-outline" class="h-6 w-6" />
                        </span>
                        <p class="text-xs font-bold text-foreground">Klik untuk memilih berkas lampiran</p>
                        <p class="mt-0.5 text-[11px] text-muted-foreground">JPG, PNG, atau PDF hingga 2MB</p>
                    </div>

                    <input ref="fileInput" type="file" accept=".jpg,.jpeg,.png,.pdf" @change="handleFile" class="hidden" />
                </div>

                <!-- Aksi -->
                <div class="flex flex-col-reverse gap-3 border-t border-border/50 pt-4 sm:flex-row sm:items-center sm:justify-end">
                    <button type="button" class="btn-ghost px-5 py-2.5" @click="router.visit(route('user.leave-requests.index'))">
                        Batal
                    </button>
                    <button type="button" class="btn-primary px-6 py-2.5" :disabled="submitting" @click="submit">
                        <span v-if="submitting" class="h-4 w-4 shrink-0 animate-spin rounded-full border-2 border-current border-t-transparent"></span>
                        <Icon v-else icon="mdi:send-outline" class="h-4 w-4" />
                        {{ submitting ? 'Mengirim Permohonan...' : 'Kirim Pengajuan Izin' }}
                    </button>
                </div>
            </div>
        </div>
    </UserLayout>
</template>
