<script setup>
import { ref, inject, computed, onMounted, onUnmounted, useSlots } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import FlashMessage from '@/Components/shared/FlashMessage.vue';

const route = inject('route');
const page = usePage();
const slots = useSlots();
const user = computed(() => page.props.auth?.user);

const userDropdownOpen = ref(false);
const mobileMenuOpen = ref(false);
const scrolled = ref(false);

const profilePhotoUrl = computed(() => {
    if (!user.value?.profile_photo) return null;
    if (user.value.profile_photo.startsWith('http://') || user.value.profile_photo.startsWith('https://') || user.value.profile_photo.startsWith('//')) {
        return user.value.profile_photo;
    }
    return '/storage/' + user.value.profile_photo + '?v=' + encodeURIComponent(user.value.profile_photo);
});

const currentDate = new Date().toLocaleDateString('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
});

/** Nama lengkap siswa yang login */
const fullName = computed(() => user.value?.name || 'Siswa');

/** Subjudul identitas: bidang, lalu kelas */
const userSubtitle = computed(() => {
    const u = user.value || {};
    const kelas = u.student_class || u.kelas;
    if (u.bidang && kelas) return `${u.bidang} · Kelas ${kelas}`;
    if (u.bidang) return u.bidang;
    if (kelas) return `Kelas ${kelas}`;
    return 'Siswa Kelas Khusus';
});

/** Judul cadangan bila halaman tidak mengirim slot header-title */
const fallbackTitle = computed(() => {
    try {
        if (route().current('user.packages.*')) return 'PR Siswa Kelas Khusus';
        if (route().current('user.leaderboard')) return 'Peringkat';
        if (route().current('user.profile.*')) return 'Profil & Akun';
        if (route().current('user.dashboard')) return 'Beranda';
        return 'PR Kelas Khusus';
    } catch (e) {
        return 'PR Kelas Khusus';
    }
});

