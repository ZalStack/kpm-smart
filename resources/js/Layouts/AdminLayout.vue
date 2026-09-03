<script setup>
import { ref, inject, onMounted, onUnmounted, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import FlashMessage from '@/Components/shared/FlashMessage.vue';

const route = inject('route');

const page = usePage();
const user = page.props.auth?.user;
const sidebarOpen = ref(false);
const profileDropdownOpen = ref(false);
const notifDropdownOpen = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);

const currentDate = new Date().toLocaleDateString('id-ID', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
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

const navItems = [
    {
        label: 'Menu Utama',
        items: [
            { href: route('admin.dashboard'), label: 'Dasbor', icon: 'M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 12a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1v-7z', route: 'admin.dashboard' },
            { href: route('admin.packages.index'), label: 'Paket Tugas', icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', route: 'admin.packages.*' },
        ],
    },
    {
        label: 'Manajemen',
        items: [
            { href: route('admin.users.index'), label: 'Pengguna', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z', route: 'admin.users.*' },
            { href: route('admin.practice-statistics.index'), label: 'Statistik Pengerjaan', icon: 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z', route: 'admin.practice-statistics.*' },
            { href: route('admin.leave-requests.index'), label: 'Pengajuan Izin', icon: 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z', route: 'admin.leave-requests.*' },
            { href: route('admin.login-logs.index'), label: 'Log Login', icon: 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z', route: 'admin.login-logs.*' },
            { href: route('admin.notifications.index'), label: 'Notifikasi', icon: 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0', route: 'admin.notifications.*' },
        ],
    },
    {
        label: 'Komunitas',
        items: [
            { href: route('admin.support.index'), label: 'Bantuan', icon: 'M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z', route: 'admin.support.*' },
        ],
    },
];

const mobileNavItems = [
    { href: route('admin.dashboard'), label: 'Beranda', icon: 'M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 12a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1v-7z', route: 'admin.dashboard' },
    { href: route('admin.users.index'), label: 'Pengguna', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z', route: 'admin.users.*' },
    { href: route('admin.packages.index'), label: 'Paket', icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', route: 'admin.packages.*' },
];

let pollInterval;

onMounted(() => {
    loadNotifications();
    pollInterval = setInterval(loadNotifications, 10000);

    document.addEventListener('click', (e) => {
        if (!e.target.closest('#profileDropdownWrap')) profileDropdownOpen.value = false;
        if (!e.target.closest('#notifWrap')) notifDropdownOpen.value = false;
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024) closeSidebar();
    });
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});
</script>

<template>
    <div class="min-h-screen bg-background">
        <!-- Sidebar Overlay -->
        <Transition name="overlay">
            <div v-if="sidebarOpen" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 lg:hidden" @click="closeSidebar" />
        </Transition>

        <!-- Sidebar -->
        <aside :class="['fixed top-0 left-0 h-full w-[272px] sidebar-modern text-white z-50 overflow-hidden transition-all duration-300 flex flex-col shadow-sidebar', sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0']">
            <div class="absolute inset-0 overflow-hidden pointer-events-none">
                <div class="absolute -top-20 -right-20 w-40 h-40 bg-white/5 rounded-full blur-3xl"></div>
                <div class="absolute bottom-20 -left-10 w-32 h-32 bg-white/5 rounded-full blur-2xl"></div>
            </div>

            <!-- Brand -->
            <div class="relative flex items-center gap-3 px-5 py-5 border-b border-white/10 flex-shrink-0">
                <div class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center flex-shrink-0">
                    <span class="text-white text-sm font-bold">K</span>
                </div>
                <div class="min-w-0">
                    <span class="text-sm font-semibold text-white block leading-tight">KPM SMART</span>
                    <span class="text-[10px] text-white/40 font-medium">Admin Panel</span>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="relative px-3 py-4 flex-1 min-h-0 overflow-y-auto sidebar-scroll">
                <template v-for="group in navItems" :key="group.label">
                    <p class="text-[10px] uppercase tracking-widest text-white/25 font-semibold px-3 mb-3 mt-4 first:mt-0">{{ group.label }}</p>
                    <Link
                        v-for="item in group.items"
                        :key="item.href"
                        :href="item.href"
                        :class="[
                            'flex items-center gap-3 px-3 py-2 rounded-md transition-all duration-200 mb-0.5',
                            route().current(item.route) ? 'nav-active' : 'text-white/55 hover:bg-white/5 hover:text-white'
                        ]"
                    >
                        <div :class="['w-8 h-8 rounded-md flex items-center justify-center text-sm flex-shrink-0 transition-colors', route().current(item.route) ? 'bg-white/10' : 'bg-white/5']">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" :d="item.icon"/></svg>
                        </div>
                        <span class="text-[13px] font-medium">{{ item.label }}</span>
                    </Link>
                </template>
            </nav>

            <!-- Sidebar Footer -->
            <div class="relative px-4 py-4 border-t border-white/10 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-md bg-white/10 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                        {{ (user?.name || 'A').charAt(0).toUpperCase() }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[13px] font-medium text-white truncate">{{ user?.name || 'Admin' }}</p>
                        <p class="text-[10px] text-white/40 truncate">{{ user?.email }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="lg:ml-[272px] min-h-screen flex flex-col">
            <!-- Top Header -->
            <header class="sticky top-0 z-30 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
                <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 h-16">
                    <div class="flex items-center gap-3 min-w-0">
                        <button @click="openSidebar" class="lg:hidden inline-flex items-center justify-center w-9 h-9 rounded-md text-muted-foreground hover:bg-accent hover:text-accent-foreground transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                        </button>
                        <div class="min-w-0">
                            <h1 class="text-lg font-semibold tracking-tight truncate">
                                <slot name="header-title" />
                            </h1>
                            <p class="text-xs text-muted-foreground hidden sm:block truncate mt-0.5">
                                <slot name="header-sub" />
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <div class="hidden md:flex items-center gap-2 text-xs text-muted-foreground bg-muted px-3 py-1.5 rounded-md">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                            <span class="font-medium">{{ currentDate }}</span>
                        </div>

                        <!-- Notifications -->
                        <div class="relative" id="notifWrap">
                            <button @click="toggleNotifDropdown" class="inline-flex items-center justify-center w-9 h-9 rounded-md text-muted-foreground hover:bg-accent hover:text-accent-foreground transition relative">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                                <span v-if="unreadCount > 0" class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-destructive text-destructive-foreground text-[10px] font-bold rounded-full flex items-center justify-center animate-pulse-soft">{{ unreadCount > 99 ? '99+' : unreadCount }}</span>
                            </button>
                            <Transition name="dropdown">
                                <div v-if="notifDropdownOpen" class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-2rem)] bg-popover text-popover-foreground rounded-lg shadow-md border py-2 z-50">
                                    <div class="px-4 py-3 border-b flex items-center justify-between">
                                        <p class="text-sm font-semibold">Notifikasi</p>
                                        <button @click="markAllRead" class="text-xs text-muted-foreground hover:text-foreground font-medium transition">Tandai semua dibaca</button>
                                    </div>
                                    <div class="max-h-72 overflow-y-auto">
                                        <div v-if="notifications.length === 0" class="px-4 py-8 text-center text-muted-foreground text-sm">Tidak ada notifikasi</div>
                                        <Link v-for="n in notifications" :key="n.id" :href="route('admin.notifications.index')" class="flex items-center gap-3 px-4 py-3 hover:bg-accent transition-colors" :class="{ 'bg-accent/50': !n.is_read }">
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-medium truncate">{{ n.title }}</p>
                                                <p class="text-xs text-muted-foreground truncate">{{ n.message }}</p>
                                            </div>
                                            <span class="text-[10px] text-muted-foreground whitespace-nowrap">{{ n.created_at }}</span>
                                        </Link>
                                    </div>
                                    <div class="border-t mt-1 pt-1">
                                        <Link :href="route('admin.notifications.index')" class="block px-4 py-2.5 text-sm text-center text-muted-foreground hover:bg-accent transition font-medium">Lihat Semua Notifikasi</Link>
                                    </div>
                                </div>
                            </Transition>
                        </div>

                        <!-- Profile Dropdown -->
                        <div class="relative" id="profileDropdownWrap">
                            <button @click="toggleProfileDropdown" class="inline-flex items-center gap-2 hover:bg-accent rounded-md px-2 py-1.5 transition">
                                <div class="w-8 h-8 rounded-md bg-primary flex items-center justify-center text-primary-foreground font-bold text-sm">
                                    {{ (user?.name || 'A').charAt(0).toUpperCase() }}
                                </div>
                                <div class="hidden sm:block text-left">
                                    <p class="text-sm font-medium leading-tight">{{ user?.name || 'Admin' }}</p>
                                    <p class="text-[10px] text-muted-foreground">Admin</p>
                                </div>
                                <svg class="hidden sm:block w-4 h-4 text-muted-foreground transition-transform duration-200" :class="{ 'rotate-180': profileDropdownOpen }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                            </button>
                            <Transition name="dropdown">
                                <div v-if="profileDropdownOpen" class="absolute right-0 mt-2 w-56 bg-popover text-popover-foreground rounded-lg shadow-md border py-2 z-50">
                                    <div class="px-4 py-3 border-b">
                                        <p class="text-sm font-semibold truncate">{{ user?.name }}</p>
                                        <p class="text-xs text-muted-foreground truncate mt-0.5">{{ user?.email }}</p>
                                    </div>
                                    <Link :href="route('admin.dashboard')" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-accent hover:text-accent-foreground transition rounded-md mx-1">
                                        <svg class="w-4 h-4 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                        Dasbor
                                    </Link>
                                    <div class="border-t my-1"></div>
                                    <Link :href="route('logout')" method="post" as="button" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-destructive hover:bg-destructive/10 transition rounded-md mx-1">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                                        Keluar
                                    </Link>
                                </div>
                            </Transition>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                <FlashMessage />
                <div class="animate-fade-in">
                    <slot />
                </div>
            </main>

            <!-- Mobile Bottom Navigation -->
            <div class="lg:hidden fixed bottom-0 left-0 right-0 bg-background/95 backdrop-blur border-t z-30 px-2 py-1 safe-area-bottom">
                <div class="flex items-center justify-around">
                    <Link
                        v-for="item in mobileNavItems"
                        :key="item.href"
                        :href="item.href"
                        :class="['mobile-nav-item', route().current(item.route) ? 'active' : '']"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" :d="item.icon"/></svg>
                        <span class="text-[10px] font-medium">{{ item.label }}</span>
                    </Link>
                    <button @click="openSidebar" class="mobile-nav-item">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                        <span class="text-[10px] font-medium">Lainnya</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.overlay-enter-active { transition: opacity 0.3s ease; }
.overlay-leave-active { transition: opacity 0.3s ease; }
.overlay-enter-from, .overlay-leave-to { opacity: 0; }

.dropdown-enter-active { transition: all 0.15s ease; }
.dropdown-leave-active { transition: all 0.1s ease; }
.dropdown-enter-from { opacity: 0; transform: scale(0.95); }
.dropdown-leave-to { opacity: 0; transform: scale(0.95); }
</style>
