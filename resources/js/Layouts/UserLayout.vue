<script setup>
import { ref, inject, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import FlashMessage from '@/Components/shared/FlashMessage.vue';
import { usePushNotification } from '@/composables/usePushNotification';

const route = inject('route');
const page = usePage();
const user = computed(() => page.props.auth?.user);
const sidebarOpen = ref(false);
const userDropdownOpen = ref(false);
const notifDropdownOpen = ref(false);
const pushNotifOpen = ref(false);
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
    return '/storage/' + user.value.profile_photo + '?v=' + encodeURIComponent(user.value.profile_photo);
});

const isUserRole = computed(() => {
    const u = user.value;
    return u && u.role === 'user';
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

const navItems = [
    {
        label: 'Menu Utama',
        items: [
            { href: route('user.dashboard'), label: 'Dasbor', icon: 'mdi:view-dashboard-outline', activeIcon: 'mdi:view-dashboard', match: 'user.dashboard' },
            { href: route('user.packages.index'), label: 'Tugas PR', icon: 'mdi:book-open-variant', activeIcon: 'mdi:book-open-page-variant', match: 'user.packages.*' },
            { href: route('user.practice.history'), label: 'Riwayat Pengerjaan', icon: 'mdi:history', activeIcon: 'mdi:history', match: 'user.practice.*' },
            { href: route('user.leaderboard'), label: 'Peringkat', icon: 'mdi:trophy-outline', activeIcon: 'mdi:trophy', match: 'user.leaderboard' },
            { href: route('user.leave-requests.index'), label: 'Pengajuan Izin', icon: 'mdi:calendar-blank-outline', activeIcon: 'mdi:calendar-check', match: 'user.leave-requests.*' },
            { href: route('user.practice.statistics'), label: 'Statistik Saya', icon: 'mdi:chart-box-outline', activeIcon: 'mdi:chart-box', match: 'user.practice.statistics' },
        ],
    },
    {
        label: 'Informasi & Bantuan',
        items: [
            { href: route('user.notifications.index'), label: 'Notifikasi', icon: 'mdi:bell-outline', activeIcon: 'mdi:bell', match: 'user.notifications.*' },
            { href: route('pages.guide'), label: 'Panduan Siswa', icon: 'mdi:help-circle-outline', activeIcon: 'mdi:help-circle', match: 'pages.guide' },
            { href: route('pages.faq'), label: 'FAQ', icon: 'mdi:information-outline', activeIcon: 'mdi:information', match: 'pages.faq' },
        ],
    },
];

const mobileNavItems = [
    { href: route('user.dashboard'), label: 'Dasbor', icon: 'mdi:view-dashboard-outline', match: 'user.dashboard' },
    { href: route('user.packages.index'), label: 'Tugas', icon: 'mdi:book-open-variant', match: 'user.packages.*' },
    { href: route('user.practice.history'), label: 'Riwayat', icon: 'mdi:history', match: 'user.practice.*' },
    { href: route('user.leaderboard'), label: 'Peringkat', icon: 'mdi:trophy-outline', match: 'user.leaderboard' },
];

function isActive(match) {
    return route().current(match);
}

function toggleUserDropdown() {
    userDropdownOpen.value = !userDropdownOpen.value;
}

function toggleNotifDropdown() {
    notifDropdownOpen.value = !notifDropdownOpen.value;
    if (notifDropdownOpen.value) loadNotifications();
}

async function loadNotifications() {
    try {
        const response = await fetch(route('user.notifications.dropdown'), {
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
        await fetch(route('user.notifications.mark-all-read'), {
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
        return route('user.announcements.show', n.data.announcement_id);
    }
    return route('user.notifications.index');
}

async function handleNotifClick(n) {
    try {
        await fetch(route('user.notifications.mark-read', n.id), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': page.props.csrfToken,
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
    } catch (e) {}
}

let pollInterval;
let clickHandler;
let keyHandler;
let resizeHandler;

function closeAllDropdowns() {
    userDropdownOpen.value = false;
    notifDropdownOpen.value = false;
    pushNotifOpen.value = false;
}

onMounted(() => {
    if (user.value) {
        loadNotifications();
        pollInterval = setInterval(loadNotifications, 15000);
    }
    clickHandler = (e) => {
        if (!e.target.closest('#userDropdown')) userDropdownOpen.value = false;
        if (!e.target.closest('#notifWrap') && !e.target.closest('#notifWrapMobile')) notifDropdownOpen.value = false;
        if (!e.target.closest('#pushNotifWrap') && !e.target.closest('#pushNotifWrapMobile')) pushNotifOpen.value = false;
    };
    keyHandler = (e) => {
        if (e.key === 'Escape') closeAllDropdowns();
    };
    resizeHandler = () => {
        if (window.innerWidth >= 1024) closeSidebar();
    };
    document.addEventListener('click', clickHandler);
    document.addEventListener('keydown', keyHandler);
    window.addEventListener('resize', resizeHandler);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
    if (clickHandler) document.removeEventListener('click', clickHandler);
    if (keyHandler) document.removeEventListener('keydown', keyHandler);
    if (resizeHandler) window.removeEventListener('resize', resizeHandler);
});
</script>

<template>
    <div class="min-h-screen bg-background text-foreground flex flex-col antialiased selection:bg-primary selection:text-primary-foreground">
        <!-- Sidebar Overlay (Mobile) -->
        <Transition name="overlay">
            <div v-if="sidebarOpen" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-40 lg:hidden" @click="closeSidebar" />
        </Transition>

        <!-- Sidebar (Desktop & Mobile Drawer) -->
        <aside
            :class="[
                'fixed top-0 left-0 h-full w-[270px] sidebar-modern text-white z-50 overflow-hidden flex flex-col shadow-2xl transition-transform duration-300 ease-in-out',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
            ]"
        >
            <!-- Brand -->
            <div class="relative flex items-center justify-between gap-3.5 px-5 py-5 border-b border-white/10 flex-shrink-0">
                <Link :href="route('user.dashboard')" class="flex items-center gap-3 min-w-0 group">
                    <div class="w-10 h-10 rounded-xl bg-white/15 backdrop-blur border border-white/20 flex items-center justify-center flex-shrink-0 shadow-sm group-hover:scale-105 transition-transform duration-200">
                        <span class="text-white text-base font-extrabold tracking-tight">K</span>
                    </div>
                    <div class="min-w-0">
                        <span class="text-sm font-bold text-white block leading-tight tracking-tight">KPM SMART</span>
                        <span class="text-[11px] text-white/70 font-medium">Platform Siswa</span>
                    </div>
                </Link>
                <button @click="closeSidebar" class="lg:hidden p-1.5 rounded-lg text-white/60 hover:text-white hover:bg-white/10 transition" aria-label="Tutup navigasi">
                    <Icon icon="mdi:close" class="w-5 h-5" />
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="relative px-3 py-4 flex-1 min-h-0 overflow-y-auto sidebar-scroll space-y-6" aria-label="Navigasi sidebar">
                <div v-for="group in navItems" :key="group.label" class="space-y-1">
                    <p class="text-[10px] uppercase tracking-widest text-white/40 font-bold px-3 mb-2">{{ group.label }}</p>
                    <Link
                        v-for="item in group.items"
                        :key="item.href"
                        :href="item.href"
                        :aria-current="isActive(item.match) ? 'page' : undefined"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group text-sm',
                            isActive(item.match)
                                ? 'bg-white/15 text-white font-semibold shadow-xs'
                                : 'text-white/70 hover:bg-white/10 hover:text-white'
                        ]"
                    >
                        <div
                            :class="[
                                'w-8 h-8 rounded-lg flex items-center justify-center text-lg flex-shrink-0 transition-all duration-200',
                                isActive(item.match) ? 'bg-white text-emerald-800 shadow-xs scale-105' : 'bg-white/10 text-white group-hover:bg-white/20'
                            ]"
                        >
                            <Icon :icon="isActive(item.match) ? item.activeIcon : item.icon" class="w-4 h-4" />
                        </div>
                        <span class="truncate">{{ item.label }}</span>
                    </Link>
                </div>
            </nav>

            <!-- Sidebar User Card Footer -->
            <div class="relative px-4 py-4 border-t border-white/10 flex-shrink-0 bg-black/10">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-white/20 border border-white/20 flex items-center justify-center text-white font-bold text-sm flex-shrink-0 overflow-hidden shadow-xs">
                        <img v-if="profilePhotoUrl" :src="profilePhotoUrl" class="w-full h-full object-cover" />
                        <span v-else>{{ (user?.name || 'S').charAt(0).toUpperCase() }}</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold text-white truncate leading-tight">{{ user?.name || 'Siswa KPM' }}</p>
                        <p class="text-[11px] text-white/60 truncate mt-0.5">{{ user?.student_class ? 'Kelas ' + user.student_class : (user?.username || user?.email) }}</p>
                    </div>
                    <Link :href="route('logout')" method="post" as="button" class="p-1.5 text-white/60 hover:text-rose-300 hover:bg-white/10 rounded-lg transition" title="Keluar">
                        <Icon icon="mdi:logout" class="w-4 h-4" />
                    </Link>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="lg:ml-[270px] min-h-screen flex flex-col flex-1">
            <!-- Top Header -->
            <header class="sticky top-0 z-30 w-full border-b border-border/80 bg-card/85 backdrop-blur-md shadow-2xs">
                <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 h-16">
                    <div class="flex items-center gap-3 min-w-0">
                        <!-- Hamburger Button (Mobile / Tablet) -->
                        <button
                            @click="openSidebar"
                            class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-xl text-muted-foreground hover:bg-accent hover:text-foreground transition border border-border/60"
                            aria-label="Buka navigasi"
                        >
                            <Icon icon="mdi:menu" class="w-5 h-5" />
                        </button>
                        <div class="min-w-0">
                            <h1 class="text-base sm:text-lg font-bold tracking-tight text-foreground truncate">
                                <slot name="header-title" />
                            </h1>
                            <p class="text-xs text-muted-foreground hidden sm:block truncate mt-0.5">
                                <slot name="header-sub" />
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2.5">
                        <!-- Date Pill -->
                        <div class="hidden md:flex items-center gap-2 text-xs font-medium text-muted-foreground bg-muted/70 border border-border/60 px-3.5 py-1.5 rounded-full">
                            <Icon icon="mdi:calendar-month-outline" class="w-3.5 h-3.5 text-primary" />
                            <span>{{ currentDate }}</span>
                        </div>

                        <!-- Notification Bell -->
                        <div v-if="user" class="relative" id="notifWrap">
                            <button
                                @click="toggleNotifDropdown"
                                :aria-expanded="notifDropdownOpen"
                                aria-label="Notifikasi"
                                class="inline-flex items-center justify-center w-10 h-10 rounded-xl text-muted-foreground hover:bg-accent/70 hover:text-foreground transition border border-border/60 relative"
                            >
                                <Icon icon="mdi:bell-outline" class="w-5 h-5" />
                                <span
                                    v-if="unreadCount > 0"
                                    class="absolute -top-1 -right-1 min-w-5 h-5 px-1 bg-rose-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center shadow-xs ring-2 ring-background animate-pulse-soft"
                                >
                                    {{ unreadCount > 99 ? '99+' : unreadCount }}
                                </span>
                            </button>

                            <Transition name="dropdown">
                                <div v-if="notifDropdownOpen" class="absolute right-0 mt-2 w-80 sm:w-96 max-w-[calc(100vw-2rem)] bg-card text-card-foreground rounded-2xl shadow-xl border border-border/80 py-2 z-50">
                                    <div class="px-4 py-3 border-b border-border/60 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-bold text-foreground">Notifikasi</p>
                                            <span v-if="unreadCount > 0" class="text-[10px] bg-rose-100 text-rose-700 font-bold px-1.5 py-0.5 rounded-full">{{ unreadCount }} baru</span>
                                        </div>
                                        <button @click="markAllRead" class="text-xs text-primary hover:text-primary/80 font-semibold transition">Tandai dibaca</button>
                                    </div>
                                    <div class="max-h-72 overflow-y-auto divide-y divide-border/40">
                                        <div v-if="notifications.length === 0" class="px-4 py-10 text-center">
                                            <div class="w-12 h-12 rounded-2xl bg-muted/60 flex items-center justify-center mx-auto mb-2 text-muted-foreground/50">
                                                <Icon icon="mdi:bell-off-outline" class="w-6 h-6" />
                                            </div>
                                            <p class="text-xs font-semibold text-muted-foreground">Tidak ada notifikasi baru</p>
                                        </div>
                                        <Link
                                            v-for="n in notifications"
                                            :key="n.id"
                                            :href="getNotifHref(n)"
                                            @click="handleNotifClick(n)"
                                            class="flex items-start gap-3 px-4 py-3 hover:bg-accent/60 transition-colors"
                                            :class="{ 'bg-primary/5': !n.is_read }"
                                        >
                                            <div :class="['w-2 h-2 rounded-full mt-1.5 shrink-0', n.is_read ? 'bg-transparent' : (n.type === 'announcement' ? 'bg-amber-500' : 'bg-primary')]"></div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-bold text-foreground truncate flex items-center gap-1.5">
                                                    <span v-if="n.type === 'announcement'" class="text-[9px] font-extrabold bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded">PENGUMUMAN</span>
                                                    <span v-if="n.type === 'sps_reminder'" class="text-[9px] font-extrabold bg-sky-100 text-sky-800 px-1.5 py-0.5 rounded">SPS</span>
                                                    {{ n.title }}
                                                </p>
                                                <p class="text-xs text-muted-foreground truncate mt-0.5">{{ n.message }}</p>
                                                <span class="text-[10px] text-muted-foreground/60 block mt-1">{{ n.created_at }}</span>
                                            </div>
                                        </Link>
                                    </div>
                                    <div class="border-t border-border/60 pt-2 px-2">
                                        <Link :href="route('user.notifications.index')" class="block py-2 text-center text-xs text-primary hover:bg-accent/60 rounded-xl font-bold transition">
                                            Lihat Semua Notifikasi
                                        </Link>
                                    </div>
                                </div>
                            </Transition>
                        </div>

                        <!-- Browser Push Toggle -->
                        <div v-if="pushSupported && isUserRole" class="relative" id="pushNotifWrap">
                            <button
                                @click="pushNotifOpen = !pushNotifOpen"
                                :aria-expanded="pushNotifOpen"
                                aria-label="Notifikasi browser"
                                class="inline-flex items-center justify-center w-10 h-10 rounded-xl text-muted-foreground hover:bg-accent/70 hover:text-foreground transition border border-border/60 relative"
                                :title="pushSubscribed ? 'Notifikasi aktif' : 'Aktifkan notifikasi'"
                            >
                                <Icon :icon="pushSubscribed ? 'mdi:bell-ring-outline' : 'mdi:bell-off-outline'" class="w-5 h-5" :class="pushSubscribed ? 'text-emerald-600' : ''" />
                            </button>

                            <Transition name="dropdown">
                                <div v-if="pushNotifOpen" class="absolute right-0 mt-2 w-72 bg-card text-card-foreground rounded-2xl shadow-xl border border-border/80 py-3 px-4 z-50">
                                    <p class="text-xs font-bold text-foreground">Notifikasi Pengingat SPS</p>
                                    <p class="text-[11px] text-muted-foreground mt-0.5">Dapatkan notifikasi langsung di browsermu ketika waktu pengerjaan dimulai.</p>
                                    <div class="mt-3 pt-3 border-t border-border/60">
                                        <button
                                            @click="togglePushNotification"
                                            :disabled="pushLoading"
                                            class="w-full py-2 px-3 rounded-xl text-xs font-bold flex items-center justify-center gap-2 transition"
                                            :class="pushSubscribed ? 'bg-rose-50 text-rose-700 hover:bg-rose-100' : 'bg-primary text-primary-foreground hover:bg-primary/90 shadow-xs'"
                                        >
                                            <Icon :icon="pushSubscribed ? 'mdi:bell-off' : 'mdi:bell-check'" class="w-4 h-4" />
                                            {{ pushLoading ? 'Memproses...' : (pushSubscribed ? 'Nonaktifkan Pengingat' : 'Aktifkan Pengingat') }}
                                        </button>
                                    </div>
                                </div>
                            </Transition>
                        </div>

                        <!-- User Profile Dropdown -->
                        <div v-if="user" class="relative" id="userDropdown">
                            <button
                                @click="toggleUserDropdown"
                                class="inline-flex items-center gap-2 hover:bg-accent/70 rounded-xl p-1.5 sm:px-2.5 transition border border-border/60"
                            >
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-600 to-teal-700 text-white font-bold text-xs flex items-center justify-center overflow-hidden shadow-2xs">
                                    <img v-if="profilePhotoUrl" :src="profilePhotoUrl" class="w-full h-full object-cover" />
                                    <span v-else>{{ (user?.name || 'S').charAt(0).toUpperCase() }}</span>
                                </div>
                                <span class="hidden sm:block text-xs font-bold text-foreground max-w-[120px] truncate leading-tight">{{ user.name }}</span>
                                <Icon icon="mdi:chevron-down" class="hidden sm:block w-3.5 h-3.5 text-muted-foreground transition-transform duration-200" :class="{ 'rotate-180': userDropdownOpen }" />
                            </button>

                            <Transition name="dropdown">
                                <div v-if="userDropdownOpen" class="absolute right-0 mt-2 w-56 bg-card text-card-foreground rounded-2xl shadow-xl border border-border/80 py-2 z-50">
                                    <div class="px-4 py-3 border-b border-border/60">
                                        <p class="text-xs font-bold text-foreground truncate">{{ user?.name }}</p>
                                        <p class="text-[11px] text-muted-foreground truncate mt-0.5">{{ user?.email || user?.username }}</p>
                                    </div>
                                    <div class="py-1 px-1.5 space-y-0.5">
                                        <Link :href="route('user.profile.edit')" class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-foreground hover:bg-accent/70 rounded-xl transition">
                                            <Icon icon="mdi:account-outline" class="w-4 h-4 text-muted-foreground" />
                                            Profil Saya
                                        </Link>
                                        <Link v-if="user.role === 'user'" :href="route('user.practice.statistics')" class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-foreground hover:bg-accent/70 rounded-xl transition">
                                            <Icon icon="mdi:chart-box-outline" class="w-4 h-4 text-muted-foreground" />
                                            Statistik Saya
                                        </Link>
                                    </div>
                                    <div class="border-t border-border/60 my-1"></div>
                                    <div class="px-1.5">
                                        <Link :href="route('logout')" method="post" as="button" class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 rounded-xl transition text-left">
                                            <Icon icon="mdi:logout" class="w-4 h-4" />
                                            Keluar
                                        </Link>
                                    </div>
                                </div>
                            </Transition>
                        </div>

                        <Link v-else :href="route('login')" class="inline-flex items-center justify-center px-4 py-2 text-xs font-bold bg-primary text-primary-foreground rounded-xl shadow-xs hover:bg-primary/90 transition">
                            Masuk
                        </Link>
                    </div>
                </div>
            </header>

            <!-- Main Content Container -->
            <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 w-full animate-fade-in pb-24 lg:pb-12">
                <FlashMessage />
                <div v-if="$slots['header-title']" class="mb-6 lg:hidden">
                    <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-foreground"><slot name="header-title" /></h1>
                    <p v-if="$slots['header-sub']" class="text-xs sm:text-sm text-muted-foreground mt-1 leading-relaxed"><slot name="header-sub" /></p>
                </div>
                <slot />
            </main>

            <!-- Desktop Footer -->
            <footer class="hidden lg:block border-t border-border/60 bg-card py-6 mt-auto">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-muted-foreground">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-foreground">KPM SMART</span>
                        <span>&copy; {{ new Date().getFullYear() }} Semua hak cipta dilindungi.</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <Link :href="route('pages.features')" class="hover:text-primary transition">Fitur</Link>
                        <Link :href="route('pages.guide')" class="hover:text-primary transition">Panduan</Link>
                        <Link :href="route('pages.faq')" class="hover:text-primary transition">FAQ</Link>
                    </div>
                </div>
            </footer>

            <!-- Mobile Bottom Navigation (Admin/User quick tabs) -->
            <nav v-if="isUserRole" class="lg:hidden fixed bottom-0 inset-x-0 z-30 bg-card/95 backdrop-blur-xl border-t border-border/80 safe-area-bottom shadow-lg">
                <div class="grid grid-cols-5 h-16 items-center px-1" role="tablist">
                    <Link
                        v-for="tab in mobileNavItems"
                        :key="tab.href"
                        :href="tab.href"
                        :aria-current="isActive(tab.match) ? 'page' : undefined"
                        class="flex flex-col items-center justify-center gap-1 py-1 relative transition-all duration-200 select-none"
                        :class="isActive(tab.match) ? 'text-primary font-bold' : 'text-muted-foreground hover:text-foreground'"
                    >
                        <div class="relative flex items-center justify-center">
                            <Icon
                                :icon="tab.icon"
                                class="w-5 h-5 transition-transform duration-200"
                                :class="isActive(tab.match) ? 'scale-110 text-primary' : ''"
                            />
                            <div v-if="isActive(tab.match)" class="absolute -bottom-1.5 left-1/2 -translate-x-1/2 w-4 h-1 rounded-full bg-primary"></div>
                        </div>
                        <span class="text-[10px] leading-tight transition-all duration-200">{{ tab.label }}</span>
                    </Link>
                    <button
                        @click="openSidebar"
                        class="flex flex-col items-center justify-center gap-1 py-1 relative text-muted-foreground hover:text-foreground transition-all duration-200 select-none"
                    >
                        <Icon icon="mdi:menu" class="w-5 h-5" />
                        <span class="text-[10px] leading-tight">Lainnya</span>
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