function isActive(match) {
    if (!match) return false;
    try {
        return route().current(match);
    } catch (e) {
        return false;
    }
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

// Navigasi Utama Siswa (Desktop Sidebar)
const desktopNavItems = [
    {
        section: 'Menu Belajar',
        items: [
            {
                href: safeRoute('user.dashboard'),
                label: 'Beranda',
                icon: 'mdi:view-dashboard-outline',
                activeIcon: 'mdi:view-dashboard',
                match: 'user.dashboard',
            },
            {
                href: safeRoute('user.packages.index'),
                label: 'PR Siswa Kelas Khusus',
                icon: 'mdi:book-open-page-variant-outline',
                activeIcon: 'mdi:book-open-page-variant',
                match: 'user.packages.*',
            },
            {
                href: safeRoute('user.leaderboard'),
                label: 'Peringkat',
                icon: 'mdi:trophy-outline',
                activeIcon: 'mdi:trophy',
                match: 'user.leaderboard',
            },
        ],
    },
    {
        section: 'Akun & Pengaturan',
        items: [
            {
                href: safeRoute('user.profile.edit'),
                label: 'Profil Saya',
                icon: 'mdi:account-outline',
                activeIcon: 'mdi:account',
                match: 'user.profile.edit',
            },
            {
                href: safeRoute('user.profile.change-password'),
                label: 'Ubah Password',
                icon: 'mdi:lock-reset',
                activeIcon: 'mdi:lock-reset',
                match: 'user.profile.change-password',
            },
        ],
    },
];

// Navigasi Bawah Handphone (Mobile Bottom Navigation Bar)
const mobileBottomTabs = [
    {
        href: safeRoute('user.dashboard'),
        label: 'Beranda',
        icon: 'mdi:view-dashboard-outline',
        activeIcon: 'mdi:view-dashboard',
        match: 'user.dashboard',
    },
    {
        href: safeRoute('user.packages.index'),
        label: 'PR Siswa',
        icon: 'mdi:book-open-page-variant-outline',
        activeIcon: 'mdi:book-open-page-variant',
        match: 'user.packages.*',
    },
    {
        href: safeRoute('user.leaderboard'),
        label: 'Peringkat',
        icon: 'mdi:trophy-outline',
        activeIcon: 'mdi:trophy',
        match: 'user.leaderboard',
    },
    {
        href: safeRoute('user.profile.edit'),
        label: 'Akun',
        icon: 'mdi:account-circle-outline',
        activeIcon: 'mdi:account-circle',
        match: 'user.profile.*',
    },
];

function toggleUserDropdown() {
    userDropdownOpen.value = !userDropdownOpen.value;
}

function toggleMobileMenu() {
    mobileMenuOpen.value = !mobileMenuOpen.value;
}

function closeAllDropdowns() {
    userDropdownOpen.value = false;
    mobileMenuOpen.value = false;
}

let clickHandler;
let keyHandler;
let scrollHandler;

function handleScroll() {
    scrolled.value = window.scrollY > 8;
}

onMounted(() => {
    handleScroll();

    clickHandler = (e) => {
        if (!e.target.closest('#desktopUserDropdown') && !e.target.closest('#mobileUserDropdown')) {
            closeAllDropdowns();
        }
    };

    keyHandler = (e) => {
        if (e.key === 'Escape') {
            closeAllDropdowns();
        }
    };

    document.addEventListener('click', clickHandler);
    document.addEventListener('keydown', keyHandler);
    window.addEventListener('scroll', handleScroll, { passive: true });
});

onUnmounted(() => {
    if (clickHandler) document.removeEventListener('click', clickHandler);
    if (keyHandler) document.removeEventListener('keydown', keyHandler);
    if (scrollHandler) window.removeEventListener('scroll', scrollHandler);
});
</script>

<template>
    <div class="min-h-screen bg-background text-foreground antialiased selection:bg-primary selection:text-primary-foreground flex flex-col">
        <!-- ==================================================================== -->
        <!-- SIDEBAR SISWA (HANYA DITAMPILKAN DI DESKTOP - lg: ke atas)           -->
        <!-- ==================================================================== -->
        <aside
            class="hidden lg:flex fixed top-0 left-0 z-40 h-full w-[280px] flex-col overflow-hidden text-white sidebar-modern border-r border-white/10 shadow-2xl transition-all duration-300"
            aria-label="Menu navigasi siswa desktop"
        >
            <!-- Brand Logo & Platform Title -->
            <div class="relative flex flex-shrink-0 items-center gap-3.5 border-b border-white/10 px-6 py-5">
                <Link :href="safeRoute('user.dashboard')" class="group flex min-w-0 items-center gap-3">
                    <span class="relative flex h-11 w-11 flex-shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br from-blue-500 via-indigo-600 to-violet-600 text-white shadow-md shadow-indigo-900/30 ring-2 ring-white/20 transition-transform duration-300 group-hover:scale-105">
                        <Icon icon="mdi:school-outline" class="h-6 w-6 text-white" />
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate text-base font-extrabold tracking-tight text-white group-hover:text-blue-200 transition-colors">PR KELAS KHUSUS</span>
                        <span class="block truncate text-[11px] font-semibold text-blue-300/80">KPM Smart Learning</span>
                    </span>
                </Link>
            </div>

            <!-- Profile Info Card di Sidebar -->
            <div class="relative mx-4 mt-4 flex items-center gap-3 rounded-2xl border border-white/15 bg-white/10 p-3.5 backdrop-blur-md">
                <div class="relative flex h-11 w-11 flex-shrink-0 items-center justify-center overflow-hidden rounded-xl border border-white/25 bg-gradient-to-br from-indigo-500 to-blue-600 text-sm font-black text-white shadow-sm">
                    <img v-if="profilePhotoUrl" :src="profilePhotoUrl" :alt="fullName" class="h-full w-full object-cover" />
                    <span v-else>{{ fullName.charAt(0).toUpperCase() }}</span>
                    <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-slate-900 bg-emerald-400"></span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-xs font-bold leading-tight text-white">{{ fullName }}</p>
                    <p class="mt-0.5 truncate text-[11px] font-medium text-blue-200/90">{{ userSubtitle }}</p>
                    <span v-if="user?.level" class="mt-1 inline-block rounded-md bg-white/20 px-1.5 py-0.5 text-[10px] font-bold text-white">
                        Level {{ user.level }}
                    </span>
                </div>
            </div>

            <!-- Navigasi Sidebar Desktop -->
            <nav class="sidebar-scroll relative min-h-0 flex-1 space-y-5 overflow-y-auto px-4 py-5">
                <div v-for="section in desktopNavItems" :key="section.section" class="space-y-1.5">
                    <p class="px-3 text-[10px] font-extrabold uppercase tracking-widest text-white/40">{{ section.section }}</p>
                    <Link
                        v-for="item in section.items"
                        :key="item.href"
                        :href="item.href"
                        :class="[
                            'group flex items-center gap-3.5 rounded-xl px-3.5 py-2.5 text-sm transition-all duration-200',
                            isActive(item.match)
                                ? 'bg-gradient-to-r from-white/20 to-white/10 font-bold text-white shadow-sm border border-white/20'
                                : 'text-white/75 hover:bg-white/10 hover:text-white',
                        ]"
                    >
                        <span
                            :class="[
                                'flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg transition-all duration-200',
                                isActive(item.match)
                                    ? 'bg-white text-blue-700 shadow-md scale-105'
                                    : 'bg-white/10 text-white group-hover:bg-white/20',
                            ]"
                        >
                            <Icon :icon="isActive(item.match) ? item.activeIcon : item.icon" class="h-4 w-4" />
                        </span>
                        <span class="truncate">{{ item.label }}</span>
                        <span v-if="isActive(item.match)" class="ml-auto h-2 w-2 rounded-full bg-cyan-300"></span>
                    </Link>
                </div>
            </nav>

            <!-- Footer Sidebar Desktop -->
            <div class="relative flex-shrink-0 border-t border-white/10 bg-black/20 p-4">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-semibold text-white/80">Tahun Ajaran Aktif</p>
                        <p class="truncate text-[11px] text-white/50">Kelas Khusus KPM</p>
                    </div>
                    <Link
                        :href="safeRoute('logout')"
                        method="post"
                        as="button"
                        class="flex items-center gap-1.5 rounded-xl border border-rose-500/30 bg-rose-500/15 px-3 py-2 text-xs font-bold text-rose-200 transition-colors hover:bg-rose-500 hover:text-white"
                        title="Keluar dari akun"
                    >
                        <Icon icon="mdi:logout" class="h-4 w-4" />
                        <span>Keluar</span>
                    </Link>
                </div>
            </div>
        </aside>

        <!-- ==================================================================== -->
        <!-- AREA UTAMA (HEADER + KONTEN LEBAR FULL)                                -->
        <!-- ==================================================================== -->
        <div class="flex min-h-screen flex-1 flex-col lg:pl-[280px]">
            <!-- ================================================================ -->
            <!-- HEADER MOBILE (TAMPIL HANYA DI HANDPHONE & TABLET KECIL)        -->
            <!-- Tanpa tombol sidebar burger, fokus identitas siswa & quick menu -->
            <!-- ================================================================ -->
            <header
                class="sticky top-0 z-30 w-full border-b border-border/70 bg-card/90 backdrop-blur-xl transition-all duration-200 lg:hidden shadow-xs"
            >
                <div class="flex h-16 items-center justify-between px-4 sm:px-6">
                    <!-- Sisi Kiri: Avatar & Info Siswa -->
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="relative flex h-10 w-10 flex-shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gradient-to-br from-primary to-indigo-600 text-sm font-extrabold text-white shadow-sm ring-1 ring-border">
                            <img v-if="profilePhotoUrl" :src="profilePhotoUrl" :alt="fullName" class="h-full w-full object-cover" />
                            <span v-else>{{ fullName.charAt(0).toUpperCase() }}</span>
                            <span class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full border-2 border-card bg-emerald-500"></span>
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-extrabold leading-tight text-foreground">{{ fullName }}</p>
                            <p class="mt-0.5 truncate text-[11px] font-semibold text-primary">{{ userSubtitle }}</p>
                        </div>
                    </div>

                    <!-- Sisi Kanan: Quick Menu Profil & Keluar -->
                    <div class="relative" id="mobileUserDropdown">
                        <button
                            @click="toggleMobileMenu"
                            class="flex h-9 w-9 items-center justify-center rounded-xl border border-border/80 bg-muted/60 text-foreground transition-all hover:bg-muted active:scale-95"
                            aria-label="Menu akun"
                        >
                            <Icon icon="mdi:chevron-down" class="h-5 w-5 transition-transform duration-200" :class="{ 'rotate-180': mobileMenuOpen }" />
                        </button>

                        <Transition name="dropdown">
                            <div
                                v-if="mobileMenuOpen"
                                class="absolute right-0 z-50 mt-2 w-64 overflow-hidden rounded-2xl border border-border/80 bg-card p-2 shadow-2xl"
                            >
                                <div class="border-b border-border/60 px-3 py-2.5">
                                    <p class="truncate text-xs font-extrabold text-foreground">{{ fullName }}</p>
                                    <p class="mt-0.5 truncate text-[11px] text-muted-foreground">{{ user?.email || user?.username }}</p>
                                </div>
                                <div class="space-y-1 py-1.5">
                                    <Link
                                        :href="safeRoute('user.profile.edit')"
                                        @click="mobileMenuOpen = false"
                                        class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold text-foreground transition hover:bg-muted"
                                    >
                                        <Icon icon="mdi:account-outline" class="h-4 w-4 text-primary" />
                                        Profil Siswa
                                    </Link>
                                    <Link
                                        :href="safeRoute('user.profile.change-password')"
                                        @click="mobileMenuOpen = false"
                                        class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold text-foreground transition hover:bg-muted"
                                    >
                                        <Icon icon="mdi:lock-reset" class="h-4 w-4 text-muted-foreground" />
                                        Ubah Password
                                    </Link>
                                </div>
                                <div class="border-t border-border/60 pt-1">
                                    <Link
                                        :href="safeRoute('logout')"
                                        method="post"
                                        as="button"
                                        class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-left text-xs font-bold text-rose-600 transition hover:bg-rose-50 dark:hover:bg-rose-950/30"
                                    >
                                        <Icon icon="mdi:logout" class="h-4 w-4" />
                                        Keluar dari Akun
                                    </Link>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </header>

            <!-- ================================================================ -->
            <!-- HEADER DESKTOP (TAMPIL HANYA DI DESKTOP - lg: ke atas)          -->
            <!-- Lebar Full, sticky, judul dinamis + tanggal + profile dropdown   -->
            <!-- ================================================================ -->
            <header
                class="hidden lg:block sticky top-0 z-30 w-full border-b border-border/70 bg-card/85 backdrop-blur-xl transition-all duration-300 shadow-xs"
            >
                <div class="flex h-16 items-center justify-between px-6 lg:px-8 xl:px-10 2xl:px-12">
                    <!-- Kiri: Judul Halaman & Subjudul -->
                    <div class="min-w-0">
                        <h1 v-if="slots['header-title']" class="truncate text-lg font-extrabold tracking-tight text-foreground">
                            <slot name="header-title" />
                        </h1>
                        <h1 v-else class="truncate text-lg font-extrabold tracking-tight text-foreground">
                            {{ fallbackTitle }}
                        </h1>
                        <p v-if="slots['header-sub']" class="mt-0.5 truncate text-xs text-muted-foreground">
                            <slot name="header-sub" />
                        </p>
                    </div>

                    <!-- Kanan: Tanggal + Status Chip + Profile Dropdown -->
                    <div class="flex flex-shrink-0 items-center gap-3">
                        <!-- Tanggal Hari Ini -->
                        <div class="hidden items-center gap-2 rounded-full border border-border/70 bg-muted/50 px-3.5 py-1.5 text-xs font-semibold text-muted-foreground xl:flex">
                            <Icon icon="mdi:calendar-month-outline" class="h-3.5 w-3.5 text-primary" />
                            <span>{{ currentDate }}</span>
                        </div>

                        <!-- Chip Identitas -->
                        <div class="hidden sm:inline-flex items-center gap-1.5 rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-bold text-primary">
                            <span>{{ userSubtitle }}</span>
                        </div>

                        <!-- Dropdown Akun Desktop -->
                        <div class="relative" id="desktopUserDropdown">
                            <button
                                @click="toggleUserDropdown"
                                class="flex items-center gap-2.5 rounded-xl border border-border/70 bg-card p-1.5 pr-3 transition-all hover:border-primary/40 hover:bg-accent/60 shadow-xs"
                            >
                                <span class="flex h-8 w-8 items-center justify-center overflow-hidden rounded-lg bg-gradient-to-br from-primary to-indigo-600 text-xs font-extrabold text-white shadow-2xs">
                                    <img v-if="profilePhotoUrl" :src="profilePhotoUrl" :alt="fullName" class="h-full w-full object-cover" />
                                    <span v-else>{{ fullName.charAt(0).toUpperCase() }}</span>
                                </span>
                                <span class="text-left">
                                    <span class="block max-w-[130px] truncate text-xs font-bold leading-tight text-foreground">{{ fullName }}</span>
                                    <span class="block truncate text-[10px] font-semibold text-muted-foreground">Siswa</span>
                                </span>
                                <Icon icon="mdi:chevron-down" class="h-3.5 w-3.5 text-muted-foreground transition-transform duration-200" :class="{ 'rotate-180': userDropdownOpen }" />
                            </button>

                            <Transition name="dropdown">
                                <div
                                    v-if="userDropdownOpen"
                                    class="absolute right-0 z-50 mt-2 w-60 overflow-hidden rounded-2xl border border-border/80 bg-card py-2 shadow-2xl"
                                >
                                    <div class="border-b border-border/60 px-4 py-2.5">
                                        <p class="truncate text-xs font-extrabold text-foreground">{{ fullName }}</p>
                                        <p class="mt-0.5 truncate text-[11px] text-muted-foreground">{{ user?.email || user?.username }}</p>
                                    </div>
                                    <div class="space-y-0.5 p-1.5">
                                        <Link
                                            :href="safeRoute('user.profile.edit')"
                                            @click="userDropdownOpen = false"
                                            class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold text-foreground transition hover:bg-muted"
                                        >
                                            <Icon icon="mdi:account-outline" class="h-4 w-4 text-primary" />
                                            Profil Saya
                                        </Link>
                                        <Link
                                            :href="safeRoute('user.profile.change-password')"
                                            @click="userDropdownOpen = false"
                                            class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold text-foreground transition hover:bg-muted"
                                        >
                                            <Icon icon="mdi:lock-reset" class="h-4 w-4 text-muted-foreground" />
                                            Ubah Password
                                        </Link>
                                    </div>
                                    <div class="my-1 border-t border-border/60"></div>
                                    <div class="p-1.5">
                                        <Link
                                            :href="safeRoute('logout')"
                                            method="post"
                                            as="button"
                                            class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-left text-xs font-bold text-rose-600 transition hover:bg-rose-50 dark:hover:bg-rose-950/30"
                                        >
                                            <Icon icon="mdi:logout" class="h-4 w-4" />
                                            Keluar
                                        </Link>
                                    </div>
                                </div>
                            </Transition>
                        </div>
                    </div>
                </div>
            </header>

            <!-- ================================================================ -->
            <!-- KONTEN UTAMA SISWA (LEBAR FULL, RESPONSIVE DI SEMUA DEVICE)       -->
            <!-- ================================================================ -->
            <main class="w-full flex-1 pb-28 lg:pb-10">
                <div class="w-full px-4 sm:px-6 lg:px-8 xl:px-10 2xl:px-12 py-5 sm:py-7">
                    <!-- Aksi header jika ada -->
                    <div v-if="slots['header-actions']" class="mb-5 flex flex-wrap items-center justify-end gap-2.5 sm:mb-6">
                        <slot name="header-actions" />
                    </div>

                    <FlashMessage />
                    <slot />
                </div>
            </main>

            <!-- ================================================================ -->
            <!-- MENU DI BAWAH (HANYA DITAMPILKAN DI HANDPHONE - lg:hidden)       -->
            <!-- Modern Mobile Bottom Navigation Bar: Beranda, PR, Peringkat, Akun -->
            <!-- ================================================================ -->
            <nav
                class="fixed inset-x-0 bottom-0 z-40 border-t border-border/70 bg-card/95 backdrop-blur-xl px-2 py-1.5 shadow-[0_-8px_25px_rgba(0,0,0,0.06)] lg:hidden"
                aria-label="Navigasi bawah siswa"
            >
                <div class="mx-auto flex max-w-lg items-center justify-around gap-1">
                    <Link
                        v-for="tab in mobileBottomTabs"
                        :key="tab.href"
                        :href="tab.href"
                        :aria-current="isActive(tab.match) ? 'page' : undefined"
                        :class="[
                            'mobile-nav-item relative flex-1 text-center py-2 px-1 transition-all duration-200',
                            isActive(tab.match) ? 'active' : '',
                        ]"
                    >
                        <!-- Active Glowing Indicator Pill -->
                        <span
                            v-if="isActive(tab.match)"
                            class="absolute -top-1.5 left-1/2 h-1 w-6 -translate-x-1/2 rounded-full bg-primary shadow-xs"
                        />
                        <Icon
                            :icon="isActive(tab.match) ? tab.activeIcon : tab.icon"
                            :class="[
                                'h-5 w-5 transition-transform duration-200',
                                isActive(tab.match) ? 'scale-110 text-primary' : 'text-muted-foreground',
                            ]"
                        />
                        <span
                            :class="[
                                'text-[11px] leading-tight mt-0.5 block truncate',
                                isActive(tab.match) ? 'font-bold text-primary' : 'font-medium text-muted-foreground',
                            ]"
                        >
                            {{ tab.label }}
                        </span>
                    </Link>
                </div>
            </nav>
        </div>
    </div>
</template>

<style scoped>
.dropdown-enter-active {
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.dropdown-leave-active {
    transition: all 0.15s ease-in;
}
.dropdown-enter-from {
    opacity: 0;
    transform: scale(0.96) translateY(-6px);
}
.dropdown-leave-to {
    opacity: 0;
    transform: scale(0.96) translateY(-6px);
}
</style>
