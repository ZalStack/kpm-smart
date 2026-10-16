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
const notifDropdownOpen = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);

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

function toggleNotifDropdown() {
    notifDropdownOpen.value = !notifDropdownOpen.value;
    if (notifDropdownOpen.value) {
        loadNotifications();
    }
}

async function loadNotifications() {
    try {
        const response = await fetch(route('admin.notifications.dropdown'), {
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
        await fetch(route('admin.notifications.mark-all-read'), {
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
        return route('admin.announcements.show', n.data.announcement_id);
    }
    return route('admin.notifications.index');
}

async function handleNotifClick(n) {
    try {
        await fetch(route('admin.notifications.mark-read', n.id), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': page.props.csrfToken,
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
    } catch (e) {}
}

const navItems = [
    {
        label: 'Menu Utama',
        items: [
            { href: route('admin.dashboard'), label: 'Dasbor', icon: 'mdi:view-dashboard-outline', activeIcon: 'mdi:view-dashboard', route: 'admin.dashboard' },
            { href: route('admin.packages.index'), label: 'Soal Tugas', icon: 'mdi:book-open-variant', activeIcon: 'mdi:book-open-page-variant', route: 'admin.packages.*' },
            { href: route('admin.announcements.index'), label: 'Pengumuman', icon: 'mdi:bullhorn-outline', activeIcon: 'mdi:bullhorn', route: 'admin.announcements.*' },
        ],
    },
    {
        label: 'Manajemen Data',
        items: [
            { href: route('admin.users.index'), label: 'Manajemen Siswa', icon: 'mdi:account-group-outline', activeIcon: 'mdi:account-group', route: 'admin.users.*' },
            { href: route('admin.users.import-excel'), label: 'Import User', icon: 'mdi:file-excel-outline', activeIcon: 'mdi:file-excel', route: 'admin.users.import-excel' },
            { href: route('admin.practice-statistics.index'), label: 'Statistik Pengerjaan', icon: 'mdi:chart-box-outline', activeIcon: 'mdi:chart-box', route: 'admin.practice-statistics.*' },
            { href: route('admin.leave-requests.index'), label: 'Pengajuan Izin', icon: 'mdi:calendar-clock-outline', activeIcon: 'mdi:calendar-clock', route: 'admin.leave-requests.*' },
            { href: route('admin.login-logs.index'), label: 'Log Login', icon: 'mdi:shield-key-outline', activeIcon: 'mdi:shield-key', route: 'admin.login-logs.*' },
            { href: route('admin.notifications.index'), label: 'Notifikasi', icon: 'mdi:bell-outline', activeIcon: 'mdi:bell', route: 'admin.notifications.*' },
        ],
    },
];

const mobileNavItems = [
    { href: route('admin.dashboard'), label: 'Beranda', icon: 'mdi:view-dashboard-outline', route: 'admin.dashboard' },
    { href: route('admin.users.index'), label: 'Manajemen Siswa', icon: 'mdi:account-group-outline', route: 'admin.users.*' },
    { href: route('admin.packages.index'), label: 'Soal', icon: 'mdi:book-open-variant', route: 'admin.packages.*' },
    { href: route('admin.practice-statistics.index'), label: 'Statistik', icon: 'mdi:chart-box-outline', route: 'admin.practice-statistics.*' },
];

let pollInterval;
let clickHandler;
let resizeHandler;

onMounted(() => {
    loadNotifications();
    pollInterval = setInterval(loadNotifications, 15000);

    clickHandler = (e) => {
        if (!e.target.closest('#profileDropdownWrap')) profileDropdownOpen.value = false;
        if (!e.target.closest('#notifWrap')) notifDropdownOpen.value = false;
    };
    document.addEventListener('click', clickHandler);

    resizeHandler = () => {
        if (window.innerWidth >= 1024) closeSidebar();
    };
    window.addEventListener('resize', resizeHandler);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
    if (clickHandler) document.removeEventListener('click', clickHandler);
    if (resizeHandler) window.removeEventListener('resize', resizeHandler);
});
</script>

<template>
    <div class="min-h-screen bg-background text-foreground flex flex-col antialiased">
        <!-- Sidebar Overlay (Mobile) -->
        <Transition name="overlay">
            <div v-if="sidebarOpen" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-40 lg:hidden" @click="closeSidebar" />
        </Transition>

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed top-0 left-0 h-full w-[270px] sidebar-modern text-white z-50 overflow-hidden flex flex-col shadow-2xl transition-transform duration-300 ease-in-out',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
            ]"
        >
            <!-- Brand -->
            <div class="relative flex items-center justify-between gap-3.5 px-5 py-5 border-b border-white/10 flex-shrink-0">
                <Link :href="route('admin.dashboard')" class="flex items-center gap-3 min-w-0 group">
                    <div class="w-10 h-10 rounded-xl bg-white/15 backdrop-blur border border-white/20 flex items-center justify-center flex-shrink-0 shadow-sm group-hover:scale-105 transition-transform duration-200">
                        <span class="text-white text-base font-extrabold tracking-tight">K</span>
                    </div>
                    <div class="min-w-0">
                        <span class="text-sm font-bold text-white block leading-tight tracking-tight">KPM SMART</span>
                        <span class="text-[11px] text-white/60 font-medium">Administrator</span>
                    </div>
                </Link>
                <button @click="closeSidebar" class="lg:hidden p-1.5 rounded-lg text-white/60 hover:text-white hover:bg-white/10 transition">
                    <Icon icon="mdi:close" class="w-5 h-5" />
                </button>
            </div>

            <!-- Navigation -->
            <nav class="relative px-3 py-4 flex-1 min-h-0 overflow-y-auto sidebar-scroll space-y-6">
                <div v-for="group in navItems" :key="group.label" class="space-y-1">
                    <p class="text-[10px] uppercase tracking-widest text-white/40 font-bold px-3 mb-2">{{ group.label }}</p>
                    <Link
                        v-for="item in group.items"
                        :key="item.href"
                        :href="item.href"
                        :class="[
                            'flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-200 group text-sm',
                            route().current(item.route)
                                ? 'bg-white/15 text-white font-semibold shadow-xs'
                                : 'text-white/70 hover:bg-white/10 hover:text-white'
                        ]"
                    >
                        <div
                            :class="[
                                'w-8 h-8 rounded-lg flex items-center justify-center text-lg flex-shrink-0 transition-all duration-200',
                                route().current(item.route) ? 'bg-white text-emerald-800 shadow-xs scale-105' : 'bg-white/10 text-white group-hover:bg-white/20'
                            ]"
                        >
                            <Icon :icon="route().current(item.route) ? item.activeIcon : item.icon" class="w-4 h-4" />
                        </div>
                        <span class="truncate">{{ item.label }}</span>
                    </Link>
                </div>
            </nav>

            <!-- Sidebar Footer -->
            <div class="relative px-4 py-4 border-t border-white/10 flex-shrink-0 bg-black/10">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-white/20 border border-white/20 flex items-center justify-center text-white font-bold text-sm flex-shrink-0 overflow-hidden shadow-xs">
                        <img v-if="profilePhotoUrl" :src="profilePhotoUrl" class="w-full h-full object-cover" />
                        <span v-else>{{ (user?.name || 'A').charAt(0).toUpperCase() }}</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold text-white truncate leading-tight">{{ user?.name || 'Administrator' }}</p>
                        <p class="text-[11px] text-white/60 truncate mt-0.5">{{ user?.email }}</p>
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

                        <!-- Notifications Dropdown -->
                        <div class="relative" id="notifWrap">
                            <button
                                @click="toggleNotifDropdown"
                                class="inline-flex items-center justify-center w-10 h-10 rounded-xl text-muted-foreground hover:bg-accent hover:text-foreground transition relative border border-border/60"
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
                                        <button @click="markAllRead" class="text-xs text-primary hover:text-primary/80 font-semibold transition">Tandai dibaca</button>
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
                                            class="flex items-start gap-3 px-4 py-3 hover:bg-accent/60 transition-colors"
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
                                        <Link :href="route('admin.notifications.index')" class="block py-2 text-center text-xs text-primary hover:bg-accent/60 rounded-xl font-bold transition">
                                            Lihat Semua Notifikasi
                                        </Link>
                                    </div>
                                </div>
                            </Transition>
                        </div>

                        <!-- Profile Dropdown -->
                        <div class="relative" id="profileDropdownWrap">
                            <button
                                @click="toggleProfileDropdown"
                                class="inline-flex items-center gap-2 hover:bg-accent/70 rounded-xl p-1.5 sm:px-2.5 transition border border-border/60"
                            >
                                <div class="w-8 h-8 rounded-lg bg-primary text-primary-foreground font-bold text-xs flex items-center justify-center overflow-hidden shadow-2xs">
                                    <img v-if="profilePhotoUrl" :src="profilePhotoUrl" class="w-full h-full object-cover" />
                                    <span v-else>{{ (user?.name || 'A').charAt(0).toUpperCase() }}</span>
                                </div>
                                <div class="hidden sm:block text-left max-w-[120px]">
                                    <p class="text-xs font-bold text-foreground truncate leading-tight">{{ user?.name || 'Admin' }}</p>
                                    <p class="text-[10px] text-muted-foreground font-medium">Administrator</p>
                                </div>
                                <Icon icon="mdi:chevron-down" class="hidden sm:block w-3.5 h-3.5 text-muted-foreground transition-transform duration-200" :class="{ 'rotate-180': profileDropdownOpen }" />
                            </button>

                            <Transition name="dropdown">
                                <div v-if="profileDropdownOpen" class="absolute right-0 mt-2 w-56 bg-card text-card-foreground rounded-2xl shadow-xl border border-border/80 py-2 z-50">
                                    <div class="px-4 py-3 border-b border-border/60">
                                        <p class="text-xs font-bold text-foreground truncate">{{ user?.name }}</p>
                                        <p class="text-[11px] text-muted-foreground truncate mt-0.5">{{ user?.email }}</p>
                                    </div>
                                    <div class="py-1 px-1.5 space-y-0.5">
                                        <Link :href="route('admin.dashboard')" class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-foreground hover:bg-accent/70 rounded-xl transition">
                                            <Icon icon="mdi:view-dashboard-outline" class="w-4 h-4 text-muted-foreground" />
                                            Dasbor
                                        </Link>
                                        <Link :href="route('admin.profile.edit')" class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-foreground hover:bg-accent/70 rounded-xl transition">
                                            <Icon icon="mdi:account-cog-outline" class="w-4 h-4 text-muted-foreground" />
                                            Profil Saya
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
                    </div>
                </div>
            </header>

            <!-- Page Content Container -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto pb-24 lg:pb-8">
                <FlashMessage />
                <div class="anim-fade-in">
                    <slot />
                </div>
            </main>

            <!-- Mobile Bottom Navigation (Admin) -->
            <div class="lg:hidden fixed bottom-0 left-0 right-0 bg-card/90 backdrop-blur-lg border-t border-border/80 z-30 px-3 py-1.5 safe-area-bottom shadow-lg">
                <div class="flex items-center justify-around">
                    <Link
                        v-for="item in mobileNavItems"
                        :key="item.href"
                        :href="item.href"
                        :class="['mobile-nav-item relative', route().current(item.route) ? 'active' : '']"
                    >
                        <div v-if="route().current(item.route)" class="absolute -top-1 left-1/2 -translate-x-1/2 w-4 h-0.5 bg-primary rounded-full"></div>
                        <Icon :icon="item.icon" class="w-5 h-5" />
                        <span class="text-[10px] font-medium mt-0.5">{{ item.label }}</span>
                    </Link>
                    <button @click="openSidebar" class="mobile-nav-item relative">
                        <Icon icon="mdi:menu" class="w-5 h-5" />
                        <span class="text-[10px] font-medium mt-0.5">Lainnya</span>
                    </button>
                </div>
            </div>
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
