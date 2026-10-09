<script setup>
import { inject, ref } from 'vue';
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';

const route = inject('route');
const page = usePage();
const form = useForm({ file: null });
const resetForm = useForm({});
const showResetConfirm = ref(false);
const fileName = ref('');
const fileSize = ref('');
const dragOver = ref(false);
const fileInput = ref(null);

function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

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
    fileSize.value = formatBytes(file.size);
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
    fileSize.value = '';
    if (fileInput.value) fileInput.value.value = '';
}

function submit() {
    if (!form.file) return;
    form.post(route('admin.users.import-excel.process'), {
        forceFormData: true,
        onSuccess: () => {
            form.file = null;
            fileName.value = '';
            fileSize.value = '';
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
        <Head title="Import User Excel - Admin" />

        <template #header-title>Import User dari Excel</template>
        <template #header-sub>Import data pengguna secara massal dengan format spreadsheet (.xlsx, .xls, .csv)</template>

        <div class="w-full space-y-6">
            <!-- Back Link -->
            <Link :href="route('admin.users.index')"
                  class="inline-flex items-center gap-2 text-sm font-semibold text-muted-foreground hover:text-primary transition-colors anim-fade-in-up">
                <Icon icon="mdi:arrow-left" class="w-4 h-4" />
                Kembali ke Daftar User
            </Link>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Upload Section (Left 2 Cols) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Dropzone Card -->
                    <div class="bg-card rounded-2xl border border-border/60 shadow-sm overflow-hidden anim-fade-in-up">
                        <div class="p-5 sm:p-6 border-b border-border/60 bg-gradient-to-r from-primary/5 to-transparent">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center text-primary flex-shrink-0">
                                    <Icon icon="mdi:file-excel-box-outline" class="w-5 h-5" />
                                </div>
                                <div>
                                    <h2 class="text-base font-bold text-foreground">Upload File Spreadsheet</h2>
                                    <p class="text-xs text-muted-foreground mt-0.5">Pilih atau letakkan file spreadsheet yang berisi data pengguna</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-5 sm:p-6 space-y-5">
                            <!-- Drop Zone -->
                            <div @dragover.prevent="dragOver = true" @dragleave="dragOver = false" @drop="handleDrop"
                                 :class="[
                                     'relative border-2 border-dashed rounded-2xl p-8 sm:p-10 text-center transition-all duration-300 cursor-pointer flex flex-col items-center justify-center',
                                     dragOver ? 'border-primary bg-primary/5 shadow-inner scale-[0.99]' : 'border-border/80 hover:border-primary/50 hover:bg-muted/30 hover:shadow-sm'
                                 ]"
                                 @click="$refs.fileInput.click()">

                                <template v-if="!fileName">
                                    <div class="w-16 h-16 sm:w-20 sm:h-20 mb-4 rounded-2xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center text-primary shadow-sm group-hover:scale-105 transition-transform">
                                        <Icon icon="mdi:cloud-upload-outline" class="w-8 h-8 sm:w-10 sm:h-10" />
                                    </div>
                                    <p class="text-sm sm:text-base font-bold text-foreground">Klik untuk upload atau seret file ke sini</p>
                                    <p class="text-xs text-muted-foreground mt-1.5">Format yang didukung: <span class="font-medium text-foreground">.xlsx, .xls, .csv</span></p>
                                    <div class="mt-4 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-muted text-[11px] font-medium text-muted-foreground">
                                        <Icon icon="mdi:information-outline" class="w-3.5 h-3.5" /> Ukuran maksimal 10MB
                                    </div>
                                </template>

                                <template v-else>
                                    <div class="w-16 h-16 mb-4 rounded-2xl bg-emerald-500/15 flex items-center justify-center text-emerald-600 shadow-sm">
                                        <Icon icon="mdi:file-check-outline" class="w-8 h-8" />
                                    </div>
                                    <p class="text-sm sm:text-base font-bold text-foreground truncate max-w-xs sm:max-w-md">{{ fileName }}</p>
                                    <p class="text-xs text-muted-foreground mt-1">{{ fileSize }} &bull; Siap diimport</p>
                                    <button @click.stop="removeFile" type="button"
                                            class="mt-4 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-destructive/10 text-destructive text-xs font-semibold hover:bg-destructive/20 transition-colors">
                                        <Icon icon="mdi:trash-can-outline" class="w-3.5 h-3.5" /> Ganti / Hapus File
                                    </button>
                                </template>
                            </div>

                            <input ref="fileInput" type="file" accept=".xlsx,.xls,.csv" class="hidden" @change="handleFile" />

                            <!-- Error Message -->
                            <div v-if="form.errors?.file" class="bg-destructive/10 border border-destructive/20 text-destructive px-4 py-3 rounded-xl text-xs font-medium flex items-center gap-2">
                                <Icon icon="mdi:alert-circle-outline" class="w-4 h-4 flex-shrink-0" />
                                <span>{{ form.errors.file }}</span>
                            </div>

                            <!-- Flash Success Message -->
                            <div v-if="page.props.flash?.success" class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 dark:text-emerald-400 px-4 py-3 rounded-xl text-xs font-medium flex items-center gap-2">
                                <Icon icon="mdi:check-circle-outline" class="w-4 h-4 flex-shrink-0" />
                                <span>{{ page.props.flash.success }}</span>
                            </div>

                            <!-- Submit Action -->
                            <div class="flex items-center justify-end pt-2">
                                <Button @click="submit" :disabled="!form.file || form.processing"
                                        class="gap-2 px-6 py-2.5 rounded-xl font-bold shadow-sm hover:shadow-md hover:shadow-primary/20 transition-all duration-200 active:scale-[0.98]">
                                    <Icon v-if="form.processing" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                                    <Icon v-else icon="mdi:file-import-outline" class="w-4 h-4" />
                                    {{ form.processing ? 'Sedang Memproses...' : 'Mulai Import Data' }}
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Format Preview & Guide -->
                    <div class="bg-card rounded-2xl border border-border/60 shadow-sm overflow-hidden anim-fade-in-up anim-delay-1">
                        <div class="p-5 sm:p-6 border-b border-border/60 bg-gradient-to-r from-amber-500/5 to-transparent flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-600 flex-shrink-0">
                                    <Icon icon="mdi:table-clock" class="w-5 h-5" />
                                </div>
                                <div>
                                    <h2 class="text-base font-bold text-foreground">Format Kolom Excel</h2>
                                    <p class="text-xs text-muted-foreground mt-0.5">Pastikan urutan dan struktur kolom sesuai tabel berikut</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-5 sm:p-6 space-y-6">
                            <!-- Table Preview -->
                            <div class="overflow-x-auto border border-border/60 rounded-xl">
                                <table class="w-full text-xs">
                                    <thead>
                                        <tr class="bg-muted/40 border-b border-border/60">
                                            <th class="text-left px-3.5 py-2.5 font-bold text-muted-foreground uppercase">No</th>
                                            <th class="text-left px-3.5 py-2.5 font-bold text-muted-foreground uppercase">Nama</th>
                                            <th class="text-left px-3.5 py-2.5 font-bold text-muted-foreground uppercase">Kelas</th>
                                            <th class="text-left px-3.5 py-2.5 font-bold text-muted-foreground uppercase">Bidang</th>
                                            <th class="text-left px-3.5 py-2.5 font-bold text-muted-foreground uppercase">Asal Sekolah</th>
                                            <th class="text-left px-3.5 py-2.5 font-bold text-muted-foreground uppercase">Password</th>
                                            <th class="text-left px-3.5 py-2.5 font-bold text-muted-foreground uppercase">Level</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-border/40">
                                        <tr class="hover:bg-muted/20">
                                            <td class="px-3.5 py-2.5 text-muted-foreground font-mono">1</td>
                                            <td class="px-3.5 py-2.5 font-semibold text-foreground">Budi Santoso</td>
                                            <td class="px-3.5 py-2.5">5</td>
                                            <td class="px-3.5 py-2.5"><span class="px-2 py-0.5 rounded-md bg-primary/10 text-primary font-medium">IPA</span></td>
                                            <td class="px-3.5 py-2.5 text-muted-foreground">SDN Menteng 01</td>
                                            <td class="px-3.5 py-2.5 text-muted-foreground/60 italic">(diabaikan)</td>
                                            <td class="px-3.5 py-2.5"><span class="px-2 py-0.5 rounded-md bg-blue-500/10 text-blue-600 font-semibold">A1</span></td>
                                        </tr>
                                        <tr class="hover:bg-muted/20 bg-muted/10">
                                            <td class="px-3.5 py-2.5 text-muted-foreground font-mono">2</td>
                                            <td class="px-3.5 py-2.5 font-semibold text-foreground">Siti Rahayu</td>
                                            <td class="px-3.5 py-2.5">6</td>
                                            <td class="px-3.5 py-2.5"><span class="px-2 py-0.5 rounded-md bg-primary/10 text-primary font-medium">Matematika</span></td>
                                            <td class="px-3.5 py-2.5 text-muted-foreground">SDIT Al Azhar 13</td>
                                            <td class="px-3.5 py-2.5 text-muted-foreground/60 italic">(diabaikan)</td>
                                            <td class="px-3.5 py-2.5"><span class="px-2 py-0.5 rounded-md bg-blue-500/10 text-blue-600 font-semibold">B2</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Rules Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                                <div class="p-3.5 rounded-xl bg-muted/40 border border-border/40 flex items-start gap-3">
                                    <div class="w-7 h-7 rounded-lg bg-primary/10 flex items-center justify-center text-primary flex-shrink-0 mt-0.5">
                                        <Icon icon="mdi:lock-outline" class="w-4 h-4" />
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-foreground">Password Default</p>
                                        <p class="text-[11px] text-muted-foreground mt-0.5">Setiap akun baru dibuatkan password: <code class="px-1.5 py-0.5 bg-background rounded font-mono text-primary font-semibold">password</code></p>
                                    </div>
                                </div>

                                <div class="p-3.5 rounded-xl bg-muted/40 border border-border/40 flex items-start gap-3">
                                    <div class="w-7 h-7 rounded-lg bg-amber-500/10 flex items-center justify-center text-amber-600 flex-shrink-0 mt-0.5">
                                        <Icon icon="mdi:email-outline" class="w-4 h-4" />
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-foreground">Format Email Otomatis</p>
                                        <p class="text-[11px] text-muted-foreground mt-0.5">Email dibuat dari nama belakang: <code class="px-1.5 py-0.5 bg-background rounded font-mono text-amber-600 font-semibold">nama@gmail.com</code></p>
                                    </div>
                                </div>

                                <div class="p-3.5 rounded-xl bg-muted/40 border border-border/40 flex items-start gap-3">
                                    <div class="w-7 h-7 rounded-lg bg-emerald-500/10 flex items-center justify-center text-emerald-600 flex-shrink-0 mt-0.5">
                                        <Icon icon="mdi:filter-check-outline" class="w-4 h-4" />
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-foreground">Baris Kosong Otomatis Dilewati</p>
                                        <p class="text-[11px] text-muted-foreground mt-0.5">Baris spreadsheet tanpa kolom nama akan otomatis diabaikan.</p>
                                    </div>
                                </div>

                                <div class="p-3.5 rounded-xl bg-muted/40 border border-border/40 flex items-start gap-3">
                                    <div class="w-7 h-7 rounded-lg bg-violet-500/10 flex items-center justify-center text-violet-600 flex-shrink-0 mt-0.5">
                                        <Icon icon="mdi:account-multiple-plus-outline" class="w-4 h-4" />
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-foreground">Penanganan Email Duplikat</p>
                                        <p class="text-[11px] text-muted-foreground mt-0.5">Jika email sudah dipakai, otomatis ditambahkan angka di belakang.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Instructions & Reset Tool -->
                <div class="space-y-6">
                    <!-- Column Explanations Card -->
                    <div class="bg-card rounded-2xl border border-border/60 shadow-sm p-5 sm:p-6 space-y-4 anim-fade-in-up anim-delay-2">
                        <div class="flex items-center gap-2.5 pb-3 border-b border-border/60">
                            <Icon icon="mdi:format-list-numbered" class="w-5 h-5 text-primary" />
                            <h3 class="text-sm font-bold text-foreground">Keterangan Kolom</h3>
                        </div>

                        <div class="space-y-3 text-xs">
                            <div class="flex items-start gap-2.5">
                                <span class="w-5 h-5 rounded-md bg-muted text-foreground font-bold flex items-center justify-center flex-shrink-0 mt-0.5 text-[10px]">1</span>
                                <div>
                                    <p class="font-bold text-foreground">No</p>
                                    <p class="text-muted-foreground text-[11px]">Nomor urut (dilewati sistem).</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <span class="w-5 h-5 rounded-md bg-primary/10 text-primary font-bold flex items-center justify-center flex-shrink-0 mt-0.5 text-[10px]">2</span>
                                <div>
                                    <p class="font-bold text-foreground">Nama <span class="text-destructive font-normal">*Wajib</span></p>
                                    <p class="text-muted-foreground text-[11px]">Nama lengkap pengguna.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <span class="w-5 h-5 rounded-md bg-muted text-foreground font-bold flex items-center justify-center flex-shrink-0 mt-0.5 text-[10px]">3</span>
                                <div>
                                    <p class="font-bold text-foreground">Kelas</p>
                                    <p class="text-muted-foreground text-[11px]">Tingkat kelas siswa (contoh: 4, 5, 6).</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <span class="w-5 h-5 rounded-md bg-muted text-foreground font-bold flex items-center justify-center flex-shrink-0 mt-0.5 text-[10px]">4</span>
                                <div>
                                    <p class="font-bold text-foreground">Bidang</p>
                                    <p class="text-muted-foreground text-[11px]">Bidang studi (contoh: IPA, Matematika).</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <span class="w-5 h-5 rounded-md bg-muted text-foreground font-bold flex items-center justify-center flex-shrink-0 mt-0.5 text-[10px]">5</span>
                                <div>
                                    <p class="font-bold text-foreground">Asal Sekolah</p>
                                    <p class="text-muted-foreground text-[11px]">Nama institusi/sekolah asal siswa.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <span class="w-5 h-5 rounded-md bg-muted text-foreground font-bold flex items-center justify-center flex-shrink-0 mt-0.5 text-[10px]">6</span>
                                <div>
                                    <p class="font-bold text-foreground">Password</p>
                                    <p class="text-muted-foreground text-[11px]">Dilewati, semua user otomatis 'password'.</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5">
                                <span class="w-5 h-5 rounded-md bg-muted text-foreground font-bold flex items-center justify-center flex-shrink-0 mt-0.5 text-[10px]">7</span>
                                <div>
                                    <p class="font-bold text-foreground">Level</p>
                                    <p class="text-muted-foreground text-[11px]">Tingkat: A1, A2, B1, B2, C1, C2.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reset Data Card -->
                    <div class="bg-card rounded-2xl border border-destructive/30 shadow-sm p-5 sm:p-6 space-y-3 anim-fade-in-up anim-delay-3 bg-gradient-to-br from-destructive/5 to-transparent">
                        <div class="flex items-center gap-2.5 text-destructive">
                            <Icon icon="mdi:alert-octagon-outline" class="w-5 h-5" />
                            <h3 class="text-sm font-bold">Zona Bahaya</h3>
                        </div>
                        <p class="text-xs text-muted-foreground leading-relaxed">
                            Hapus seluruh akun pengguna yang diimport melalui file Excel. Akun administrator tidak akan terhapus.
                        </p>
                        <Button @click="showResetConfirm = true" variant="destructive"
                                class="w-full gap-2 rounded-xl text-xs font-bold py-2.5">
                            <Icon icon="mdi:delete-sweep-outline" class="w-4 h-4" />
                            Reset Data User Import
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reset Confirmation Modal -->
        <Teleport to="body">
            <div v-if="showResetConfirm" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showResetConfirm = false"></div>
                <div class="relative bg-card border border-border/80 rounded-2xl shadow-2xl w-full max-w-md p-6 anim-fade-in-up space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-destructive/10 text-destructive flex items-center justify-center flex-shrink-0">
                            <Icon icon="mdi:alert-outline" class="w-6 h-6" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-foreground">Reset Semua Data Import?</h3>
                            <p class="text-xs text-muted-foreground">Tindakan ini permanen dan tidak dapat dibatalkan.</p>
                        </div>
                    </div>
                    <p class="text-xs text-muted-foreground leading-relaxed bg-muted/40 p-3.5 rounded-xl border border-border/40">
                        Seluruh user hasil import Excel beserta data pengerjaan sesi latihan mereka akan dihapus dari database.
                    </p>
                    <div class="flex justify-end gap-2.5 pt-2">
                        <Button variant="ghost" @click="showResetConfirm = false" class="rounded-xl text-xs">
                            Batal
                        </Button>
                        <Button variant="destructive" @click="confirmReset" :disabled="resetForm.processing" class="rounded-xl text-xs font-bold gap-1.5">
                            <Icon v-if="resetForm.processing" icon="mdi:loading" class="w-3.5 h-3.5 animate-spin" />
                            <Icon v-else icon="mdi:delete-outline" class="w-3.5 h-3.5" />
                            {{ resetForm.processing ? 'Menghapus...' : 'Ya, Hapus Semua' }}
                        </Button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AdminLayout>
</template>
