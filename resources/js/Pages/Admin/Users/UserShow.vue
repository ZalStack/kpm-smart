<script setup>
import { inject } from 'vue';
const route = inject('route');

import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Button from '@/Components/ui/button/Button.vue';
import Badge from '@/Components/ui/badge/Badge.vue';
import Card from '@/Components/ui/card/Card.vue';

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
        <Head :title="user.name + ' - Detail User'" />

        <template #header-title>Detail User</template>
        <template #header-sub>Informasi lengkap mengenai pengguna</template>

        <Link :href="route('admin.users.index')" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground transition mb-6 group">
            <svg class="w-4 h-4 group-hover:-translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
            Kembali ke Daftar User
        </Link>

        <!-- Profile Header -->
        <div class="bg-card rounded-2xl border border-border/60 shadow-sm overflow-hidden mb-6 sm:mb-8 anim-fade-in-up">
            <!-- Cover Banner -->
            <div class="h-32 sm:h-44 bg-gradient-to-br from-[#344e41] via-[#3a5a40] to-primary relative overflow-hidden">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_50%,rgba(255,255,255,0.12),transparent)]"></div>
                <div class="absolute inset-0 grid-dots opacity-20"></div>
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/5 rounded-full blur-2xl"></div>
                <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-white/5 rounded-full blur-2xl"></div>
                <!-- Decorative circles -->
                <div class="absolute top-6 right-12 w-16 h-16 border border-white/10 rounded-full"></div>
                <div class="absolute top-10 right-8 w-8 h-8 border border-white/10 rounded-full"></div>
            </div>

            <!-- Profile Info -->
            <div class="px-5 sm:px-7 pb-6 -mt-14 sm:-mt-16 relative">
                <div class="flex flex-col sm:flex-row items-start sm:items-end gap-4 sm:gap-5">
                    <!-- Avatar -->
                    <div class="w-24 h-24 sm:w-32 sm:h-32 rounded-2xl border-4 border-card overflow-hidden bg-gradient-to-br from-primary/20 to-primary/5 flex items-center justify-center shadow-xl ring-4 ring-background">
                        <img v-if="user.profile_photo" :src="'/storage/' + user.profile_photo" class="w-full h-full object-cover" />
                        <span v-else class="text-4xl sm:text-5xl font-extrabold text-primary/70">{{ user.name?.charAt(0)?.toUpperCase() }}</span>
                    </div>

                    <!-- Name & Info -->
                    <div class="flex-1 min-w-0">
                        <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight">{{ user.name }}</h1>
                        <p class="text-muted-foreground text-sm mt-1 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                            </svg>
                            {{ user.email }}
                        </p>
                        <div class="flex items-center gap-2 mt-2.5 flex-wrap">
                            <Badge :variant="user.is_active ? 'success' : 'destructive'" class="gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold">
                                <span class="w-2 h-2 rounded-full animate-pulse-soft" :class="user.is_active ? 'bg-emerald-500' : 'bg-red-500'"></span>
                                {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                            </Badge>
                            <Badge variant="outline" class="gap-1.5 capitalize px-2.5 py-1 rounded-lg text-xs font-semibold">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                </svg>
                                {{ user.role }}
                            </Badge>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-2 w-full sm:w-auto" @click.stop>
                        <Link :href="route('admin.users.edit', user.id)" class="inline-flex items-center justify-center gap-1.5 bg-gradient-to-r from-primary to-primary/90 text-primary-foreground px-5 py-2.5 rounded-xl text-sm font-semibold hover:shadow-lg hover:shadow-primary/20 transition-all duration-300 active:scale-[0.97] flex-1 sm:flex-none">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                            Edit
                        </Link>
                        <Button variant="outline" size="sm" @click="toggleActive" class="gap-1.5 flex-1 sm:flex-none h-10 px-4 rounded-xl font-semibold hover:shadow-md transition-all">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182" />
                            </svg>
                            {{ user.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </Button>
                        <Button variant="destructive" size="sm" @click="deleteUser" class="gap-1.5 flex-1 sm:flex-none h-10 px-4 rounded-xl font-semibold hover:shadow-md transition-all">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                            Hapus
                        </Button>
                    </div>
                </div>

                <!-- Registration Date -->
                <p class="text-xs text-muted-foreground mt-5 flex items-center gap-1.5 bg-muted/40 w-fit px-3 py-1.5 rounded-lg">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                    Terdaftar sejak {{ user.created_at ? new Date(user.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-' }}
                </p>
            </div>
        </div>

        <!-- Details Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <!-- Data Siswa -->
            <Card class="p-5 sm:p-6 border-border/60 shadow-sm hover:shadow-md transition-shadow duration-300 anim-fade-in-up anim-delay-1 rounded-2xl">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500/15 to-amber-500/5 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-base">Data Siswa</h3>
                </div>
                <div class="space-y-0">
                    <div class="flex justify-between items-center py-3 border-b border-border/40 last:border-0 group">
                        <span class="text-muted-foreground text-sm flex items-center gap-2">
                            <svg class="w-4 h-4 text-muted-foreground/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" /></svg>
                            Kelas
                        </span>
                        <span class="font-semibold text-sm group-hover:text-primary transition-colors">{{ user.student_class || '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center py-3 border-b border-border/40 last:border-0 group">
                        <span class="text-muted-foreground text-sm flex items-center gap-2">
                            <svg class="w-4 h-4 text-muted-foreground/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>
                            Bidang
                        </span>
                        <span class="font-semibold text-sm group-hover:text-primary transition-colors">{{ user.bidang || '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center py-3 border-b border-border/40 last:border-0 group">
                        <span class="text-muted-foreground text-sm flex items-center gap-2">
                            <svg class="w-4 h-4 text-muted-foreground/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
                            Level
                        </span>
                        <span class="font-semibold text-sm group-hover:text-primary transition-colors">{{ user.level || '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center py-3 group">
                        <span class="text-muted-foreground text-sm flex items-center gap-2">
                            <svg class="w-4 h-4 text-muted-foreground/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" /></svg>
                            Sekolah
                        </span>
                        <span class="font-semibold text-sm group-hover:text-primary transition-colors text-right max-w-[200px]">{{ user.school_name || '-' }}</span>
                    </div>
                </div>
            </Card>

            <!-- Info Kontak -->
            <Card class="p-5 sm:p-6 border-border/60 shadow-sm hover:shadow-md transition-shadow duration-300 anim-fade-in-up anim-delay-2 rounded-2xl">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-fern/15 to-fern/5 flex items-center justify-center">
                        <svg class="w-5 h-5 text-fern" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-base">Info Kontak</h3>
                </div>
                <div class="space-y-0">
                    <div class="flex justify-between items-center py-3 border-b border-border/40 last:border-0 group">
                        <span class="text-muted-foreground text-sm flex items-center gap-2">
                            <svg class="w-4 h-4 text-muted-foreground/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3" /></svg>
                            No. HP/WA
                        </span>
                        <span class="font-semibold text-sm group-hover:text-primary transition-colors">{{ user.phone || '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center py-3 border-b border-border/40 last:border-0 group">
                        <span class="text-muted-foreground text-sm flex items-center gap-2">
                            <svg class="w-4 h-4 text-muted-foreground/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                            Jenis Kelamin
                        </span>
                        <span class="font-semibold text-sm group-hover:text-primary transition-colors">{{ user.gender || '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center py-3 border-b border-border/40 last:border-0 group">
                        <span class="text-muted-foreground text-sm flex items-center gap-2">
                            <svg class="w-4 h-4 text-muted-foreground/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
                            Agama
                        </span>
                        <span class="font-semibold text-sm group-hover:text-primary transition-colors">{{ user.religion || '-' }}</span>
                    </div>
                    <div class="flex justify-between items-start py-3 group">
                        <span class="text-muted-foreground text-sm flex items-center gap-2">
                            <svg class="w-4 h-4 text-muted-foreground/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
                            Alamat
                        </span>
                        <span class="font-semibold text-sm group-hover:text-primary transition-colors text-right max-w-[220px]">{{ user.address || '-' }}</span>
                    </div>
                </div>
            </Card>

            <!-- Info Tambahan -->
            <Card class="p-5 sm:p-6 lg:col-span-2 border-border/60 shadow-sm hover:shadow-md transition-shadow duration-300 anim-fade-in-up anim-delay-3 rounded-2xl">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary/15 to-primary/5 flex items-center justify-center">
                        <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                    </div>
                    <h3 class="font-bold text-base">Info Tambahan</h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    <div class="flex flex-col gap-2 p-4 rounded-xl bg-gradient-to-br from-muted/40 to-muted/20 border border-border/30 hover:border-primary/20 transition-all duration-300">
                        <span class="text-muted-foreground text-xs font-semibold uppercase tracking-wider">Role</span>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                            </svg>
                            <Badge variant="outline" class="capitalize font-semibold rounded-lg">{{ user.role }}</Badge>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 p-4 rounded-xl bg-gradient-to-br from-muted/40 to-muted/20 border border-border/30 hover:border-primary/20 transition-all duration-300">
                        <span class="text-muted-foreground text-xs font-semibold uppercase tracking-wider">Status</span>
                        <div class="flex items-center gap-2">
                            <Badge :variant="user.is_active ? 'success' : 'destructive'" class="gap-1.5 font-semibold rounded-lg">
                                <span class="w-2 h-2 rounded-full" :class="user.is_active ? 'bg-emerald-500' : 'bg-red-500'"></span>
                                {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                            </Badge>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 p-4 rounded-xl bg-gradient-to-br from-muted/40 to-muted/20 border border-border/30 hover:border-primary/20 transition-all duration-300">
                        <span class="text-muted-foreground text-xs font-semibold uppercase tracking-wider">Terdaftar Sejak</span>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-muted-foreground/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                            <span class="font-semibold text-sm">{{ user.created_at ? new Date(user.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-' }}</span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-2 p-4 rounded-xl bg-gradient-to-br from-muted/40 to-muted/20 border border-border/30 hover:border-primary/20 transition-all duration-300">
                        <span class="text-muted-foreground text-xs font-semibold uppercase tracking-wider">Terakhir Diperbarui</span>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-muted-foreground/50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182" />
                            </svg>
                            <span class="font-semibold text-sm">{{ user.updated_at ? new Date(user.updated_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-' }}</span>
                        </div>
                    </div>
                </div>
            </Card>
        </div>
    </AdminLayout>
</template>
