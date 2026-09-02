{{-- layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'KPM Belajar Online')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav class="sticky top-0 z-50 w-full border-b border-border/40 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="{{ Auth::check() && Auth::user()->role === 'admin' ? route('admin.dashboard') : (Auth::check() ? route('user.dashboard') : '/') }}"
                   class="flex items-center gap-2 text-lg font-bold text-foreground hover:text-primary transition">
                    <span class="w-8 h-8 rounded-lg bg-primary flex items-center justify-center text-primary-foreground text-sm font-bold">K</span>
                    <span class="hidden sm:inline">KPM Belajar Online</span>
                </a>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center gap-1">
                    @auth
                        @if(Auth::user()->role === 'user')
                            <a href="{{ route('packages.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-accent rounded-md transition {{ request()->routeIs('packages.*') ? 'text-foreground bg-accent' : '' }}">Paket</a>
                            <a href="{{ route('videos.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-accent rounded-md transition {{ request()->routeIs('videos.*') ? 'text-foreground bg-accent' : '' }}">Video</a>
                            <a href="{{ route('practice.history') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-accent rounded-md transition {{ request()->routeIs('practice.*') ? 'text-foreground bg-accent' : '' }}">Latihan</a>
                            <a href="{{ route('orders.index') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-accent rounded-md transition {{ request()->routeIs('orders.*') ? 'text-foreground bg-accent' : '' }}">Pesanan</a>
                        @endif
                        <a href="{{ Auth::user()->role === 'admin' ? route('admin.dashboard') : route('user.dashboard') }}"
                           class="inline-flex items-center justify-center bg-primary text-primary-foreground px-4 py-2 rounded-md text-sm font-medium hover:bg-primary/90 transition ml-2">
                            Dasbor
                        </a>

                        {{-- Notification Bell --}}
                        <div class="relative" id="userNotifWrap">
                            <button id="userNotifBtn" class="inline-flex items-center justify-center w-9 h-9 rounded-md text-muted-foreground hover:bg-accent hover:text-accent-foreground transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring relative">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                                <span id="userNotifBadge" class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-destructive text-destructive-foreground text-[10px] font-bold rounded-full items-center justify-center hidden">0</span>
                            </button>
                            <div id="userNotifDropdown" class="hidden absolute right-0 mt-2 w-80 max-w-[calc(100vw-2rem)] bg-popover text-popover-foreground rounded-lg shadow-md border py-2 z-50 animate-scale-in">
                                <div class="px-4 py-3 border-b flex items-center justify-between">
                                    <p class="text-sm font-semibold">Notifikasi</p>
                                    <button onclick="markAllReadUser()" class="text-xs text-muted-foreground hover:text-foreground font-medium transition">Tandai semua dibaca</button>
                                </div>
                                <div class="max-h-72 overflow-y-auto" id="userNotifList">
                                    <div class="px-4 py-6 text-center text-muted-foreground text-sm">Memuat...</div>
                                </div>
                                <div class="border-t mt-1 pt-1">
                                    <a href="{{ route('notifications.index') }}" class="block px-4 py-2.5 text-sm text-center text-muted-foreground hover:bg-accent transition font-medium">Lihat Semua Notifikasi</a>
                                </div>
                            </div>
                        </div>

                        <div class="relative" id="userDropdown">
                            <button onclick="toggleUserDropdown()" class="inline-flex items-center gap-2 text-muted-foreground hover:text-foreground transition px-2 py-1.5 rounded-md hover:bg-accent">
                                <span class="w-7 h-7 rounded-full bg-primary flex items-center justify-center text-xs font-bold text-primary-foreground">{{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}</span>
                                <span class="hidden sm:inline text-sm">{{ Str::limit(Auth::user()->name ?? 'Admin', 15) }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div id="userDropdownMenu" class="hidden absolute right-0 mt-2 w-56 bg-popover text-popover-foreground rounded-lg shadow-md py-1 z-50 border">
                                <div class="px-4 py-3 border-b">
                                    <p class="font-semibold text-sm truncate">{{ Auth::user()->name ?? 'Admin' }}</p>
                                    <p class="text-xs text-muted-foreground truncate mt-0.5">{{ Auth::user()->email ?? '' }}</p>
                                </div>
                                <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-accent hover:text-accent-foreground transition rounded-md mx-1">Profil Saya</a>
                                @if(Auth::user()->role === 'user')
                                    <a href="{{ route('orders.index') }}" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-accent hover:text-accent-foreground transition rounded-md mx-1">Pesanan Saya</a>
                                    <a href="{{ route('videos.index') }}" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-accent hover:text-accent-foreground transition rounded-md mx-1">Video Pembahasan</a>
                                    <a href="{{ route('practice.history') }}" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-accent hover:text-accent-foreground transition rounded-md mx-1">Riwayat Latihan</a>
                                    <a href="{{ route('practice.statistics') }}" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-accent hover:text-accent-foreground transition rounded-md mx-1">Statistik</a>
                                @endif
                                <div class="border-t my-1"></div>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="flex items-center gap-2 px-4 py-2 text-sm text-destructive hover:bg-destructive/10 transition w-full text-left rounded-md mx-1">Keluar</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center px-3 py-2 text-sm font-medium text-muted-foreground hover:text-foreground hover:bg-accent rounded-md transition">Masuk</a>
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center bg-primary text-primary-foreground px-4 py-2 rounded-md text-sm font-medium hover:bg-primary/90 transition ml-2">Daftar</a>
                    @endauth
                </div>

                <!-- Mobile Hamburger -->
                <button id="mobileMenuBtn" onclick="toggleMobileMenu()" aria-label="Menu"
                        class="md:hidden inline-flex items-center justify-center w-9 h-9 rounded-md text-muted-foreground hover:bg-accent hover:text-accent-foreground transition">
                    <svg id="menuIconOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    <svg id="menuIconClose" class="hidden w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobileMenu" class="md:hidden border-t bg-background shadow-lg hidden">
            <div class="px-4 py-3 space-y-1">
                @auth
                    <div class="flex items-center gap-3 px-3 py-3 rounded-lg bg-muted mb-2">
                        <span class="w-9 h-9 rounded-full bg-primary flex items-center justify-center text-sm font-bold text-primary-foreground flex-shrink-0">{{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}</span>
                        <div class="min-w-0">
                            <p class="font-semibold text-sm truncate">{{ Auth::user()->name ?? 'Pengguna' }}</p>
                            <p class="text-xs text-muted-foreground truncate">{{ Auth::user()->email ?? '' }}</p>
                        </div>
                    </div>
                    @if(Auth::user()->role === 'user')
                        <a href="{{ route('user.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-accent transition text-sm {{ request()->routeIs('user.dashboard') ? 'bg-accent text-foreground' : 'text-muted-foreground' }}">Dasbor</a>
                        <a href="{{ route('packages.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-accent transition text-sm {{ request()->routeIs('packages.*') ? 'bg-accent text-foreground' : 'text-muted-foreground' }}">Paket Bank Soal</a>
                        <a href="{{ route('videos.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-accent transition text-sm {{ request()->routeIs('videos.*') ? 'bg-accent text-foreground' : 'text-muted-foreground' }}">Video Pembahasan</a>
                        <a href="{{ route('practice.history') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-accent transition text-sm {{ request()->routeIs('practice.*') ? 'bg-accent text-foreground' : 'text-muted-foreground' }}">Riwayat Latihan</a>
                        <a href="{{ route('practice.statistics') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-accent transition text-sm {{ request()->routeIs('practice.statistics') ? 'bg-accent text-foreground' : 'text-muted-foreground' }}">Statistik</a>
                        <a href="{{ route('orders.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-accent transition text-sm {{ request()->routeIs('orders.*') ? 'bg-accent text-foreground' : 'text-muted-foreground' }}">Pesanan Saya</a>
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-accent transition text-sm {{ request()->routeIs('profile.*') ? 'bg-accent text-foreground' : 'text-muted-foreground' }}">Profil Saya</a>
                    @else
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-accent transition text-sm {{ request()->routeIs('admin.dashboard') ? 'bg-accent text-foreground' : 'text-muted-foreground' }}">Dasbor Admin</a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST" class="border-t pt-2">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg text-destructive hover:bg-destructive/10 transition text-sm text-left">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-accent transition text-sm text-muted-foreground">Masuk</a>
                    <a href="{{ route('register') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-primary text-primary-foreground font-semibold text-sm mt-1">Daftar Sekarang</a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-8 w-full animate-fade-in">
        @if(session('success'))
            <div class="bg-success-50 border border-success-200 p-4 rounded-lg mb-6 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-success-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-success-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-sm text-success-700 font-medium">{{ session('success') }}</p>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-danger-50 border border-danger-200 p-4 rounded-lg mb-6 flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-danger-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-danger-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                </div>
                <p class="text-sm text-danger-700 font-medium">{{ session('error') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-danger-50 border border-danger-200 p-4 rounded-lg mb-6">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-danger-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-danger-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-danger-800">Terjadi kesalahan:</p>
                        <ul class="text-sm text-danger-700 list-disc list-inside mt-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        @yield('content')
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
                    <p class="text-muted-foreground text-sm leading-relaxed">Platform Bank Soal Berbayar untuk mendukung pembelajaran dan latihan mandiri.</p>
                </div>
                <div>
                    <h4 class="font-semibold mb-4 text-sm">Menu</h4>
                    <ul class="space-y-2 text-muted-foreground text-sm">
                        <li><a href="{{ route('pages.features') }}" class="hover:text-foreground transition-all duration-200">Fitur Unggulan</a></li>
                        <li><a href="{{ auth()->check() ? route('packages.index') : url('/#packages') }}" class="hover:text-foreground transition-all duration-200">Paket Bank Soal</a></li>
                        @auth
                            <li><a href="{{ route('videos.index') }}" class="hover:text-foreground transition-all duration-200">Video Pembahasan</a></li>
                            <li><a href="{{ route('practice.history') }}" class="hover:text-foreground transition-all duration-200">Latihan</a></li>
                            <li><a href="{{ route('orders.index') }}" class="hover:text-foreground transition-all duration-200">Pesanan</a></li>
                        @endauth
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold mb-4 text-sm">Bantuan</h4>
                    <ul class="space-y-2 text-muted-foreground text-sm">
                        <li><a href="{{ route('pages.guide') }}" class="hover:text-foreground transition-all duration-200 {{ request()->routeIs('pages.guide') ? 'text-foreground font-medium' : '' }}">Panduan Penggunaan</a></li>
                        <li><a href="{{ route('pages.faq') }}" class="hover:text-foreground transition-all duration-200 {{ request()->routeIs('pages.faq') ? 'text-foreground font-medium' : '' }}">FAQ</a></li>
                        <li><button type="button" onclick="toggleChat()" class="hover:text-foreground transition-all duration-200">Hubungi Kami</button></li>
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
            <div class="border-t mt-8 pt-6 text-center text-muted-foreground text-sm">&copy; {{ date('Y') }} KPM Belajar Online. Hak cipta dilindungi.</div>
        </div>
    </footer>

    <!-- Floating AI Chat -->
    <div class="fixed bottom-6 right-6 z-50 flex flex-col items-end gap-3" id="floatingChat">
        <div class="w-[340px] sm:w-[380px] max-w-[calc(100vw-40px)] max-h-[520px] bg-card rounded-lg shadow-lg border overflow-hidden hidden flex-col" id="chatWindow">
            <!-- Header -->
            <div class="bg-primary text-primary-foreground p-4 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-white/20 backdrop-blur flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-sm">Asisten AI KPM</h3>
                        <p class="text-[10px] text-white/70">Online</p>
                    </div>
                </div>
                <button onclick="toggleChat()" class="text-white/70 hover:text-white transition w-8 h-8 rounded-md hover:bg-white/10 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Messages -->
            <div class="flex-1 overflow-y-auto p-4 space-y-3 min-h-[120px] max-h-[320px]" id="chatBody">
                <div class="flex items-start gap-2.5" id="chatWelcome">
                    <div class="w-7 h-7 rounded-lg bg-primary text-primary-foreground flex items-center justify-center flex-shrink-0 text-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                    </div>
                    <div class="bg-muted text-foreground rounded-lg rounded-tl-sm p-3 max-w-[85%] text-sm leading-relaxed">
                        Halo! Saya asisten AI <strong>KPM Belajar Online</strong>. Ada yang bisa saya bantu?
                        <span class="text-[10px] text-muted-foreground block mt-1.5">Sekarang</span>
                    </div>
                </div>
                <div id="chatMessages"></div>
            </div>

            <!-- Quick Actions -->
            <div class="px-3 pb-2 flex flex-wrap gap-1.5" id="quickActions">
                <button onclick="quickAsk('Apa itu KPM Belajar Online?')" class="text-[11px] px-2.5 py-1.5 rounded-md border text-muted-foreground hover:bg-accent transition font-medium">Tentang KPM</button>
                <button onclick="quickAsk('Cara membeli paket membership?')" class="text-[11px] px-2.5 py-1.5 rounded-md border text-muted-foreground hover:bg-accent transition font-medium">Beli Paket</button>
                <button onclick="quickAsk('Cara melatih soal?')" class="text-[11px] px-2.5 py-1.5 rounded-md border text-muted-foreground hover:bg-accent transition font-medium">Latihan Soal</button>
            </div>

            <!-- Input -->
            <div class="p-3 border-t bg-background flex-shrink-0">
                <div class="flex gap-2 items-end">
                    <textarea id="chatInput" rows="1" placeholder="Ketik pertanyaan Anda..."
                        oninput="autoResize(this)"
                        class="flex-1 border border-input rounded-md px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-ring transition min-h-[40px] max-h-[80px] leading-relaxed bg-background placeholder:text-muted-foreground"></textarea>
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
        // Mobile Menu Toggle with animation
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            const iconOpen = document.getElementById('menuIconOpen');
            const iconClose = document.getElementById('menuIconClose');
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        menu.classList.add('menu-open');
                    });
                });
            } else {
                menu.classList.remove('menu-open');
                setTimeout(() => {
                    menu.classList.add('hidden');
                }, 350);
            }
            iconOpen.classList.toggle('hidden');
            iconClose.classList.toggle('hidden');
        }

        // User Dropdown
        function toggleUserDropdown() {
            const menu = document.getElementById('userDropdownMenu');
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
            } else {
                menu.classList.add('hidden');
            }
        }
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('userDropdown');
            if (dropdown && !dropdown.contains(e.target)) {
                document.getElementById('userDropdownMenu')?.classList.add('hidden');
            }
            const notifWrap = document.getElementById('userNotifWrap');
            if (notifWrap && !notifWrap.contains(e.target)) {
                document.getElementById('userNotifDropdown')?.classList.add('hidden');
            }
            const menuBtn = document.getElementById('mobileMenuBtn');
            const mobileMenu = document.getElementById('mobileMenu');
            if (mobileMenu && menuBtn && !mobileMenu.classList.contains('hidden') && !menuBtn.contains(e.target) && !mobileMenu.contains(e.target)) {
                toggleMobileMenu();
            }
        });

        // User Notifications
        const userNotifBtn = document.getElementById('userNotifBtn');
        const userNotifDropdown = document.getElementById('userNotifDropdown');

        userNotifBtn?.addEventListener('click', function(e) {
            e.stopPropagation();
            userNotifDropdown?.classList.toggle('hidden');
            if (!userNotifDropdown?.classList.contains('hidden')) {
                loadUserNotifications();
            }
        });

        function loadUserNotifications() {
            fetch('{{ route("notifications.dropdown") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                updateUserNotifBadge(data.unread_count);
                renderUserNotifDropdown(data.notifications);
            })
            .catch(() => {});
        }

        function updateUserNotifBadge(count) {
            const badge = document.getElementById('userNotifBadge');
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
        }

        function renderUserNotifDropdown(notifications) {
            const container = document.getElementById('userNotifList');
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
                html += '<a href="{{ route("notifications.index") }}" class="flex items-center gap-3 px-4 py-3 hover:bg-accent transition-colors ' + (!n.is_read ? 'bg-accent/50' : '') + '">'
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

        function markAllReadUser() {
            fetch('{{ route("notifications.mark-all-read") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            }).then(r => r.json()).then(data => {
                updateUserNotifBadge(0);
                loadUserNotifications();
            });
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Initial load + polling every 10 seconds
        @auth
        loadUserNotifications();
        setInterval(loadUserNotifications, 10000);
        @endauth

        // AI Chat
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

    @stack('scripts')
</body>
</html>
