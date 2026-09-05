<script setup>
import { inject, ref, computed } from 'vue';
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';
import UserLayout from '@/Layouts/UserLayout.vue';
import Input from '@/Components/ui/input/Input.vue';
import Label from '@/Components/ui/label/Label.vue';
import Button from '@/Components/ui/button/Button.vue';
import Select from '@/Components/ui/select/Select.vue';
import Textarea from '@/Components/ui/textarea/Textarea.vue';
const route = inject('route');

const page = usePage();
const user = computed(() => page.props.auth?.user);

const form = useForm({
    name: user.value?.name || '',
    phone: user.value?.phone || '',
    student_class: user.value?.student_class || '',
    bidang: user.value?.bidang || '',
    level: user.value?.level || '',
    school_name: user.value?.school_name || '',
    address: user.value?.address || '',
    gender: user.value?.gender || '',
    religion: user.value?.religion || '',
    profile_photo: null,
});

const photoPreview = ref(null);
const fileInput = ref(null);
const photoDragOver = ref(false);

const userInitial = computed(() => (user.value?.name || 'U').charAt(0).toUpperCase());

const profilePhotoUrl = computed(() => {
    if (photoPreview.value) return photoPreview.value;
    if (user.value?.profile_photo) return '/storage/' + user.value.profile_photo + '?v=' + encodeURIComponent(user.value.profile_photo);
    return null;
});

function handlePhotoSelect(e) {
    const file = e.target.files[0];
    if (!file) return;
    processFile(file);
}

function processFile(file) {
    if (file.size > 2 * 1024 * 1024) {
        alert('Ukuran foto maksimal 2MB');
        return;
    }
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
        alert('Format foto harus JPG, PNG, atau WebP');
        return;
    }
    form.profile_photo = file;
    const reader = new FileReader();
    reader.onload = (e) => { photoPreview.value = e.target.result; };
    reader.readAsDataURL(file);
}

function handleDrop(e) {
    e.preventDefault();
    photoDragOver.value = false;
    const file = e.dataTransfer.files[0];
    if (file) processFile(file);
}

function removePhoto() {
    form.profile_photo = null;
    photoPreview.value = null;
    if (fileInput.value) fileInput.value.value = '';
}

function submit() {
    form.put(route('profile.update'), {
        forceFormData: true,
    });
}
</script>

