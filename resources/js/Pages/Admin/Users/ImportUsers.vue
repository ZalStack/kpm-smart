<script setup>
import { inject, ref } from 'vue';
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
const route = inject('route');

const page = usePage();
const form = useForm({ file: null });
const resetForm = useForm({});
const showResetConfirm = ref(false);
const fileName = ref('');
const dragOver = ref(false);
const fileInput = ref(null);

function handleFile(e) {
    const file = e.target.files[0];
    if (!file) return;
    processFile(file);
}

function processFile(file) {
    const allowed = ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel', 'text/csv'];
    if (!allowed.includes(file.type) && !file.name.match(/\.(xlsx|xls|csv)$/i)) {
        alert('Format file harus .xlsx, .xls, atau .csv');
        return;
    }
    if (file.size > 10 * 1024 * 1024) {
        alert('Ukuran file maksimal 10MB');
        return;
    }
    form.file = file;
    fileName.value = file.name;
}

function handleDrop(e) {
    e.preventDefault();
    dragOver.value = false;
    const file = e.dataTransfer.files[0];
    if (file) processFile(file);
}

function removeFile() {
    form.file = null;
    fileName.value = '';
    if (fileInput.value) fileInput.value.value = '';
}

function submit() {
    if (!form.file) return;
    form.post(route('admin.users.import-excel.process'), {
        forceFormData: true,
        onSuccess: () => {
            form.file = null;
            fileName.value = '';
            if (fileInput.value) fileInput.value.value = '';
        },
    });
}

function confirmReset() {
    resetForm.post(route('admin.users.reset-imported'), {
        onSuccess: () => {
            showResetConfirm.value = false;
        },
    });
}
</script>

