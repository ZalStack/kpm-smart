<script setup>
import { ref, inject, computed, onMounted, onUnmounted, useSlots } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import FlashMessage from '@/Components/shared/FlashMessage.vue';
import { usePushNotification } from '@/composables/usePushNotification';

const route = inject('route');
const page = usePage();
const slots = useSlots();
const user = computed(() => page.props.auth?.user);

const userDropdownOpen = ref(false);
const notifDropdownOpen = ref(false);
const pushNotifOpen = ref(false);
const mobileMenuOpen = ref(false);
const scrolled = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);

const {
    isSupported: pushSupported,
    permission: pushPermission,
    isSubscribed: pushSubscribed,
    isLoading: pushLoading,
    error: pushError,
    requestPermission,
    unsubscribe: unsubscribePush,
} = usePushNotification();

async function togglePushNotification() {
    if (pushSubscribed.value) {
        await unsubscribePush();
    } else {
        await requestPermission();
    }
}

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

/** Halaman menyiapkan judul sendiri lewat slot? */
const hasHeader = computed(() => !!(slots['header-title'] || slots['header-sub'] || slots['header-actions']));

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

// Navigasi Khusus Siswa / Peserta (Bersih dari menu Admin/Superadmin)
const navLinks = [
    {
        href: safeRoute('user.dashboard'),
        label: 'Dasbor',
        icon: 'mdi:view-dashboard-outline',
        activeIcon: 'mdi:view-dashboard',
        match: 'user.dashboard',
    },
    {
        href: safeRoute('user.packages.index'),
        label: 'PR Pekanan',
        icon: 'mdi:book-open-variant',
        activeIcon: 'mdi:book-open-page-variant',
        match: 'user.packages.*',
    },
    {
        href: safeRoute('user.practice.history'),
        label: 'Riwayat',
        icon: 'mdi:history',
        activeIcon: 'mdi:history',
        match: 'user.practice.history',
    },
    {
        href: safeRoute('user.practice.statistics'),
        label: 'Statistik',
        icon: 'mdi:chart-box-outline',
        activeIcon: 'mdi:chart-box',
        match: 'user.practice.statistics',
    },
    {
        href: safeRoute('user.leaderboard'),
        label: 'Peringkat',
        icon: 'mdi:trophy-outline',
        activeIcon: 'mdi:trophy',
        match: 'user.leaderboard',
    },
    {
        href: safeRoute('user.leave-requests.index'),
        label: 'Pengajuan Izin',
        icon: 'mdi:calendar-clock-outline',
        activeIcon: 'mdi:calendar-clock',
        match: 'user.leave-requests.*',
    },
];

// Mobile Bottom Bar Tabs (menu utama di bawah pada layar HP)
const mobileBottomTabs = [
    {
        href: safeRoute('user.dashboard'),
        label: 'Dasbor',
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
        href: safeRoute('user.practice.history'),
        label: 'Riwayat',
        icon: 'mdi:history',
        activeIcon: 'mdi:history',
        match: 'user.practice.history',
    },
    {
        href: safeRoute('user.leaderboard'),
        label: 'Peringkat',
        icon: 'mdi:trophy-outline',
        activeIcon: 'mdi:trophy',
        match: 'user.leaderboard',
    },
];

function toggleUserDropdown() {
    userDropdownOpen.value = !userDropdownOpen.value;
}

function toggleNotifDropdown() {
    notifDropdownOpen.value = !notifDropdownOpen.value;
    if (notifDropdownOpen.value) loadNotifications();
}

function openMobileMenu() {
    mobileMenuOpen.value = true;
    document.body.style.overflow = 'hidden';
}

function closeMobileMenu() {
    mobileMenuOpen.value = false;
    document.body.style.overflow = '';
}

