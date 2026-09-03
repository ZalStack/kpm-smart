<script setup>
import { inject, ref } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Input from '@/Components/ui/input/Input.vue';
import Label from '@/Components/ui/label/Label.vue';
import Textarea from '@/Components/ui/textarea/Textarea.vue';
import Select from '@/Components/ui/select/Select.vue';
const route = inject('route');

const props = defineProps({
    user: { type: Object, required: true },
});

const form = useForm({
    name: props.user.name || '',
    email: props.user.email || '',
    password: '',
    password_confirmation: '',
    student_class: props.user.student_class || '',
    bidang: props.user.bidang || '',
    level: props.user.level || '',
    school_name: props.user.school_name || '',
    phone: props.user.phone || '',
    gender: props.user.gender || '',
    religion: props.user.religion || '',
    address: props.user.address || '',
    profile_photo: null,
});

const photoPreview = ref(props.user.profile_photo ? '/storage/' + props.user.profile_photo : null);
const dragOver = ref(false);

function handlePhoto(e) {
    const file = e.target.files[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) { alert('Ukuran file maksimal 2MB'); return; }
    form.profile_photo = file;
    const reader = new FileReader();
    reader.onload = (ev) => { photoPreview.value = ev.target.result; };
    reader.readAsDataURL(file);
}

function handleDrop(e) {
    dragOver.value = false;
    const file = e.dataTransfer.files[0];
    if (!file || !file.type.startsWith('image/')) return;
    if (file.size > 2 * 1024 * 1024) { alert('Ukuran file maksimal 2MB'); return; }
    form.profile_photo = file;
    const reader = new FileReader();
    reader.onload = (ev) => { photoPreview.value = ev.target.result; };
    reader.readAsDataURL(file);
}

function submit() {
    form.put(route('admin.users.update', props.user.id), { forceFormData: true });
}
</script>

