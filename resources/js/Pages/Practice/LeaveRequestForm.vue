<script setup>
import { inject, ref } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import UserLayout from '@/Layouts/UserLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
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
        <Head title="Ajukan Izin - KPM SMART" />

        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <Icon icon="mdi:calendar-edit" class="w-5 h-5" />
                </span>
                <span>Formulir Pengajuan Izin</span>
            </div>
        </template>
        <template #header-sub>
            Lengkapi data di bawah ini untuk mengajukan izin ketidakhadiran
        </template>

        <div class="max-w-2xl mx-auto space-y-5">
            <!-- Back navigation -->
            <Link :href="route('user.leave-requests.index')"
                  class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-medium text-muted-foreground hover:text-foreground transition group">
                <Icon icon="mdi:arrow-left" class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" />
                Kembali ke Daftar Izin
            </Link>

            <!-- Error Banner -->
            <div v-if="errorMsg" class="flex items-center gap-2.5 bg-destructive/10 border border-destructive/20 text-destructive text-sm rounded-2xl p-4">
                <Icon icon="mdi:alert-circle-outline" class="w-5 h-5 shrink-0" />
                <p class="font-medium text-xs sm:text-sm">{{ errorMsg }}</p>
            </div>

            <!-- Form Card -->
            <div class="bg-card rounded-2xl border border-border/60 p-6 sm:p-7 shadow-sm space-y-5">
                <div class="space-y-2">
                    <Label for="reason" class="text-sm font-semibold flex items-center justify-between">
                        <span>Alasan Pengajuan Izin <span class="text-destructive">*</span></span>
                        <span class="text-xs font-normal text-muted-foreground">{{ (form.reason || '').length }}/500</span>
                    </Label>
                    <Textarea id="reason"
                              v-model="form.reason"
                              rows="4"
                              maxlength="500"
                              class="rounded-xl border-border/60 resize-none text-sm"
                              placeholder="Contoh: Mengikuti olimpiade sains tingkat kota pada tanggal..., atau sedang sakit..." />
                </div>

                <div class="space-y-2">
                    <Label class="text-sm font-semibold">Lampiran Bukti (Opsional)</Label>
                    <p class="text-xs text-muted-foreground">Upload surat dokter, surat tugas, atau dokumen pendukung (JPG, PNG, PDF maks. 2MB)</p>

                    <!-- Selected file card -->
                    <div v-if="fileName" class="flex items-center justify-between p-3.5 bg-muted/40 rounded-xl border border-border/60">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="w-8 h-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center shrink-0">
                                <Icon icon="mdi:file-document-outline" class="w-4 h-4" />
                            </span>
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-foreground truncate">{{ fileName }}</p>
                                <p class="text-[10px] text-muted-foreground">{{ fileSize }}</p>
                            </div>
                        </div>
                        <button type="button" @click="removeFile" class="text-xs font-semibold text-destructive hover:underline px-2 py-1">
                            Hapus
                        </button>
                    </div>

                    <!-- Dropzone -->
                    <div v-else
                         @click="$refs.fileInput.click()"
                         class="border-2 border-dashed border-border/70 hover:border-primary/60 hover:bg-muted/20 rounded-2xl p-6 text-center cursor-pointer transition-all duration-200">
                        <div class="w-12 h-12 rounded-2xl bg-muted/70 flex items-center justify-center mx-auto mb-2 text-muted-foreground">
                            <Icon icon="mdi:cloud-upload-outline" class="w-6 h-6" />
                        </div>
                        <p class="text-xs font-bold text-foreground">Klik untuk memilih berkas lampiran</p>
                        <p class="text-[11px] text-muted-foreground mt-0.5">JPG, PNG, atau PDF hingga 2MB</p>
                    </div>

                    <input ref="fileInput" type="file" accept=".jpg,.jpeg,.png,.pdf" @change="handleFile" class="hidden" />
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col-reverse sm:flex-row items-center justify-end gap-3 pt-4 border-t border-border/50">
                    <Button variant="ghost" type="button" @click="router.visit(route('user.leave-requests.index'))" class="w-full sm:w-auto">
                        Batal
                    </Button>
                    <Button type="button" @click="submit" :loading="submitting" class="w-full sm:w-auto gap-2 font-semibold shadow-sm min-h-11">
                        <Icon v-if="!submitting" icon="mdi:send-outline" class="w-4 h-4" />
                        {{ submitting ? 'Mengirim Permohonan...' : 'Kirim Pengajuan Izin' }}
                    </Button>
                </div>
            </div>
        </div>
    </UserLayout>
</template>