async function loadNotifications() {
    if (typeof document !== 'undefined' && document.hidden) return;
    try {
        const notifUrl = safeRoute('user.notifications.dropdown');
        const response = await fetch(notifUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': page.props.csrfToken,
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const data = await response.json();
        notifications.value = data.notifications || [];
        unreadCount.value = data.unread_count || 0;
    } catch (e) {}
}

async function markAllRead() {
    try {
        const markUrl = safeRoute('user.notifications.mark-all-read');
        await fetch(markUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': page.props.csrfToken,
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        unreadCount.value = 0;
        loadNotifications();
    } catch (e) {}
}

function getNotifHref(n) {
    if (n.type === 'announcement' && n.data?.announcement_id) {
        return safeRoute('user.announcements.show', n.data.announcement_id);
    }
    return safeRoute('user.notifications.index');
}

async function handleNotifClick(n) {
    try {
        const readUrl = safeRoute('user.notifications.mark-read', n.id);
        await fetch(readUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': page.props.csrfToken,
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
    } catch (e) {}
}

function closeAllDropdowns() {
    userDropdownOpen.value = false;
    notifDropdownOpen.value = false;
    pushNotifOpen.value = false;
}

let pollInterval;
let clickHandler;
let keyHandler;
let resizeHandler;
let scrollHandler;

function handleScroll() {
    scrolled.value = window.scrollY > 6;
}

onMounted(() => {
    if (user.value) {
        loadNotifications();
        pollInterval = setInterval(loadNotifications, 5000);
    }

    handleScroll();

    clickHandler = (e) => {
        if (!e.target.closest('#userDropdown')) userDropdownOpen.value = false;
        if (!e.target.closest('#notifWrap') && !e.target.closest('#notifWrapMobile')) notifDropdownOpen.value = false;
        if (!e.target.closest('#pushNotifWrap') && !e.target.closest('#pushNotifWrapMobile')) pushNotifOpen.value = false;
    };

    keyHandler = (e) => {
        if (e.key === 'Escape') {
            closeAllDropdowns();
            closeMobileMenu();
        }
    };

    resizeHandler = () => {
        if (window.innerWidth >= 1024) closeMobileMenu();
    };

    document.addEventListener('click', clickHandler);
    document.addEventListener('keydown', keyHandler);
    window.addEventListener('resize', resizeHandler);
    window.addEventListener('scroll', handleScroll, { passive: true });
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
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
        <!-- HEADER                                                               -->
        <!-- Sticky, transparan di atas, "menempel" berwarna saat halaman digulir.  -->
        <!-- ==================================================================== -->
        <header
            class="sticky top-0 z-40 w-full transition-all duration-300"
            :class="scrolled
                ? 'border-b border-border/70 bg-card/85 shadow-sm backdrop-blur-xl'
                : 'border-b border-transparent bg-background/60 backdrop-blur-sm'"
        >
            <div class="page-shell-wide">
                <div class="flex h-16 items-center justify-between gap-3 sm:h-[4.5rem] sm:gap-5">
                    <!-- Brand -->
                    <Link :href="safeRoute('user.dashboard')" class="group flex shrink-0 items-center gap-2.5 select-none" aria-label="KPM SMART - Dasbor Siswa">
                        <span class="relative flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-primary via-indigo-600 to-violet-700 text-base font-black text-white shadow-md shadow-primary/25 transition-transform duration-300 group-hover:scale-105">
                            K
                            <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-background bg-emerald-500"></span>
                        </span>
                        <span class="leading-tight">
                            <span class="block text-[15px] font-black tracking-tight text-foreground sm:text-base">KPM SMART</span>
                            <span class="block text-[10px] font-bold uppercase tracking-[0.16em] text-primary">Platform Siswa</span>
                        </span>
                    </Link>

                    <!-- Navigasi Desktop -->
                    <nav class="hidden items-center gap-1 lg:flex" aria-label="Menu utama siswa">
                        <Link
                            v-for="item in navLinks"
                            :key="item.href"
                            :href="item.href"
                            :aria-current="isActive(item.match) ? 'page' : undefined"
                            class="relative inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold transition-all duration-200 select-none xl:px-3.5"
                            :class="isActive(item.match)
                                ? 'bg-primary/10 text-primary'
                                : 'text-muted-foreground hover:bg-muted/70 hover:text-foreground'"
                        >
                            <Icon :icon="isActive(item.match) ? item.activeIcon : item.icon" class="h-[18px] w-[18px] shrink-0" />
                            <span>{{ item.label }}</span>
                        </Link>
                    </nav>

                    <!-- Kontrol Kanan -->
                    <div class="flex shrink-0 items-center gap-1.5 sm:gap-2">
                        <!-- Tanggal (desktop) -->
                        <div class="hidden items-center gap-2 rounded-full border border-border/60 bg-muted/40 px-3.5 py-1.5 text-xs font-semibold text-muted-foreground xl:flex">
                            <Icon icon="mdi:calendar-month-outline" class="h-3.5 w-3.5 text-primary" />
                            <span>{{ currentDate }}</span>
                        </div>

                        <!-- Notifikasi Browser -->
                        <div v-if="pushSupported" class="relative" id="pushNotifWrap">
                            <button
                                @click="pushNotifOpen = !pushNotifOpen"
                                :aria-expanded="pushNotifOpen"
                                aria-label="Notifikasi browser"
                                class="tap-target relative flex h-10 w-10 items-center justify-center rounded-xl border border-border/60 bg-card/60 text-muted-foreground transition-all hover:border-primary/30 hover:bg-accent/70 hover:text-foreground"
                                :title="pushSubscribed ? 'Notifikasi aktif' : 'Aktifkan notifikasi'"
                            >
                                <Icon :icon="pushSubscribed ? 'mdi:bell-ring-outline' : 'mdi:bell-off-outline'" class="h-5 w-5" :class="pushSubscribed ? 'text-primary' : ''" />
                            </button>

                            <Transition name="dropdown">
                                <div v-if="pushNotifOpen" class="absolute right-0 z-50 mt-2 w-72 rounded-2xl border border-border/80 bg-card p-4 text-card-foreground shadow-xl">
                                    <p class="text-xs font-bold text-foreground">Notifikasi Browser</p>
                                    <p class="mt-0.5 text-[11px] leading-relaxed text-muted-foreground">Dapatkan pemberitahuan di browser saat jadwal PR pekanan dimulai.</p>
                                    <div class="mt-3 border-t border-border/60 pt-3">
                                        <button
                                            @click="togglePushNotification"
                                            :disabled="pushLoading"
                                            class="w-full rounded-xl px-3 py-2 text-xs font-bold transition"
                                            :class="pushSubscribed ? 'bg-rose-50 text-rose-700 hover:bg-rose-100' : 'btn-primary'"
                                        >
                                            <Icon :icon="pushSubscribed ? 'mdi:bell-off' : 'mdi:bell-check'" class="h-4 w-4" />
                                            {{ pushLoading ? 'Memproses...' : (pushSubscribed ? 'Nonaktifkan' : 'Aktifkan Notifikasi') }}
                                        </button>
                                    </div>
                                </div>
                            </Transition>
                        </div>

                        <!-- Lonceng Notifikasi -->
                        <div v-if="user" class="relative" id="notifWrap">
                            <button
                                @click="toggleNotifDropdown"
                                class="tap-target relative flex h-10 w-10 items-center justify-center rounded-xl border border-border/60 bg-card/60 text-muted-foreground transition-all hover:border-primary/30 hover:bg-accent/70 hover:text-foreground"
                                aria-label="Notifikasi"
                            >
                                <Icon icon="mdi:bell-outline" class="h-5 w-5" />
                                <span
                                    v-if="unreadCount > 0"
                                    class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white shadow-sm ring-2 ring-background"
                                >
                                    {{ unreadCount > 99 ? '99+' : unreadCount }}
                                </span>
                            </button>

                            <Transition name="dropdown">
                                <div v-if="notifDropdownOpen" class="absolute right-0 z-50 mt-2 w-80 max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl border border-border/80 bg-card py-1.5 shadow-xl sm:w-96">
                                    <div class="flex items-center justify-between border-b border-border/60 px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-bold text-foreground">Notifikasi</p>
                                            <span v-if="unreadCount > 0" class="rounded-full bg-primary/10 px-2 py-0.5 text-[10px] font-bold text-primary">{{ unreadCount }} baru</span>
                                        </div>
                                        <button @click="markAllRead" class="text-xs font-bold text-primary transition hover:text-primary/80">Tandai dibaca</button>
                                    </div>
                                    <div class="max-h-80 divide-y divide-border/40 overflow-y-auto">
                                        <div v-if="notifications.length === 0" class="px-4 py-10 text-center">
                                            <div class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-2xl bg-muted/60 text-muted-foreground/50">
                                                <Icon icon="mdi:bell-off-outline" class="h-6 w-6" />
                                            </div>
                                            <p class="text-xs font-semibold text-muted-foreground">Tidak ada notifikasi baru</p>
                                        </div>
                                        <Link
                                            v-for="n in notifications"
                                            :key="n.id"
                                            :href="getNotifHref(n)"
                                            @click="handleNotifClick(n)"
                                            class="flex items-start gap-3 px-4 py-3 transition-colors hover:bg-muted/50"
                                            :class="{ 'bg-primary/5': !n.is_read }"
                                        >
                                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full" :class="n.is_read ? 'bg-transparent' : (n.type === 'announcement' ? 'bg-amber-500' : 'bg-primary')"></span>
                                            <span class="min-w-0 flex-1">
                                                <span class="flex items-center gap-1.5 text-xs font-bold text-foreground">
                                                    <span v-if="n.type === 'announcement'" class="rounded bg-amber-100 px-1.5 py-0.5 text-[9px] font-extrabold text-amber-800">PENGUMUMAN</span>
                                                    <span class="truncate">{{ n.title }}</span>
                                                </span>
                                                <span class="mt-0.5 block truncate text-xs text-muted-foreground">{{ n.message }}</span>
                                                <span class="mt-1 block text-[10px] text-muted-foreground/60">{{ n.created_at }}</span>
                                            </span>
                                        </Link>
                                    </div>
                                    <div class="border-t border-border/60 p-2">
                                        <Link :href="safeRoute('user.notifications.index')" class="block rounded-xl py-2 text-center text-xs font-bold text-primary transition hover:bg-muted/60">
                                            Lihat Semua Notifikasi
                                        </Link>
                                    </div>
                                </div>
                            </Transition>
                        </div>

                        <!-- Menu Profil -->
                        <div v-if="user" class="relative" id="userDropdown">
                            <button
                                @click="toggleUserDropdown"
                                class="flex items-center gap-2 rounded-xl border border-border/60 bg-card/60 p-1 pr-1.5 transition-all hover:border-primary/30 hover:bg-accent/70 sm:pr-2.5"
                            >
                                <span class="flex h-8 w-8 items-center justify-center overflow-hidden rounded-lg bg-gradient-to-br from-primary to-indigo-700 text-xs font-bold text-white shadow-2xs">
                                    <img v-if="profilePhotoUrl" :src="profilePhotoUrl" :alt="user?.name" class="h-full w-full object-cover" />
                                    <span v-else>{{ (user?.name || 'A').charAt(0).toUpperCase() }}</span>
                                </span>
                                <span class="hidden text-left sm:block">
                                    <span class="block max-w-[120px] truncate text-xs font-bold leading-tight text-foreground">{{ user.name }}</span>
                                    <span v-if="user.bidang" class="block truncate text-[10px] font-semibold leading-tight text-primary">{{ user.bidang }}</span>
                                </span>
                                <Icon icon="mdi:chevron-down" class="hidden h-3.5 w-3.5 text-muted-foreground transition-transform duration-200 sm:block" :class="{ 'rotate-180': userDropdownOpen }" />
                            </button>

                            <Transition name="dropdown">
                                <div v-if="userDropdownOpen" class="absolute right-0 z-50 mt-2 w-60 overflow-hidden rounded-2xl border border-border/80 bg-card py-1.5 shadow-xl">
                                    <div class="border-b border-border/60 px-4 py-3">
                                        <p class="truncate text-xs font-extrabold text-foreground">{{ user?.name }}</p>
                                        <p class="mt-0.5 truncate text-[11px] text-muted-foreground">{{ user?.email || user?.username }}</p>
                                        <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                            <span v-if="user?.bidang" class="chip border border-primary/20 bg-primary/10 text-primary">{{ user.bidang }}</span>
                                            <span v-if="user?.kelas" class="chip bg-muted text-muted-foreground">Kelas {{ user.kelas }}</span>
                                        </div>
                                    </div>
                                    <div class="space-y-0.5 p-1.5">
                                        <Link :href="safeRoute('user.profile.edit')" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold text-foreground transition hover:bg-muted/70">
                                            <Icon icon="mdi:account-outline" class="h-4 w-4 text-primary" />
                                            Profil Saya
                                        </Link>
                                        <Link :href="safeRoute('user.practice.statistics')" class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold text-foreground transition hover:bg-muted/70">
                                            <Icon icon="mdi:chart-box-outline" class="h-4 w-4 text-indigo-600" />
                                            Statistik Saya
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

        <!-- ==================================================================== -->
        <!-- KONTEN UTAMA                                                          -->
        <!-- ==================================================================== -->
        <main class="flex-1 w-full pb-28 lg:pb-16">
            <div class="page-shell py-6 sm:py-8">
                <!-- Header halaman yang disediakan halaman via slot -->
                <div v-if="hasHeader" class="mb-6 flex flex-col gap-4 sm:mb-8 sm:flex-row sm:items-end sm:justify-between">
                    <div class="min-w-0">
                        <h1 v-if="slots['header-title']" class="page-heading">
                            <slot name="header-title" />
                        </h1>
                        <p v-if="slots['header-sub']" class="page-subheading">
                            <slot name="header-sub" />
                        </p>
                    </div>
                    <div v-if="slots['header-actions']" class="flex shrink-0 flex-wrap items-center gap-2.5">
                        <slot name="header-actions" />
                    </div>
                </div>

                <FlashMessage />
                <slot />
            </div>
        </main>

        <!-- ==================================================================== -->
        <!-- FOOTER                                                               -->
        <!-- ==================================================================== -->
        <footer class="mt-auto border-t border-border/70 bg-card/60 pb-24 backdrop-blur-md lg:pb-0">
            <div class="page-shell-wide py-10">
                <div class="grid grid-cols-2 gap-8 md:grid-cols-4">
                    <div class="col-span-2 space-y-3">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-primary via-indigo-600 to-violet-700 text-sm font-extrabold text-white shadow-sm">K</span>
                            <span class="text-base font-extrabold tracking-tight text-foreground">KPM SMART</span>
                        </div>
                        <p class="max-w-sm text-xs leading-relaxed text-muted-foreground">
                            Platform pembelajaran dan pengerjaan PR online Klinik Pendidikan MIPA (KPM). Menumbuhkan keunggulan nalar matematika dan sains secara mandiri dan terarah.
                        </p>
                    </div>

                    <div>
                        <h4 class="mb-3 text-[11px] font-bold uppercase tracking-wider text-foreground">Menu Siswa</h4>
                        <ul class="space-y-2.5 text-xs text-muted-foreground">
                            <li><Link :href="safeRoute('user.dashboard')" class="transition hover:text-primary">Dasbor</Link></li>
                            <li><Link :href="safeRoute('user.packages.index')" class="transition hover:text-primary">PR Pekanan</Link></li>
                            <li><Link :href="safeRoute('user.practice.history')" class="transition hover:text-primary">Riwayat Pengerjaan</Link></li>
                            <li><Link :href="safeRoute('user.leaderboard')" class="transition hover:text-primary">Papan Peringkat</Link></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="mb-3 text-[11px] font-bold uppercase tracking-wider text-foreground">Informasi</h4>
                        <ul class="space-y-2.5 text-xs text-muted-foreground">
                            <li><Link :href="safeRoute('pages.guide')" class="transition hover:text-primary">Panduan Penggunaan</Link></li>
                            <li><Link :href="safeRoute('pages.faq')" class="transition hover:text-primary">Tanya Jawab (FAQ)</Link></li>
                            <li><Link :href="safeRoute('pages.features')" class="transition hover:text-primary">Fitur Unggulan</Link></li>
                            <li><Link :href="safeRoute('user.leave-requests.index')" class="transition hover:text-primary">Pengajuan Izin</Link></li>
                        </ul>
                    </div>
                </div>

                <div class="mt-8 flex flex-col items-center justify-between gap-3 border-t border-border/60 pt-6 text-xs text-muted-foreground sm:flex-row">
                    <p>&copy; {{ new Date().getFullYear() }} KPM SMART · Klinik Pendidikan MIPA. Semua hak cipta dilindungi.</p>
                    <p class="flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Sistem Berjalan Normal
                    </p>
                </div>
            </div>
        </footer>

        <!-- ==================================================================== -->
        <!-- NAVIGASI BAWAH (MOBILE) — apung dengan sudut membulat                  -->
        <!-- ==================================================================== -->
        <nav class="fixed inset-x-0 bottom-0 z-40 px-3 pb-[calc(env(safe-area-inset-bottom,0px)+0.75rem)] pt-2 lg:hidden" aria-label="Navigasi bawah siswa">
            <div class="mx-auto grid max-w-md grid-cols-5 items-center gap-1 rounded-2xl border border-border/70 bg-card/90 p-1.5 shadow-xl shadow-slate-900/10 backdrop-blur-xl">
                <Link
                    v-for="tab in mobileBottomTabs"
                    :key="tab.href"
                    :href="tab.href"
                    :aria-current="isActive(tab.match) ? 'page' : undefined"
                    class="tap-target flex flex-col items-center justify-center gap-1 rounded-xl py-1.5 transition-all duration-200 select-none"
                    :class="isActive(tab.match) ? 'bg-primary/10 text-primary' : 'text-muted-foreground'"
                >
                    <Icon
                        :icon="isActive(tab.match) ? tab.activeIcon : tab.icon"
                        class="h-[22px] w-[22px] transition-transform duration-200"
                        :class="isActive(tab.match) ? 'scale-110' : ''"
                    />
                    <span class="text-[10px] font-bold leading-none">{{ tab.label }}</span>
                </Link>

                <button
                    @click="openMobileMenu"
                    class="tap-target flex flex-col items-center justify-center gap-1 rounded-xl py-1.5 transition-all duration-200 select-none"
                    :class="mobileMenuOpen ? 'bg-primary/10 text-primary' : 'text-muted-foreground'"
                    aria-label="Menu lainnya"
                >
                    <Icon icon="mdi:dots-horizontal-circle-outline" class="h-[22px] w-[22px]" />
                    <span class="text-[10px] font-bold leading-none">Lainnya</span>
                </button>
            </div>
        </nav>

        <!-- ==================================================================== -->
        <!-- LEMBAR BAWAH "MENU LAINNYA" (MOBILE)                                   -->
        <!-- ==================================================================== -->
        <Transition name="overlay">
            <div v-if="mobileMenuOpen" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-xs lg:hidden" @click="closeMobileMenu" />
        </Transition>

        <Transition name="sheet">
            <div
                v-if="mobileMenuOpen"
                class="fixed inset-x-0 bottom-0 z-50 max-h-[85vh] overflow-y-auto rounded-t-3xl border-t border-border bg-card p-5 pb-[calc(env(safe-area-inset-bottom,0px)+1.25rem)] shadow-2xl lg:hidden"
            >
                <div class="mx-auto mb-4 h-1.5 w-12 rounded-full bg-muted-foreground/30"></div>

                <!-- Kartu Ringkas Pengguna -->
                <div class="mb-5 flex items-center gap-3 rounded-2xl border border-border/60 bg-muted/40 p-3.5">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gradient-to-br from-primary to-indigo-700 text-base font-extrabold text-white shadow-sm">
                        <img v-if="profilePhotoUrl" :src="profilePhotoUrl" :alt="user?.name" class="h-full w-full object-cover" />
                        <span v-else>{{ (user?.name || 'A').charAt(0).toUpperCase() }}</span>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-bold text-foreground">{{ user?.name }}</p>
                        <p class="truncate text-xs text-muted-foreground">{{ user?.email || user?.username }}</p>
                        <div class="mt-1 flex flex-wrap items-center gap-1.5">
                            <span v-if="user?.bidang" class="chip border border-primary/20 bg-primary/10 text-primary">{{ user.bidang }}</span>
                            <span v-if="user?.kelas" class="chip border border-border bg-card text-muted-foreground">Kelas {{ user.kelas }}</span>
                        </div>
                    </div>
                    <button @click="closeMobileMenu" class="rounded-lg p-2 text-muted-foreground transition hover:text-foreground" aria-label="Tutup menu">
                        <Icon icon="mdi:close" class="h-5 w-5" />
                    </button>
                </div>

                <!-- Menu Utama -->
                <p class="mb-2 px-2 text-[11px] font-bold uppercase tracking-wider text-muted-foreground">Menu Siswa</p>
                <div class="mb-5 space-y-1">
                    <Link
                        :href="safeRoute('user.practice.statistics')"
                        @click="closeMobileMenu"
                        class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold text-foreground transition hover:bg-muted/70"
                    >
                        <span class="icon-box-sm border border-indigo-200 bg-indigo-50 text-indigo-600">
                            <Icon icon="mdi:chart-box-outline" class="h-[18px] w-[18px]" />
                        </span>
                        <span>Statistik Belajar</span>
                    </Link>

                    <Link
                        :href="safeRoute('user.leave-requests.index')"
                        @click="closeMobileMenu"
                        class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold text-foreground transition hover:bg-muted/70"
                    >
                        <span class="icon-box-sm border border-sky-200 bg-sky-50 text-sky-600">
                            <Icon icon="mdi:calendar-clock-outline" class="h-[18px] w-[18px]" />
                        </span>
                        <span>Pengajuan Izin Siswa</span>
                    </Link>

                    <Link
                        :href="safeRoute('user.notifications.index')"
                        @click="closeMobileMenu"
                        class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold text-foreground transition hover:bg-muted/70"
                    >
                        <span class="icon-box-sm border border-amber-200 bg-amber-50 text-amber-600">
                            <Icon icon="mdi:bell-badge-outline" class="h-[18px] w-[18px]" />
                        </span>
                        <span>Pusat Notifikasi</span>
                    </Link>
                </div>

                <!-- Bantuan -->
                <p class="mb-2 px-2 text-[11px] font-bold uppercase tracking-wider text-muted-foreground">Bantuan</p>
                <div class="mb-5 space-y-1">
                    <Link
                        :href="safeRoute('pages.guide')"
                        @click="closeMobileMenu"
                        class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold text-foreground transition hover:bg-muted/70"
                    >
                        <Icon icon="mdi:help-circle-outline" class="h-[18px] w-[18px] shrink-0 text-muted-foreground" />
                        <span>Panduan Belajar</span>
                    </Link>

                    <Link
                        :href="safeRoute('pages.faq')"
                        @click="closeMobileMenu"
                        class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold text-foreground transition hover:bg-muted/70"
                    >
                        <Icon icon="mdi:information-outline" class="h-[18px] w-[18px] shrink-0 text-muted-foreground" />
                        <span>Tanya Jawab (FAQ)</span>
                    </Link>
                </div>

                <!-- Akun -->
                <p class="mb-2 px-2 text-[11px] font-bold uppercase tracking-wider text-muted-foreground">Akun Saya</p>
                <div class="mb-5 space-y-1">
                    <Link
                        :href="safeRoute('user.profile.edit')"
                        @click="closeMobileMenu"
                        class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold text-foreground transition hover:bg-muted/70"
                    >
                        <Icon icon="mdi:account-outline" class="h-[18px] w-[18px] shrink-0 text-primary" />
                        <span>Profil Saya</span>
                    </Link>

                    <Link
                        :href="safeRoute('user.profile.change-password')"
                        @click="closeMobileMenu"
                        class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-semibold text-foreground transition hover:bg-muted/70"
                    >
                        <Icon icon="mdi:lock-reset" class="h-[18px] w-[18px] shrink-0 text-muted-foreground" />
                        <span>Ubah Kata Sandi</span>
                    </Link>
                </div>

                <Link
                    :href="safeRoute('logout')"
                    method="post"
                    as="button"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-rose-50 px-4 py-3 text-sm font-bold text-rose-600 transition hover:bg-rose-100"
                >
                    <Icon icon="mdi:logout" class="h-4 w-4" />
                    Keluar dari Akun
                </Link>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.overlay-enter-active, .overlay-leave-active { transition: opacity 0.25s ease; }
.overlay-enter-from, .overlay-leave-to { opacity: 0; }

.dropdown-enter-active { transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1); }
.dropdown-leave-active { transition: all 0.15s ease-in; }
.dropdown-enter-from { opacity: 0; transform: scale(0.96) translateY(-6px); }
.dropdown-leave-to { opacity: 0; transform: scale(0.96) translateY(-6px); }

.sheet-enter-active { transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
.sheet-leave-active { transition: transform 0.2s ease-in; }
.sheet-enter-from { transform: translateY(100%); }
.sheet-leave-to { transform: translateY(100%); }
</style>
