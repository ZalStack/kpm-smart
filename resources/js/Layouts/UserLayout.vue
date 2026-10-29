<script setup>
import { ref, inject, computed, onMounted, onUnmounted, useSlots } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import FlashMessage from '@/Components/shared/FlashMessage.vue';

const route = inject('route');
const page = usePage();
const slots = useSlots();
const user = computed(() => page.props.auth?.user);

const sidebarOpen = ref(false);
const userDropdownOpen = ref(false);
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

/** Nama lengkap siswa yang login (ditampilkan di sidebar & header mobile). */
const fullName = computed(() => user.value?.name || 'Siswa');

/** Subjudul identitas: bidang, lalu kelas. */
const userSubtitle = computed(() => {
    const u = user.value || {};
    const kelas = u.student_class || u.kelas;
    if (u.bidang && kelas) return `${u.bidang} · Kelas ${kelas}`;
    if (u.bidang) return u.bidang;
    if (kelas) return `Kelas ${kelas}`;
    return 'Siswa Kelas Khusus';
});

/** Judul cadangan bila halaman tidak mengirim slot header-title. */
const fallbackTitle = computed(() => {
    try {
        const name = route().current('user.packages.*')
            ? 'PR Siswa Kelas Khusus'
            : route().current('user.leaderboard')
                ? 'Peringkat'
                : route().current('user.dashboard')
                    ? 'Beranda'
                    : 'PR KK';
        return name;
    } catch (e) {
        return 'PR KK';
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

// Navigasi Siswa (Beranda, PR Siswa Kelas Khusus, Peringkat)
const navItems = [
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
        icon: 'mdi:book-open-variant',
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
];

// Navigasi Bawah (Mobile)
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
        label: 'PR',
        icon: 'mdi:book-open-variant',
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
];

function openSidebar() {
    sidebarOpen.value = true;
    document.body.style.overflow = 'hidden';
}

function closeSidebar() {
    sidebarOpen.value = false;
    document.body.style.overflow = '';
}

function toggleUserDropdown() {
    userDropdownOpen.value = !userDropdownOpen.value;
}

function closeAllDropdowns() {
    userDropdownOpen.value = false;
}

let clickHandler;
let keyHandler;
let resizeHandler;
let scrollHandler;

function handleScroll() {
    scrolled.value = window.scrollY > 6;
}

onMounted(() => {
    handleScroll();

    clickHandler = (e) => {
        if (!e.target.closest('#userDropdown')) userDropdownOpen.value = false;
    };

    keyHandler = (e) => {
        if (e.key === 'Escape') {
            closeAllDropdowns();
            closeSidebar();
        }
    };

    resizeHandler = () => {
        if (window.innerWidth >= 1024) closeSidebar();
    };

    document.addEventListener('click', clickHandler);
    document.addEventListener('keydown', keyHandler);
    window.addEventListener('resize', resizeHandler);
    window.addEventListener('scroll', handleScroll, { passive: true });
});

onUnmounted(() => {
    if (clickHandler) document.removeEventListener('click', clickHandler);
    if (keyHandler) document.removeEventListener('keydown', keyHandler);
    if (resizeHandler) window.removeEventListener('resize', resizeHandler);
    if (scrollHandler) window.removeEventListener('scroll', scrollHandler);
    document.body.style.overflow = '';
});
</script>

<template>
    <div class="min-h-screen flex flex-col bg-background text-foreground antialiased selection:bg-primary selection:text-primary-foreground">
        <!-- ==================================================================== -->
        <!-- OVERLAY SIDEBAR (MOBILE)                                              -->
        <!-- ==================================================================== -->
        <Transition name="overlay">
            <div
                v-if="sidebarOpen"
                class="fixed inset-0 z-40 bg-slate-900/55 backdrop-blur-xs lg:hidden"
                @click="closeSidebar"
            />
        </Transition>

        <!-- ==================================================================== -->
        <!-- SIDEBAR                                                               -->
        <!-- ==================================================================== -->
        <aside
            :class="[
                'sidebar-modern fixed top-0 left-0 z-50 flex h-full w-[270px] flex-col overflow-hidden text-white shadow-2xl transition-transform duration-300 ease-in-out',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
            ]"
            aria-label="Menu siswa"
        >
            <!-- Brand: Nama Lengkap Siswa -->
            <div class="relative flex flex-shrink-0 items-center justify-between gap-3 border-b border-white/10 px-5 py-5">
                <Link :href="safeRoute('user.dashboard')" class="group flex min-w-0 items-center gap-3">
                    <span class="relative flex h-10 w-10 flex-shrink-0 items-center justify-center overflow-hidden rounded-xl border border-white/20 bg-white/15 text-base font-extrabold text-white shadow-sm backdrop-blur transition-transform duration-200 group-hover:scale-105">
                        <img v-if="profilePhotoUrl" :src="profilePhotoUrl" :alt="fullName" class="h-full w-full object-cover" />
                        <span v-else>{{ fullName.charAt(0).toUpperCase() }}</span>
                        <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-slate-900 bg-emerald-500"></span>
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-bold leading-tight tracking-tight text-white">{{ fullName }}</span>
                        <span class="block truncate text-[11px] font-medium text-white/60">{{ userSubtitle }}</span>
                    </span>
                </Link>
                <button
                    @click="closeSidebar"
                    class="rounded-lg p-1.5 text-white/60 transition hover:bg-white/10 hover:text-white lg:hidden"
                    aria-label="Tutup menu"
                >
                    <Icon icon="mdi:close" class="h-5 w-5" />
                </button>
            </div>

            <!-- Navigasi -->
            <nav class="sidebar-scroll relative min-h-0 flex-1 space-y-6 overflow-y-auto px-3 py-4">
                <div class="space-y-1">
                    <p class="mb-2 px-3 text-[10px] font-bold uppercase tracking-widest text-white/40">Menu Siswa</p>
                    <Link
                        v-for="item in navItems"
                        :key="item.href"
                        :href="item.href"
                        @click="closeSidebar"
                        :class="[
                            'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition-all duration-200',
                            isActive(item.match)
                                ? 'bg-white/15 font-semibold text-white shadow-xs'
                                : 'text-white/70 hover:bg-white/10 hover:text-white',
                        ]"
                    >
                        <span
                            :class="[
                                'flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg transition-all duration-200',
                                isActive(item.match)
                                    ? 'scale-105 bg-white text-blue-700 shadow-xs'
                                    : 'bg-white/10 text-white group-hover:bg-white/20',
                            ]"
                        >
                            <Icon :icon="isActive(item.match) ? item.activeIcon : item.icon" class="h-4 w-4" />
                        </span>
                        <span class="truncate">{{ item.label }}</span>
                    </Link>
                </div>
            </nav>

            <!-- Info Akun di Sidebar -->
            <div class="relative flex-shrink-0 border-t border-white/10 bg-black/10 px-4 py-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center overflow-hidden rounded-xl border border-white/20 bg-white/20 text-sm font-bold text-white shadow-xs">
                        <img v-if="profilePhotoUrl" :src="profilePhotoUrl" :alt="fullName" class="h-full w-full object-cover" />
                        <span v-else>{{ fullName.charAt(0).toUpperCase() }}</span>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-bold leading-tight text-white">{{ fullName }}</p>
                        <p class="mt-0.5 truncate text-[11px] text-white/60">{{ user?.email || user?.username }}</p>
                    </div>
                    <Link
                        :href="safeRoute('logout')"
                        method="post"
                        as="button"
                        class="rounded-lg p-1.5 text-white/60 transition hover:bg-white/10 hover:text-rose-300"
                        title="Keluar"
                    >
                        <Icon icon="mdi:logout" class="h-4 w-4" />
                    </Link>
                </div>
            </div>
        </aside>

        <!-- ==================================================================== -->
        <!-- AREA KONTEN (HEADER + ISI TANPA FOOTER)                               -->
        <!-- ==================================================================== -->
        <div class="flex min-h-screen flex-1 flex-col lg:ml-[270px]">
            <!-- HEADER -->
            <header
                class="sticky top-0 z-30 w-full transition-all duration-300"
                :class="scrolled
                    ? 'border-b border-border/70 bg-card/85 shadow-sm backdrop-blur-xl'
                    : 'border-b border-border/60 bg-background/75 backdrop-blur-sm'"
            >
                <div class="page-shell-wide">
                    <div class="flex h-16 items-center justify-between gap-3 sm:gap-5">
                        <!-- Kiri: Tombol menu + nama lengkap (mobile) / judul halaman (desktop) -->
                        <div class="flex min-w-0 items-center gap-3">
                            <button
                                @click="openSidebar"
                                class="inline-flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl border border-border/60 text-muted-foreground transition hover:bg-accent hover:text-foreground lg:hidden"
                                aria-label="Buka navigasi"
                            >
                                <Icon icon="mdi:menu" class="h-5 w-5" />
                            </button>

                            <!-- Brand nama lengkap (mobile) -->
                            <div class="flex min-w-0 items-center gap-2.5 lg:hidden">
                                <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gradient-to-br from-primary to-indigo-700 text-sm font-bold text-white shadow-sm">
                                    <img v-if="profilePhotoUrl" :src="profilePhotoUrl" :alt="fullName" class="h-full w-full object-cover" />
                                    <span v-else>{{ fullName.charAt(0).toUpperCase() }}</span>
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-extrabold leading-tight text-foreground">{{ fullName }}</p>
                                    <p class="truncate text-[11px] font-semibold leading-tight text-primary">{{ userSubtitle }}</p>
                                </div>
                            </div>

                            <!-- Judul halaman (desktop) -->
                            <div class="hidden min-w-0 lg:block">
                                <h1
                                    v-if="slots['header-title']"
                                    class="truncate text-lg font-bold tracking-tight text-foreground"
                                >
                                    <slot name="header-title" />
                                </h1>
                                <h1 v-else class="truncate text-lg font-bold tracking-tight text-foreground">
                                    {{ fallbackTitle }}
                                </h1>
                                <p
                                    v-if="slots['header-sub']"
                                    class="mt-0.5 truncate text-xs text-muted-foreground"
                                >
                                    <slot name="header-sub" />
                                </p>
                            </div>
                        </div>

                        <!-- Kanan: Kontrol -->
                        <div class="flex flex-shrink-0 items-center gap-1.5 sm:gap-2">
                            <!-- Tanggal (desktop) -->
                            <div class="hidden items-center gap-2 rounded-full border border-border/60 bg-muted/40 px-3.5 py-1.5 text-xs font-semibold text-muted-foreground xl:flex">
                                <Icon icon="mdi:calendar-month-outline" class="h-3.5 w-3.5 text-primary" />
                                <span>{{ currentDate }}</span>
                            </div>



                            <!-- Menu Profil -->
                            <div v-if="user" class="relative" id="userDropdown">
                                <button
                                    @click="toggleUserDropdown"
                                    class="flex items-center gap-2 rounded-xl border border-border/60 bg-card/60 p-1 pr-1.5 transition-all hover:border-primary/30 hover:bg-accent/70 sm:pr-2.5"
                                >
                                    <span class="flex h-8 w-8 items-center justify-center overflow-hidden rounded-lg bg-gradient-to-br from-primary to-indigo-700 text-xs font-bold text-white shadow-2xs">
                                        <img v-if="profilePhotoUrl" :src="profilePhotoUrl" :alt="fullName" class="h-full w-full object-cover" />
                                        <span v-else>{{ fullName.charAt(0).toUpperCase() }}</span>
                                    </span>
                                    <span class="hidden text-left sm:block">
                                        <span class="block max-w-[140px] truncate text-xs font-bold leading-tight text-foreground">{{ fullName }}</span>
                                        <span class="block truncate text-[10px] font-semibold leading-tight text-primary">{{ userSubtitle }}</span>
                                    </span>
                                    <Icon icon="mdi:chevron-down" class="hidden h-3.5 w-3.5 text-muted-foreground transition-transform duration-200 sm:block" :class="{ 'rotate-180': userDropdownOpen }" />
                                </button>

                                <Transition name="dropdown">
                                    <div v-if="userDropdownOpen" class="absolute right-0 z-50 mt-2 w-60 overflow-hidden rounded-2xl border border-border/80 bg-card py-1.5 shadow-xl">
                                        <div class="border-b border-border/60 px-4 py-3">
                                            <p class="truncate text-xs font-extrabold text-foreground">{{ fullName }}</p>
                                            <p class="mt-0.5 truncate text-[11px] text-muted-foreground">{{ user?.email || user?.username }}</p>
                                            <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                                <span v-if="user?.bidang" class="chip border border-primary/20 bg-primary/10 text-primary">{{ user.bidang }}</span>
                                                <span v-if="user?.student_class || user?.kelas" class="chip bg-muted text-muted-foreground">Kelas {{ user.student_class || user.kelas }}</span>
                                            </div>
                                        </div>
                                        <div class="space-y-0.5 p-1.5">
                                            <Link :href="safeRoute('user.profile.edit')" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold text-foreground transition hover:bg-muted/70">
                                                <Icon icon="mdi:account-outline" class="h-4 w-4 text-primary" />
                                                Profil Saya
                                            </Link>
                                            <Link :href="safeRoute('user.profile.change-password')" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold text-foreground transition hover:bg-muted/70">
                                                <Icon icon="mdi:lock-reset" class="h-4 w-4 text-muted-foreground" />
                                                Ubah Password
                                            </Link>
                                        </div>
                                        <div class="my-1 border-t border-border/60"></div>
                                        <div class="p-1.5">
                                            <Link :href="safeRoute('logout')" method="post" as="button" class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-left text-xs font-semibold text-rose-600 transition hover:bg-rose-50">
                                                <Icon icon="mdi:logout" class="h-4 w-4" />
                                                Keluar
                                            </Link>
                                        </div>
                                    </div>
                                </Transition>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- ISI KONTEN (tanpa footer) -->
            <main class="w-full flex-1 pb-24 lg:pb-8">
                <div class="page-shell py-6 sm:py-8">
                    <!-- Aksi halaman (disediakan halaman via slot) -->
                    <div v-if="slots['header-actions']" class="mb-5 flex flex-wrap items-center justify-end gap-2.5 sm:mb-6">
                        <slot name="header-actions" />
                    </div>

                    <FlashMessage />
                    <slot />
                </div>
            </main>

            <!-- ==================================================================== -->
            <!-- NAVIGASI BAWAH (MOBILE)                                               -->
            <!-- ==================================================================== -->
            <nav class="fixed inset-x-0 bottom-0 z-40 border-t border-border/70 bg-card/90 px-3 py-1.5 shadow-lg backdrop-blur-lg lg:hidden" aria-label="Navigasi bawah siswa">
                <div class="mx-auto flex max-w-md items-center justify-around">
                    <Link
                        v-for="tab in mobileBottomTabs"
                        :key="tab.href"
                        :href="tab.href"
                        :aria-current="isActive(tab.match) ? 'page' : undefined"
                        class="mobile-nav-item relative"
                        :class="isActive(tab.match) ? 'active' : ''"
                    >
                        <span v-if="isActive(tab.match)" class="absolute -top-1 left-1/2 h-0.5 w-4 -translate-x-1/2 rounded-full bg-primary"></span>
                        <Icon :icon="isActive(tab.match) ? tab.activeIcon : tab.icon" class="h-5 w-5" />
                        <span class="text-[10px] font-medium leading-none">{{ tab.label }}</span>
                    </Link>

                    <button
                        @click="openSidebar"
                        class="mobile-nav-item relative"
                        :class="sidebarOpen ? 'active' : ''"
                        aria-label="Menu lainnya"
                    >
                        <Icon icon="mdi:dots-horizontal-circle-outline" class="h-5 w-5" />
                        <span class="text-[10px] font-medium leading-none">Lainnya</span>
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
