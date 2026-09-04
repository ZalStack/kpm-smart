<script setup>
import { inject, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
const route = inject('route');

const page = usePage();
const user = page.props.auth?.user;
const mobileMenuOpen = ref(false);
</script>

<template>
    <div class="min-h-screen flex flex-col bg-background">
        <!-- Navbar with glassmorphism -->
        <nav class="nav-glass sticky top-0 z-50 w-full border-b border-border/40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <Link
                        :href="user?.role === 'admin' ? route('admin.dashboard') : (user ? route('user.dashboard') : '/')"
                        class="flex items-center gap-2.5 text-lg font-bold text-foreground hover:text-primary transition-all duration-300 group"
                    >
                        <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary to-primary/80 flex items-center justify-center text-primary-foreground text-sm font-bold shadow-lg shadow-primary/25 group-hover:shadow-primary/40 transition-shadow duration-300">K</span>
                        <span class="hidden sm:inline tracking-tight">KPM SMART</span>
                    </Link>

                    <div class="hidden md:flex items-center gap-1">
                        <Link :href="route('pages.features')" class="nav-link inline-flex items-center px-3.5 py-2 text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-accent/60 rounded-lg transition-all duration-200">Fitur</Link>
                        <Link :href="route('pages.guide')" class="nav-link inline-flex items-center px-3.5 py-2 text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-accent/60 rounded-lg transition-all duration-200">Panduan</Link>
                        <Link :href="route('pages.faq')" class="nav-link inline-flex items-center px-3.5 py-2 text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-accent/60 rounded-lg transition-all duration-200">FAQ</Link>
                        <Link v-if="user" :href="user.role === 'admin' ? route('admin.dashboard') : route('user.dashboard')" class="inline-flex items-center justify-center bg-gradient-to-r from-primary to-primary/90 text-primary-foreground px-5 py-2 rounded-lg text-sm font-medium hover:shadow-lg hover:shadow-primary/25 transition-all duration-300 ml-3">Dasbor</Link>
                        <Link v-else :href="route('login')" class="nav-link inline-flex items-center px-3.5 py-2 text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-accent/60 rounded-lg transition-all duration-200 ml-3">Masuk</Link>
                    </div>

                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden inline-flex items-center justify-center w-10 h-10 rounded-xl text-muted-foreground hover:bg-accent hover:text-accent-foreground transition-all duration-200" aria-label="Menu">
                        <svg v-if="!mobileMenuOpen" class="w-5 h-5 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                        <svg v-else class="w-5 h-5 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
            </div>

            <!-- Mobile menu -->
            <Transition name="mobile-menu">
                <div v-if="mobileMenuOpen" class="md:hidden border-t border-border/40 nav-glass">
                    <div class="px-4 py-4 space-y-1.5">
                        <Link :href="route('pages.features')" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-accent/60 transition-all duration-200 text-sm text-muted-foreground hover:text-foreground group">
                            <svg class="w-4 h-4 opacity-60 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Fitur
                        </Link>
                        <Link :href="route('pages.guide')" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-accent/60 transition-all duration-200 text-sm text-muted-foreground hover:text-foreground group">
                            <svg class="w-4 h-4 opacity-60 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            Panduan
                        </Link>
                        <Link :href="route('pages.faq')" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-accent/60 transition-all duration-200 text-sm text-muted-foreground hover:text-foreground group">
                            <svg class="w-4 h-4 opacity-60 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            FAQ
                        </Link>
                        <div class="border-t border-border/40 pt-3 mt-3">
                            <Link v-if="user" :href="user.role === 'admin' ? route('admin.dashboard') : route('user.dashboard')" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-accent/60 transition-all duration-200 text-sm text-muted-foreground hover:text-foreground group">
                                <svg class="w-4 h-4 opacity-60 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                                Dasbor
                            </Link>
                            <Link v-else :href="route('login')" @click="mobileMenuOpen = false" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-accent/60 transition-all duration-200 text-sm text-muted-foreground hover:text-foreground group">
                                <svg class="w-4 h-4 opacity-60 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                Masuk
                            </Link>
                        </div>
                    </div>
                </div>
            </Transition>
        </nav>

        <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-8 w-full animate-fade-in">
            <slot />
        </main>

        <!-- Modern Footer -->
        <footer class="border-t bg-muted/30 mt-auto">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Main footer grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-12 pt-14 md:pt-16 pb-10">
                    <!-- Brand column -->
                    <div class="sm:col-span-2 lg:col-span-4">
                        <div class="flex items-center gap-3 mb-5">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-primary/80 flex items-center justify-center text-primary-foreground text-sm font-bold shadow-lg shadow-primary/20">K</div>
                            <div>
                                <span class="text-lg font-bold tracking-tight">KPM SMART</span>
                                <span class="text-xs block -mt-0.5 text-muted-foreground">Belajar Online</span>
                            </div>
                        </div>
                        <p class="text-muted-foreground text-sm leading-relaxed mb-6 max-w-xs">Platform Tugas Online untuk mendukung pembelajaran dan pengerjaan tugas mandiri.</p>
                        <!-- Social links -->
                        <div class="flex items-center gap-3">
                            <a href="#" class="w-9 h-9 rounded-lg bg-muted hover:bg-primary/10 flex items-center justify-center text-muted-foreground hover:text-primary transition-all duration-200" aria-label="Instagram">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                            </a>
                            <a href="#" class="w-9 h-9 rounded-lg bg-muted hover:bg-primary/10 flex items-center justify-center text-muted-foreground hover:text-primary transition-all duration-200" aria-label="YouTube">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                            </a>
                            <a href="#" class="w-9 h-9 rounded-lg bg-muted hover:bg-primary/10 flex items-center justify-center text-muted-foreground hover:text-primary transition-all duration-200" aria-label="WhatsApp">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            </a>
                            <a href="#" class="w-9 h-9 rounded-lg bg-muted hover:bg-primary/10 flex items-center justify-center text-muted-foreground hover:text-primary transition-all duration-200" aria-label="Email">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </a>
                        </div>
                    </div>

                    <!-- Menu column -->
                    <div class="lg:col-span-2">
                        <h4 class="font-semibold mb-5 text-sm tracking-wide uppercase text-foreground">Menu</h4>
                        <ul class="space-y-3 text-muted-foreground text-sm">
                            <li><Link :href="route('pages.features')" class="hover:text-primary transition-colors duration-200 inline-flex items-center gap-1.5"><svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>Fitur Unggulan</Link></li>
                            <li><Link :href="user ? route('packages.index') : '/#packages'" class="hover:text-primary transition-colors duration-200 inline-flex items-center gap-1.5"><svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>Soal Tugas</Link></li>
                            <li v-if="user"><Link :href="route('practice.history')" class="hover:text-primary transition-colors duration-200 inline-flex items-center gap-1.5"><svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>Tugas</Link></li>
                        </ul>
                    </div>

                    <!-- Bantuan column -->
                    <div class="lg:col-span-3">
                        <h4 class="font-semibold mb-5 text-sm tracking-wide uppercase text-foreground">Bantuan</h4>
                        <ul class="space-y-3 text-muted-foreground text-sm">
                            <li><Link :href="route('pages.guide')" class="hover:text-primary transition-colors duration-200 inline-flex items-center gap-1.5"><svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>Panduan Penggunaan</Link></li>
                            <li><Link :href="route('pages.faq')" class="hover:text-primary transition-colors duration-200 inline-flex items-center gap-1.5"><svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>FAQ</Link></li>
                            <li><span class="text-muted-foreground inline-flex items-center gap-1.5"><svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>Hubungi Kami</span></li>
                        </ul>
                    </div>

                    <!-- Kontak column -->
                    <div class="lg:col-span-3">
                        <h4 class="font-semibold mb-5 text-sm tracking-wide uppercase text-foreground">Kontak</h4>
                        <ul class="space-y-3 text-muted-foreground text-sm">
                            <li class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-primary/70 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                info@pkalitbang.id
                            </li>
                            <li class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-primary/70 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                +62 821-2343-9604
                            </li>
                            <li class="flex items-center gap-2.5">
                                <svg class="w-4 h-4 text-primary/70 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Bogor, Indonesia
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Bottom bar -->
                <div class="border-t border-border/50 py-6 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <p class="text-muted-foreground text-xs">&copy; {{ new Date().getFullYear() }} KPM SMART. Hak cipta dilindungi.</p>
                    <div class="flex items-center gap-4 text-xs text-muted-foreground">
                        <a href="#" class="hover:text-primary transition-colors duration-200">Kebijakan Privasi</a>
                        <span class="opacity-30">|</span>
                        <a href="#" class="hover:text-primary transition-colors duration-200">Syarat & Ketentuan</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* Glassmorphism navbar */
.nav-glass {
    background: color-mix(in srgb, var(--background) 75%, transparent);
    backdrop-filter: blur(16px) saturate(180%);
    -webkit-backdrop-filter: blur(16px) saturate(180%);
}

/* Nav link hover underline effect */
.nav-link {
    position: relative;
}
.nav-link::after {
    content: '';
    position: absolute;
    bottom: 2px;
    left: 50%;
    width: 0;
    height: 2px;
    background: currentColor;
    border-radius: 1px;
    transition: width 0.25s cubic-bezier(0.16, 1, 0.3, 1), left 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    opacity: 0.5;
}
.nav-link:hover::after {
    width: 60%;
    left: 20%;
}

/* Mobile menu transitions */
.mobile-menu-enter-active {
    transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}
.mobile-menu-leave-active {
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.mobile-menu-enter-from {
    max-height: 0;
    opacity: 0;
    overflow: hidden;
    transform: translateY(-8px);
}
.mobile-menu-enter-to {
    max-height: 500px;
    opacity: 1;
    transform: translateY(0);
}
.mobile-menu-leave-from {
    max-height: 500px;
    opacity: 1;
    transform: translateY(0);
}
.mobile-menu-leave-to {
    max-height: 0;
    opacity: 0;
    overflow: hidden;
    transform: translateY(-8px);
}
</style>
