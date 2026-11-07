<script setup>
import { ref, inject, onMounted, onUnmounted, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import FlashMessage from '@/Components/shared/FlashMessage.vue';
import { Icon } from '@iconify/vue/offline';

const route = inject('route');

const page = usePage();
const user = computed(() => page.props.auth?.user);
const sidebarOpen = ref(false);
const profileDropdownOpen = ref(false);

const profilePhotoUrl = computed(() => {
    if (!user.value?.profile_photo) return null;
    if (user.value.profile_photo.startsWith('http://') || user.value.profile_photo.startsWith('https://') || user.value.profile_photo.startsWith('//')) {
        return user.value.profile_photo;
    }
    return '/storage/' + user.value.profile_photo + '?v=' + encodeURIComponent(user.value.profile_photo);
});

const currentDate = new Date().toLocaleDateString('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'short',
    day: 'numeric',
});

function openSidebar() {
    sidebarOpen.value = true;
    document.body.style.overflow = 'hidden';
}

function closeSidebar() {
    sidebarOpen.value = false;
    document.body.style.overflow = '';
}

function toggleProfileDropdown() {
    profileDropdownOpen.value = !profileDropdownOpen.value;
}

function safeRoute(name, params, fallback = '#') {
    try {
        if (typeof route === 'function') {
            return route(name, params);
        }
    } catch (e) {
        return fallback;
    }
    return fallback;
}

function isCurrentRoute(match) {
    if (!match) return false;
    try {
        return route().current(match);
    } catch (e) {
        return false;
    }
}

const navItems = [
    {
        label: 'Menu Utama',
        items: [
            { href: safeRoute('admin.dashboard'), label: 'Dasbor', icon: 'mdi:view-dashboard-outline', activeIcon: 'mdi:view-dashboard', route: 'admin.dashboard' },
            { href: safeRoute('admin.packages.index'), label: 'PR Pekanan', icon: 'mdi:book-open-page-variant-outline', activeIcon: 'mdi:book-open-page-variant', route: 'admin.packages.*' },
            { href: safeRoute('admin.leaderboard'), label: 'Papan Peringkat', icon: 'mdi:trophy-outline', activeIcon: 'mdi:trophy', route: 'admin.leaderboard' },
        ],
    },
    {
        label: 'Manajemen & Statistik',
        items: [
            { href: safeRoute('admin.users.index'), label: 'Manajemen Siswa', icon: 'mdi:account-group-outline', activeIcon: 'mdi:account-group', route: 'admin.users.*' },
            { href: safeRoute('admin.practice-statistics.index'), label: 'Statistik Pengerjaan', icon: 'mdi:chart-box-outline', activeIcon: 'mdi:chart-box', route: 'admin.practice-statistics.*' },
        ],
    },
    {
        label: 'Sistem & Informasi',
        items: [
            { href: safeRoute('admin.announcements.index'), label: 'Pengumuman', icon: 'mdi:bullhorn-outline', activeIcon: 'mdi:bullhorn', route: 'admin.announcements.*' },
            { href: safeRoute('admin.login-logs.index'), label: 'Log Aktivitas', icon: 'mdi:history', activeIcon: 'mdi:history', route: 'admin.login-logs.*' },
        ],
    },
];

const mobileNavItems = [
    { href: safeRoute('admin.dashboard'), label: 'Dasbor', icon: 'mdi:view-dashboard-outline', route: 'admin.dashboard' },
    { href: safeRoute('admin.packages.index'), label: 'PR', icon: 'mdi:book-open-page-variant-outline', route: 'admin.packages.*' },
    { href: safeRoute('admin.users.index'), label: 'Siswa', icon: 'mdi:account-group-outline', route: 'admin.users.*' },
    { href: safeRoute('admin.practice-statistics.index'), label: 'Statistik', icon: 'mdi:chart-box-outline', route: 'admin.practice-statistics.*' },
];

let clickHandler;
let resizeHandler;

onMounted(() => {
    clickHandler = (e) => {
        if (!e.target.closest('#profileDropdownWrap')) profileDropdownOpen.value = false;
    };
    document.addEventListener('click', clickHandler);

    resizeHandler = () => {
        if (window.innerWidth >= 1024) closeSidebar();
    };
    window.addEventListener('resize', resizeHandler);
});

onUnmounted(() => {
    if (clickHandler) document.removeEventListener('click', clickHandler);
    if (resizeHandler) window.removeEventListener('resize', resizeHandler);
    document.body.style.overflow = '';
});
</script>