<template>
    <AdminLayout>
        <Head :title="'Edit ' + user.name + ' - Admin'" />

        <template #header-title>Edit User</template>
        <template #header-sub>{{ user.name }}</template>

        <Link :href="route('admin.users.show', user.id)" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition mb-6 group">
            <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            Kembali ke Detail User
        </Link>

        <div>
            <form @submit.prevent="submit" enctype="multipart/form-data">
                <!-- Photo Upload Section -->
                <div class="bg-card rounded-2xl border border-border/60 shadow-sm overflow-hidden mb-6 anim-fade-in-up">
                    <div class="p-5 sm:p-6">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
                            <!-- Photo Preview -->
                            <div
                                class="relative group"
                                @dragover.prevent="dragOver = true"
                                @dragleave.prevent="dragOver = false"
                                @drop.prevent="handleDrop"
                            >
                                <label class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl overflow-hidden bg-gradient-to-br from-muted to-muted/50 flex items-center justify-center flex-shrink-0 ring-4 ring-background shadow-inner cursor-pointer transition-all duration-300 hover:shadow-lg"
                                    :class="{ 'ring-primary/30 shadow-primary/10': dragOver }">
                                    <img v-if="photoPreview" :src="photoPreview" class="w-full h-full object-cover" />
                                    <div v-else class="flex flex-col items-center gap-1.5">
                                        <svg class="w-8 h-8 text-muted-foreground/50 group-hover:text-primary/60 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                                        </svg>
                                        <span class="text-[10px] text-muted-foreground/60 font-medium">Upload</span>
                                    </div>
                                    <!-- Hover overlay -->
                                    <div class="absolute inset-0 bg-primary/5 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center rounded-2xl">
                                        <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" /></svg>
                                        </div>
                                    </div>
                                </label>
                                <input type="file" accept="image/*" @change="handlePhoto" class="sr-only" />
                            </div>
                            <div class="flex-1">
                                <Label class="text-sm font-bold">Foto Profil</Label>
                                <p class="text-xs text-muted-foreground mt-1 leading-relaxed">Ganti foto profil pengguna. Kosongkan jika tidak ingin diubah.</p>
                                <div class="flex items-center gap-2 mt-2">
                                    <span class="inline-flex items-center gap-1 text-[11px] text-muted-foreground/60 bg-muted/50 px-2 py-0.5 rounded-md">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                                        Maks 2MB
                                    </span>
                                    <span class="inline-flex items-center gap-1 text-[11px] text-amber-600 bg-amber-50 px-2 py-0.5 rounded-md">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                                        Kosongkan jika tidak diubah
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Akun Section -->
                <div class="bg-card rounded-2xl border border-border/60 shadow-sm overflow-hidden mb-6 anim-fade-in-up anim-delay-1">
                    <div class="p-5 sm:p-6 border-b border-border/40">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center">
                                <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold">Info Akun</h2>
                                <p class="text-xs text-muted-foreground mt-0.5">Informasi login dan identitas pengguna</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-5 sm:p-6 space-y-4">
                        <div class="space-y-2">
                            <Label class="text-sm font-semibold">Nama Lengkap <span class="text-destructive">*</span></Label>
                            <Input v-model="form.name" required class="h-11 rounded-xl" />
                            <p v-if="form.errors.name" class="text-xs text-destructive flex items-center gap-1.5 mt-1">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                                {{ form.errors.name }}
                            </p>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-sm font-semibold">Email <span class="text-destructive">*</span></Label>
                            <Input v-model="form.email" type="email" required class="h-11 rounded-xl" />
                            <p v-if="form.errors.email" class="text-xs text-destructive flex items-center gap-1.5 mt-1">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                                {{ form.errors.email }}
                            </p>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label class="text-sm font-semibold">Password Baru</Label>
                                <Input v-model="form.password" type="password" placeholder="Kosongkan jika tidak diubah" class="h-11 rounded-xl" />
                            </div>
                            <div class="space-y-2">
                                <Label class="text-sm font-semibold">Konfirmasi Password</Label>
                                <Input v-model="form.password_confirmation" type="password" placeholder="Ulangi password baru" class="h-11 rounded-xl" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Siswa Section -->
                <div class="bg-card rounded-2xl border border-border/60 shadow-sm overflow-hidden mb-6 anim-fade-in-up anim-delay-2">
                    <div class="p-5 sm:p-6 border-b border-border/40">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500/15 to-amber-500/5 flex items-center justify-center">
                                <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold">Data Siswa</h2>
                                <p class="text-xs text-muted-foreground mt-0.5">Informasi akademis dan pendidikan</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-5 sm:p-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label class="text-sm font-semibold">Kelas</Label>
                                <Input v-model="form.student_class" class="h-11 rounded-xl" />
                            </div>
                            <div class="space-y-2">
                                <Label class="text-sm font-semibold">Bidang</Label>
                                <Input v-model="form.bidang" class="h-11 rounded-xl" />
                            </div>
                            <div class="space-y-2">
                                <Label class="text-sm font-semibold">Level</Label>
                                <Input v-model="form.level" class="h-11 rounded-xl" />
                            </div>
                            <div class="space-y-2">
                                <Label class="text-sm font-semibold">Sekolah</Label>
                                <Input v-model="form.school_name" class="h-11 rounded-xl" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Kontak Section -->
                <div class="bg-card rounded-2xl border border-border/60 shadow-sm overflow-hidden mb-6 anim-fade-in-up anim-delay-3">
                    <div class="p-5 sm:p-6 border-b border-border/40">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-fern/15 to-fern/5 flex items-center justify-center">
                                <svg class="w-5 h-5 text-fern" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-base font-bold">Info Kontak</h2>
                                <p class="text-xs text-muted-foreground mt-0.5">Informasi kontak dan data pribadi</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-5 sm:p-6 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label class="text-sm font-semibold">No. HP/WA</Label>
                                <Input v-model="form.phone" class="h-11 rounded-xl" />
                            </div>
                            <div class="space-y-2">
                                <Label class="text-sm font-semibold">Jenis Kelamin</Label>
                                <Select v-model="form.gender" class="h-11 rounded-xl">
                                    <option value="">Pilih Jenis Kelamin</option>
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </Select>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-sm font-semibold">Agama</Label>
                            <Select v-model="form.religion" class="h-11 rounded-xl">
                                <option value="">Pilih Agama</option>
                                <option value="Islam">Islam</option>
                                <option value="Kristen">Kristen</option>
                                <option value="Katolik">Katolik</option>
                                <option value="Hindu">Hindu</option>
                                <option value="Buddha">Buddha</option>
                                <option value="Konghucu">Konghucu</option>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label class="text-sm font-semibold">Alamat</Label>
                            <Textarea v-model="form.address" :rows="2" class="resize-none rounded-xl" />
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-card rounded-2xl border border-border/60 shadow-sm p-5 sm:p-6 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-3 anim-fade-in-up anim-delay-4">
                    <Link :href="route('admin.users.show', user.id)" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl border bg-background text-sm font-semibold hover:bg-accent transition-all duration-200 active:scale-[0.98]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        Batal
                    </Link>
                    <Button type="submit" :disabled="form.processing" class="gap-2 h-11 px-8 rounded-xl font-semibold shadow-sm hover:shadow-lg hover:shadow-primary/20 transition-all duration-300 active:scale-[0.97]">
                        <svg v-if="!form.processing" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                        <div v-else class="spinner"></div>
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </Button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