<template>
    <AdminLayout>
        <Head title="Import User - Admin" />

        <template #header-title>Import User dari Excel</template>
        <template #header-sub>Import data pengguna secara massal dari file Excel</template>

        <div class="w-full">
            <!-- Back Link -->
            <Link :href="route('admin.users.index')"
                  class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-primary transition mb-6 anim-fade-in-up">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                Kembali ke Daftar User
            </Link>

            <div class="space-y-6">
                <!-- Upload Section -->
                <div class="anim-fade-in-up rounded-2xl border border-border/60 bg-card shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-border/60 bg-gradient-to-r from-primary/5 to-transparent">
                        <h2 class="text-lg font-bold flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center"><svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg></div>
                            Upload File Excel
                        </h2>
                        <p class="text-sm text-muted-foreground mt-1">Pilih file Excel yang berisi data pengguna</p>
                    </div>
                    <div class="p-6">
                        <!-- Drop Zone -->
                        <div @dragover.prevent="dragOver = true" @dragleave="dragOver = false" @drop="handleDrop"
                             :class="['border-2 border-dashed rounded-2xl p-8 sm:p-12 text-center transition-all duration-300 cursor-pointer',
                                      dragOver ? 'border-primary bg-primary/5 shadow-md' : 'border-border hover:border-primary/50 hover:bg-muted/30 hover:shadow-sm']"
                             @click="$refs.fileInput.click()">

                            <template v-if="!fileName">
                                <div class="w-20 h-20 mx-auto mb-5 rounded-2xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center">
                                    <svg class="w-10 h-10 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                                </div>
                                <p class="text-base font-semibold text-foreground">Klik atau seret file Excel ke sini</p>
                                <p class="text-sm text-muted-foreground mt-2">Format: .xlsx, .xls, atau .csv &middot; Maks 10MB</p>
                            </template>

                            <template v-else>
                                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-emerald-100 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <p class="text-base font-semibold text-foreground">{{ fileName }}</p>
                                <p class="text-sm text-muted-foreground mt-1">File siap diupload</p>
                                <button @click.stop="removeFile" class="mt-3 text-sm text-red-500 hover:text-red-600 font-medium transition">
                                    Hapus file
                                </button>
                            </template>
                        </div>

                        <input ref="fileInput" type="file" accept=".xlsx,.xls,.csv" class="hidden" @change="handleFile" />

                        <!-- Error -->
                        <p v-if="form.errors?.file" class="text-sm text-red-500 mt-3 flex items-center gap-1.5">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/></svg>
                            {{ form.errors.file }}
                        </p>

                        <!-- Success -->
                        <div v-if="page.props.flash?.success" class="bg-emerald-50 border border-emerald-200 border-l-4 border-l-emerald-500 text-emerald-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2 mt-3">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ page.props.flash.success }}
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end mt-6">
                            <Button @click="submit" :disabled="!form.file || form.processing"
                                    class="gap-2 px-6 py-2.5 rounded-xl font-semibold shadow-sm hover:shadow-md hover:shadow-primary/20 transition-all duration-300 active:scale-[0.97]">
                                <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                                {{ form.processing ? 'Mengimport...' : 'Import Sekarang' }}
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Format Instructions -->
                <div class="anim-fade-in-up anim-delay-1 rounded-2xl border border-border/60 bg-card shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-border/60 bg-gradient-to-r from-amber-500/5 to-transparent">
                        <h2 class="text-lg font-bold flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center"><svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg></div>
                            Format File Excel
                        </h2>
                        <p class="text-sm text-muted-foreground mt-1">Pastikan file Excel sesuai dengan format berikut</p>
                    </div>
                    <div class="p-6">
                        <!-- Table Preview -->
                        <div class="overflow-x-auto mb-6">
                            <table class="w-full text-sm border border-border/60 rounded-xl overflow-hidden">
                                <thead>
                                    <tr class="bg-gradient-to-r from-muted/40 to-muted/20 border-b border-border/60">
                                        <th class="text-left px-4 py-3 font-semibold text-xs text-muted-foreground uppercase tracking-wider">No</th>
                                        <th class="text-left px-4 py-3 font-semibold text-xs text-muted-foreground uppercase tracking-wider">Nama</th>
                                        <th class="text-left px-4 py-3 font-semibold text-xs text-muted-foreground uppercase tracking-wider">Kelas</th>
                                        <th class="text-left px-4 py-3 font-semibold text-xs text-muted-foreground uppercase tracking-wider">Bidang</th>
                                        <th class="text-left px-4 py-3 font-semibold text-xs text-muted-foreground uppercase tracking-wider">Asal Sekolah</th>
                                        <th class="text-left px-4 py-3 font-semibold text-xs text-muted-foreground uppercase tracking-wider">Password</th>
                                        <th class="text-left px-4 py-3 font-semibold text-xs text-muted-foreground uppercase tracking-wider">Level</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b border-border/30">
                                        <td class="px-4 py-3 text-muted-foreground">1</td>
                                        <td class="px-4 py-3 font-medium">Budi Santoso</td>
                                        <td class="px-4 py-3">5</td>
                                        <td class="px-4 py-3">IPA</td>
                                        <td class="px-4 py-3">SDN Menteng 01</td>
                                        <td class="px-4 py-3 text-muted-foreground">(dihapus)</td>
                                        <td class="px-4 py-3">Berbakat A</td>
                                    </tr>
                                    <tr class="border-b border-border/30 bg-muted/20">
                                        <td class="px-4 py-3 text-muted-foreground">2</td>
                                        <td class="px-4 py-3 font-medium">Siti Rahayu</td>
                                        <td class="px-4 py-3">6</td>
                                        <td class="px-4 py-3">IPA</td>
                                        <td class="px-4 py-3">SDIT Al Azhar 13</td>
                                        <td class="px-4 py-3 text-muted-foreground">(dihapus)</td>
                                        <td class="px-4 py-3">Berbakat B</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Rules -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="flex items-start gap-3 p-4 bg-muted/30 rounded-xl">
                                <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold">Password Default</p>
                                    <p class="text-xs text-muted-foreground mt-0.5">Semua user akan menggunakan password: <code class="bg-muted px-1.5 py-0.5 rounded text-primary font-mono text-xs">password</code></p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 p-4 bg-muted/30 rounded-xl">
                                <div class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold">Email Otomatis</p>
                                    <p class="text-xs text-muted-foreground mt-0.5">Email dibuat dari nama belakang: <code class="bg-muted px-1.5 py-0.5 rounded text-primary font-mono text-xs">belakang@gmail.com</code></p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 p-4 bg-muted/30 rounded-xl">
                                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold">Baris Kosong Dilewati</p>
                                    <p class="text-xs text-muted-foreground mt-0.5">Baris tanpa nama akan otomatis dilewati</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-3 p-4 bg-muted/30 rounded-xl">
                                <div class="w-8 h-8 rounded-lg bg-red-500/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                                    <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold">Email Duplikat</p>
                                    <p class="text-xs text-muted-foreground mt-0.5">Jika email sudah ada, akan ditambahkan angka: <code class="bg-muted px-1.5 py-0.5 rounded text-primary font-mono text-xs">belakang2@gmail.com</code></p>
                                </div>
                            </div>
                        </div>

                        <!-- Column Details -->
                        <div class="mt-6 p-4 bg-muted/20 rounded-xl">
                            <p class="text-sm font-semibold mb-3">Keterangan Kolom:</p>
                            <ul class="space-y-2 text-sm text-muted-foreground">
                                <li class="flex items-start gap-2">
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded bg-primary/10 text-primary text-xs font-bold flex-shrink-0 mt-0.5">1</span>
                                    <span><strong>No</strong> &mdash; Nomor urut (kolom ini dilewati, hanya sebagai referensi)</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded bg-primary/10 text-primary text-xs font-bold flex-shrink-0 mt-0.5">2</span>
                                    <span><strong>Nama</strong> &mdash; Nama lengkap user (wajib diisi)</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded bg-primary/10 text-primary text-xs font-bold flex-shrink-0 mt-0.5">3</span>
                                    <span><strong>Kelas</strong> &mdash; Kelas user (opsional)</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded bg-primary/10 text-primary text-xs font-bold flex-shrink-0 mt-0.5">4</span>
                                    <span><strong>Bidang</strong> &mdash; Bidang/keahlian (opsional)</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded bg-primary/10 text-primary text-xs font-bold flex-shrink-0 mt-0.5">5</span>
                                    <span><strong>Asal Sekolah</strong> &mdash; Nama sekolah (opsional)</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded bg-muted text-muted-foreground text-xs font-bold flex-shrink-0 mt-0.5">6</span>
                                    <span><strong>Password</strong> &mdash; Diabaikan, semua user mendapat password default</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded bg-primary/10 text-primary text-xs font-bold flex-shrink-0 mt-0.5">7</span>
                                    <span><strong>Level</strong> &mdash; Level/tingkatan user (opsional)</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Reset Data Section -->
                <div class="anim-fade-in-up anim-delay-2 rounded-2xl border border-red-200 bg-red-50/50 shadow-sm overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-sm font-semibold text-red-800">Reset Data Import</h3>
                                <p class="text-xs text-red-600/80 mt-1">Menghapus semua data user yang diimport dari Excel. Admin tidak akan terhapus.</p>
                                <button @click="showResetConfirm = true"
                                        class="mt-3 px-4 py-2 text-xs font-semibold text-red-600 bg-white border border-red-300 rounded-xl hover:bg-red-50 hover:shadow-sm transition-all duration-200 active:scale-[0.97]">
                                    Reset Semua Data
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reset Confirmation Modal -->
                <Teleport to="body">
                    <div v-if="showResetConfirm" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showResetConfirm = false"></div>
                        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 anim-fade-in-up">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/></svg>
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-foreground">Reset Data Import?</h3>
                                    <p class="text-xs text-muted-foreground">Semua user yang diimport akan dihapus permanen.</p>
                                </div>
                            </div>
                            <p class="text-sm text-muted-foreground mb-6">Tindakan ini tidak dapat dibatalkan. Data user hasil import akan hilang selamanya.</p>
                            <div class="flex justify-end gap-3">
                                <button @click="showResetConfirm = false"
                                        class="px-4 py-2 text-sm font-medium text-muted-foreground bg-muted rounded-xl hover:bg-muted/80 transition-all duration-200 active:scale-[0.97]">
                                    Batal
                                </button>
                                <button @click="confirmReset" :disabled="resetForm.processing"
                                        class="px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-xl hover:bg-red-700 hover:shadow-md transition-all duration-200 disabled:opacity-50 active:scale-[0.97]">
                                    {{ resetForm.processing ? 'Menghapus...' : 'Ya, Hapus Semua' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </Teleport>
            </div>
        </div>
    </AdminLayout>
</template>
