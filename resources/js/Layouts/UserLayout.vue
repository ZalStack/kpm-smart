<script setup>
import { ref, inject, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const route = inject('route');
import FlashMessage from '@/Components/shared/FlashMessage.vue';

const page = usePage();
const user = page.props.auth?.user;
const mobileMenuOpen = ref(false);
const userDropdownOpen = ref(false);
const notifDropdownOpen = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);

function toggleMobileMenu() {
    mobileMenuOpen.value = !mobileMenuOpen.value;
}

function closeMobileMenu() {
    mobileMenuOpen.value = false;
}

function toggleUserDropdown() {
    userDropdownOpen.value = !userDropdownOpen.value;
}

function toggleNotifDropdown() {
    notifDropdownOpen.value = !notifDropdownOpen.value;
    if (notifDropdownOpen.value) {
        loadNotifications();
    }
}

async function loadNotifications() {
    try {
        const response = await fetch(route('notifications.dropdown'), {
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
        await fetch(route('notifications.mark-all-read'), {
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

let pollInterval;

onMounted(() => {
    if (user) {
        loadNotifications();
        pollInterval = setInterval(loadNotifications, 10000);
    }

    document.addEventListener('click', (e) => {
        if (!e.target.closest('#userDropdown')) userDropdownOpen.value = false;
        if (!e.target.closest('#notifWrap')) notifDropdownOpen.value = false;
    });
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});
</script>

<template>
    <div class="min-h-screen flex flex-col bg-background">
        <!-- Navbar -->
        <nav class="sticky top-0 z-50 w-full border-b border-border/40 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Logo -->
                    <Link
                        :href="user?.role === 'admin' ? route('admin.dashboard') : (user ? route('user.dashboard') : '/')"
                        class="flex items-center gap-2 text-lg font-bold text-foreground hover:text-primary transition"
                    >
                        <span class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center text-primary-foreground text-sm font-bold">K</span>
                        <span class="hidden sm:inline">KPM SMART</span>
                    </Link>

                    <!-- Desktop Menu -->
                    <div class="hidden md:flex items-center gap-1">
                        <template v-if="user">
                            <template v-if="user.role === 'user'">
                                <Link :href="route('packages.index')" class="inline-flex items-center px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-accent rounded-md transition" :class="{ 'text-foreground bg-accent': route().current('packages.*') }">Tugas</Link>
                                <Link :href="route('practice.history')" class="inline-flex items-center px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-accent rounded-md transition" :class="{ 'text-foreground bg-accent': route().current('practice.*') }">Riwayat</Link>
                                <Link :href="route('leave-requests.index')" class="inline-flex items-center px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-accent rounded-md transition" :class="{ 'text-foreground bg-accent': route().current('leave-requests.*') }">Izin</Link>
                            </template>
                            <Link :href="user.role === 'admin' ? route('admin.dashboard') : route('user.dashboard')" class="inline-flex items-center justify-center bg-primary text-primary-foreground px-4 py-2 rounded-md text-sm font-medium hover:bg-primary/90 transition ml-2">Dasbor</Link>

                            <!-- Notification Bell -->
                            <div class="relative" id="notifWrap">
                                <button @click="toggleNotifDropdown" class="inline-flex items-center justify-center w-9 h-9 rounded-md text-muted-foreground hover:bg-accent hover:text-accent-foreground transition relative">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                                    <span v-if="unreadCount > 0" class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-destructive text-destructive-foreground text-[10px] font-bold rounded-full flex items-center justify-center">{{ unreadCount > 99 ? '99+' : unreadCount }}</span>
                                </button>
                                <Transition name="dropdown">
                                    <div v-if="notifDropdownOpen" class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-2rem)] bg-popover text-popover-foreground rounded-lg shadow-md border py-2 z-50">
                                        <div class="px-4 py-3 border-b flex items-center justify-between">
                                            <p class="text-sm font-semibold">Notifikasi</p>
                                            <button @click="markAllRead" class="text-xs text-muted-foreground hover:text-foreground font-medium transition">Tandai semua dibaca</button>
                                        </div>
                                        <div class="max-h-72 overflow-y-auto">
                                            <div v-if="notifications.length === 0" class="px-4 py-8 text-center text-muted-foreground text-sm">Tidak ada notifikasi</div>
                                            <Link v-for="n in notifications" :key="n.id" :href="route('notifications.index')" class="flex items-center gap-3 px-4 py-3 hover:bg-accent transition-colors" :class="{ 'bg-accent/50': !n.is_read }">
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm font-medium truncate">{{ n.title }}</p>
                                                    <p class="text-xs text-muted-foreground truncate">{{ n.message }}</p>
                                                </div>
                                                <span class="text-[10px] text-muted-foreground whitespace-nowrap">{{ n.created_at }}</span>
                                            </Link>
                                        </div>
                                        <div class="border-t mt-1 pt-1">
                                            <Link :href="route('notifications.index')" class="block px-4 py-2.5 text-sm text-center text-muted-foreground hover:bg-accent transition font-medium">Lihat Semua Notifikasi</Link>
                                        </div>
                                    </div>
                                </Transition>
                            </div>

                            <!-- User Dropdown -->
                            <div class="relative" id="userDropdown">
                                <button @click="toggleUserDropdown" class="inline-flex items-center gap-2 text-muted-foreground hover:text-foreground transition px-2 py-1.5 rounded-md hover:bg-accent">
                                    <span class="w-7 h-7 rounded-full bg-primary flex items-center justify-center text-xs font-bold text-primary-foreground">{{ (user.name || 'A').charAt(0).toUpperCase() }}</span>
                                    <span class="hidden sm:inline text-sm">{{ user.name?.substring(0, 15) }}</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                                <Transition name="dropdown">
                                    <div v-if="userDropdownOpen" class="absolute right-0 mt-2 w-56 bg-popover text-popover-foreground rounded-lg shadow-md py-1 z-50 border">
                                        <div class="px-4 py-3 border-b">
                                            <p class="font-semibold text-sm truncate">{{ user.name }}</p>
                                            <p class="text-xs text-muted-foreground truncate mt-0.5">{{ user.email }}</p>
                                        </div>
                                        <Link :href="route('profile.edit')" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-accent hover:text-accent-foreground transition rounded-md mx-1">Profil Saya</Link>
                                        <Link v-if="user.role === 'user'" :href="route('practice.statistics')" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-accent hover:text-accent-foreground transition rounded-md mx-1">Statistik</Link>
                                        <div class="border-t my-1"></div>
                                        <Link :href="route('logout')" method="post" as="button" class="flex items-center gap-2 px-4 py-2 text-sm text-destructive hover:bg-destructive/10 transition w-full text-left rounded-md mx-1">Keluar</Link>
                                    </div>
                                </Transition>
                            </div>
                        </template>
                        <Link v-else :href="route('login')" class="inline-flex items-center px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-accent rounded-md transition">Masuk</Link>
                    </div>

                    <!-- Mobile Hamburger -->
                    <button @click="toggleMobileMenu" class="md:hidden inline-flex items-center justify-center w-9 h-9 rounded-md text-muted-foreground hover:bg-accent hover:text-accent-foreground transition" aria-label="Menu">
                        <svg v-if="!mobileMenuOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <Transition name="mobile-menu">
                <div v-if="mobileMenuOpen" class="md:hidden border-t bg-background shadow-lg">
                    <div class="px-4 py-3 space-y-1">
                        <template v-if="user">
                            <div class="flex items-center gap-3 px-3 py-3 rounded-lg bg-muted mb-2">
                                <span class="w-9 h-9 rounded-full bg-primary flex items-center justify-center text-sm font-bold text-primary-foreground flex-shrink-0">{{ (user.name || 'A').charAt(0).toUpperCase() }}</span>
                                <div class="min-w-0">
                                    <p class="font-semibold text-sm truncate">{{ user.name }}</p>
                                    <p class="text-xs text-muted-foreground truncate">{{ user.email }}</p>
                                </div>
                            </div>
                            <template v-if="user.role === 'user'">
                                <Link :href="route('user.dashboard')" @click="closeMobileMenu" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-accent transition text-sm" :class="route().current('user.dashboard') ? 'bg-accent text-foreground' : 'text-muted-foreground'">Dasbor</Link>
                                <Link :href="route('packages.index')" @click="closeMobileMenu" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-accent transition text-sm" :class="route().current('packages.*') ? 'bg-accent text-foreground' : 'text-muted-foreground'">Tugas</Link>
                                <Link :href="route('practice.history')" @click="closeMobileMenu" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-accent transition text-sm" :class="route().current('practice.*') ? 'bg-accent text-foreground' : 'text-muted-foreground'">Riwayat</Link>
                                <Link :href="route('leave-requests.index')" @click="closeMobileMenu" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-accent transition text-sm" :class="route().current('leave-requests.*') ? 'bg-accent text-foreground' : 'text-muted-foreground'">Izin</Link>
                                <Link :href="route('profile.edit')" @click="closeMobileMenu" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-accent transition text-sm" :class="route().current('profile.*') ? 'bg-accent text-foreground' : 'text-muted-foreground'">Profil Saya</Link>
                            </template>
                            <template v-else>
                                <Link :href="route('admin.dashboard')" @click="closeMobileMenu" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-accent transition text-sm" :class="route().current('admin.dashboard') ? 'bg-accent text-foreground' : 'text-muted-foreground'">Dasbor Admin</Link>
                            </template>
                            <Link :href="route('logout')" method="post" as="button" @click="closeMobileMenu" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-destructive hover:bg-destructive/10 transition text-sm text-left border-t pt-2">Keluar</Link>
                        </template>
                        <Link v-else :href="route('login')" @click="closeMobileMenu" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-accent transition text-sm text-muted-foreground">Masuk</Link>
                    </div>
                </div>
            </Transition>
        </nav>

        <!-- Main Content -->
        <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-8 w-full animate-fade-in">
            <FlashMessage />
            <div v-if="$slots['header-title']" class="mb-6">
                <h1 class="text-lg font-semibold tracking-tight truncate"><slot name="header-title" /></h1>
                <p v-if="$slots['header-sub']" class="text-xs text-muted-foreground mt-0.5 truncate"><slot name="header-sub" /></p>
            </div>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="border-t bg-muted/40 pt-12 md:pt-16 pb-6 md:pb-8 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 md:gap-10">
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-9 h-9 rounded-lg bg-primary flex items-center justify-center text-primary-foreground text-sm font-bold">K</div>
                            <div>
                                <span class="text-lg font-bold">KPM</span>
                                <span class="text-xs block -mt-0.5 text-muted-foreground">Belajar Online</span>
                            </div>
                        </div>
                        <p class="text-muted-foreground text-sm leading-relaxed">Platform Tugas Online untuk mendukung pembelajaran dan pengerjaan tugas mandiri.</p>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-4 text-sm">Menu</h4>
                        <ul class="space-y-2 text-muted-foreground text-sm">
                            <li><Link :href="route('pages.features')" class="hover:text-foreground transition-all duration-200">Fitur Unggulan</Link></li>
                            <li><Link :href="user ? route('packages.index') : '/#packages'" class="hover:text-foreground transition-all duration-200">Tugas</Link></li>
                            <li v-if="user"><Link :href="route('practice.history')" class="hover:text-foreground transition-all duration-200">Riwayat</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-4 text-sm">Bantuan</h4>
                        <ul class="space-y-2 text-muted-foreground text-sm">
                            <li><Link :href="route('pages.guide')" class="hover:text-foreground transition-all duration-200">Panduan Penggunaan</Link></li>
                            <li><Link :href="route('pages.faq')" class="hover:text-foreground transition-all duration-200">FAQ</Link></li>
                            <li><button type="button" class="hover:text-foreground transition-all duration-200">Hubungi Kami</button></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-4 text-sm">Kontak</h4>
                        <ul class="space-y-2 text-muted-foreground text-sm">
                            <li>info@pkalitbang.id</li>
                            <li>+62 821-2343-9604</li>
                            <li>Bogor, Indonesia</li>
                        </ul>
                    </div>
                </div>
                <div class="border-t mt-8 pt-6 text-center text-muted-foreground text-sm">&copy; {{ new Date().getFullYear() }} KPM SMART. Hak cipta dilindungi.</div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.dropdown-enter-active { transition: all 0.15s ease; }
.dropdown-leave-active { transition: all 0.1s ease; }
.dropdown-enter-from { opacity: 0; transform: scale(0.95); }
.dropdown-leave-to { opacity: 0; transform: scale(0.95); }

.mobile-menu-enter-active { transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
.mobile-menu-leave-active { transition: all 0.25s ease; }
.mobile-menu-enter-from { max-height: 0; opacity: 0; overflow: hidden; }
.mobile-menu-enter-to { max-height: 500px; opacity: 1; }
.mobile-menu-leave-from { max-height: 500px; opacity: 1; }
.mobile-menu-leave-to { max-height: 0; opacity: 0; overflow: hidden; }
</style>
