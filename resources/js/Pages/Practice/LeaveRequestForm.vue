<script setup>
import { inject, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import UserLayout from '@/Layouts/UserLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
const route = inject('route');

const form = ref({
    reason: '',
    proof_file: null,
});

const fileInput = ref(null);
const fileName = ref('');
const submitting = ref(false);
const errorMsg = ref('');

function handleFile(e) {
    const file = e.target.files[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) {
        errorMsg.value = 'Ukuran file maksimal 2MB';
        fileInput.value.value = '';
        return;
    }
    errorMsg.value = '';
    form.value.proof_file = file;
    fileName.value = file.name;
}

function removeFile() {
    form.value.proof_file = null;
    fileName.value = '';
    if (fileInput.value) fileInput.value.value = '';
}

function submit() {
    errorMsg.value = '';
    if (!form.value.reason.trim()) {
        errorMsg.value = 'Alasan izin wajib diisi';
        return;
    }
    submitting.value = true;

    const formData = new FormData();
    formData.append('reason', form.value.reason);
    if (form.value.proof_file) {
        formData.append('proof_file', form.value.proof_file);
    }

    router.post(route('leave-requests.store'), formData, {
        onFinish: () => { submitting.value = false; },
    });
}
</script>

<template>
    <UserLayout>
        <Head title="Ajukan Izin" />

        <template #header-title>Ajukan Izin</template>
        <template #header-sub>Isi form di bawah untuk mengajukan izin</template>

        <div v-if="errorMsg" class="flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 text-sm rounded-2xl p-3 mb-4">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
            {{ errorMsg }}
        </div>

        <div class="bg-card rounded-2xl border p-6 space-y-4 anim-fade-in-up hover:shadow-lg transition-shadow duration-300">
            <div>
                <label class="block text-sm font-medium mb-1.5">Alasan Izin <span class="text-red-500">*</span></label>
                <textarea v-model="form.reason" rows="4" maxlength="500"
                          class="w-full rounded-xl border border-input bg-background px-3 py-2.5 text-sm placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary min-h-[120px] transition-all duration-200"
                          placeholder="Tuliskan alasan pengajuan izin..."></textarea>
                <p class="text-xs text-muted-foreground mt-1 text-right">{{ (form.reason || '').length }}/500</p>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1.5">Lampiran (opsional)</label>
                <p class="text-xs text-muted-foreground mb-2">Format: JPG, PNG, PDF. Maksimal 2MB.</p>
                <div v-if="fileName" class="flex items-center gap-2 bg-muted rounded-lg p-2 mb-2">
                    <span class="text-sm truncate flex-1 inline-flex items-center gap-1"><Icon icon="mdi:paperclip" class="w-4 h-4 inline-block align-middle" /> {{ fileName }}</span>
                    <button @click="removeFile" class="text-xs text-red-500 hover:underline">Hapus</button>
                </div>
                <input ref="fileInput" type="file" accept=".jpg,.jpeg,.png,.pdf" @change="handleFile"
                       class="w-full text-sm text-muted-foreground file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer" />
            </div>

            <div class="flex gap-3 pt-2">
                <Button variant="ghost" @click="router.visit(route('leave-requests.index'))" class="min-h-10">Batal</Button>
                <Button @click="submit" :disabled="submitting" class="flex-1 hover:shadow-md active:scale-[0.98] transition-all duration-200 min-h-12">
                    {{ submitting ? 'Mengirim...' : 'Kirim Pengajuan' }}
                </Button>
            </div>
        </div>
    </UserLayout>
</template>