<template>
    <div class="min-h-screen bg-background text-foreground flex flex-col antialiased selection:bg-primary selection:text-primary-foreground">
        <!-- ==================================================================== -->
        <!-- SIDEBAR OVERLAY (MOBILE & TABLET KECIL)                               -->
        <!-- ==================================================================== -->
        <Transition name="overlay">
            <div
                v-if="sidebarOpen"
                class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-40 lg:hidden"
                @click="closeSidebar"
            />
        </Transition>

        <!-- ==================================================================== -->
        <!-- SIDEBAR ADMIN (RESPONSIF - FIXED DESKTOP, DRAWER MOBILE)             -->
        <!-- ==================================================================== -->
        <aside
            :class="[
                'fixed top-0 left-0 h-full w-[280px] sidebar-modern text-white z-50 overflow-hidden flex flex-col shadow-2xl transition-transform duration-300 ease-in-out border-r border-white/10',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
            ]"
            aria-label="Menu navigasi administrator"
        >
            <!-- Brand Platform -->
            <div class="relative flex items-center justify-between gap-3 px-6 py-5 border-b border-white/10 flex-shrink-0">
                <Link :href="safeRoute('admin.dashboard')" class="flex items-center gap-3.5 min-w-0 group">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 border border-white/20 flex items-center justify-center flex-shrink-0 shadow-md shadow-indigo-950/40 group-hover:scale-105 transition-transform duration-200">
                        <Icon icon="mdi:shield-crown-outline" class="w-6 h-6 text-white" />
                    </div>
                    <div class="min-w-0">
                        <span class="text-base font-extrabold text-white block leading-tight tracking-tight group-hover:text-blue-200 transition-colors">PR KELAS KHUSUS</span>
                        <span class="text-[11px] text-blue-300/80 font-semibold">Panel Administrator</span>
                    </div>
                </Link>
                <button
                    @click="closeSidebar"
                    class="lg:hidden p-1.5 rounded-xl text-white/60 hover:text-white hover:bg-white/10 transition"
                    aria-label="Tutup navigasi"
                >
                    <Icon icon="mdi:close" class="w-5 h-5" />
                </button>
            </div>

            <!-- Profile Ringkas Admin di Sidebar -->
            <div class="relative mx-4 mt-4 flex items-center gap-3 rounded-2xl border border-white/15 bg-white/10 p-3.5 backdrop-blur-md">
                <div class="relative w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-indigo-600 border border-white/25 flex items-center justify-center text-white font-extrabold text-sm flex-shrink-0 overflow-hidden shadow-sm">
                    <img v-if="profilePhotoUrl" :src="profilePhotoUrl" :alt="user?.name" class="w-full h-full object-cover" />
                    <span v-else>{{ (user?.name || 'A').charAt(0).toUpperCase() }}</span>
                    <span class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full border-2 border-slate-900 bg-emerald-400"></span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold text-white truncate leading-tight">{{ user?.name || 'Administrator' }}</p>
                    <p class="text-[11px] text-blue-200/80 truncate mt-0.5">{{ user?.email }}</p>
                </div>
            </div>

            <!-- Navigasi Menu Admin -->
            <nav class="relative px-4 py-5 flex-1 min-h-0 overflow-y-auto sidebar-scroll space-y-5">
                <div v-for="group in navItems" :key="group.label" class="space-y-1.5">
                    <p class="text-[10px] uppercase tracking-widest text-white/40 font-extrabold px-3">{{ group.label }}</p>
                    <Link
                        v-for="item in group.items"
                        :key="item.href"
                        :href="item.href"
                        @click="closeSidebar"
                        :class="[
                            'flex items-center gap-3.5 px-3.5 py-2.5 rounded-xl transition-all duration-200 group text-sm',
                            isCurrentRoute(item.route)
                                ? 'bg-gradient-to-r from-white/20 to-white/10 text-white font-bold shadow-sm border border-white/20'
                                : 'text-white/75 hover:bg-white/10 hover:text-white'
                        ]"
                    >
                        <div
                            :class="[
                                'w-8 h-8 rounded-lg flex items-center justify-center text-lg flex-shrink-0 transition-all duration-200',
                                isCurrentRoute(item.route)
                                    ? 'bg-white text-blue-700 shadow-md scale-105'
                                    : 'bg-white/10 text-white group-hover:bg-white/20'
                            ]"
                        >
                            <Icon :icon="isCurrentRoute(item.route) ? item.activeIcon : item.icon" class="w-4 h-4" />
                        </div>
                        <span class="truncate">{{ item.label }}</span>
                        <span v-if="isCurrentRoute(item.route)" class="ml-auto h-2 w-2 rounded-full bg-cyan-300"></span>
                    </Link>
                </div>
            </nav>

            <!-- Sidebar Footer -->
            <div class="relative px-4 py-4 border-t border-white/10 flex-shrink-0 bg-black/20">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold text-white/80">PR Smart System</p>
                        <p class="text-[11px] text-white/50">v2.0 • Administrator</p>
                    </div>
                    <Link
                        :href="safeRoute('logout')"
                        method="post"
                        as="button"
                        class="flex items-center gap-1.5 rounded-xl border border-rose-500/30 bg-rose-500/15 px-3 py-2 text-xs font-bold text-rose-200 transition-colors hover:bg-rose-500 hover:text-white"
                        title="Keluar dari akun admin"
                    >
                        <Icon icon="mdi:logout" class="w-4 h-4" />
                        <span>Keluar</span>
                    </Link>
                </div>
            </div>
        </aside>

        <!-- ==================================================================== -->
        <!-- AREA UTAMA KONTEN (LEBAR FULL TANPA max-w-7xl)                       -->
        <!-- ==================================================================== -->
        <div class="lg:pl-[280px] min-h-screen flex flex-col flex-1">
            <!-- Top Header Admin (Lebar Full) -->
            <header class="sticky top-0 z-30 w-full border-b border-border/70 bg-card/85 backdrop-blur-xl shadow-xs transition-all duration-300">
                <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 h-16">
                    <!-- Sisi Kiri: Hamburger (Mobile) + Judul Halaman -->
                    <div class="flex items-center gap-3.5 min-w-0">
                        <button
                            @click="openSidebar"
                            class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-xl text-muted-foreground hover:bg-accent hover:text-foreground transition border border-border/70 shadow-xs"
                            aria-label="Buka navigasi administrator"
                        >
                            <Icon icon="mdi:menu" class="w-5 h-5" />
                        </button>
                        <div class="min-w-0">
                            <h1 class="text-base sm:text-lg font-extrabold tracking-tight text-foreground truncate">
                                <slot name="header-title" />
                            </h1>
                            <p class="text-xs text-muted-foreground hidden sm:block truncate mt-0.5">
                                <slot name="header-sub" />
                            </p>
                        </div>
                    </div>

                    <!-- Sisi Kanan: Tanggal + Dropdown Akun Admin -->
                    <div class="flex items-center gap-3">
                        <!-- Date Pill Desktop -->
                        <div class="hidden md:flex items-center gap-2 text-xs font-semibold text-muted-foreground bg-muted/50 border border-border/70 px-3.5 py-1.5 rounded-full shadow-2xs">
                            <Icon icon="mdi:calendar-month-outline" class="w-3.5 h-3.5 text-primary" />
                            <span>{{ currentDate }}</span>
                        </div>

                        <!-- Profile Dropdown -->
                        <div class="relative" id="profileDropdownWrap">
                            <button
                                @click="toggleProfileDropdown"
                                class="inline-flex items-center gap-2.5 hover:bg-accent/70 rounded-xl p-1.5 sm:px-3 transition border border-border/70 shadow-xs bg-card"
                            >
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary to-indigo-600 text-white font-extrabold text-xs flex items-center justify-center overflow-hidden shadow-2xs">
                                    <img v-if="profilePhotoUrl" :src="profilePhotoUrl" :alt="user?.name" class="w-full h-full object-cover" />
                                    <span v-else>{{ (user?.name || 'A').charAt(0).toUpperCase() }}</span>
                                </div>
                                <div class="hidden sm:block text-left max-w-[130px]">
                                    <p class="text-xs font-bold text-foreground truncate leading-tight">{{ user?.name || 'Admin' }}</p>
                                    <p class="text-[10px] text-primary font-semibold">Administrator</p>
                                </div>
                                <Icon icon="mdi:chevron-down" class="hidden sm:block w-3.5 h-3.5 text-muted-foreground transition-transform duration-200" :class="{ 'rotate-180': profileDropdownOpen }" />
                            </button>

                            <Transition name="dropdown">
                                <div v-if="profileDropdownOpen" class="absolute right-0 mt-2 w-60 bg-card text-card-foreground rounded-2xl shadow-2xl border border-border/80 py-2 z-50">
                                    <div class="px-4 py-2.5 border-b border-border/60">
                                        <p class="text-xs font-extrabold text-foreground truncate">{{ user?.name }}</p>
                                        <p class="text-[11px] text-muted-foreground truncate mt-0.5">{{ user?.email }}</p>
                                    </div>
                                    <div class="py-1.5 px-1.5 space-y-0.5">
                                        <Link :href="safeRoute('admin.dashboard')" @click="profileDropdownOpen = false" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-foreground hover:bg-muted rounded-xl transition">
                                            <Icon icon="mdi:view-dashboard-outline" class="w-4 h-4 text-primary" />
                                            Dasbor Admin
                                        </Link>
                                        <Link :href="safeRoute('admin.profile.edit')" @click="profileDropdownOpen = false" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-foreground hover:bg-muted rounded-xl transition">
                                            <Icon icon="mdi:account-outline" class="w-4 h-4 text-muted-foreground" />
                                            Profil Saya
                                        </Link>
                                        <Link :href="safeRoute('admin.profile.change-password')" @click="profileDropdownOpen = false" class="flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-foreground hover:bg-muted rounded-xl transition">
                                            <Icon icon="mdi:lock-reset" class="w-4 h-4 text-muted-foreground" />
                                            Ubah Password
                                        </Link>
                                    </div>
                                    <div class="border-t border-border/60 my-1"></div>
                                    <div class="px-1.5">
                                        <Link :href="safeRoute('logout')" method="post" as="button" class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-xl transition text-left">
                                            <Icon icon="mdi:logout" class="w-4 h-4" />
                                            Keluar dari Akun
                                        </Link>
                                    </div>
                                </div>
                            </Transition>
                        </div>
                    </div>
                </div>
            </header>

            <!-- ================================================================ -->
            <!-- ISI KONTEN ADMIN (LEBAR FULL MODEREN, RESPONSIVE SEMUA DEVICE)   -->
            <!-- ================================================================ -->
            <main class="flex-1 w-full px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 py-6 sm:py-8 pb-28 lg:pb-10">
                <FlashMessage />
                <div class="w-full anim-fade-in">
                    <slot />
                </div>
            </main>

            <!-- ================================================================ -->
            <!-- MOBILE BOTTOM NAVIGATION (ADMIN)                                 -->
            <!-- ================================================================ -->
            <nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-card/95 backdrop-blur-xl border-t border-border/80 z-30 px-2 py-1.5 safe-area-bottom shadow-[0_-8px_25px_rgba(0,0,0,0.06)]" aria-label="Navigasi bawah admin">
                <div class="flex items-center justify-around">
                    <Link
                        v-for="item in mobileNavItems"
                        :key="item.href"
                        :href="item.href"
                        :class="[
                            'mobile-nav-item relative flex-1 text-center py-2 px-1',
                            isCurrentRoute(item.route) ? 'active' : ''
                        ]"
                    >
                        <div v-if="isCurrentRoute(item.route)" class="absolute -top-1.5 left-1/2 -translate-x-1/2 w-6 h-1 bg-primary rounded-full shadow-xs"></div>
                        <Icon :icon="item.icon" :class="['w-5 h-5 transition-transform', isCurrentRoute(item.route) ? 'scale-110 text-primary' : 'text-muted-foreground']" />
                        <span :class="['text-[11px] leading-tight mt-0.5 block truncate', isCurrentRoute(item.route) ? 'font-bold text-primary' : 'font-medium text-muted-foreground']">{{ item.label }}</span>
                    </Link>
                    <button @click="openSidebar" class="mobile-nav-item relative flex-1 text-center py-2 px-1" aria-label="Buka menu lengkap">
                        <Icon icon="mdi:menu" class="w-5 h-5 text-muted-foreground" />
                        <span class="text-[11px] font-medium leading-tight mt-0.5 text-muted-foreground block truncate">Menu</span>
                    </button>
                </div>
            </nav>
        </div>
    </div>
</template>

<style scoped>
.overlay-enter-active, .overlay-leave-active { transition: opacity 0.25s ease; }
.overlay-enter-from, .overlay-leave-to { opacity: 0; }

.dropdown-enter-active { transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1); }
.dropdown-leave-active { transition: all 0.15s ease-in; }
.dropdown-enter-from { opacity: 0; transform: scale(0.96) translateY(-6px); }
.dropdown-leave-to { opacity: 0; transform: scale(0.96) translateY(-6px); }
</style>
