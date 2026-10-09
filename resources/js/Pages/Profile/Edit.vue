<script setup>
import { inject, ref, computed } from 'vue';
import { Head, useForm, usePage, Link } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
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
        alert('Ukuran foto profil maksimal 2MB');
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
    form.put(route('user.profile.update'), {
        forceFormData: true,
    });
}
</script>

<template>
    <UserLayout>
        <Head title="Pengaturan Profil - KPM SMART" />

        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <Icon icon="mdi:account-cog-outline" class="w-5 h-5" />
                </span>
                <span>Pengaturan Profil Siswa</span>
            </div>
        </template>
        <template #header-sub>
            Kelola data diri, kontak, foto profil, dan keamanan akun belajarmu
        </template>

        <div class="max-w-4xl mx-auto space-y-6">

            <!-- Profile Overview & Photo Card -->
            <div class="bg-card rounded-3xl border border-border/60 p-6 sm:p-7 shadow-sm">
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
                    <!-- Photo Avatar -->
                    <div class="relative group shrink-0">
                        <div v-if="profilePhotoUrl"
                             class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl overflow-hidden ring-4 ring-primary/10 border-2 border-border/50 shadow-md">
                            <img :src="profilePhotoUrl" alt="Foto Profil" class="w-full h-full object-cover" />
                        </div>
                        <div v-else
                             class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl bg-gradient-to-br from-primary/20 to-primary/5 border border-primary/20 flex items-center justify-center ring-4 ring-primary/10 shadow-md">
                            <span class="text-3xl sm:text-4xl font-black text-primary">{{ userInitial }}</span>
                        </div>

                        <button v-if="photoPreview"
                                type="button"
                                @click="removePhoto"
                                class="absolute -top-2 -right-2 w-7 h-7 bg-destructive text-destructive-foreground rounded-full flex items-center justify-center shadow-lg hover:scale-110 transition">
                            <Icon icon="mdi:close" class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- Details & Dropzone -->
                    <div class="flex-1 space-y-3 text-center sm:text-left w-full">
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-foreground">{{ user?.name }}</h2>
                            <p class="text-xs sm:text-sm text-muted-foreground">{{ user?.email }}</p>
                        </div>

                        <!-- Admin tags -->
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 pt-1">
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-primary/10 text-primary border border-primary/20">
                                <Icon icon="mdi:school" class="w-3.5 h-3.5" /> Kelas: {{ user?.student_class || '-' }}
                            </span>
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800/40">
                                <Icon icon="mdi:calculator-variant" class="w-3.5 h-3.5" /> {{ user?.bidang || '-' }}
                            </span>
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/40">
                                <Icon icon="mdi:star-outline" class="w-3.5 h-3.5" /> Level: {{ user?.level || '-' }}
                            </span>
                        </div>

                        <!-- Dropzone trigger -->
                        <div @dragover.prevent="photoDragOver = true"
                             @dragleave="photoDragOver = false"
                             @drop="handleDrop"
                             @click="$refs.fileInput.click()"
                             :class="[
                                 'border-2 border-dashed rounded-2xl p-4 text-center cursor-pointer transition-all duration-200 mt-2',
                                 photoDragOver ? 'border-primary bg-primary/5' : 'border-border/70 hover:border-primary/50 hover:bg-muted/30'
                             ]">
                            <p class="text-xs font-bold text-foreground">Klik atau seret foto baru ke sini untuk mengganti avatar</p>
                            <p class="text-[11px] text-muted-foreground mt-0.5">JPG, PNG, atau WebP (Maks. 2MB)</p>
                        </div>
                        <input ref="fileInput" type="file" accept=".jpg,.jpeg,.png,.webp" class="hidden" @change="handlePhotoSelect" />
                        <p v-if="form.errors?.profile_photo" class="text-xs text-destructive mt-1">{{ form.errors.profile_photo }}</p>
                    </div>
                </div>
            </div>

            <!-- Profile Form -->
            <div class="bg-card rounded-3xl border border-border/60 p-6 sm:p-7 shadow-sm">
                <div class="flex items-center justify-between pb-4 border-b border-border/50 mb-6">
                    <div class="flex items-center gap-2">
                        <Icon icon="mdi:card-account-details-outline" class="w-5 h-5 text-primary" />
                        <h3 class="font-bold text-base text-foreground">Informasi Data Pribadi</h3>
                    </div>
                    <span class="text-xs text-muted-foreground">Tanda (<span class="text-destructive">*</span>) wajib diisi</span>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div class="space-y-2">
                            <Label for="name">Nama Lengkap <span class="text-destructive">*</span></Label>
                            <Input id="name" v-model="form.name" required placeholder="Masukkan nama lengkap" class="rounded-xl" />
                            <p v-if="form.errors?.name" class="text-xs text-destructive">{{ form.errors.name }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="phone">Nomor WhatsApp / Telepon <span class="text-destructive">*</span></Label>
                            <Input id="phone" v-model="form.phone" required placeholder="08xxxxxxxxxx" class="rounded-xl" />
                            <p v-if="form.errors?.phone" class="text-xs text-destructive">{{ form.errors.phone }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="school_name">Nama Asal Sekolah <span class="text-destructive">*</span></Label>
                            <Input id="school_name" v-model="form.school_name" required placeholder="Contoh: SMP Negeri 1..." class="rounded-xl" />
                            <p v-if="form.errors?.school_name" class="text-xs text-destructive">{{ form.errors.school_name }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="gender">Jenis Kelamin</Label>
                            <Select id="gender" v-model="form.gender" class="rounded-xl">
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label for="religion">Agama</Label>
                            <Select id="religion" v-model="form.religion" class="rounded-xl">
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
                            <Label for="student_class">Kelas (Dikelola Administrator)</Label>
                            <Input id="student_class" :value="form.student_class || '-'" disabled class="rounded-xl opacity-75 bg-muted/40 cursor-not-allowed" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="address">Alamat Tempat Tinggal</Label>
                        <Textarea id="address" v-model="form.address" rows="3" placeholder="Tuliskan alamat lengkap..." class="rounded-xl resize-none text-sm" />
                    </div>

                    <!-- Flash Message -->
                    <div v-if="page.props.flash?.success" class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 dark:text-emerald-400 text-xs sm:text-sm font-medium flex items-center gap-2">
                        <Icon icon="mdi:check-circle" class="w-5 h-5 shrink-0" />
                        {{ page.props.flash.success }}
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center justify-end pt-3">
                        <Button type="submit" :loading="form.processing" class="w-full sm:w-auto gap-2 font-semibold shadow-sm min-h-11">
                            <Icon v-if="!form.processing" icon="mdi:content-save-outline" class="w-4 h-4" />
                            {{ form.processing ? 'Menyimpan Perubahan...' : 'Simpan Pembaruan Data' }}
                        </Button>
                    </div>
                </form>
            </div>

            <!-- Security & Password Card -->
            <div class="bg-card rounded-3xl border border-border/60 p-6 sm:p-7 shadow-sm">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-4 text-center sm:text-left">
                        <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
                            <Icon icon="mdi:shield-lock-outline" class="w-6 h-6" />
                        </div>
                        <div>
                            <h4 class="font-bold text-base text-foreground">Keamanan Kata Sandi</h4>
                            <p class="text-xs text-muted-foreground mt-0.5">Perbarui kata sandi secara berkala untuk menjaga keamanan akun Anda</p>
                        </div>
                    </div>

                    <Link :href="route('user.profile.change-password')" class="w-full sm:w-auto">
                        <Button variant="outline" class="w-full sm:w-auto gap-2 font-semibold shadow-sm min-h-10">
                            <Icon icon="mdi:key-outline" class="w-4 h-4 text-amber-500" />
                            Ubah Kata Sandi
                        </Button>
                    </Link>
                </div>
            </div>

        </div>
    </UserLayout>
</template>
