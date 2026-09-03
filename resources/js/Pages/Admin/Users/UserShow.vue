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
        <div class="bg-card rounded-xl border shadow-sm overflow-hidden mb-6 anim-fade-in-up">
            <div class="h-36 sm:h-40 bg-gradient-to-br from-[#344e41] to-primary relative">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_50%,rgba(255,255,255,0.1),transparent)]"></div>
                <div class="absolute inset-0 grid-dots opacity-30"></div>
            </div>
            <div class="px-5 sm:px-6 pb-6 -mt-14 relative">
                <div class="flex flex-col sm:flex-row items-start sm:items-end gap-4">
                    <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl border-4 border-card overflow-hidden bg-primary/10 flex items-center justify-center shadow-lg ring-4 ring-background">
                        <img v-if="user.profile_photo" :src="'/storage/' + user.profile_photo" class="w-full h-full object-cover" />
                        <span v-else class="text-3xl sm:text-4xl font-bold text-primary">{{ user.name?.charAt(0) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h1 class="text-xl sm:text-2xl font-bold">{{ user.name }}</h1>
                        <p class="text-muted-foreground text-sm mt-0.5">{{ user.email }}</p>
                        <div class="flex items-center gap-2 mt-2">
                            <Badge :variant="user.is_active ? 'success' : 'destructive'" class="gap-1">
                                <span class="w-1.5 h-1.5 rounded-full" :class="user.is_active ? 'bg-success-600' : 'bg-danger-500'"></span>
                                {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                            </Badge>
                            <Badge variant="outline" class="gap-1 capitalize">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ user.role }}
                            </Badge>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 w-full sm:w-auto" @click.stop>
                        <Link :href="route('admin.users.edit', user.id)" class="inline-flex items-center justify-center gap-1.5 bg-primary text-primary-foreground px-4 py-2 rounded-lg text-sm font-medium hover:bg-primary/90 transition-all duration-200 hover:shadow-md active:scale-[0.98] flex-1 sm:flex-none">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                            Edit
                        </Link>
                        <Button variant="outline" size="sm" @click="toggleActive" class="gap-1.5 flex-1 sm:flex-none">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182" />
                            </svg>
                            {{ user.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </Button>
                        <Button variant="destructive" size="sm" @click="deleteUser" class="gap-1.5 flex-1 sm:flex-none">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                            Hapus
                        </Button>
                    </div>
                </div>
                <p class="text-xs text-muted-foreground mt-4 flex items-center gap-1.5">
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
            <Card class="p-5 sm:p-6 anim-fade-in-up anim-delay-1">
                <div class="flex items-center gap-2.5 mb-5">
                    <div class="w-9 h-9 rounded-lg bg-warning-500/10 flex items-center justify-center">
                        <svg class="w-4.5 h-4.5 text-warning-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" />
                        </svg>
                    </div>
                    <h3 class="font-semibold">Data Siswa</h3>
                </div>
                <div class="space-y-3.5 text-sm">
                    <div class="flex justify-between items-center py-1.5 border-b border-border/50 last:border-0">
                        <span class="text-muted-foreground">Kelas</span>
                        <span class="font-medium text-right">{{ user.student_class || '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b border-border/50 last:border-0">
                        <span class="text-muted-foreground">Bidang</span>
                        <span class="font-medium text-right">{{ user.bidang || '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b border-border/50 last:border-0">
                        <span class="text-muted-foreground">Level</span>
                        <span class="font-medium text-right">{{ user.level || '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1.5">
                        <span class="text-muted-foreground">Sekolah</span>
                        <span class="font-medium text-right">{{ user.school_name || '-' }}</span>
                    </div>
                </div>
            </Card>

            <!-- Info Kontak -->
            <Card class="p-5 sm:p-6 anim-fade-in-up anim-delay-2">
                <div class="flex items-center gap-2.5 mb-5">
                    <div class="w-9 h-9 rounded-lg bg-fern/10 flex items-center justify-center">
                        <svg class="w-4.5 h-4.5 text-fern" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                        </svg>
                    </div>
                    <h3 class="font-semibold">Info Kontak</h3>
                </div>
                <div class="space-y-3.5 text-sm">
                    <div class="flex justify-between items-center py-1.5 border-b border-border/50 last:border-0">
                        <span class="text-muted-foreground">No. HP/WA</span>
                        <span class="font-medium text-right">{{ user.phone || '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b border-border/50 last:border-0">
                        <span class="text-muted-foreground">Jenis Kelamin</span>
                        <span class="font-medium text-right">{{ user.gender || '-' }}</span>
                    </div>
                    <div class="flex justify-between items-center py-1.5 border-b border-border/50 last:border-0">
                        <span class="text-muted-foreground">Agama</span>
                        <span class="font-medium text-right">{{ user.religion || '-' }}</span>
                    </div>
                    <div class="flex justify-between items-start py-1.5">
                        <span class="text-muted-foreground">Alamat</span>
                        <span class="font-medium text-right max-w-[220px]">{{ user.address || '-' }}</span>
                    </div>
                </div>
            </Card>

            <!-- Info Tambahan -->
            <Card class="p-5 sm:p-6 lg:col-span-2 anim-fade-in-up anim-delay-3">
                <div class="flex items-center gap-2.5 mb-5">
                    <div class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center">
                        <svg class="w-4.5 h-4.5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                    </div>
                    <h3 class="font-semibold">Info Tambahan</h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                    <div class="flex flex-col gap-1.5 p-3 rounded-lg bg-muted/40">
                        <span class="text-muted-foreground text-xs font-medium">Role</span>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                            </svg>
                            <Badge variant="outline" class="capitalize font-medium">{{ user.role }}</Badge>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1.5 p-3 rounded-lg bg-muted/40">
                        <span class="text-muted-foreground text-xs font-medium">Status</span>
                        <div class="flex items-center gap-1.5">
                            <Badge :variant="user.is_active ? 'success' : 'destructive'" class="gap-1 font-medium">
                                <span class="w-1.5 h-1.5 rounded-full" :class="user.is_active ? 'bg-success-600' : 'bg-danger-500'"></span>
                                {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                            </Badge>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1.5 p-3 rounded-lg bg-muted/40">
                        <span class="text-muted-foreground text-xs font-medium">Terdaftar Sejak</span>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                            <span class="font-medium">{{ user.created_at ? new Date(user.created_at).toLocaleDateString('id-ID') : '-' }}</span>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1.5 p-3 rounded-lg bg-muted/40">
                        <span class="text-muted-foreground text-xs font-medium">Terakhir Diperbarui</span>
                        <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182" />
                            </svg>
                            <span class="font-medium">{{ user.updated_at ? new Date(user.updated_at).toLocaleDateString('id-ID') : '-' }}</span>
                        </div>
                    </div>
                </div>
            </Card>
        </div>
    </AdminLayout>
</template>
