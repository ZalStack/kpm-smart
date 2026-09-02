{{-- layouts/admin.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin - KPM Belajar Online')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    @stack('styles')
</head>

<body class="bg-background antialiased text-foreground">
    <!-- Sidebar Overlay (Mobile) -->
    <div id="sidebarOverlay"
        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 hidden lg:hidden transition-all duration-300 opacity-0">
    </div>

    <!-- Sidebar -->
    <aside id="sidebar"
        class="fixed top-0 left-0 h-full w-[272px] sidebar-modern text-white z-50 overflow-hidden transition-all duration-300 -translate-x-full lg:translate-x-0 flex flex-col shadow-sidebar">

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
                <span class="text-sm font-semibold text-white block leading-tight">KPM Belajar Online</span>
                <span class="text-[10px] text-white/40 font-medium">Admin Panel</span>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="relative px-3 py-4 flex-1 min-h-0 overflow-y-auto sidebar-scroll">
            <p class="text-[10px] uppercase tracking-widest text-white/25 font-semibold px-3 mb-3">Menu Utama</p>

            <a href="{{ route('admin.dashboard') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-md transition-all duration-200 mb-0.5 {{ request()->routeIs('admin.dashboard') ? 'nav-active' : 'text-white/55 hover:bg-white/5 hover:text-white' }}">
                <div class="w-8 h-8 rounded-md {{ request()->routeIs('admin.dashboard') ? 'bg-white/10' : 'bg-white/5' }} flex items-center justify-center text-sm flex-shrink-0 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 12a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1v-7z"/></svg>
                </div>
                <span class="text-[13px] font-medium">Dasbor</span>
            </a>

            <a href="{{ route('admin.packages.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-md transition-all duration-200 mb-0.5 {{ request()->routeIs('admin.packages.*') ? 'nav-active' : 'text-white/55 hover:bg-white/5 hover:text-white' }}">
                <div class="w-8 h-8 rounded-md {{ request()->routeIs('admin.packages.*') ? 'bg-white/10' : 'bg-white/5' }} flex items-center justify-center text-sm flex-shrink-0 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                </div>
                <span class="text-[13px] font-medium">Paket Bank Soal</span>
            </a>

            <p class="text-[10px] uppercase tracking-widest text-white/25 font-semibold px-3 mt-6 mb-3">Manajemen</p>

            <a href="{{ route('admin.users.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-md transition-all duration-200 mb-0.5 {{ request()->routeIs('admin.users.*') ? 'nav-active' : 'text-white/55 hover:bg-white/5 hover:text-white' }}">
                <div class="w-8 h-8 rounded-md {{ request()->routeIs('admin.users.*') ? 'bg-white/10' : 'bg-white/5' }} flex items-center justify-center text-sm flex-shrink-0 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                </div>
                <span class="text-[13px] font-medium">Pengguna</span>
            </a>

            <a href="{{ route('admin.practice-statistics.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-md transition-all duration-200 mb-0.5 {{ request()->routeIs('admin.practice-statistics.*') ? 'nav-active' : 'text-white/55 hover:bg-white/5 hover:text-white' }}">
                <div class="w-8 h-8 rounded-md {{ request()->routeIs('admin.practice-statistics.*') ? 'bg-white/10' : 'bg-white/5' }} flex items-center justify-center text-sm flex-shrink-0 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                </div>
                <span class="text-[13px] font-medium">Statistik Pengerjaan</span>
            </a>

            <a href="{{ route('admin.login-logs.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-md transition-all duration-200 mb-0.5 {{ request()->routeIs('admin.login-logs.*') ? 'nav-active' : 'text-white/55 hover:bg-white/5 hover:text-white' }}">
                <div class="w-8 h-8 rounded-md {{ request()->routeIs('admin.login-logs.*') ? 'bg-white/10' : 'bg-white/5' }} flex items-center justify-center text-sm flex-shrink-0 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                </div>
                <span class="text-[13px] font-medium">Log Login</span>
            </a>

            <a href="{{ route('admin.notifications.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-md transition-all duration-200 mb-0.5 {{ request()->routeIs('admin.notifications.*') ? 'nav-active' : 'text-white/55 hover:bg-white/5 hover:text-white' }}">
                <div class="w-8 h-8 rounded-md {{ request()->routeIs('admin.notifications.*') ? 'bg-white/10' : 'bg-white/5' }} flex items-center justify-center text-sm flex-shrink-0 transition-colors relative">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                </div>
                <span class="text-[13px] font-medium">Notifikasi</span>
                <span id="sidebarNotifBadge" class="ml-auto bg-destructive text-destructive-foreground text-[10px] font-bold px-2 py-0.5 rounded-md min-w-[20px] text-center animate-pulse-soft hidden">0</span>
            </a>

            <p class="text-[10px] uppercase tracking-widest text-white/25 font-semibold px-3 mt-6 mb-3">Komunitas</p>

            <a href="{{ route('admin.support.index') }}"
                class="flex items-center gap-3 px-3 py-2 rounded-md transition-all duration-200 mb-0.5 {{ request()->routeIs('admin.support.*') ? 'nav-active' : 'text-white/55 hover:bg-white/5 hover:text-white' }}">
                <div class="w-8 h-8 rounded-md {{ request()->routeIs('admin.support.*') ? 'bg-white/10' : 'bg-white/5' }} flex items-center justify-center text-sm flex-shrink-0 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"/></svg>
                </div>
                <span class="text-[13px] font-medium">Bantuan</span>
                @php $pendingSupport = \App\Models\SupportTicket::where('status', 'pending')->count(); @endphp
                @if ($pendingSupport > 0)
                    <span class="ml-auto bg-destructive text-destructive-foreground text-[10px] font-bold px-2 py-0.5 rounded-md min-w-[20px] text-center animate-pulse-soft">{{ $pendingSupport }}</span>
                @endif
            </a>
        </nav>

        <!-- Sidebar Footer -->
        <div class="relative px-4 py-4 border-t border-white/10 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-md bg-white/10 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                    {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[13px] font-medium text-white truncate">{{ Auth::user()->name ?? 'Admin' }}</p>
                    <p class="text-[10px] text-white/40 truncate">{{ Auth::user()->email ?? '' }}</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="lg:ml-[272px] min-h-screen flex flex-col">
        <!-- Top Header -->
        <header id="adminHeader"
            class="sticky top-0 z-30 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 h-16">
                <!-- Left: Menu toggle + Title -->
                <div class="flex items-center gap-3 min-w-0">
                    <button id="menuToggle"
                        class="lg:hidden inline-flex items-center justify-center w-9 h-9 rounded-md text-muted-foreground hover:bg-accent hover:text-accent-foreground transition">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                    </button>
                    <div class="min-w-0">
                        <h1 class="text-lg font-semibold tracking-tight truncate">@yield('header-title', 'Dasbor')</h1>
                        <p class="text-xs text-muted-foreground hidden sm:block truncate mt-0.5">@yield('header-sub', 'Kelola sistem membership dengan mudah')</p>
                    </div>
                </div>

                <!-- Right: Date + Notifications + Profile -->
                <div class="flex items-center gap-2">
                    <div class="hidden md:flex items-center gap-2 text-xs text-muted-foreground bg-muted px-3 py-1.5 rounded-md">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                        <span class="font-medium">{{ now()->translatedFormat('l, d M Y') }}</span>
                    </div>

                    <!-- Notifications Bell -->
                    <div class="relative" id="notifWrap">
                        <button id="notifBtn" class="inline-flex items-center justify-center w-9 h-9 rounded-md text-muted-foreground hover:bg-accent hover:text-accent-foreground transition relative">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                            <span id="adminNotifBadge" class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-destructive text-destructive-foreground text-[10px] font-bold rounded-full items-center justify-center animate-pulse-soft hidden">0</span>
                        </button>
                        <div id="notifDropdown" class="hidden absolute right-0 mt-2 w-80 max-w-[calc(100vw-2rem)] bg-popover text-popover-foreground rounded-lg shadow-md border py-2 z-50 animate-scale-in">
                            <div class="px-4 py-3 border-b flex items-center justify-between">
                                <p class="text-sm font-semibold">Notifikasi</p>
                                <button onclick="markAllReadAdmin()" class="text-xs text-muted-foreground hover:text-foreground font-medium transition">Tandai semua dibaca</button>
                            </div>
                            <div class="max-h-72 overflow-y-auto" id="adminNotifList">
                                <div class="px-4 py-6 text-center text-muted-foreground text-sm">Memuat...</div>
                            </div>
                            <div class="border-t mt-1 pt-1">
                                <a href="{{ route('admin.notifications.index') }}" class="block px-4 py-2.5 text-sm text-center text-muted-foreground hover:bg-accent transition font-medium">Lihat Semua Notifikasi</a>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Dropdown -->
                    <div class="relative" id="profileDropdownWrap">
                        <button id="profileDropdownBtn"
                            class="inline-flex items-center gap-2 hover:bg-accent rounded-md px-2 py-1.5 transition">
                            <div class="w-8 h-8 rounded-md bg-primary flex items-center justify-center text-primary-foreground font-bold text-sm">
                                {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                            </div>
                            <div class="hidden sm:block text-left">
                                <p class="text-sm font-medium leading-tight">{{ Auth::user()->name ?? 'Admin' }}</p>
                                <p class="text-[10px] text-muted-foreground">Admin</p>
                            </div>
                            <svg class="hidden sm:block w-4 h-4 text-muted-foreground transition-transform duration-200" id="dropdownArrow" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                        </button>

                        <div id="profileDropdown"
                            class="hidden absolute right-0 mt-2 w-56 bg-popover text-popover-foreground rounded-lg shadow-md border py-2 z-50 animate-scale-in">
                            <div class="px-4 py-3 border-b">
                                <p class="text-sm font-semibold truncate">{{ Auth::user()->name ?? 'Admin' }}</p>
                                <p class="text-xs text-muted-foreground truncate mt-0.5">{{ Auth::user()->email ?? '' }}</p>
                            </div>
                            <a href="{{ route('admin.dashboard') }}"
                                class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-accent hover:text-accent-foreground transition rounded-md mx-1">
                                <svg class="w-4 h-4 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                Dasbor
                            </a>
                            <div class="border-t my-1"></div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit"
                                    class="w-full flex items-center gap-2 px-4 py-2 text-sm text-destructive hover:bg-destructive/10 transition rounded-md mx-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            <!-- Flash Messages -->
            @if (session('success'))
                <div class="flash-msg mb-6 bg-success-50 border border-success-200 p-4 rounded-lg animate-slide-up">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-success-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-success-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <p class="text-sm text-success-700 font-medium flex-1">{{ session('success') }}</p>
                        <button type="button" class="text-muted-foreground hover:text-foreground transition-colors close-flash p-1">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="flash-msg mb-6 bg-danger-50 border border-danger-200 p-4 rounded-lg animate-slide-up">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-danger-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-danger-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                        </div>
                        <p class="text-sm text-danger-700 font-medium flex-1">{{ session('error') }}</p>
                        <button type="button" class="text-muted-foreground hover:text-foreground transition-colors close-flash p-1">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="flash-msg mb-6 bg-danger-50 border border-danger-200 p-4 rounded-lg animate-slide-up">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-full bg-danger-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                            <svg class="w-4 h-4 text-danger-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-danger-800">Terjadi kesalahan:</p>
                            <ul class="text-sm text-danger-700 list-disc list-inside mt-1 space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button type="button" class="text-muted-foreground hover:text-foreground transition-colors close-flash p-1">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>
            @endif

            <div class="page-fade">
                @yield('content')
            </div>
        </main>

        <!-- Mobile Bottom Navigation -->
        <div id="mobileBottomNav" class="lg:hidden fixed bottom-0 left-0 right-0 bg-background/95 backdrop-blur border-t z-30 px-2 py-1 safe-area-bottom">
            <div class="flex items-center justify-around">
                <a href="{{ route('admin.dashboard') }}" class="mobile-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v5a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 12a1 1 0 011-1h4a1 1 0 011 1v7a1 1 0 01-1 1h-4a1 1 0 01-1-1v-7z"/></svg>
                    <span class="text-[10px] font-medium">Beranda</span>
                </a>
                <a href="{{ route('admin.users.index') }}" class="mobile-nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                    <span class="text-[10px] font-medium">Pengguna</span>
                </a>
                <a href="{{ route('admin.packages.index') }}" class="mobile-nav-item {{ request()->routeIs('admin.packages.*') ? 'active' : '' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    <span class="text-[10px] font-medium">Paket</span>
                </a>
                <button id="mobileMenuBtn" class="mobile-nav-item">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                    <span class="text-[10px] font-medium">Lainnya</span>
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const toggleBtn = document.getElementById('menuToggle');
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');

            function openSidebar() {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                requestAnimationFrame(() => {
                    overlay.classList.add('opacity-100');
                    overlay.classList.remove('opacity-0');
                });
                document.body.style.overflow = 'hidden';
            }

            function closeSidebar() {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('opacity-0');
                overlay.classList.remove('opacity-100');
                setTimeout(() => overlay.classList.add('hidden'), 300);
                document.body.style.overflow = '';
            }

            toggleBtn?.addEventListener('click', function() {
                if (sidebar.classList.contains('-translate-x-full')) {
                    openSidebar();
                } else {
                    closeSidebar();
                }
            });

            mobileMenuBtn?.addEventListener('click', openSidebar);
            overlay?.addEventListener('click', closeSidebar);

            window.addEventListener('resize', function() {
                if (window.innerWidth >= 1024) {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.add('hidden');
                    overlay.classList.remove('opacity-0', 'opacity-100');
                    document.body.style.overflow = '';
                } else if (!sidebar.classList.contains('-translate-x-full')) {
                    closeSidebar();
                }
            });

            // Profile dropdown
            const profileBtn = document.getElementById('profileDropdownBtn');
            const profileDropdown = document.getElementById('profileDropdown');
            const dropdownArrow = document.getElementById('dropdownArrow');

            profileBtn?.addEventListener('click', function(e) {
                e.stopPropagation();
                const isOpen = !profileDropdown.classList.contains('hidden');
                profileDropdown.classList.toggle('hidden');
                dropdownArrow?.classList.toggle('rotate-180', !isOpen);
            });

            // Notifications dropdown
            const notifBtn = document.getElementById('notifBtn');
            const notifDropdown = document.getElementById('notifDropdown');

            notifBtn?.addEventListener('click', function(e) {
                e.stopPropagation();
                notifDropdown?.classList.toggle('hidden');
                profileDropdown?.classList.add('hidden');
                dropdownArrow?.classList.remove('rotate-180');
                if (!notifDropdown?.classList.contains('hidden')) {
                    loadAdminNotifications();
                }
            });

            document.addEventListener('click', function(e) {
                if (!e.target.closest('#profileDropdownWrap')) {
                    profileDropdown?.classList.add('hidden');
                    dropdownArrow?.classList.remove('rotate-180');
                }
                if (!e.target.closest('#notifWrap')) {
                    notifDropdown?.classList.add('hidden');
                }
            });

            // Real-time notification polling
            function loadAdminNotifications() {
                fetch('{{ route("admin.notifications.dropdown") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    updateAdminNotifBadge(data.unread_count);
                    renderAdminNotifDropdown(data.notifications);
                })
                .catch(() => {});
            }

            function updateAdminNotifBadge(count) {
                const badge = document.getElementById('adminNotifBadge');
                const sidebarBadge = document.getElementById('sidebarNotifBadge');
                if (badge) {
                    if (count > 0) {
                        badge.textContent = count > 99 ? '99+' : count;
                        badge.classList.remove('hidden');
                        badge.classList.add('flex');
                    } else {
                        badge.classList.add('hidden');
                        badge.classList.remove('flex');
                    }
                }
                if (sidebarBadge) {
                    if (count > 0) {
                        sidebarBadge.textContent = count > 99 ? '99+' : count;
                        sidebarBadge.classList.remove('hidden');
                    } else {
                        sidebarBadge.classList.add('hidden');
                    }
                }
            }

            function renderAdminNotifDropdown(notifications) {
                const container = document.getElementById('adminNotifList');
                if (!container) return;
                if (!notifications || notifications.length === 0) {
                    container.innerHTML = '<div class="px-4 py-8 text-center text-muted-foreground text-sm">Tidak ada notifikasi</div>';
                    return;
                }
                let html = '';
                const iconMap = {
                    order: '<svg class="w-4 h-4 text-gold-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
                    testimonial: '<svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227"/></svg>',
                    support: '<svg class="w-4 h-4 text-danger-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0"/></svg>',
                    enroll: '<svg class="w-4 h-4 text-success-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/></svg>',
                    video: '<svg class="w-4 h-4 text-pink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z"/></svg>',
                    account: '<svg class="w-4 h-4 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0"/></svg>'
                };
                const bgMap = {
                    order: 'bg-gold-100', testimonial: 'bg-primary/10', support: 'bg-danger-100',
                    enroll: 'bg-success-100', video: 'bg-pink-100', account: 'bg-muted'
                };
                notifications.forEach(n => {
                    const icon = iconMap[n.type] || iconMap.account;
                    const bg = bgMap[n.type] || bgMap.account;
                    html += '<a href="{{ route("admin.notifications.index") }}" class="flex items-center gap-3 px-4 py-3 hover:bg-accent transition-colors ' + (!n.is_read ? 'bg-accent/50' : '') + '">'
                        + '<div class="w-8 h-8 rounded-md ' + bg + ' flex items-center justify-center flex-shrink-0">' + icon + '</div>'
                        + '<div class="min-w-0 flex-1">'
                        + '<p class="text-sm font-medium truncate">' + escapeHtml(n.title) + '</p>'
                        + '<p class="text-xs text-muted-foreground truncate">' + escapeHtml(n.message) + '</p>'
                        + '</div>'
                        + '<span class="text-[10px] text-muted-foreground whitespace-nowrap">' + n.created_at + '</span>'
                        + '</a>';
                });
                container.innerHTML = html;
            }

            function markAllReadAdmin() {
                fetch('{{ route("admin.notifications.mark-all-read") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    }
                }).then(r => r.json()).then(data => {
                    updateAdminNotifBadge(0);
                    loadAdminNotifications();
                });
            }

            function escapeHtml(text) {
                if (!text) return '';
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // Initial load + polling every 10 seconds
            loadAdminNotifications();
            setInterval(loadAdminNotifications, 10000);

            // Auto-dismiss flash messages
            document.querySelectorAll('.flash-msg').forEach(function(msg) {
                const close = msg.querySelector('.close-flash');
                close?.addEventListener('click', function() {
                    msg.style.transition = 'opacity 0.3s, transform 0.3s';
                    msg.style.opacity = '0';
                    msg.style.transform = 'translateY(-8px)';
                    setTimeout(() => msg.remove(), 300);
                });
                setTimeout(() => {
                    msg.style.transition = 'opacity 0.5s, transform 0.5s';
                    msg.style.opacity = '0';
                    msg.style.transform = 'translateY(-8px)';
                    setTimeout(() => msg.remove(), 500);
                }, 6000);
            });

            // Hide bottom nav when sidebar is open on mobile
            const bottomNav = document.getElementById('mobileBottomNav');
            const observer = new MutationObserver(function() {
                if (window.innerWidth < 1024) {
                    if (!sidebar.classList.contains('-translate-x-full')) {
                        bottomNav.style.transform = 'translateY(100%)';
                    } else {
                        bottomNav.style.transform = '';
                    }
                }
            });
            observer.observe(sidebar, { attributes: true, attributeFilter: ['class'] });
        });
    </script>

    <!-- Floating AI Chat (Admin) -->
    <div class="fixed bottom-20 lg:bottom-6 right-4 sm:right-6 z-50 flex flex-col items-end gap-3" id="floatingChat">
        <div class="w-[340px] sm:w-[380px] max-w-[calc(100vw-40px)] max-h-[520px] bg-card rounded-lg shadow-lg border overflow-hidden hidden flex-col" id="chatWindow">
            <div class="bg-primary text-primary-foreground p-4 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-white/20 backdrop-blur flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-sm">Asisten AI Admin</h3>
                        <p class="text-[10px] text-white/70">Online</p>
                    </div>
                </div>
                <button onclick="toggleChat()" class="text-white/70 hover:text-white transition w-8 h-8 rounded-md hover:bg-white/10 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-4 space-y-3 min-h-[120px] max-h-[320px]" id="chatBody">
                <div class="flex items-start gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-primary text-primary-foreground flex items-center justify-center flex-shrink-0 text-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                    </div>
                    <div class="bg-muted text-foreground rounded-lg rounded-tl-sm p-3 max-w-[85%] text-sm leading-relaxed">
                        Halo Admin! Saya asisten AI untuk membantu mengelola platform KPM Belajar Online.
                        <span class="text-[10px] text-muted-foreground block mt-1.5">Sekarang</span>
                    </div>
                </div>
                <div id="chatMessages"></div>
            </div>
            <div class="px-3 pb-2 flex flex-wrap gap-1.5" id="quickActions">
                <button onclick="quickAsk('Ringkasan aktivitas hari ini')" class="text-[11px] px-2.5 py-1.5 rounded-md border text-muted-foreground hover:bg-accent transition font-medium">Ringkasan</button>
                <button onclick="quickAsk('Cara mengelola pesanan?')" class="text-[11px] px-2.5 py-1.5 rounded-md border text-muted-foreground hover:bg-accent transition font-medium">Kelola Pesanan</button>
            </div>
            <div class="p-3 border-t bg-background flex-shrink-0">
                <div class="flex gap-2 items-end">
                    <textarea id="chatInput" rows="1" placeholder="Ketik pertanyaan Anda..." oninput="autoResize(this)" class="flex-1 border border-input rounded-md px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-ring transition min-h-[40px] max-h-[80px] leading-relaxed bg-background placeholder:text-muted-foreground"></textarea>
                    <button onclick="sendMessage()" id="chatSendBtn" class="w-9 h-9 rounded-md bg-primary text-primary-foreground flex items-center justify-center hover:bg-primary/90 transition flex-shrink-0 disabled:opacity-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                    </button>
                </div>
                <div class="flex items-center justify-between mt-2">
                    <button onclick="escalateToAdmin()" class="text-[11px] text-muted-foreground hover:text-foreground transition flex items-center gap-1">
                        Hubungi Admin
                    </button>
                    <span class="text-[10px] text-muted-foreground/50">Powered by AI</span>
                </div>
            </div>
        </div>

        <!-- Toggle Button -->
        <button onclick="toggleChat()" id="chatToggle" class="w-14 h-14 rounded-full bg-primary text-primary-foreground shadow-lg hover:shadow-xl hover:scale-110 transition-all duration-300 flex items-center justify-center relative group">
            <svg class="w-6 h-6 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
            <span class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-success-500 rounded-full border-2 border-background animate-pulse"></span>
        </button>
    </div>

    <script>
        // Chat functions for admin layout
        let chatOpen = false;
        let chatSending = false;

        function toggleChat() {
            chatOpen = !chatOpen;
            const win = document.getElementById('chatWindow');
            if (win) {
                win.classList.toggle('hidden');
                if (chatOpen) {
                    win.classList.add('flex');
                    document.getElementById('chatInput')?.focus();
                } else {
                    win.classList.remove('flex');
                }
            }
        }

        function autoResize(el) {
            if (el) { el.style.height = 'auto'; el.style.height = Math.min(el.scrollHeight, 80) + 'px'; }
        }

        function scrollToBottom() {
            const body = document.getElementById('chatBody');
            if (body) body.scrollTop = body.scrollHeight;
        }

        function formatAIResponse(text) {
            let html = escapeHtml(text);
            html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
            html = html.replace(/\n/g, '<br>');
            return html;
        }

        function addMessage(text, isUser) {
            const container = document.getElementById('chatMessages');
            if (!container) return;
            const now = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            const div = document.createElement('div');
            if (isUser) {
                div.className = 'flex items-start gap-2.5 justify-end';
                div.innerHTML = `<div class="bg-primary text-primary-foreground rounded-lg rounded-tr-sm p-3 max-w-[85%] text-sm leading-relaxed">${escapeHtml(text)}<span class="text-[10px] opacity-50 block mt-1.5 text-right">${now}</span></div>`;
            } else {
                div.className = 'flex items-start gap-2.5';
                div.innerHTML = `<div class="w-7 h-7 rounded-lg bg-primary text-primary-foreground flex items-center justify-center flex-shrink-0 text-xs"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg></div><div class="bg-muted text-foreground rounded-lg rounded-tl-sm p-3 max-w-[85%] text-sm leading-relaxed">${formatAIResponse(text)}<span class="text-[10px] text-muted-foreground block mt-1.5">${now}</span></div>`;
            }
            container.appendChild(div);
            scrollToBottom();
        }

        function showTyping() {
            const container = document.getElementById('chatMessages');
            if (!container) return;
            const div = document.createElement('div');
            div.id = 'typingIndicator';
            div.className = 'flex items-start gap-2.5';
            div.innerHTML = `<div class="w-7 h-7 rounded-lg bg-primary text-primary-foreground flex items-center justify-center flex-shrink-0 text-xs"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg></div><div class="bg-muted rounded-lg rounded-tl-sm px-4 py-3"><div class="flex gap-1"><span class="typing-dot w-2 h-2 bg-muted-foreground/60 rounded-full"></span><span class="typing-dot w-2 h-2 bg-muted-foreground/60 rounded-full" style="animation-delay:0.15s"></span><span class="typing-dot w-2 h-2 bg-muted-foreground/60 rounded-full" style="animation-delay:0.3s"></span></div></div>`;
            container.appendChild(div);
            scrollToBottom();
        }

        function hideTyping() {
            document.getElementById('typingIndicator')?.remove();
        }

        function quickAsk(question) {
            document.getElementById('chatInput').value = question;
            sendMessage();
        }

        function sendMessage() {
            if (chatSending) return;
            const input = document.getElementById('chatInput');
            const message = input?.value.trim();
            if (!message) return;

            chatSending = true;
            const sendBtn = document.getElementById('chatSendBtn');
            if (sendBtn) sendBtn.disabled = true;

            addMessage(message, true);
            input.value = '';
            input.style.height = 'auto';
            document.getElementById('quickActions')?.classList.add('hidden');
            showTyping();

            fetch('{{ route("chat.send") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ message })
            })
            .then(r => r.json())
            .then(data => {
                hideTyping();
                if (data.success) {
                    addMessage(data.data.message, false);
                } else {
                    addMessage('Maaf, terjadi kesalahan. Silakan coba lagi.', false);
                }
            })
            .catch(err => {
                hideTyping();
                addMessage('Koneksi bermasalah. Silakan coba lagi.', false);
                console.error(err);
            })
            .finally(() => {
                chatSending = false;
                if (sendBtn) sendBtn.disabled = false;
                input?.focus();
            });
        }

        function escalateToAdmin() {
            if (!confirm('Hubungi admin untuk bantuan lebih lanjut?')) return;
            const question = 'Meminta bantuan langsung dari admin.';
            fetch('{{ route("support.submit") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ question })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    addMessage('Pesan Anda telah diteruskan ke admin. Mohon tunggu, admin akan segera merespons.', false);
                } else {
                    addMessage('Gagal menghubungi admin. Silakan coba lagi nanti.', false);
                }
            })
            .catch(() => addMessage('Gagal menghubungi admin. Silakan coba lagi.', false));
        }

        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('chatInput');
            if (input) {
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
                });
            }
        });
    </script>
</body>
</html>
