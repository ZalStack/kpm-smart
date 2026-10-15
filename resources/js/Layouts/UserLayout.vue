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

const allTabs = [
    { label: 'Dasbor', route: 'user.dashboard', icon: 'mdi:view-dashboard-outline', iconActive: 'mdi:view-dashboard', match: 'user.dashboard' },
    { label: 'Tugas PR', route: 'user.packages.index', icon: 'mdi:book-open-variant', iconActive: 'mdi:book-open-page-variant', match: 'user.packages.*' },
    { label: 'Riwayat', route: 'user.practice.history', icon: 'mdi:history', iconActive: 'mdi:history', match: 'user.practice.*' },
    { label: 'Peringkat', route: 'user.leaderboard', icon: 'mdi:trophy-outline', iconActive: 'mdi:trophy', match: 'user.leaderboard' },
    { label: 'Izin', route: 'user.leave-requests.index', icon: 'mdi:calendar-blank-outline', iconActive: 'mdi:calendar-check', match: 'user.leave-requests.*' },
];

const bottomTabs = computed(() => {
    const u = user.value;
    if (!u || u.role !== 'user') return [];
    return allTabs;
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
    document.addEventListener('click', clickHandler);
    document.addEventListener('keydown', keyHandler);
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
    if (clickHandler) document.removeEventListener('click', clickHandler);
    if (keyHandler) document.removeEventListener('keydown', keyHandler);
});
</script>

