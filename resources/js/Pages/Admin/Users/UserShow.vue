<script setup>
import { inject } from 'vue';
const route = inject('route');

import { Head, Link, router } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Badge from '@/Components/ui/badge/Badge.vue';

const props = defineProps({
    user: { type: Object, required: true },
});

function toggleActive() {
    if (confirm('Yakin ingin mengubah status akun ini?')) {
        router.post(route('admin.users.toggle-active', props.user.id));
    }
}

function deleteUser() {
    if (confirm('Yakin ingin menghapus user ini? Tindakan ini tidak dapat dibatalkan.')) {
        router.delete(route('admin.users.destroy', props.user.id));
    }
}
</script>

<template>
    <AdminLayout>
        <Head :title="user.name + ' - Detail Siswa'" />

        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <Icon icon="mdi:account-details-outline" class="w-5 h-5" />
                </span>
                <span>Detail Siswa</span>
            </div>
        </template>
        <template #header-sub>{{ user.name }}</template>

        <div class="max-w-5xl mx-auto space-y-6">
            <Link :href="route('admin.users.index')" class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-medium text-muted-foreground hover:text-foreground transition group">
                <Icon icon="mdi:arrow-left" class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" />
                Kembali ke Daftar Siswa
            </Link>

            <!-- Profile Header Card -->
            <div class="bg-card rounded-3xl border border-border/60 shadow-sm overflow-hidden">
                <!-- Cover Banner -->
                <div class="h-32 sm:h-40 bg-gradient-to-r from-primary via-emerald-600 to-teal-700 relative overflow-hidden">
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_50%,rgba(255,255,255,0.15),transparent)]"></div>
                </div>

                <!-- Profile Info -->
                <div class="px-6 sm:px-8 pb-6 -mt-12 sm:-mt-14 relative">
                    <div class="flex flex-col sm:flex-row items-center sm:items-end justify-between gap-5">
                        <div class="flex flex-col sm:flex-row items-center sm:items-end gap-4 text-center sm:text-left">
                            <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-3xl border-4 border-card overflow-hidden bg-primary/10 text-primary flex items-center justify-center shadow-xl shrink-0 font-black text-3xl">
                                <img v-if="user.profile_photo" :src="user.profile_photo.startsWith('http') ? user.profile_photo : '/storage/' + user.profile_photo" class="w-full h-full object-cover" />
                                <span v-else>{{ user.name?.charAt(0)?.toUpperCase() }}</span>
                            </div>

                            <div class="space-y-1">
                                <div class="flex items-center justify-center sm:justify-start gap-2 flex-wrap">
                                    <h1 class="text-xl sm:text-2xl font-black tracking-tight text-foreground">{{ user.name }}</h1>
                                    <Badge :variant="user.is_active ? 'success' : 'destructive'" class="text-xs font-bold gap-1 px-2.5 py-0.5 rounded-full">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="user.is_active ? 'bg-emerald-500' : 'bg-red-500'"></span>
                                        {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </Badge>
                                </div>
                                <p class="text-xs sm:text-sm text-muted-foreground flex items-center justify-center sm:justify-start gap-1.5">
                                    <Icon icon="mdi:email-outline" class="w-4 h-4 text-primary" />
                                    {{ user.email }}
                                </p>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-2.5 w-full sm:w-auto">
                            <Link :href="route('admin.users.edit', user.id)"
                                  class="flex-1 sm:flex-none inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-primary text-primary-foreground hover:bg-primary/90 text-xs sm:text-sm font-semibold shadow-sm transition">
                                <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                                <span>Edit Profil</span>
                            </Link>

                            <Button variant="outline" size="sm" @click="toggleActive" class="h-10 px-4 rounded-xl font-semibold gap-1.5">
                                <Icon :icon="user.is_active ? 'mdi:account-off-outline' : 'mdi:account-check-outline'" class="w-4 h-4" />
                                <span>{{ user.is_active ? 'Nonaktifkan' : 'Aktifkan' }}</span>
                            </Button>

                            <Button variant="destructive" size="sm" @click="deleteUser" class="h-10 px-3.5 rounded-xl font-semibold">
                                <Icon icon="mdi:trash-can-outline" class="w-4 h-4" />
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Academic Data Card -->
                <div class="bg-card rounded-3xl border border-border/60 p-6 sm:p-7 shadow-sm space-y-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-border/50">
                        <Icon icon="mdi:school-outline" class="w-5 h-5 text-primary" />
                        <h3 class="font-bold text-base tracking-tight text-foreground">Data Akademis</h3>
                    </div>

                    <div class="divide-y divide-border/40 text-xs sm:text-sm">
                        <div class="flex justify-between items-center py-2.5">
                            <span class="text-muted-foreground flex items-center gap-2">
                                <Icon icon="mdi:order-numeric-ascending" class="w-4 h-4 text-primary" /> Tingkat Kelas
                            </span>
                            <span class="font-bold text-foreground">Kelas {{ user.student_class || '-' }}</span>
                        </div>

                        <div class="flex justify-between items-center py-2.5">
                            <span class="text-muted-foreground flex items-center gap-2">
                                <Icon icon="mdi:book-open-page-variant-outline" class="w-4 h-4 text-primary" /> Bidang Studi
                            </span>
                            <span class="font-bold text-foreground">{{ user.bidang || '-' }}</span>
                        </div>

                        <div class="flex justify-between items-center py-2.5">
                            <span class="text-muted-foreground flex items-center gap-2">
                                <Icon icon="mdi:trophy-outline" class="w-4 h-4 text-primary" /> Level
                            </span>
                            <span class="font-bold text-primary bg-primary/10 px-2.5 py-0.5 rounded-full">{{ user.level || '-' }}</span>
                        </div>

                        <div class="flex justify-between items-center py-2.5">
                            <span class="text-muted-foreground flex items-center gap-2">
                                <Icon icon="mdi:domain" class="w-4 h-4 text-primary" /> Asal Sekolah
                            </span>
                            <span class="font-semibold text-foreground text-right">{{ user.school_name || '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Contact Info Card -->
                <div class="bg-card rounded-3xl border border-border/60 p-6 sm:p-7 shadow-sm space-y-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-border/50">
                        <Icon icon="mdi:card-account-phone-outline" class="w-5 h-5 text-primary" />
                        <h3 class="font-bold text-base tracking-tight text-foreground">Informasi Kontak & Pribadi</h3>
                    </div>

                    <div class="divide-y divide-border/40 text-xs sm:text-sm">
                        <div class="flex justify-between items-center py-2.5">
                            <span class="text-muted-foreground flex items-center gap-2">
                                <Icon icon="mdi:phone-outline" class="w-4 h-4 text-primary" /> No. WhatsApp/HP
                            </span>
                            <span class="font-bold text-foreground">{{ user.phone || '-' }}</span>
                        </div>

                        <div class="flex justify-between items-center py-2.5">
                            <span class="text-muted-foreground flex items-center gap-2">
                                <Icon icon="mdi:gender-male-female" class="w-4 h-4 text-primary" /> Jenis Kelamin
                            </span>
                            <span class="font-semibold text-foreground">{{ user.gender || '-' }}</span>
                        </div>

                        <div class="flex justify-between items-center py-2.5">
                            <span class="text-muted-foreground flex items-center gap-2">
                                <Icon icon="mdi:church" class="w-4 h-4 text-primary" /> Agama
                            </span>
                            <span class="font-semibold text-foreground">{{ user.religion || '-' }}</span>
                        </div>

                        <div class="flex justify-between items-start py-2.5">
                            <span class="text-muted-foreground flex items-center gap-2 shrink-0">
                                <Icon icon="mdi:map-marker-outline" class="w-4 h-4 text-primary" /> Alamat
                            </span>
                            <span class="font-medium text-foreground text-right max-w-[220px]">{{ user.address || '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- System Metadata Card -->
                <div class="bg-card rounded-3xl border border-border/60 p-6 sm:p-7 shadow-sm md:col-span-2 space-y-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-border/50">
                        <Icon icon="mdi:shield-check-outline" class="w-5 h-5 text-primary" />
                        <h3 class="font-bold text-base tracking-tight text-foreground">Informasi Sistem</h3>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                        <div class="p-3.5 rounded-2xl bg-muted/30 border border-border/40 space-y-1">
                            <span class="text-muted-foreground font-medium uppercase tracking-wider text-[10px]">Peran (Role)</span>
                            <p class="font-bold text-foreground capitalize">{{ user.role }}</p>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-muted/30 border border-border/40 space-y-1">
                            <span class="text-muted-foreground font-medium uppercase tracking-wider text-[10px]">Status Akun</span>
                            <p class="font-bold" :class="user.is_active ? 'text-emerald-600' : 'text-red-500'">{{ user.is_active ? 'Aktif' : 'Nonaktif' }}</p>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-muted/30 border border-border/40 space-y-1">
                            <span class="text-muted-foreground font-medium uppercase tracking-wider text-[10px]">Terdaftar Pada</span>
                            <p class="font-bold text-foreground">{{ user.created_at ? new Date(user.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-' }}</p>
                        </div>

                        <div class="p-3.5 rounded-2xl bg-muted/30 border border-border/40 space-y-1">
                            <span class="text-muted-foreground font-medium uppercase tracking-wider text-[10px]">Pembaruan Terakhir</span>
                            <p class="font-bold text-foreground">{{ user.updated_at ? new Date(user.updated_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
