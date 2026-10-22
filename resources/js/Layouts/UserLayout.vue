<script setup>
import { ref, inject, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';
import FlashMessage from '@/Components/shared/FlashMessage.vue';
import { usePushNotification } from '@/composables/usePushNotification';

const route = inject('route');
const page = usePage();
const user = computed(() => page.props.auth?.user);

const userDropdownOpen = ref(false);
const notifDropdownOpen = ref(false);
const pushNotifOpen = ref(false);
const mobileMenuOpen = ref(false);
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
    year: 'numeric',
    month: 'short',
    day: 'numeric',
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
        label: 'Riwayat Pengerjaan',
        icon: 'mdi:history',
        activeIcon: 'mdi:history',
        match: 'user.practice.history',
    },
    {
        href: safeRoute('user.practice.statistics'),
        label: 'Statistik Saya',
        icon: 'mdi:chart-bar',
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

// Mobile Bottom Bar Tabs (5 menu utama di bawah pada layar HP)
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
        icon: 'mdi:chart-bar',
        activeIcon: 'mdi:chart-box',
        match: 'user.practice.statistics',
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

onMounted(() => {
    if (user.value) {
        loadNotifications();
        pollInterval = setInterval(loadNotifications, 5000);
    }

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
        <!-- ====================================================================== -->
        <!-- HEADER (DESKTOP & MOBILE)                                              -->
        <!-- Layout Siswa: Header di atas, Konten di tengah, Footer di bawah         -->
        <!-- ====================================================================== -->
        <header class="sticky top-0 z-30 w-full border-b border-border/80 bg-card/90 backdrop-blur-md shadow-xs transition-colors duration-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16 sm:h-20 gap-4">
                    <!-- Brand & Logo -->
                    <div class="flex items-center gap-3 sm:gap-6 shrink-0">
                        <Link :href="safeRoute('user.dashboard')" class="flex items-center gap-3 group select-none">
                            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center text-white font-extrabold text-lg shadow-md shadow-blue-600/25 group-hover:scale-105 transition-transform duration-200">
                                K
                            </div>
                            <div class="leading-tight">
                                <span class="font-black text-base sm:text-lg tracking-tight block text-foreground">KPM SMART</span>
                                <span class="text-[10px] sm:text-[11px] font-bold text-primary tracking-wide uppercase">Platform Siswa</span>
                            </div>
                        </Link>
                    </div>

                    <!-- Desktop Navigation Links (Menu Siswa Bersih) -->
                    <nav class="hidden lg:flex items-center gap-1.5 xl:gap-2">
                        <Link
                            v-for="item in navLinks"
                            :key="item.href"
                            :href="item.href"
                            :aria-current="isActive(item.match) ? 'page' : undefined"
                            :class="[
                                'inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs xl:text-sm font-bold transition-all duration-200 select-none',
                                isActive(item.match)
                                    ? 'bg-primary/10 text-primary shadow-2xs'
                                    : 'text-muted-foreground hover:text-foreground hover:bg-muted/70'
                            ]"
                        >
                            <Icon :icon="isActive(item.match) ? item.activeIcon : item.icon" class="w-4 h-4 shrink-0" />
                            <span>{{ item.label }}</span>
                        </Link>
                    </nav>

                    <!-- Right Controls: Date, Push, Notifications, Profile -->
                    <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">
                        <!-- Date Pill (Desktop) -->
                        <div class="hidden xl:flex items-center gap-2 text-xs font-semibold text-muted-foreground bg-muted/60 border border-border/60 px-3.5 py-1.5 rounded-full">
                            <Icon icon="mdi:calendar-month-outline" class="w-3.5 h-3.5 text-primary" />
                            <span>{{ currentDate }}</span>
                        </div>

                        <!-- Push Notification Bell -->
                        <div v-if="pushSupported" class="relative" id="pushNotifWrap">
                            <button
                                @click="pushNotifOpen = !pushNotifOpen"
                                :aria-expanded="pushNotifOpen"
                                aria-label="Notifikasi Browser"
                                class="inline-flex items-center justify-center w-10 h-10 rounded-xl text-muted-foreground hover:bg-muted/80 hover:text-foreground transition border border-border/70 relative"
                                :title="pushSubscribed ? 'Notifikasi aktif' : 'Aktifkan notifikasi'"
                            >
                                <Icon :icon="pushSubscribed ? 'mdi:bell-ring-outline' : 'mdi:bell-off-outline'" class="w-5 h-5" :class="pushSubscribed ? 'text-primary' : ''" />
                            </button>

                            <Transition name="dropdown">
                                <div v-if="pushNotifOpen" class="absolute right-0 mt-2 w-72 bg-card text-card-foreground rounded-2xl shadow-xl border border-border/80 py-3 px-4 z-50">
                                    <p class="text-xs font-bold text-foreground">Notifikasi Browser</p>
                                    <p class="text-[11px] text-muted-foreground mt-0.5">Dapatkan notifikasi di browsermu ketika waktu pengerjaan PR dimulai.</p>
                                    <div class="mt-3 pt-3 border-t border-border/60">
                                        <button
                                            @click="togglePushNotification"
                                            :disabled="pushLoading"
                                            class="w-full py-2 px-3 rounded-xl text-xs font-bold flex items-center justify-center gap-2 transition"
                                            :class="pushSubscribed ? 'bg-rose-50 text-rose-700 hover:bg-rose-100' : 'bg-primary text-primary-foreground hover:bg-primary/90 shadow-xs'"
                                        >
                                            <Icon :icon="pushSubscribed ? 'mdi:bell-off' : 'mdi:bell-check'" class="w-4 h-4" />
                                            {{ pushLoading ? 'Memproses...' : (pushSubscribed ? 'Nonaktifkan' : 'Aktifkan Notifikasi') }}
                                        </button>
                                    </div>
                                </div>
                            </Transition>
                        </div>

                        <!-- Notification Bell & Dropdown -->
                        <div v-if="user" class="relative" id="notifWrap">
                            <button
                                @click="toggleNotifDropdown"
                                class="inline-flex items-center justify-center w-10 h-10 rounded-xl text-muted-foreground hover:bg-muted/80 hover:text-foreground transition relative border border-border/70"
                                aria-label="Notifikasi"
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
                                        <button @click="markAllRead" class="text-xs text-primary hover:text-primary/80 font-bold transition">Tandai dibaca</button>
                                    </div>
                                    <div class="max-h-80 overflow-y-auto divide-y divide-border/40">
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
                                            class="flex items-start gap-3 px-4 py-3 hover:bg-muted/60 transition-colors"
                                            :class="{ 'bg-primary/5': !n.is_read }"
                                        >
                                            <div :class="['w-2 h-2 rounded-full mt-1.5 shrink-0', n.is_read ? 'bg-transparent' : (n.type === 'announcement' ? 'bg-amber-500' : 'bg-primary')]"></div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-xs font-bold text-foreground truncate flex items-center gap-1.5">
                                                    <span v-if="n.type === 'announcement'" class="text-[9px] font-extrabold bg-amber-100 text-amber-800 px-1.5 py-0.5 rounded">PENGUMUMAN</span>
                                                    {{ n.title }}
                                                </p>
                                                <p class="text-xs text-muted-foreground truncate mt-0.5">{{ n.message }}</p>
                                                <span class="text-[10px] text-muted-foreground/60 block mt-1">{{ n.created_at }}</span>
                                            </div>
                                        </Link>
                                    </div>
                                    <div class="border-t border-border/60 pt-2 px-2">
                                        <Link :href="safeRoute('user.notifications.index')" class="block py-2 text-center text-xs text-primary hover:bg-muted/60 rounded-xl font-bold transition">
                                            Lihat Semua Notifikasi
                                        </Link>
                                    </div>
                                </div>
                            </Transition>
                        </div>

                        <!-- User Profile Dropdown -->
                        <div v-if="user" class="relative" id="userDropdown">
                            <button
                                @click="toggleUserDropdown"
                                class="inline-flex items-center gap-2 hover:bg-muted/70 rounded-xl p-1.5 sm:px-2.5 transition border border-border/70"
                            >
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-600 to-indigo-700 text-white font-bold text-xs flex items-center justify-center overflow-hidden shadow-2xs">
                                    <img v-if="profilePhotoUrl" :src="profilePhotoUrl" class="w-full h-full object-cover" />
                                    <span v-else>{{ (user?.name || 'A').charAt(0).toUpperCase() }}</span>
                                </div>
                                <div class="hidden sm:block text-left">
                                    <span class="text-xs font-bold text-foreground max-w-[120px] truncate block leading-tight">{{ user.name }}</span>
                                    <span v-if="user.bidang" class="text-[10px] text-primary font-semibold block leading-tight">{{ user.bidang }}</span>
                                </div>
                                <Icon icon="mdi:chevron-down" class="hidden sm:block w-3.5 h-3.5 text-muted-foreground transition-transform duration-200" :class="{ 'rotate-180': userDropdownOpen }" />
                            </button>

                            <Transition name="dropdown">
                                <div v-if="userDropdownOpen" class="absolute right-0 mt-2 w-60 bg-card text-card-foreground rounded-2xl shadow-xl border border-border/80 py-2 z-50">
                                    <div class="px-4 py-3 border-b border-border/60">
                                        <p class="text-xs font-extrabold text-foreground truncate">{{ user?.name }}</p>
                                        <p class="text-[11px] text-muted-foreground truncate mt-0.5">{{ user?.email || user?.username }}</p>
                                        <div class="flex items-center gap-1.5 mt-2">
                                            <span v-if="user?.bidang" class="text-[10px] font-bold bg-primary/10 text-primary border border-primary/20 px-2 py-0.5 rounded-md">
                                                {{ user.bidang }}
                                            </span>
                                            <span v-if="user?.kelas" class="text-[10px] font-bold bg-muted text-muted-foreground px-2 py-0.5 rounded-md">
                                                Kelas {{ user.kelas }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="py-1 px-1.5 space-y-0.5">
                                        <Link :href="safeRoute('user.profile.edit')" class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-foreground hover:bg-muted/70 rounded-xl transition">
                                            <Icon icon="mdi:account-outline" class="w-4 h-4 text-primary" />
                                            Profil Saya
                                        </Link>
                                        <Link :href="safeRoute('user.practice.statistics')" class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-foreground hover:bg-muted/70 rounded-xl transition">
                                            <Icon icon="mdi:chart-bar" class="w-4 h-4 text-indigo-600" />
                                            Statistik Saya
                                        </Link>
                                        <Link :href="safeRoute('user.profile.change-password')" class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-foreground hover:bg-muted/70 rounded-xl transition">
                                            <Icon icon="mdi:lock-reset" class="w-4 h-4 text-muted-foreground" />
                                            Ubah Password
                                        </Link>
                                    </div>
                                    <div class="border-t border-border/60 my-1"></div>
                                    <div class="px-1.5">
                                        <Link :href="safeRoute('logout')" method="post" as="button" class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 rounded-xl transition text-left">
                                            <Icon icon="mdi:logout" class="w-4 h-4" />
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

        <!-- ====================================================================== -->
        <!-- MAIN CONTENT                                                           -->
        <!-- ====================================================================== -->
        <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 w-full animate-fade-in pb-28 lg:pb-12">
            <FlashMessage />
            <slot />
        </main>

        <!-- ====================================================================== -->
        <!-- FOOTER (DESKTOP & TABLET/MOBILE CONTENT FOOTER)                        -->
        <!-- ====================================================================== -->
        <footer class="border-t border-border/80 bg-card/60 backdrop-blur-md py-8 sm:py-10 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                    <!-- Brand info -->
                    <div class="md:col-span-2 space-y-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-700 flex items-center justify-center text-white font-extrabold text-sm shadow-sm">
                                K
                            </div>
                            <span class="font-extrabold text-base tracking-tight text-foreground">KPM SMART</span>
                        </div>
                        <p class="text-xs text-muted-foreground leading-relaxed max-w-sm">
                            Platform pembelajaran dan pengerjaan PR online Klinik Pendidikan MIPA (KPM). Menumbuhkan keunggulan nalar matematika dan sains secara mandiri dan terarah.
                        </p>
                    </div>

                    <!-- Quick Navigation -->
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-foreground mb-3">Menu Siswa</h4>
                        <ul class="space-y-2 text-xs text-muted-foreground">
                            <li><Link :href="safeRoute('user.dashboard')" class="hover:text-primary transition">Dasbor</Link></li>
                            <li><Link :href="safeRoute('user.packages.index')" class="hover:text-primary transition">PR Pekanan</Link></li>
                            <li><Link :href="safeRoute('user.practice.history')" class="hover:text-primary transition">Riwayat Pengerjaan</Link></li>
                            <li><Link :href="safeRoute('user.leaderboard')" class="hover:text-primary transition">Papan Peringkat</Link></li>
                        </ul>
                    </div>

                    <!-- Information & Support -->
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-foreground mb-3">Informasi</h4>
                        <ul class="space-y-2 text-xs text-muted-foreground">
                            <li><Link :href="safeRoute('pages.guide')" class="hover:text-primary transition">Panduan Penggunaan</Link></li>
                            <li><Link :href="safeRoute('pages.faq')" class="hover:text-primary transition">Tanya Jawab (FAQ)</Link></li>
                            <li><Link :href="safeRoute('pages.features')" class="hover:text-primary transition">Fitur Unggulan</Link></li>
                            <li><Link :href="safeRoute('user.leave-requests.index')" class="hover:text-primary transition">Pengajuan Izin</Link></li>
                        </ul>
                    </div>
                </div>

                <div class="pt-6 border-t border-border/60 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-muted-foreground">
                    <p>&copy; {{ new Date().getFullYear() }} KPM SMART · Klinik Pendidikan MIPA. Semua hak cipta dilindungi.</p>
                    <p class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Sistem Berjalan Normal
                    </p>
                </div>
            </div>
        </footer>

        <!-- ====================================================================== -->
        <!-- MOBILE BOTTOM NAVIGATION BAR (KHUSUS MODE HANDPHONE / RESPONSIVE)      -->
        <!-- Menu diletakkan di bawah saat beralih ke responsive mode (HP)          -->
        <!-- ====================================================================== -->
        <nav class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-card/95 backdrop-blur-xl border-t border-border/80 safe-area-bottom shadow-2xl">
            <div class="grid grid-cols-5 h-16 items-center px-1" role="tablist">
                <!-- Tab items for students -->
                <Link
                    v-for="tab in mobileBottomTabs"
                    :key="tab.href"
                    :href="tab.href"
                    :aria-current="isActive(tab.match) ? 'page' : undefined"
                    class="flex flex-col items-center justify-center gap-1 py-1 relative transition-all duration-200 select-none"
                    :class="isActive(tab.match) ? 'text-primary font-bold' : 'text-muted-foreground hover:text-foreground'"
                >
                    <div class="relative flex items-center justify-center">
                        <Icon
                            :icon="isActive(tab.match) ? tab.activeIcon : tab.icon"
                            class="w-5 h-5 transition-transform duration-200"
                            :class="isActive(tab.match) ? 'scale-110 text-primary' : ''"
                        />
                        <div v-if="isActive(tab.match)" class="absolute -bottom-1.5 left-1/2 -translate-x-1/2 w-4 h-1 rounded-full bg-primary shadow-xs"></div>
                    </div>
                    <span class="text-[10px] leading-tight truncate max-w-[58px]">{{ tab.label }}</span>
                </Link>

                <!-- 5th Tab: Menu Lain / Profile Bottom Sheet Trigger -->
                <button
                    @click="openMobileMenu"
                    class="flex flex-col items-center justify-center gap-1 py-1 relative text-muted-foreground hover:text-foreground transition-all duration-200 select-none"
                    :class="mobileMenuOpen ? 'text-primary font-bold' : ''"
                >
                    <div class="relative flex items-center justify-center">
                        <Icon icon="mdi:dots-horizontal-circle-outline" class="w-5 h-5" />
                    </div>
                    <span class="text-[10px] leading-tight">Lainnya</span>
                </button>
            </div>
        </nav>

        <!-- ====================================================================== -->
        <!-- MOBILE MENU BOTTOM SHEET (POPUP DARI BAWAH)                             -->
        <!-- ====================================================================== -->
        <Transition name="overlay">
            <div v-if="mobileMenuOpen" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-50 lg:hidden" @click="closeMobileMenu" />
        </Transition>

        <Transition name="sheet">
            <div
                v-if="mobileMenuOpen"
                class="fixed bottom-0 inset-x-0 bg-card rounded-t-3xl border-t border-border z-50 max-h-[85vh] overflow-y-auto safe-area-bottom shadow-2xl p-5 lg:hidden"
            >
                <div class="w-12 h-1.5 rounded-full bg-muted-foreground/30 mx-auto mb-4"></div>

                <!-- User Mini Card -->
                <div class="flex items-center gap-3 p-3.5 bg-muted/40 rounded-2xl border border-border/60 mb-5">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white font-extrabold text-base flex items-center justify-center shrink-0 shadow-sm overflow-hidden">
                        <img v-if="profilePhotoUrl" :src="profilePhotoUrl" class="w-full h-full object-cover" />
                        <span v-else>{{ (user?.name || 'A').charAt(0).toUpperCase() }}</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-foreground truncate">{{ user?.name }}</p>
                        <p class="text-xs text-muted-foreground truncate">{{ user?.email || user?.username }}</p>
                        <div class="flex items-center gap-1.5 mt-1">
                            <span v-if="user?.bidang" class="text-[10px] font-bold bg-primary/10 text-primary px-2 py-0.5 rounded-md border border-primary/20">
                                {{ user.bidang }}
                            </span>
                            <span v-if="user?.kelas" class="text-[10px] font-bold bg-card border border-border px-2 py-0.5 rounded-md">
                                Kelas {{ user.kelas }}
                            </span>
                        </div>
                    </div>
                    <button @click="closeMobileMenu" class="p-2 text-muted-foreground hover:text-foreground rounded-lg">
                        <Icon icon="mdi:close" class="w-5 h-5" />
                    </button>
                </div>

                <!-- Menu Siswa Tambahan -->
                <p class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground px-2 mb-2">Menu Siswa</p>
                <div class="space-y-1 mb-5">
                    <Link
                        :href="safeRoute('user.leaderboard')"
                        @click="closeMobileMenu"
                        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold text-foreground hover:bg-muted/70 transition"
                    >
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-200 shrink-0">
                            <Icon icon="mdi:trophy-outline" class="w-4 h-4" />
                        </div>
                        <span>Papan Peringkat</span>
                    </Link>

                    <Link
                        :href="safeRoute('user.leave-requests.index')"
                        @click="closeMobileMenu"
                        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold text-foreground hover:bg-muted/70 transition"
                    >
                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-200 shrink-0">
                            <Icon icon="mdi:calendar-clock-outline" class="w-4 h-4" />
                        </div>
                        <span>Pengajuan Izin Siswa</span>
                    </Link>

                    <Link
                        :href="safeRoute('pages.guide')"
                        @click="closeMobileMenu"
                        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold text-foreground hover:bg-muted/70 transition"
                    >
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-200 shrink-0">
                            <Icon icon="mdi:help-circle-outline" class="w-4 h-4" />
                        </div>
                        <span>Panduan Belajar</span>
                    </Link>

                    <Link
                        :href="safeRoute('pages.faq')"
                        @click="closeMobileMenu"
                        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold text-foreground hover:bg-muted/70 transition"
                    >
                        <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center border border-sky-200 shrink-0">
                            <Icon icon="mdi:information-outline" class="w-4 h-4" />
                        </div>
                        <span>Tanya Jawab (FAQ)</span>
                    </Link>
                </div>

                <!-- Akun & Pengaturan -->
                <p class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground px-2 mb-2">Akun Saya</p>
                <div class="space-y-1 mb-5">
                    <Link
                        :href="safeRoute('user.profile.edit')"
                        @click="closeMobileMenu"
                        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold text-foreground hover:bg-muted/70 transition"
                    >
                        <Icon icon="mdi:account-outline" class="w-4 h-4 text-primary shrink-0" />
                        <span>Profil Saya</span>
                    </Link>

                    <Link
                        :href="safeRoute('user.profile.change-password')"
                        @click="closeMobileMenu"
                        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold text-foreground hover:bg-muted/70 transition"
                    >
                        <Icon icon="mdi:lock-reset" class="w-4 h-4 text-muted-foreground shrink-0" />
                        <span>Ubah Kata Sandi</span>
                    </Link>
                </div>

                <!-- Logout Button -->
                <div class="pt-3 border-t border-border/60">
                    <Link
                        :href="safeRoute('logout')"
                        method="post"
                        as="button"
                        class="w-full py-3 px-4 rounded-xl text-xs font-bold bg-rose-50 text-rose-600 hover:bg-rose-100 flex items-center justify-center gap-2 transition"
                    >
                        <Icon icon="mdi:logout" class="w-4 h-4" />
                        Keluar dari Akun
                    </Link>
                </div>
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
