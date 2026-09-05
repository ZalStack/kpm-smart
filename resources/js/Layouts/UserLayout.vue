<script setup>
import { ref, inject, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue';
import FlashMessage from '@/Components/shared/FlashMessage.vue';

const route = inject('route');
const page = usePage();
const user = computed(() => page.props.auth?.user);
const userDropdownOpen = ref(false);
const notifDropdownOpen = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);

const profilePhotoUrl = computed(() => {
    if (!user.value?.profile_photo) return null;
    return '/storage/' + user.value.profile_photo + '?v=' + encodeURIComponent(user.value.profile_photo);
});

const bottomTabs = computed(() => {
    if (!user || user.role !== 'user') return [];
    return [
        { label: 'Dashboard', route: 'user.dashboard', icon: 'mdi:view-dashboard-outline', iconActive: 'mdi:view-dashboard', match: 'user.dashboard' },
        { label: 'Tugas PR', route: 'packages.index', icon: 'mdi:book-open-variant', iconActive: 'mdi:book-open-page-variant', match: 'packages.*' },
        { label: 'Riwayat', route: 'practice.history', icon: 'mdi:history', iconActive: 'mdi:history', match: 'practice.*' },
        { label: 'Peringkat', route: 'leaderboard', icon: 'mdi:trophy-outline', iconActive: 'mdi:trophy', match: 'leaderboard' },
        { label: 'Izin', route: 'leave-requests.index', icon: 'mdi:calendar-blank-outline', iconActive: 'mdi:calendar-check', match: 'leave-requests.*' },
    ];
});

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
    if (user.value) {
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
        <!-- ======================== DESKTOP TOP NAVBAR ======================== -->
        <nav class="hidden md:block sticky top-0 z-50 w-full border-b border-border/30 bg-background/80 backdrop-blur-xl supports-[backdrop-filter]:bg-background/60 shadow-[0_1px_3px_0_rgb(0,0,0,0.04)]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <Link :href="user?.role === 'admin' ? route('admin.dashboard') : (user ? route('user.dashboard') : '/')" class="flex items-center gap-2.5 text-lg font-bold text-foreground hover:text-primary transition-all duration-300 group">
                        <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary to-primary/80 flex items-center justify-center text-primary-foreground text-sm font-bold shadow-sm group-hover:shadow-md group-hover:scale-105 transition-all duration-300">K</span>
                        <span class="tracking-tight">KPM SMART</span>
                    </Link>

                    <div class="flex items-center gap-1">
                        <template v-if="user">
                            <template v-if="user.role === 'user'">
                                <Link :href="route('user.dashboard')" class="nav-link relative inline-flex items-center px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-accent/70 rounded-lg transition-all duration-200" :class="{ 'text-foreground bg-accent': isActive('user.dashboard') }">Dashboard</Link>
                                <Link :href="route('packages.index')" class="nav-link relative inline-flex items-center px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-accent/70 rounded-lg transition-all duration-200" :class="{ 'text-foreground bg-accent': isActive('packages.*') }">Tugas PR</Link>
                                <Link :href="route('practice.history')" class="nav-link relative inline-flex items-center px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-accent/70 rounded-lg transition-all duration-200" :class="{ 'text-foreground bg-accent': isActive('practice.*') }">Riwayat</Link>
                                <Link :href="route('leaderboard')" class="nav-link relative inline-flex items-center px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-accent/70 rounded-lg transition-all duration-200" :class="{ 'text-foreground bg-accent': isActive('leaderboard') }">Peringkat</Link>
                                <Link :href="route('leave-requests.index')" class="nav-link relative inline-flex items-center px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-accent/70 rounded-lg transition-all duration-200" :class="{ 'text-foreground bg-accent': isActive('leave-requests.*') }">Izin</Link>
                            </template>

                            <!-- Desktop Notification Bell -->
                            <div class="relative" id="notifWrap">
                                <button @click="toggleNotifDropdown" class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-muted-foreground hover:bg-accent/70 hover:text-accent-foreground transition-all duration-200 relative">
                                    <Icon icon="mdi:bell-outline" class="w-[18px] h-[18px]" />
                                    <span v-if="unreadCount > 0" class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] bg-destructive text-destructive-foreground text-[10px] font-bold rounded-full flex items-center justify-center shadow-sm ring-2 ring-background">{{ unreadCount > 99 ? '99+' : unreadCount }}</span>
                                </button>
                                <Transition name="dropdown">
                                    <div v-if="notifDropdownOpen" class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-2rem)] bg-popover text-popover-foreground rounded-xl shadow-xl border py-2 z-50">
                                        <div class="px-4 py-3 border-b flex items-center justify-between">
                                            <p class="text-sm font-semibold">Notifikasi</p>
                                            <button @click="markAllRead" class="text-xs text-muted-foreground hover:text-foreground font-medium transition-colors duration-200">Tandai semua dibaca</button>
                                        </div>
                                        <div class="max-h-72 overflow-y-auto">
                                            <div v-if="notifications.length === 0" class="px-4 py-10 text-center">
                                                <Icon icon="mdi:bell-off-outline" class="w-10 h-10 text-muted-foreground/40 mx-auto mb-2" />
                                                <p class="text-sm text-muted-foreground font-medium">Tidak ada notifikasi</p>
                                                <p class="text-xs text-muted-foreground/60 mt-0.5">Notifikasi baru akan muncul di sini</p>
                                            </div>
                                            <Link v-for="n in notifications" :key="n.id" :href="route('notifications.index')" class="flex items-center gap-3 px-4 py-3 hover:bg-accent/70 transition-all duration-200 border-b border-border/40 last:border-0" :class="{ 'bg-accent/40': !n.is_read }">
                                                <div class="flex-shrink-0 w-2 h-2 rounded-full bg-primary/60" :class="{ 'opacity-0': n.is_read }"></div>
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm font-medium truncate">{{ n.title }}</p>
                                                    <p class="text-xs text-muted-foreground truncate mt-0.5">{{ n.message }}</p>
                                                </div>
                                                <span class="text-[10px] text-muted-foreground/70 whitespace-nowrap">{{ n.created_at }}</span>
                                            </Link>
                                        </div>
                                        <div class="border-t mx-2 mt-1 pt-1">
                                            <Link :href="route('notifications.index')" class="block px-3 py-2.5 text-sm text-center text-muted-foreground hover:text-foreground hover:bg-accent/70 transition-all duration-200 rounded-lg font-medium">Lihat Semua Notifikasi</Link>
                                        </div>
                                    </div>
                                </Transition>
                            </div>

                            <!-- Desktop User Dropdown -->
                            <div class="relative" id="userDropdown">
                                <button @click="toggleUserDropdown" class="inline-flex items-center gap-2 text-muted-foreground hover:text-foreground transition-all duration-200 px-2 py-1.5 rounded-lg hover:bg-accent/70">
                                    <span class="w-7 h-7 rounded-full bg-gradient-to-br from-primary to-primary/80 flex items-center justify-center text-xs font-bold text-primary-foreground shadow-sm overflow-hidden">
                                        <img v-if="profilePhotoUrl" :src="profilePhotoUrl" class="w-full h-full object-cover" />
                                        <span v-else>{{ (user?.name || 'A').charAt(0).toUpperCase() }}</span>
                                    </span>
                                    <span class="text-sm">{{ user.name?.substring(0, 15) }}</span>
                                    <Icon icon="mdi:chevron-down" class="w-3.5 h-3.5 transition-transform duration-200" :class="{ 'rotate-180': userDropdownOpen }" />
                                </button>
                                <Transition name="dropdown">
                                    <div v-if="userDropdownOpen" class="absolute right-0 mt-2 w-56 bg-popover text-popover-foreground rounded-xl shadow-xl py-1.5 z-50 border">
                                        <div class="px-4 py-3 border-b mx-1">
                                            <p class="font-semibold text-sm truncate">{{ user.name }}</p>
                                            <p class="text-xs text-muted-foreground truncate mt-0.5">{{ user.email }}</p>
                                        </div>
                                        <div class="py-1">
                                            <Link :href="route('profile.edit')" class="flex items-center gap-2.5 px-4 py-2 text-sm hover:bg-accent/70 hover:text-accent-foreground transition-all duration-200 rounded-lg mx-1.5">
                                                <Icon icon="mdi:account-outline" class="w-4 h-4" /> Profil Saya
                                            </Link>
                                            <Link v-if="user.role === 'user'" :href="route('practice.statistics')" class="flex items-center gap-2.5 px-4 py-2 text-sm hover:bg-accent/70 hover:text-accent-foreground transition-all duration-200 rounded-lg mx-1.5">
                                                <Icon icon="mdi:chart-bar" class="w-4 h-4" /> Statistik
                                            </Link>
                                        </div>
                                        <div class="border-t mx-3 my-1"></div>
                                        <Link :href="route('logout')" method="post" as="button" class="flex items-center gap-2.5 px-4 py-2 text-sm text-destructive hover:bg-destructive/10 transition-all duration-200 w-full text-left rounded-lg mx-1.5">
                                            <Icon icon="mdi:logout" class="w-4 h-4" /> Keluar
                                        </Link>
                                    </div>
                                </Transition>
                            </div>
                        </template>
                        <Link v-else :href="route('login')" class="inline-flex items-center px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-accent/70 rounded-lg transition-all duration-200">Masuk</Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- ======================== MOBILE TOP BAR ======================== -->
        <nav class="md:hidden sticky top-0 z-50 w-full bg-background/80 backdrop-blur-xl supports-[backdrop-filter]:bg-background/60 border-b border-border/20">
            <div class="flex items-center justify-between h-14 px-4">
                <Link :href="route('user.dashboard')" class="flex items-center gap-2 text-base font-bold text-foreground">
                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-primary to-primary/80 flex items-center justify-center text-primary-foreground text-xs font-bold shadow-sm">K</span>
                    <span class="tracking-tight">KPM SMART</span>
                </Link>
                <div class="flex items-center gap-1">
                    <!-- Mobile Notification Bell -->
                    <div class="relative" id="notifWrapMobile">
                        <button @click="toggleNotifDropdown" class="relative inline-flex items-center justify-center w-9 h-9 rounded-lg text-muted-foreground hover:bg-accent/70 transition-all duration-200">
                            <Icon icon="mdi:bell-outline" class="w-5 h-5" />
                            <span v-if="unreadCount > 0" class="absolute top-1 right-1 min-w-[16px] h-[16px] bg-destructive text-destructive-foreground text-[9px] font-bold rounded-full flex items-center justify-center shadow-sm ring-2 ring-background">{{ unreadCount > 99 ? '99+' : unreadCount }}</span>
                        </button>
                        <Transition name="dropdown">
                            <div v-if="notifDropdownOpen" class="absolute right-0 mt-2 w-72 max-w-[calc(100vw-2rem)] bg-popover text-popover-foreground rounded-xl shadow-xl border py-2 z-50">
                                <div class="px-4 py-3 border-b flex items-center justify-between">
                                    <p class="text-sm font-semibold">Notifikasi</p>
                                    <button @click="markAllRead" class="text-xs text-muted-foreground hover:text-foreground font-medium transition-colors duration-200">Tandai semua dibaca</button>
                                </div>
                                <div class="max-h-64 overflow-y-auto">
                                    <div v-if="notifications.length === 0" class="px-4 py-8 text-center">
                                        <Icon icon="mdi:bell-off-outline" class="w-8 h-8 text-muted-foreground/40 mx-auto mb-2" />
                                        <p class="text-sm text-muted-foreground font-medium">Tidak ada notifikasi</p>
                                    </div>
                                    <Link v-for="n in notifications" :key="n.id" :href="route('notifications.index')" class="flex items-center gap-3 px-4 py-3 hover:bg-accent/70 transition-all duration-200 border-b border-border/40 last:border-0" :class="{ 'bg-accent/40': !n.is_read }">
                                        <div class="flex-shrink-0 w-2 h-2 rounded-full bg-primary/60" :class="{ 'opacity-0': n.is_read }"></div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium truncate">{{ n.title }}</p>
                                            <p class="text-xs text-muted-foreground truncate mt-0.5">{{ n.message }}</p>
                                        </div>
                                        <span class="text-[10px] text-muted-foreground/70 whitespace-nowrap">{{ n.created_at }}</span>
                                    </Link>
                                </div>
                                <div class="border-t mx-2 mt-1 pt-1">
                                    <Link :href="route('notifications.index')" class="block px-3 py-2.5 text-sm text-center text-muted-foreground hover:text-foreground hover:bg-accent/70 transition-all duration-200 rounded-lg font-medium">Lihat Semua</Link>
                                </div>
                            </div>
                        </Transition>
                    </div>
                    <!-- Mobile Profile Avatar -->
                    <Link :href="route('profile.edit')" class="w-8 h-8 rounded-full bg-gradient-to-br from-primary to-primary/80 flex items-center justify-center text-xs font-bold text-primary-foreground shadow-sm overflow-hidden">
                        <img v-if="profilePhotoUrl" :src="profilePhotoUrl" class="w-full h-full object-cover" />
                        <span v-else>{{ (user?.name || 'A').charAt(0).toUpperCase() }}</span>
                    </Link>
                </div>
            </div>
        </nav>

        <!-- ======================== MAIN CONTENT ======================== -->
        <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 md:py-8 w-full animate-fade-in pb-24 md:pb-8">
            <FlashMessage />
            <div v-if="$slots['header-title']" class="mb-5 md:mb-6">
                <h1 class="text-lg font-semibold tracking-tight truncate"><slot name="header-title" /></h1>
                <p v-if="$slots['header-sub']" class="text-xs text-muted-foreground mt-0.5 truncate"><slot name="header-sub" /></p>
            </div>
            <slot />
        </main>

        <!-- ======================== DESKTOP FOOTER ======================== -->
        <footer class="hidden md:block border-t bg-muted/30 pt-10 pb-6 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center text-muted-foreground text-sm">&copy; {{ new Date().getFullYear() }} KPM SMART. Hak cipta dilindungi.</div>
            </div>
        </footer>

        <!-- ======================== MOBILE BOTTOM NAV ======================== -->
        <nav v-if="user?.role === 'user'" class="md:hidden fixed bottom-0 inset-x-0 z-50 bg-background/90 backdrop-blur-xl supports-[backdrop-filter]:bg-background/80 border-t border-border/30 safe-area-pb">
            <div class="grid grid-cols-5 h-16">
                <Link v-for="tab in bottomTabs" :key="tab.route" :href="route(tab.route)" class="bottom-tab flex flex-col items-center justify-center gap-0.5 relative transition-all duration-200" :class="isActive(tab.match) ? 'text-primary' : 'text-muted-foreground'">
                    <div class="relative">
                        <Icon :icon="isActive(tab.match) ? tab.iconActive : tab.icon" class="w-5 h-5 transition-all duration-200" :class="isActive(tab.match) ? 'scale-110' : ''" />
                        <div v-if="isActive(tab.match)" class="absolute -bottom-1.5 left-1/2 -translate-x-1/2 w-4 h-1 rounded-full bg-primary"></div>
                    </div>
                    <span class="text-[10px] font-medium leading-tight mt-1 transition-all duration-200">{{ tab.label }}</span>
                </Link>
            </div>
        </nav>
    </div>
</template>

<style scoped>
.dropdown-enter-active { transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1); }
.dropdown-leave-active { transition: all 0.15s ease-in; }
.dropdown-enter-from { opacity: 0; transform: scale(0.95) translateY(-4px); }
.dropdown-leave-to { opacity: 0; transform: scale(0.95) translateY(-4px); }

.nav-link {
  position: relative;
}

.bottom-tab {
  -webkit-tap-highlight-color: transparent;
}

.bottom-tab:active {
  transform: scale(0.92);
}

/* iOS safe area for bottom nav */
.safe-area-pb {
  padding-bottom: env(safe-area-inset-bottom, 0px);
}
</style>
