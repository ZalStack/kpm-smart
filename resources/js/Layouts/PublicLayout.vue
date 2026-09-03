<script setup>
import { inject,  ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
const route = inject('route');

const page = usePage();
const user = page.props.auth?.user;
const mobileMenuOpen = ref(false);
</script>

<template>
    <div class="min-h-screen flex flex-col bg-background">
        <nav class="sticky top-0 z-50 w-full border-b border-border/40 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <Link
                        :href="user?.role === 'admin' ? route('admin.dashboard') : (user ? route('user.dashboard') : '/')"
                        class="flex items-center gap-2 text-lg font-bold text-foreground hover:text-primary transition"
                    >
                        <span class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center text-primary-foreground text-sm font-bold">K</span>
                        <span class="hidden sm:inline">KPM SMART</span>
                    </Link>

                    <div class="hidden md:flex items-center gap-1">
                        <Link :href="route('pages.features')" class="inline-flex items-center px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-accent rounded-md transition">Fitur</Link>
                        <Link :href="route('pages.guide')" class="inline-flex items-center px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-accent rounded-md transition">Panduan</Link>
                        <Link :href="route('pages.faq')" class="inline-flex items-center px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-accent rounded-md transition">FAQ</Link>
                        <Link v-if="user" :href="user.role === 'admin' ? route('admin.dashboard') : route('user.dashboard')" class="inline-flex items-center justify-center bg-primary text-primary-foreground px-4 py-2 rounded-md text-sm font-medium hover:bg-primary/90 transition ml-2">Dasbor</Link>
                        <Link v-else :href="route('login')" class="inline-flex items-center px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-accent rounded-md transition ml-2">Masuk</Link>
                    </div>

                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden inline-flex items-center justify-center w-9 h-9 rounded-md text-muted-foreground hover:bg-accent hover:text-accent-foreground transition" aria-label="Menu">
                        <svg v-if="!mobileMenuOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>

            <Transition name="mobile-menu">
                <div v-if="mobileMenuOpen" class="md:hidden border-t bg-background shadow-lg">
                    <div class="px-4 py-3 space-y-1">
                        <Link :href="route('pages.features')" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-accent transition text-sm text-muted-foreground">Fitur</Link>
                        <Link :href="route('pages.guide')" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-accent transition text-sm text-muted-foreground">Panduan</Link>
                        <Link :href="route('pages.faq')" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-accent transition text-sm text-muted-foreground">FAQ</Link>
                        <div class="border-t pt-2 mt-2">
                            <Link v-if="user" :href="user.role === 'admin' ? route('admin.dashboard') : route('user.dashboard')" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-accent transition text-sm text-muted-foreground">Dasbor</Link>
                            <Link v-else :href="route('login')" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-accent transition text-sm text-muted-foreground">Masuk</Link>
                        </div>
                    </div>
                </div>
            </Transition>
        </nav>

        <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-8 w-full animate-fade-in">
            <slot />
        </main>

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
                            <li><Link :href="user ? route('packages.index') : '/#packages'" class="hover:text-foreground transition-all duration-200">Paket Tugas</Link></li>
                            <li v-if="user"><Link :href="route('practice.history')" class="hover:text-foreground transition-all duration-200">Tugas</Link></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-4 text-sm">Bantuan</h4>
                        <ul class="space-y-2 text-muted-foreground text-sm">
                            <li><Link :href="route('pages.guide')" class="hover:text-foreground transition-all duration-200">Panduan Penggunaan</Link></li>
                            <li><Link :href="route('pages.faq')" class="hover:text-foreground transition-all duration-200">FAQ</Link></li>
                            <li><span class="text-muted-foreground">Hubungi Kami</span></li>
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
.mobile-menu-enter-active { transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
.mobile-menu-leave-active { transition: all 0.25s ease; }
.mobile-menu-enter-from { max-height: 0; opacity: 0; overflow: hidden; }
.mobile-menu-enter-to { max-height: 500px; opacity: 1; }
.mobile-menu-leave-from { max-height: 500px; opacity: 1; }
.mobile-menu-leave-to { max-height: 0; opacity: 0; overflow: hidden; }
</style>