<template>
    <div class="min-h-screen flex flex-col bg-background text-foreground antialiased selection:bg-primary selection:text-primary-foreground">
        <!-- ======================== DESKTOP TOP NAVBAR ======================== -->
        <nav class="hidden md:block sticky top-0 z-50 w-full border-b border-border/80 bg-card/85 backdrop-blur-md shadow-2xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <!-- Brand -->
                    <Link
                        :href="user?.role === 'admin' ? route('admin.dashboard') : (user ? route('user.dashboard') : '/')"
                        class="flex items-center gap-3 text-lg font-bold text-foreground hover:text-primary transition-all duration-200 group"
                    >
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-600 to-teal-700 flex items-center justify-center text-white text-base font-extrabold shadow-sm shadow-emerald-600/20 group-hover:scale-105 transition-transform duration-200">
                            K
                        </div>
                        <div class="leading-tight">
                            <span class="tracking-tight text-base font-extrabold block">KPM SMART</span>
                            <span class="text-[10px] text-muted-foreground font-semibold uppercase tracking-wider block -mt-0.5">Platform Belajar</span>
                        </div>
                    </Link>

                    <!-- Navigation Links -->
                    <div class="flex items-center gap-1.5">
                        <template v-if="user && user.role === 'user'">
                            <nav class="flex items-center gap-1 bg-muted/60 p-1 rounded-2xl border border-border/60" aria-label="Navigasi utama">
                                <Link
                                    v-for="tab in allTabs"
                                    :key="tab.route"
                                    :href="route(tab.route)"
                                    :aria-current="isActive(tab.match) ? 'page' : undefined"
                                    :class="[
                                        'inline-flex items-center gap-2 px-3.5 py-1.5 text-xs font-semibold rounded-xl transition-all duration-200 select-none',
                                        isActive(tab.match)
                                            ? 'bg-card text-foreground shadow-xs font-bold text-primary'
                                            : 'text-muted-foreground hover:text-foreground hover:bg-card/50'
                                    ]"
                                >
                                    <Icon :icon="isActive(tab.match) ? tab.iconActive : tab.icon" class="w-4 h-4" />
                                    {{ tab.label }}
                                </Link>
                            </nav>
                        </template>

                        <!-- Notification Bell -->
                        <div v-if="user" class="relative ml-2" id="notifWrap">
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
                        <div v-if="user" class="relative ml-1" id="userDropdown">
                            <button
                                @click="toggleUserDropdown"
                                class="inline-flex items-center gap-2 hover:bg-accent/70 rounded-xl p-1.5 sm:px-2.5 transition border border-border/60"
                            >
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-600 to-teal-700 text-white font-bold text-xs flex items-center justify-center overflow-hidden shadow-2xs">
                                    <img v-if="profilePhotoUrl" :src="profilePhotoUrl" class="w-full h-full object-cover" />
                                    <span v-else>{{ (user?.name || 'A').charAt(0).toUpperCase() }}</span>
                                </div>
                                <span class="text-xs font-bold text-foreground max-w-[120px] truncate leading-tight">{{ user.name }}</span>
                                <Icon icon="mdi:chevron-down" class="w-3.5 h-3.5 text-muted-foreground transition-transform duration-200" :class="{ 'rotate-180': userDropdownOpen }" />
                            </button>

                            <Transition name="dropdown">
                                <div v-if="userDropdownOpen" class="absolute right-0 mt-2 w-56 bg-card text-card-foreground rounded-2xl shadow-xl border border-border/80 py-2 z-50">
                                    <div class="px-4 py-3 border-b border-border/60">
                                        <p class="text-xs font-bold text-foreground truncate">{{ user?.name }}</p>
                                        <p class="text-[11px] text-muted-foreground truncate mt-0.5">{{ user?.email }}</p>
                                    </div>
                                    <div class="py-1 px-1.5 space-y-0.5">
                                        <Link :href="route('user.profile.edit')" class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-foreground hover:bg-accent/70 rounded-xl transition">
                                            <Icon icon="mdi:account-outline" class="w-4 h-4 text-muted-foreground" />
                                            Profil Saya
                                        </Link>
                                        <Link v-if="user.role === 'user'" :href="route('user.practice.statistics')" class="flex items-center gap-2.5 px-3 py-2 text-xs font-medium text-foreground hover:bg-accent/70 rounded-xl transition">
                                            <Icon icon="mdi:chart-bar" class="w-4 h-4 text-muted-foreground" />
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
            </div>
        </nav>

        <!-- ======================== MOBILE TOP BAR ======================== -->
        <nav class="md:hidden sticky top-0 z-40 w-full bg-card/90 backdrop-blur-md border-b border-border/80 shadow-2xs">
            <div class="flex items-center justify-between h-14 px-4">
                <Link :href="route('user.dashboard')" class="flex items-center gap-2.5 text-foreground font-bold">
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-emerald-600 to-teal-700 flex items-center justify-center text-white text-xs font-extrabold shadow-2xs">
                        K
                    </div>
                    <span class="tracking-tight text-sm font-extrabold">KPM SMART</span>
                </Link>

                <div class="flex items-center gap-2">
                    <!-- Notification Bell Mobile -->
                    <div class="relative" id="notifWrapMobile">
                        <button
                            @click="toggleNotifDropdown"
                            class="relative inline-flex items-center justify-center w-9 h-9 rounded-xl text-muted-foreground hover:bg-accent/70 transition border border-border/60"
                        >
                            <Icon icon="mdi:bell-outline" class="w-5 h-5" />
                            <span v-if="unreadCount > 0" class="absolute -top-1 -right-1 min-w-4 h-4 px-1 bg-rose-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center shadow-xs">
                                {{ unreadCount > 99 ? '99+' : unreadCount }}
                            </span>
                        </button>
                    </div>

                    <!-- Profile Avatar Mobile -->
                    <Link
                        :href="route('user.profile.edit')"
                        class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-600 to-teal-700 flex items-center justify-center text-xs font-bold text-white shadow-2xs overflow-hidden border border-border/60"
                    >
                        <img v-if="profilePhotoUrl" :src="profilePhotoUrl" class="w-full h-full object-cover" />
                        <span v-else>{{ (user?.name || 'A').charAt(0).toUpperCase() }}</span>
                    </Link>
                </div>
            </div>
        </nav>

        <!-- ======================== MAIN CONTENT ======================== -->
        <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 w-full animate-fade-in pb-24 md:pb-12">
            <FlashMessage />
            <div v-if="$slots['header-title']" class="mb-6">
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-foreground"><slot name="header-title" /></h1>
                <p v-if="$slots['header-sub']" class="text-xs sm:text-sm text-muted-foreground mt-1 leading-relaxed"><slot name="header-sub" /></p>
            </div>
            <slot />
        </main>

        <!-- ======================== DESKTOP FOOTER ======================== -->
        <footer class="hidden md:block border-t border-border/60 bg-card py-8 mt-auto">
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

        <!-- ======================== MOBILE BOTTOM NAVIGATION ======================== -->
        <nav v-if="isUserRole" class="md:hidden fixed bottom-0 inset-x-0 z-50 bg-card/95 backdrop-blur-xl border-t border-border/80 safe-area-bottom shadow-lg">
            <div class="grid grid-cols-5 h-16 items-center px-1" role="tablist">
                <Link
                    v-for="tab in bottomTabs"
                    :key="tab.route"
                    :href="route(tab.route)"
                    :aria-current="isActive(tab.match) ? 'page' : undefined"
                    class="flex flex-col items-center justify-center gap-1 py-1 relative transition-all duration-200 select-none"
                    :class="isActive(tab.match) ? 'text-primary font-bold' : 'text-muted-foreground hover:text-foreground'"
                >
                    <div class="relative flex items-center justify-center">
                        <Icon
                            :icon="isActive(tab.match) ? tab.iconActive : tab.icon"
                            class="w-5 h-5 transition-transform duration-200"
                            :class="isActive(tab.match) ? 'scale-110 text-primary' : ''"
                        />
                        <div v-if="isActive(tab.match)" class="absolute -bottom-1.5 left-1/2 -translate-x-1/2 w-4 h-1 rounded-full bg-primary"></div>
                    </div>
                    <span class="text-[10px] leading-tight transition-all duration-200">{{ tab.label }}</span>
                </Link>
            </div>
        </nav>
    </div>
</template>

<style scoped>
.dropdown-enter-active { transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1); }
.dropdown-leave-active { transition: all 0.15s ease-in; }
.dropdown-enter-from { opacity: 0; transform: scale(0.96) translateY(-6px); }
.dropdown-leave-to { opacity: 0; transform: scale(0.96) translateY(-6px); }
</style>