<template>
    <UserLayout>
        <Head title="Profil Saya - KPM SMART" />

        <template #header-title>Profil Saya</template>
        <template #header-sub>Perbarui informasi profil dan keamanan akun Anda</template>

        <div class="space-y-6">
            <!-- Profile Photo Section -->
            <div class="anim-fade-in-up rounded-2xl border bg-card shadow-card overflow-hidden hover:shadow-lg transition-shadow duration-300">
                <div class="p-6 border-b bg-gradient-to-r from-primary/5 to-transparent">
                    <h2 class="text-lg font-semibold flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        Foto Profil
                    </h2>
                    <p class="text-sm text-muted-foreground mt-1">Upload foto profil Anda (JPG, PNG, WebP, maks 2MB)</p>
                </div>
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row items-center gap-6">
                        <!-- Photo Preview -->
                        <div class="relative group">
                            <div v-if="profilePhotoUrl"
                                 class="w-28 h-28 rounded-2xl overflow-hidden ring-4 ring-primary/10 shadow-card">
                                <img :src="profilePhotoUrl" alt="Foto Profil" class="w-full h-full object-cover" />
                            </div>
                            <div v-else
                                 class="w-28 h-28 rounded-2xl bg-gradient-to-br from-primary/20 to-primary/5 flex items-center justify-center ring-4 ring-primary/10 shadow-card">
                                <span class="text-4xl font-bold text-primary">{{ userInitial }}</span>
                            </div>
                            <div v-if="photoPreview" @click="removePhoto"
                                 class="absolute -top-2 -right-2 w-7 h-7 bg-red-500 text-white rounded-full flex items-center justify-center cursor-pointer shadow-lg opacity-0 group-hover:opacity-100 transition-opacity">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </div>
                        </div>

                        <!-- Upload Area -->
                        <div class="flex-1 w-full">
                            <div @dragover.prevent="photoDragOver = true" @dragleave="photoDragOver = false" @drop="handleDrop"
                                 :class="['border-2 border-dashed rounded-2xl p-6 text-center transition-all cursor-pointer min-h-[120px] flex flex-col items-center justify-center',
                                          photoDragOver ? 'border-primary bg-primary/5' : 'border-border hover:border-primary/50 hover:bg-muted/30']"
                                 @click="$refs.fileInput.click()">
                                <svg class="w-8 h-8 mx-auto text-muted-foreground mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                                <p class="text-sm font-medium text-foreground">Klik atau seret foto ke sini</p>
                                <p class="text-xs text-muted-foreground mt-1">JPG, PNG, WebP · Maks 2MB</p>
                            </div>
                            <input ref="fileInput" type="file" accept=".jpg,.jpeg,.png,.webp" class="hidden" @change="handlePhotoSelect" />
                            <p v-if="form.errors?.profile_photo" class="text-xs text-red-500 mt-2">{{ form.errors.profile_photo }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Information -->
            <div class="anim-fade-in-up anim-delay-1 rounded-2xl border bg-card shadow-card overflow-hidden hover:shadow-lg transition-shadow duration-300">
                <div class="p-6 border-b bg-gradient-to-r from-primary/5 to-transparent">
                    <h2 class="text-lg font-semibold flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        Informasi Profil
                    </h2>
                    <p class="text-sm text-muted-foreground mt-1">Perbarui data diri Anda</p>
                </div>
                <form @submit.prevent="submit" class="p-6 space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <Label for="name">Nama Lengkap <span class="text-red-500">*</span></Label>
                            <Input id="name" v-model="form.name" required placeholder="Masukkan nama lengkap" />
                        </div>
                        <div class="space-y-2">
                            <Label for="phone">Nomor Telepon <span class="text-red-500">*</span></Label>
                            <Input id="phone" v-model="form.phone" required placeholder="Masukkan nomor telepon" />
                        </div>
                        <div class="space-y-2">
                            <Label for="student_class">Kelas <span class="text-muted-foreground text-xs font-normal">(ditentukan admin)</span></Label>
                            <Input id="student_class" :value="form.student_class || '-'" disabled class="opacity-70 cursor-not-allowed bg-muted/30" />
                        </div>
                        <div class="space-y-2">
                            <Label for="school_name">Nama Sekolah <span class="text-red-500">*</span></Label>
                            <Input id="school_name" v-model="form.school_name" required placeholder="Masukkan nama sekolah" />
                        </div>
                        <div class="space-y-2">
                            <Label for="bidang">Bidang <span class="text-muted-foreground text-xs font-normal">(ditentukan admin)</span></Label>
                            <Input id="bidang" :value="form.bidang || '-'" disabled class="opacity-70 cursor-not-allowed bg-muted/30" />
                        </div>
                        <div class="space-y-2">
                            <Label for="level">Level <span class="text-muted-foreground text-xs font-normal">(ditentukan admin)</span></Label>
                            <Input id="level" :value="form.level || '-'" disabled class="opacity-70 cursor-not-allowed bg-muted/30" />
                        </div>
                        <div class="space-y-2">
                            <Label for="gender">Jenis Kelamin</Label>
                            <Select id="gender" v-model="form.gender">
                                <option value="">Pilih</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </Select>
                        </div>
                        <div class="space-y-2">
                            <Label for="religion">Agama</Label>
                            <Select id="religion" v-model="form.religion">
                                <option value="">Pilih</option>
                                <option value="Islam">Islam</option>
                                <option value="Kristen">Kristen</option>
                                <option value="Katolik">Katolik</option>
                                <option value="Hindu">Hindu</option>
                                <option value="Buddha">Buddha</option>
                                <option value="Konghucu">Konghucu</option>
                            </Select>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label for="address">Alamat</Label>
                        <Textarea id="address" v-model="form.address" :rows="3" placeholder="Masukkan alamat lengkap" />
                    </div>

                    <div v-if="form.errors && Object.keys(form.errors).length > 0" class="bg-red-50 border border-red-200 border-l-4 border-l-red-500 text-red-700 px-4 py-3 rounded-xl text-sm flex items-start gap-2">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/></svg>
                        <ul class="space-y-0.5">
                            <li v-for="(error, key) in form.errors" :key="key">{{ error }}</li>
                        </ul>
                    </div>

                    <div v-if="page.props.flash?.success" class="bg-emerald-50 border border-emerald-200 border-l-4 border-l-emerald-500 text-emerald-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ page.props.flash.success }}
                    </div>

                    <div class="flex flex-col sm:flex-row justify-end gap-3 pt-2">
                        <Button type="submit" :disabled="form.processing" class="sm:w-auto w-full hover:shadow-md active:scale-[0.98] transition-all duration-200 min-h-12">
                            <svg v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                        </Button>
                    </div>
                </form>
            </div>

            <!-- Change Password -->
            <div class="anim-fade-in-up anim-delay-2 rounded-2xl border bg-card shadow-card overflow-hidden hover:shadow-lg transition-shadow duration-300">
                <div class="p-6 border-b bg-gradient-to-r from-amber-500/5 to-transparent">
                    <h2 class="text-lg font-semibold flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                        Keamanan Akun
                    </h2>
                    <p class="text-sm text-muted-foreground mt-1">Ubah password untuk menjaga keamanan akun Anda</p>
                </div>
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 p-4 bg-muted/30 rounded-2xl">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center text-amber-600">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <div>
                                <p class="font-semibold text-sm">Ubah Password</p>
                                <p class="text-xs text-muted-foreground">Terakhir diubah: tidak diketahui</p>
                            </div>
                        </div>
                        <Link :href="route('profile.change-password')"
                              class="inline-flex items-center gap-2 bg-amber-500 text-white px-5 py-2.5 rounded-xl text-sm font-semibold hover:bg-amber-600 hover:shadow-md active:scale-95 transition-all duration-200 shadow-sm w-full sm:w-auto justify-center min-h-10">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                            Ubah Password
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </UserLayout>
</template>
