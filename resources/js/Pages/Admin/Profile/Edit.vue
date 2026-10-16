<script setup>
import { inject, ref, computed } from 'vue';
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Input from '@/Components/ui/input/Input.vue';
import Label from '@/Components/ui/label/Label.vue';
import Button from '@/Components/ui/button/Button.vue';

const route = inject('route');
const page = usePage();
const user = page.props.auth?.user;

const form = useForm({
    name: user?.name || '',
    email: user?.email || '',
    phone: user?.phone || '',
    profile_photo: null,
});

const photoPreview = ref(null);
const fileInput = ref(null);
const photoDragOver = ref(false);

const userInitial = computed(() => (user?.name || 'A').charAt(0).toUpperCase());

const profilePhotoUrl = computed(() => {
    if (photoPreview.value) return photoPreview.value;
    if (!user?.profile_photo) return null;
    if (user.profile_photo.startsWith('http://') || user.profile_photo.startsWith('https://') || user.profile_photo.startsWith('//')) {
        return user.profile_photo;
    }
    return '/storage/' + user.profile_photo;
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
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(route('admin.profile.update'), {
        forceFormData: true,
    });
}
</script>

<template>
    <AdminLayout>
        <Head title="Profil Admin - KPM SMART" />

        <template #header-title>Profil Admin</template>
        <template #header-sub>Kelola informasi pribadi dan keamanan akun Anda</template>

        <div class="w-full space-y-6">
            <!-- Profile Overview Card -->
            <div class="bg-card rounded-2xl border border-border/60 shadow-sm overflow-hidden anim-fade-in-up">
                <!-- Avatar & Info Banner -->
                <div class="bg-gradient-to-r from-primary/10 via-primary/5 to-transparent px-6 sm:px-8 py-8 flex flex-col sm:flex-row items-center sm:items-end gap-5">
                    <div class="relative group">
                        <div v-if="profilePhotoUrl"
                             class="w-24 h-24 rounded-2xl overflow-hidden ring-4 ring-white shadow-lg">
                            <img :src="profilePhotoUrl" alt="Foto Profil" class="w-full h-full object-cover" />
                        </div>
                        <div v-else
                             class="w-24 h-24 rounded-2xl bg-gradient-to-br from-primary/20 to-primary/10 flex items-center justify-center ring-4 ring-white shadow-lg">
                            <span class="text-3xl font-black text-primary">{{ userInitial }}</span>
                        </div>
                        <!-- Remove Photo Overlay -->
                        <div v-if="photoPreview" @click="removePhoto"
                             class="absolute inset-0 rounded-2xl bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity cursor-pointer">
                            <Icon icon="mdi:trash-can-outline" class="w-6 h-6 text-white" />
                        </div>
                    </div>

                    <div class="text-center sm:text-left pb-1">
                        <h2 class="text-xl font-black text-foreground">{{ user?.name || 'Admin' }}</h2>
                        <p class="text-sm text-muted-foreground">{{ user?.email }}</p>
                        <span class="inline-flex items-center gap-1.5 mt-2 px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-bold border border-primary/20">
                            <Icon icon="mdi:shield-account-outline" class="w-3.5 h-3.5" /> Administrator
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                <!-- Left: Upload Photo Card -->
                <div class="lg:col-span-2 anim-fade-in-up anim-delay-1">
                    <div class="bg-card rounded-2xl border border-border/60 shadow-sm overflow-hidden h-full">
                        <div class="p-5 border-b border-border/60 bg-gradient-to-r from-primary/5 to-transparent flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                                <Icon icon="mdi:camera-outline" class="w-4 h-4" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-foreground">Foto Profil</h3>
                                <p class="text-xs text-muted-foreground">JPG, PNG, WebP · Maks 2MB</p>
                            </div>
                        </div>

                        <div class="p-5">
                            <div @dragover.prevent="photoDragOver = true" @dragleave="photoDragOver = false" @drop="handleDrop"
                                 :class="[
                                     'border-2 border-dashed rounded-2xl p-6 text-center transition-all cursor-pointer',
                                     photoDragOver ? 'border-primary bg-primary/5 scale-[0.99]' : 'border-border/80 hover:border-primary/50 hover:bg-muted/30'
                                 ]"
                                 @click="$refs.fileInput.click()">
                                <Icon icon="mdi:cloud-upload-outline" class="w-8 h-8 mx-auto text-muted-foreground/50 mb-2" />
                                <p class="text-xs font-semibold text-foreground">Klik atau seret foto ke sini</p>
                                <p class="text-[11px] text-muted-foreground mt-1">Ukuran maks 2MB</p>
                            </div>
                            <input ref="fileInput" type="file" accept=".jpg,.jpeg,.png,.webp" class="hidden" @change="handlePhotoSelect" />

                            <p v-if="form.errors?.profile_photo" class="text-xs text-destructive mt-2 flex items-center gap-1">
                                <Icon icon="mdi:alert-circle-outline" class="w-3.5 h-3.5" />
                                {{ form.errors.profile_photo }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Right: Profile Info Form -->
                <div class="lg:col-span-3 anim-fade-in-up anim-delay-2">
                    <div class="bg-card rounded-2xl border border-border/60 shadow-sm overflow-hidden">
                        <div class="p-5 border-b border-border/60 bg-gradient-to-r from-primary/5 to-transparent flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center text-primary">
                                <Icon icon="mdi:account-edit-outline" class="w-4 h-4" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-foreground">Informasi Profil</h3>
                                <p class="text-xs text-muted-foreground">Perbarui data diri Anda</p>
                            </div>
                        </div>

                        <form @submit.prevent="submit" class="p-5 space-y-4">
                            <div class="space-y-1.5">
                                <Label for="name" class="text-xs font-bold">Nama Lengkap <span class="text-destructive">*</span></Label>
                                <Input id="name" v-model="form.name" required placeholder="Masukkan nama lengkap" class="rounded-xl" />
                                <p v-if="form.errors?.name" class="text-[11px] text-destructive flex items-center gap-1">
                                    <Icon icon="mdi:alert-circle-outline" class="w-3.5 h-3.5" />{{ form.errors.name }}
                                </p>
                            </div>

                            <div class="space-y-1.5">
                                <Label for="email" class="text-xs font-bold">Alamat Email <span class="text-destructive">*</span></Label>
                                <Input id="email" v-model="form.email" type="email" required placeholder="Masukkan alamat email" class="rounded-xl" />
                                <p v-if="form.errors?.email" class="text-[11px] text-destructive flex items-center gap-1">
                                    <Icon icon="mdi:alert-circle-outline" class="w-3.5 h-3.5" />{{ form.errors.email }}
                                </p>
                            </div>

                            <div class="space-y-1.5">
                                <Label for="phone" class="text-xs font-bold">Nomor Telepon</Label>
                                <Input id="phone" v-model="form.phone" placeholder="Contoh: 081234567890" class="rounded-xl" />
                                <p v-if="form.errors?.phone" class="text-[11px] text-destructive flex items-center gap-1">
                                    <Icon icon="mdi:alert-circle-outline" class="w-3.5 h-3.5" />{{ form.errors.phone }}
                                </p>
                            </div>

                            <!-- Error Alert -->
                            <div v-if="form.errors && Object.keys(form.errors).filter(k => !['name','email','phone','profile_photo'].includes(k)).length > 0"
                                 class="bg-destructive/10 border border-destructive/20 text-destructive px-4 py-3 rounded-xl text-xs flex items-start gap-2">
                                <Icon icon="mdi:alert-outline" class="w-4 h-4 flex-shrink-0 mt-0.5" />
                                <p>Terjadi kesalahan, silakan coba lagi.</p>
                            </div>

                            <!-- Success Alert -->
                            <div v-if="page.props.flash?.success"
                                 class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 px-4 py-3 rounded-xl text-xs flex items-center gap-2">
                                <Icon icon="mdi:check-circle-outline" class="w-4 h-4 flex-shrink-0" />
                                <span>{{ page.props.flash.success }}</span>
                            </div>

                            <div class="flex justify-end pt-2">
                                <Button type="submit" :disabled="form.processing" class="gap-2 px-6 rounded-xl font-bold">
                                    <Icon v-if="form.processing" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                                    <Icon v-else icon="mdi:content-save-outline" class="w-4 h-4" />
                                    {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Security Card -->
            <div class="bg-card rounded-2xl border border-border/60 shadow-sm overflow-hidden anim-fade-in-up anim-delay-3">
                <div class="p-5 border-b border-border/60 bg-gradient-to-r from-amber-500/5 to-transparent flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-amber-500/10 flex items-center justify-center text-amber-600">
                        <Icon icon="mdi:shield-lock-outline" class="w-4 h-4" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-foreground">Keamanan Akun</h3>
                        <p class="text-xs text-muted-foreground">Kelola keamanan dan autentikasi</p>
                    </div>
                </div>
                <div class="p-5">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-4 bg-muted/30 rounded-2xl border border-border/40">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-600">
                                <Icon icon="mdi:lock-outline" class="w-5 h-5" />
                            </div>
                            <div>
                                <p class="text-sm font-bold text-foreground">Ubah Password</p>
                                <p class="text-xs text-muted-foreground mt-0.5">Perbarui password untuk menjaga keamanan akun</p>
                            </div>
                        </div>
                        <Link :href="route('admin.profile.change-password')"
                              class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-sm hover:shadow-md transition-all w-full sm:w-auto justify-center">
                            <Icon icon="mdi:pencil-lock-outline" class="w-4 h-4" />
                            Ubah Password
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
