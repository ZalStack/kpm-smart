<script setup>
import { inject, ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Icon } from '@iconify/vue/offline';

const route = inject('route');
const page = usePage();
const user = computed(() => page.props.auth?.user);
const mobileMenuOpen = ref(false);
</script>

<template>
    <div class="min-h-screen flex flex-col bg-background text-foreground antialiased selection:bg-primary selection:text-primary-foreground">
        <!-- Sticky Navbar -->
        <nav class="sticky top-0 z-50 w-full border-b border-border/80 bg-card/85 backdrop-blur-md shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <Link
                        :href="user?.role === 'admin' ? route('admin.dashboard') : (user ? route('user.dashboard') : '/')"
                        class="flex items-center gap-3 text-lg font-extrabold text-foreground hover:text-primary transition-all duration-200 group"
                    >
                        <div class="w-11 h-11 rounded-3xl bg-gradient-to-r from-brand-indigo to-brand-blue flex items-center justify-center text-xs font-black text-white shadow-lg shadow-indigo-300/40 group-hover:scale-105 transition-transform duration-200">
                            PR
                        </div>
                        <div class="leading-tight">
                            <span class="tracking-tight text-base font-extrabold block">PR KK</span>
                            <span class="text-[10px] text-primary font-extrabold uppercase tracking-wider block -mt-0.5">Siswa Kelas Khusus</span>
                        </div>
                    </Link>

                    <!-- Desktop Navigation Links -->
                    <div class="hidden md:flex items-center gap-2">
                        <Link :href="route('pages.features')" class="px-3.5 py-2 text-xs font-extrabold text-muted-foreground hover:text-primary hover:bg-accent/80 rounded-xl transition">
                            Fitur
                        </Link>
                        <Link :href="route('pages.guide')" class="px-3.5 py-2 text-xs font-extrabold text-muted-foreground hover:text-primary hover:bg-accent/80 rounded-xl transition">
                            Panduan
                        </Link>
                        <Link :href="route('pages.faq')" class="px-3.5 py-2 text-xs font-extrabold text-muted-foreground hover:text-primary hover:bg-accent/80 rounded-xl transition">
                            FAQ
                        </Link>
                        <div class="w-px h-5 bg-border mx-1"></div>
                        <Link
                            v-if="user"
                            :href="user.role === 'admin' ? route('admin.dashboard') : route('user.dashboard')"
                            class="inline-flex items-center justify-center bg-primary text-primary-foreground px-5 py-2 rounded-xl text-xs font-extrabold hover:bg-primary/90 shadow-sm shadow-primary/25 transition-all duration-200"
                        >
                            Dasbor
                        </Link>
                        <Link
                            v-else
                            :href="route('login')"
                            class="inline-flex items-center justify-center bg-primary text-primary-foreground px-5 py-2 rounded-xl text-xs font-extrabold hover:bg-primary/90 shadow-sm shadow-primary/25 transition-all duration-200"
                        >
                            Masuk
                        </Link>
                    </div>

                    <!-- Mobile Hamburger -->
                    <button
                        @click="mobileMenuOpen = !mobileMenuOpen"
                        class="md:hidden inline-flex items-center justify-center w-10 h-10 rounded-xl text-muted-foreground hover:bg-accent/80 hover:text-primary transition border border-border"
                        aria-label="Menu"
                    >
                        <Icon :icon="mobileMenuOpen ? 'mdi:close' : 'mdi:menu'" class="w-5 h-5" />
                    </button>
                </div>
            </div>

            <!-- Mobile Menu Dropdown -->
            <Transition name="mobile-menu">
                <div v-if="mobileMenuOpen" class="md:hidden border-t border-border/80 bg-card p-4 space-y-2 shadow-xl">
                    <Link :href="route('pages.features')" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-extrabold text-muted-foreground hover:text-primary hover:bg-accent/80 transition">
                        <Icon icon="mdi:sparkles" class="w-4 h-4 text-primary" />
                        Fitur Unggulan
                    </Link>
                    <Link :href="route('pages.guide')" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-extrabold text-muted-foreground hover:text-primary hover:bg-accent/80 transition">
                        <Icon icon="mdi:book-open-page-variant" class="w-4 h-4 text-primary" />
                        Panduan Penggunaan
                    </Link>
                    <Link :href="route('pages.faq')" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-xs font-extrabold text-muted-foreground hover:text-primary hover:bg-accent/80 transition">
                        <Icon icon="mdi:help-circle-outline" class="w-4 h-4 text-primary" />
                        FAQ (Tanya Jawab)
                    </Link>
                    <div class="border-t border-border pt-3 mt-3">
                        <Link
                            v-if="user"
                            :href="user.role === 'admin' ? route('admin.dashboard') : route('user.dashboard')"
                            @click="mobileMenuOpen = false"
                            class="w-full inline-flex items-center justify-center py-2.5 rounded-xl text-xs font-extrabold bg-primary text-primary-foreground shadow-xs shadow-primary/30 transition"
                        >
                            Ke Dasbor
                        </Link>
                        <Link
                            v-else
                            :href="route('login')"
                            @click="mobileMenuOpen = false"
                            class="w-full inline-flex items-center justify-center py-2.5 rounded-xl text-xs font-extrabold bg-primary text-primary-foreground shadow-xs shadow-primary/30 transition"
                        >
                            Masuk ke Akun
                        </Link>
                    </div>
                </div>
            </Transition>
        </nav>

        <!-- Main Slot -->
        <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 w-full animate-fade-in">
            <slot />
        </main>

        <!-- Modern Footer -->
        <footer class="border-t border-border/80 bg-card mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-8 lg:gap-12">
                    <div class="sm:col-span-2 lg:col-span-5 space-y-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-3xl bg-gradient-to-r from-brand-indigo to-brand-blue flex items-center justify-center text-white text-xs font-black shadow-lg shadow-indigo-300/40">
                                PR
                            </div>
                            <span class="text-base font-extrabold tracking-tight">PR KK</span>
                        </div>
                        <p class="text-muted-foreground text-sm leading-relaxed max-w-sm">
                            Platform pembelajaran dan PR Pekanan digital terpadu untuk latihan mandiri dan evaluasi terarah siswa kelas khusus. Modern, responsif, dan user-friendly di semua perangkat.
                        </p>
                        <div class="flex items-center gap-3 mt-4">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="text-xs font-extrabold text-foreground">Terpercaya oleh ribuan siswa</span>
                        </div>
                    </div>

                    <div class="lg:col-span-3 space-y-4">
                        <p class="text-xs font-extrabold uppercase tracking-wider text-foreground/60">Menu Utama</p>
                        <ul class="space-y-3 text-xs text-muted-foreground">
                            <li><Link :href="route('pages.features')" class="hover:text-primary transition-colors">Fitur Unggulan</Link></li>
                            <li><Link :href="user ? route('user.packages.index') : route('login')" class="hover:text-primary transition-colors">PR Siswa Kelas Khusus</Link></li>
                            <li><Link :href="route('pages.guide')" class="hover:text-primary transition-colors">Panduan Sistem</Link></li>
                        </ul>
                    </div>

                    <div class="lg:col-span-4 space-y-4">
                        <p class="text-xs font-extrabold uppercase tracking-wider text-foreground/60">Kontak & Bantuan</p>
                        <ul class="space-y-3 text-xs text-muted-foreground">
                            <li><Link :href="route('pages.faq')" class="hover:text-primary transition-colors">FAQ (Tanya Jawab)</Link></li>
                            <li class="flex items-center gap-2">
                                <Icon icon="mdi:email-outline" class="w-3.5 h-3.5 text-primary" />
                                <span class="text-muted-foreground">info@pkalitbang.id</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <Icon icon="mdi:whatsapp" class="w-3.5 h-3.5 text-emerald-500" />
                                <span class="text-muted-foreground">+62 821-2343-9604</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="border-t border-border/60 mt-12 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-muted-foreground">
                    <p>&copy; {{ new Date().getFullYear() }} PR KK. Hak cipta dilindungi.</p>
                    <div class="flex items-center gap-6">
                        <Link :href="route('pages.guide')" class="hover:text-primary transition-colors">Panduan</Link>
                        <Link :href="route('pages.faq')" class="hover:text-primary transition-colors">Bantuan</Link>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.mobile-menu-enter-active, .mobile-menu-leave-active { transition: all 0.25s ease-out; }
.mobile-menu-enter-from, .mobile-menu-leave-to { opacity: 0; transform: translateY(-8px); }
</style>