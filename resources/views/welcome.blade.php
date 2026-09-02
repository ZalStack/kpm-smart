<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KPM Belajar Online - Platform Belajar Terpercaya</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        *, *::before, *::after { font-family: 'Inter', sans-serif; }
        .font-display { font-family: 'Sora', 'Inter', sans-serif; }

        /* Smooth Scroll */
        html { scroll-behavior: smooth; scroll-padding-top: 80px; }

        /* Hero */
        .hero-bg {
            background: linear-gradient(160deg, #0a0e27 0%, #161758 30%, #1e3a8a 60%, #161758 85%, #0a0e27 100%);
            position: relative; overflow: hidden;
        }
        .hero-bg::before {
            content: ''; position: absolute; inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 75% 20%, rgba(0,162,233,.18) 0%, transparent 55%),
                radial-gradient(ellipse 60% 50% at 15% 75%, rgba(252,198,38,.10) 0%, transparent 55%),
                radial-gradient(ellipse 40% 40% at 50% 50%, rgba(99,102,241,.08) 0%, transparent 50%);
            pointer-events: none;
        }
        .hero-bg::after {
            content: ''; position: absolute; inset: 0;
            background-image: radial-gradient(rgba(255,255,255,.04) 1px, transparent 1px);
            background-size: 32px 32px; pointer-events: none;
        }

        /* Navbar */
        .nav-modern {
            background: rgba(10, 14, 39, 0.85);
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
            border-bottom: 1px solid rgba(255,255,255,.06);
            transition: all .3s ease;
        }
        .nav-modern.scrolled {
            background: rgba(10, 14, 39, 0.95);
            box-shadow: 0 4px 30px rgba(0,0,0,.2);
        }

        /* Cards */
        .card-modern {
            background: #fff; border-radius: 20px;
            border: 1px solid rgba(0,0,0,.04);
            box-shadow: 0 1px 3px rgba(0,0,0,.03), 0 4px 20px rgba(0,0,0,.02);
            transition: all .4s cubic-bezier(.16,1,.3,1);
        }
        .card-modern:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 50px rgba(22,23,88,.10);
            border-color: rgba(0,162,233,.15);
        }

        /* Package Card */
        .pkg-card { position: relative; overflow: hidden; border-radius: 20px; }
        .pkg-card .pkg-thumb { transition: transform .6s cubic-bezier(.16,1,.3,1); }
        .pkg-card:hover .pkg-thumb { transform: scale(1.08); }

        /* Feature Card */
        .feature-card {
            background: linear-gradient(135deg, #f8fafc 0%, #fff 100%);
            border: 1px solid rgba(0,0,0,.04);
            border-radius: 20px; padding: 2rem 1.5rem;
            transition: all .4s cubic-bezier(.16,1,.3,1);
            position: relative; overflow: hidden;
        }
        .feature-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
            background: var(--accent); opacity: 0; transition: opacity .3s;
        }
        .feature-card:hover { transform: translateY(-4px); box-shadow: 0 16px 40px rgba(0,0,0,.06); border-color: transparent; }
        .feature-card:hover::before { opacity: 1; }

        /* Testimonial */
        .testi-card {
            background: #fff; border-radius: 20px; padding: 1.5rem;
            border: 1px solid rgba(0,0,0,.04);
            box-shadow: 0 1px 3px rgba(0,0,0,.02);
            transition: all .4s cubic-bezier(.16,1,.3,1);
        }
        .testi-card:hover { transform: translateY(-4px); box-shadow: 0 16px 40px rgba(0,0,0,.06); }

        .testi-quote { position: relative; padding-left: 1rem; border-left: 3px solid #00a2e9; }
        .testi-quote::before {
            content: '\201C'; position: absolute; top: -12px; left: -12px;
            font-size: 48px; color: #00a2e9; opacity: .12; font-family: Georgia, serif;
        }

        .testi-avatar {
            width: 48px; height: 48px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 16px; color: #fff; flex-shrink: 0;
        }

        /* Stats Counter */
        .stat-counter {
            background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.08);
            border-radius: 16px; backdrop-filter: blur(8px);
            transition: all .3s;
        }
        .stat-counter:hover { background: rgba(255,255,255,.10); border-color: rgba(255,255,255,.15); }

        /* CTA */
        .cta-section {
            background: linear-gradient(160deg, #161758 0%, #1e3a8a 50%, #161758 100%);
            position: relative; overflow: hidden;
        }
        .cta-section::before {
            content: ''; position: absolute; inset: 0;
            background: radial-gradient(ellipse 60% 50% at 50% 50%, rgba(252,198,38,.06) 0%, transparent 60%);
            pointer-events: none;
        }

        /* Button */
        .btn-gold {
            background: linear-gradient(135deg, #FCC626 0%, #f5b800 100%);
            color: #161758; font-weight: 700; border-radius: 14px;
            padding: 0.875rem 2rem; display: inline-flex; align-items: center; gap: .5rem;
            transition: all .3s cubic-bezier(.16,1,.3,1);
            box-shadow: 0 4px 16px rgba(252,198,38,.25);
        }
        .btn-gold:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(252,198,38,.35);
            background: linear-gradient(135deg, #f5b800 0%, #d4a00a 100%);
        }
        .btn-gold:active { transform: translateY(0) scale(.98); }

        .btn-outline {
            background: transparent; color: rgba(255,255,255,.75);
            border: 1px solid rgba(255,255,255,.2); border-radius: 14px;
            padding: 0.875rem 2rem; font-weight: 600;
            display: inline-flex; align-items: center; gap: .5rem;
            transition: all .3s;
        }
        .btn-outline:hover {
            color: #fff; background: rgba(255,255,255,.08);
            border-color: rgba(255,255,255,.35);
        }

        .btn-white {
            background: #fff; color: #161758; font-weight: 700; border-radius: 14px;
            padding: 0.875rem 2rem; display: inline-flex; align-items: center; gap: .5rem;
            transition: all .3s cubic-bezier(.16,1,.3,1);
            box-shadow: 0 4px 16px rgba(0,0,0,.1);
        }
        .btn-white:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(0,0,0,.15); }

        /* Section Badge */
        .section-badge {
            display: inline-flex; align-items: center; gap: .5rem;
            padding: .375rem 1rem; border-radius: 9999px;
            font-size: .75rem; font-weight: 600;
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(0,0,0,.1); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,.2); }

        /* Animations */
        @keyframes fadeUp { from { opacity: 0; transform: translateY(32px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeUpSmall { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes scaleIn { from { opacity: 0; transform: scale(.92); } to { opacity: 1; transform: scale(1); } }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-12px); } }
        @keyframes pulse-glow { 0%, 100% { box-shadow: 0 0 0 0 rgba(0,162,233,.3); } 50% { box-shadow: 0 0 0 10px rgba(0,162,233,0); } }
        @keyframes shimmer { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }

        .anim-up { animation: fadeUp .7s cubic-bezier(.16,1,.3,1) both; }
        .anim-up-sm { animation: fadeUpSmall .6s cubic-bezier(.16,1,.3,1) both; }
        .anim-scale { animation: scaleIn .5s cubic-bezier(.16,1,.3,1) both; }
        .anim-float { animation: float 6s ease-in-out infinite; }

        .d1 { animation-delay: .1s; } .d2 { animation-delay: .2s; }
        .d3 { animation-delay: .3s; } .d4 { animation-delay: .4s; }
        .d5 { animation-delay: .5s; } .d6 { animation-delay: .6s; }

        /* Mobile Menu */
        #mobileNav {
            max-height: 0; overflow: hidden;
            transition: max-height .4s cubic-bezier(.16,1,.3,1), opacity .3s;
            opacity: 0;
        }
        #mobileNav.open { max-height: 600px; opacity: 1; }

        /* Responsive Typography */
        @media (max-width: 640px) {
            .hero-title { font-size: 1.875rem !important; line-height: 1.2 !important; }
            .hero-sub { font-size: 1rem !important; }
        }
    </style>
</head>
<body class="bg-muted min-h-screen flex flex-col">

    <!-- ==================== NAVBAR ==================== -->
    <nav class="nav-modern fixed top-0 left-0 right-0 z-50" id="mainNav">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 md:h-18">
                <!-- Logo -->
                <a href="/" class="flex items-center gap-2.5 shrink-0">
                    <div class="w-9 h-9 rounded-md bg-gradient-to-br from-yellow-400 to-amber-500 flex items-center justify-center shadow-lg shadow-amber-500/20">
                        <span class="text-foreground font-extrabold text-sm font-display">K</span>
                    </div>
                    <span class="text-white font-bold text-lg font-display hidden sm:block">KPM Belajar Online</span>
                </a>

                <!-- Desktop Nav -->
                <div class="hidden md:flex items-center gap-1">
                    <a href="#packages" class="text-white/60 hover:text-white px-3 py-2 rounded-lg text-sm font-medium transition">Paket</a>
                    <a href="#features" class="text-white/60 hover:text-white px-3 py-2 rounded-lg text-sm font-medium transition">Keunggulan</a>
                    <a href="#testimonials" class="text-white/60 hover:text-white px-3 py-2 rounded-lg text-sm font-medium transition">Testimoni</a>
                </div>

                <!-- Auth Buttons -->
                <div class="flex items-center gap-2">
                    @auth
                        <a href="{{ Auth::user()->role === 'admin' ? route('admin.dashboard') : route('user.dashboard') }}"
                           class="btn-gold text-sm !py-2 !px-5 rounded-md">
                            Dasbor
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="text-white/70 hover:text-white text-sm font-medium px-3 py-2 rounded-lg transition hidden sm:block">Masuk</a>
                        <a href="{{ route('register') }}" class="btn-gold text-sm !py-2 !px-5 rounded-md">Daftar</a>
                    @endauth

                    <!-- Mobile Toggle -->
                    <button onclick="toggleMobileNav()" class="md:hidden w-10 h-10 rounded-md bg-card/10 flex items-center justify-center text-white/80 hover:bg-card/20 transition border border-white/10" aria-label="Menu">
                        <svg id="navOpen" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                        <svg id="navClose" class="hidden w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Nav -->
        <div id="mobileNav" class="md:hidden border-t border-white/10">
            <div class="px-4 py-3 space-y-1">
                <a href="#packages" onclick="closeMobileNav()" class="block px-4 py-2.5 rounded-md text-white/80 hover:bg-card/10 text-sm font-medium transition">📦 Paket</a>
                <a href="#features" onclick="closeMobileNav()" class="block px-4 py-2.5 rounded-md text-white/80 hover:bg-card/10 text-sm font-medium transition">✨ Keunggulan</a>
                <a href="#testimonials" onclick="closeMobileNav()" class="block px-4 py-2.5 rounded-md text-white/80 hover:bg-card/10 text-sm font-medium transition">💬 Testimoni</a>
                <a href="{{ route('pages.features') }}" class="block px-4 py-2.5 rounded-md text-white/60 hover:bg-card/10 text-sm transition">Fitur Unggulan</a>
                <a href="{{ route('pages.guide') }}" class="block px-4 py-2.5 rounded-md text-white/60 hover:bg-card/10 text-sm transition">Panduan</a>
                <a href="{{ route('pages.faq') }}" class="block px-4 py-2.5 rounded-md text-white/60 hover:bg-card/10 text-sm transition">FAQ</a>
            </div>
        </div>
    </nav>

    <!-- ==================== HERO ==================== -->
    <section class="hero-bg pt-32 pb-20 md:pt-40 md:pb-28 lg:pt-48 lg:pb-36 text-white relative">
        <!-- Floating Dots -->
        <div class="absolute top-24 left-[8%] w-2 h-2 bg-gold-400 rounded-full opacity-40 anim-float" style="animation-delay:0s"></div>
        <div class="absolute top-40 right-[12%] w-3 h-3 bg-cyan-400 rounded-full opacity-25 anim-float" style="animation-delay:1.5s"></div>
        <div class="absolute bottom-20 left-[18%] w-2 h-2 bg-success-400 rounded-full opacity-30 anim-float" style="animation-delay:3s"></div>
        <div class="absolute top-48 left-[45%] w-1.5 h-1.5 bg-card rounded-full opacity-15 anim-float" style="animation-delay:.7s"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="max-w-3xl mx-auto text-center">
                <!-- Badge -->
                <div class="anim-up inline-flex items-center gap-2 bg-card/[.07] border border-white/[.1] rounded-full px-4 py-1.5 mb-6 md:mb-8 backdrop-blur-sm">
                    <span class="w-2 h-2 bg-success-400 rounded-full animate-pulse"></span>
                    <span class="text-white/70 text-xs md:text-sm font-medium">Platform Belajar #1 untuk Siswa Indonesia</span>
                </div>

                <!-- Title -->
                <h1 class="hero-title anim-up d1 text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-extrabold leading-[1.15] mb-5 md:mb-6">
                    Wujudkan Impianmu dengan
                    <span class="bg-gradient-to-r from-yellow-400 via-amber-400 to-yellow-500 bg-clip-text text-transparent"> KPM Mastery</span>
                </h1>

                <!-- Subtitle -->
                <p class="hero-sub anim-up d2 text-white/50 text-base md:text-lg lg:text-xl max-w-xl mx-auto mb-8 md:mb-10 leading-relaxed">
                    Platform belajar online dengan ribuan soal berkualitas, pembahasan detail, dan video interaktif untuk mempersiapkan ujianmu.
                </p>

                <!-- CTAs -->
                <div class="anim-up d3 flex flex-col sm:flex-row items-center justify-center gap-3 md:gap-4">
                    @auth
                        <a href="{{ Auth::user()->role === 'admin' ? route('admin.dashboard') : route('packages.index') }}" class="btn-gold">
                            Mulai Belajar
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="btn-gold">
                            Daftar Gratis
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                        </a>
                    @endauth
                    <a href="#packages" class="btn-outline">
                        Lihat Paket
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3"/></svg>
                    </a>
                </div>

                <!-- Trust Indicators -->
                <div class="anim-up d4 flex flex-wrap items-center justify-center gap-6 md:gap-10 mt-12 md:mt-16">
                    <div class="flex items-center gap-2">
                        <div class="flex -space-x-1">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-amber-400 to-amber-500 border-2 border-navy flex items-center justify-center text-[10px] font-bold">A</div>
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-cyan-400 to-cyan-500 border-2 border-navy flex items-center justify-center text-[10px] font-bold">S</div>
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-500 border-2 border-navy flex items-center justify-center text-[10px] font-bold">R</div>
                        </div>
                        <div class="text-left">
                            <div class="flex items-center gap-0.5 text-gold-400 text-xs">★★★★★</div>
                            <p class="text-white/40 text-[11px]">4.8 dari 5.0</p>
                        </div>
                    </div>
                    <div class="w-px h-8 bg-card/10 hidden sm:block"></div>
                    <div class="text-center">
                        <p class="text-white/80 font-bold text-lg">1.000+</p>
                        <p class="text-white/40 text-[11px]">Soal Latihan</p>
                    </div>
                    <div class="w-px h-8 bg-card/10 hidden sm:block"></div>
                    <div class="text-center">
                        <p class="text-white/80 font-bold text-lg">500+</p>
                        <p class="text-white/40 text-[11px]">Siswa Aktif</p>
                    </div>
                    <div class="w-px h-8 bg-card/10 hidden sm:block"></div>
                    <div class="text-center">
                        <p class="text-white/80 font-bold text-lg">98%</p>
                        <p class="text-white/40 text-[11px]">Kepuasan</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== PACKAGES ==================== -->
    <section class="py-16 md:py-20 lg:py-24 bg-muted" id="packages">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-10 md:mb-14">
                <span class="section-badge bg-foreground/5 text-foreground mb-3">📦 Paket Belajar</span>
                <h2 class="text-2xl md:text-3xl lg:text-4xl font-bold text-foreground font-display">Pilih Paket Terbaikmu</h2>
                <p class="text-muted-foreground mt-2 text-sm md:text-base max-w-lg mx-auto">Temukan paket yang sesuai dengan kebutuhan dan jenjang belajarmu</p>
            </div>

            @php
                $packages = \App\Models\Package::where('is_active', true)->paginate(12);
                $pkgColors = [
                    'bg-gradient-to-br from-violet-500 to-indigo-600',
                    'bg-gradient-to-br from-emerald-500 to-green-600',
                    'bg-gradient-to-br from-red-500 to-rose-600',
                    'bg-gradient-to-br from-amber-400 to-orange-500',
                    'bg-gradient-to-br from-cyan-500 to-blue-600',
                    'bg-gradient-to-br from-pink-500 to-fuchsia-600',
                    'bg-gradient-to-br from-teal-500 to-emerald-600',
                    'bg-gradient-to-br from-blue-500 to-indigo-600',
                ];
                $pkgIcons = ['📖','📚','🎯','💡','🚀','🌟','🎓','📊','⚡','🏆','💎','🔥'];
                $startOffset = ($packages->currentPage() - 1) * $packages->perPage();
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 md:gap-6">
                @forelse($packages as $index => $package)
                    @php
                        $gi = $startOffset + $index;
                        $colorClass = $pkgColors[$gi % count($pkgColors)];
                        $icon = $pkgIcons[$gi % count($pkgIcons)];
                        $totalCards = count($package->cards ?? []);
                        $totalQuestions = count($package->questions ?? []);
                        $videoCount = $package->videos()->where('is_active', true)->count();
                        $hasAccess = auth()->check() ? \App\Models\Order::where('user_id', auth()->id())
                            ->where('package_id', $package->id)
                            ->where('payment_status', 'paid')
                            ->whereJsonContains('enrollment->activated', true)
                            ->exists() : false;
                    @endphp

                    <div class="pkg-card card-modern flex flex-col group overflow-hidden">
                        {{-- Thumbnail --}}
                        <div class="relative h-44 overflow-hidden">
                            @if($package->thumbnail)
                                <img src="{{ asset('storage/' . $package->thumbnail) }}" alt="{{ $package->title }}"
                                     class="pkg-thumb w-full h-full object-cover" loading="lazy">
                            @else
                                <div class="w-full h-full {{ $colorClass }} flex items-center justify-center relative">
                                    <div class="absolute inset-0 opacity-10">
                                        <svg class="w-full h-full" viewBox="0 0 100 100"><circle cx="25" cy="25" r="18" fill="white" opacity=".3"/><circle cx="75" cy="35" r="22" fill="white" opacity=".2"/><circle cx="50" cy="75" r="28" fill="white" opacity=".2"/></svg>
                                    </div>
                                    <span class="text-6xl opacity-80 group-hover:scale-110 transition-transform duration-500">{{ $icon }}</span>
                                </div>
                            @endif

                            {{-- Badges --}}
                            <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
                                @if($package->is_pay_what_you_want)
                                    <span class="bg-success-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-full shadow-lg">💝 Seikhlasnya</span>
                                @elseif($package->hasDiscount())
                                    <span class="bg-danger-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-full shadow-lg">🔥 -{{ $package->discount_percent }}%</span>
                                @endif
                            </div>
                            <span class="absolute top-3 right-3 bg-card/90 backdrop-blur text-foreground text-[10px] font-semibold px-2.5 py-1 rounded-full shadow">⏳ {{ $package->membership_duration_label }}</span>
                            @if($hasAccess)
                                <div class="absolute bottom-3 left-3">
                                    <span class="bg-success-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-full shadow-lg">✓ Dimiliki</span>
                                </div>
                            @endif
                        </div>

                        <div class="p-4 md:p-5 flex flex-col flex-1">
                            {{-- Title --}}
                            <div class="flex items-start justify-between gap-2 mb-1.5">
                                <h3 class="font-bold text-foreground text-[15px] leading-snug line-clamp-1">{{ $package->title }}</h3>
                                @if($hasAccess)
                                    <span class="shrink-0 bg-success-100 text-success-600 text-[9px] px-2 py-0.5 rounded-full font-bold">✓ Dimiliki</span>
                                @endif
                            </div>

                            {{-- Tags --}}
                            <div class="flex flex-wrap gap-1 mb-2">
                                @if($package->kelas)
                                    <span class="text-[10px] bg-cyan-50 text-cyan-600 font-semibold px-2 py-0.5 rounded-full">🏫 {{ $package->kelas }}</span>
                                @endif
                                @if($package->jenjang)
                                    <span class="text-[10px] bg-foreground/5 text-foreground font-semibold px-2 py-0.5 rounded-full">🎓 {{ $package->jenjang }}</span>
                                @endif
                            </div>

                            <p class="text-muted-foreground text-xs line-clamp-2 mb-3 leading-relaxed">{{ $package->description }}</p>

                            {{-- Stats --}}
                            <div class="flex items-center gap-3 text-[11px] text-muted-foreground mb-3">
                                <span class="inline-flex items-center gap-1">📋 {{ $totalCards }} Card</span>
                                <span class="inline-flex items-center gap-1">❓ {{ $totalQuestions }} Soal</span>
                                @if($videoCount > 0)
                                    <span class="inline-flex items-center gap-1">🎬 {{ $videoCount }} Video</span>
                                @endif
                            </div>

                            {{-- Practice --}}
                            <div class="flex items-center gap-2 text-[11px] text-muted-foreground mb-3">
                                <span>⏱️ {{ $package->time_limit_minutes > 0 ? $package->time_limit_minutes . 'm' : 'Bebas' }}</span>
                                <span class="text-border">|</span>
                                <span>{{ $package->membership_duration_label }}</span>
                            </div>

                            {{-- Price & CTA --}}
                            <div class="mt-auto pt-3 border-t border-border">
                                <div class="flex items-center justify-between mb-3">
                                    @if($hasAccess)
                                        <span class="text-success-500 font-bold text-sm">✅ Sudah Dimiliki</span>
                                    @elseif($package->is_pay_what_you_want)
                                        <div>
                                            <span class="text-sm font-bold text-success-500">💝 Seikhlasnya</span>
                                            <div class="text-[10px] text-muted-foreground">Min. Rp {{ number_format($package->min_pay_amount ?? 0, 0, ',', '.') }}</div>
                                        </div>
                                    @elseif($package->hasDiscount())
                                        <div>
                                            <span class="text-lg font-bold text-danger-500">Rp {{ number_format($package->final_price, 0, ',', '.') }}</span>
                                            <div class="text-[10px] text-muted-foreground line-through">Rp {{ number_format($package->price, 0, ',', '.') }}</div>
                                        </div>
                                    @else
                                        <span class="text-lg font-bold text-foreground">Rp {{ number_format($package->price, 0, ',', '.') }}</span>
                                    @endif
                                </div>

                                @auth
                                    @if($hasAccess)
                                        <a href="{{ route('packages.show', $package->id) }}" class="block w-full text-center bg-success-500 text-white py-2.5 rounded-md font-semibold text-sm hover:bg-success-600 hover:shadow-lg transition-all">📖 Mulai Belajar</a>
                                    @else
                                        <a href="{{ route('packages.show', $package->id) }}" class="block w-full text-center bg-foreground text-white py-2.5 rounded-md font-semibold text-sm hover:bg-foreground hover:shadow-lg transition-all">🛒 Beli Sekarang</a>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" class="block w-full text-center bg-foreground text-white py-2.5 rounded-md font-semibold text-sm hover:bg-foreground hover:shadow-lg transition-all">🔑 Masuk untuk Beli</a>
                                @endauth
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-16 card-modern">
                        <div class="text-6xl mb-4">📭</div>
                        <h3 class="text-xl font-bold text-muted-foreground">Belum Ada Paket</h3>
                        <p class="text-muted-foreground mt-2 text-sm">Paket belajar akan segera tersedia</p>
                    </div>
                @endforelse
            </div>

            @if($packages->hasPages())
                <div class="mt-10">{{ $packages->links() }}</div>
            @endif
        </div>
    </section>

    <!-- ==================== FEATURES ==================== -->
    <section class="py-16 md:py-20 lg:py-24 bg-card" id="features">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10 md:mb-14">
                <span class="section-badge bg-cyan-50 text-cyan-600 mb-3">✨ Keunggulan</span>
                <h2 class="text-2xl md:text-3xl lg:text-4xl font-bold text-foreground font-display">Mengapa KPM?</h2>
                <p class="text-muted-foreground mt-2 text-sm md:text-base max-w-lg mx-auto">Fitur unggulan yang dirancang untuk membantu belajar lebih efektif</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 md:gap-6">
                <div class="feature-card text-center" style="--accent: linear-gradient(90deg, #00a2e9, #3b82f6)">
                    <div class="w-14 h-14 mx-auto mb-4 rounded-lg bg-cyan-50 flex items-center justify-center text-3xl">📚</div>
                    <h3 class="font-bold text-foreground text-lg mb-2">Soal Lengkap & Terbaru</h3>
                    <p class="text-muted-foreground text-sm leading-relaxed">Ribuan soal dengan berbagai tingkat kesulitan sesuai kurikulum terbaru dan standar ujian nasional</p>
                </div>
                <div class="feature-card text-center" style="--accent: linear-gradient(90deg, #10b981, #059669)">
                    <div class="w-14 h-14 mx-auto mb-4 rounded-lg bg-success-50 flex items-center justify-center text-3xl">💡</div>
                    <h3 class="font-bold text-foreground text-lg mb-2">Pembahasan Detail</h3>
                    <p class="text-muted-foreground text-sm leading-relaxed">Setiap soal dilengkapi pembahasan lengkap dan step-by-step agar mudah dipahami</p>
                </div>
                <div class="feature-card text-center" style="--accent: linear-gradient(90deg, #FCC626, #f59e0b)">
                    <div class="w-14 h-14 mx-auto mb-4 rounded-lg bg-gold-50 flex items-center justify-center text-3xl">📱</div>
                    <h3 class="font-bold text-foreground text-lg mb-2">Akses Fleksibel</h3>
                    <p class="text-muted-foreground text-sm leading-relaxed">Belajar kapan saja, di mana saja dari HP, tablet, atau laptop dengan tampilan responsif</p>
                </div>
            </div>

            <!-- Extra Features Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-8 md:mt-10">
                <div class="bg-muted rounded-lg p-4 text-center border border-border hover:border-cyan-200 transition">
                    <span class="text-2xl mb-2 block">🤖</span>
                    <p class="text-xs font-semibold text-foreground">Asisten AI</p>
                    <p class="text-[11px] text-muted-foreground mt-0.5">Tanya jawab 24/7</p>
                </div>
                <div class="bg-muted rounded-lg p-4 text-center border border-border hover:border-cyan-200 transition">
                    <span class="text-2xl mb-2 block">🎬</span>
                    <p class="text-xs font-semibold text-foreground">Video Belajar</p>
                    <p class="text-[11px] text-muted-foreground mt-0.5">Pembahasan visual</p>
                </div>
                <div class="bg-muted rounded-lg p-4 text-center border border-border hover:border-cyan-200 transition">
                    <span class="text-2xl mb-2 block">📊</span>
                    <p class="text-xs font-semibold text-foreground">Statistik</p>
                    <p class="text-[11px] text-muted-foreground mt-0.5">Pantau perkembangan</p>
                </div>
                <div class="bg-muted rounded-lg p-4 text-center border border-border hover:border-cyan-200 transition">
                    <span class="text-2xl mb-2 block">🏆</span>
                    <p class="text-xs font-semibold text-foreground">Sertifikat</p>
                    <p class="text-[11px] text-muted-foreground mt-0.5">Penghargaan belajar</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== HOW IT WORKS ==================== -->
    <section class="py-16 md:py-20 lg:py-24 bg-muted">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10 md:mb-14">
                <span class="section-badge bg-violet-50 text-violet-600 mb-3">🚀 Cara Kerja</span>
                <h2 class="text-2xl md:text-3xl lg:text-4xl font-bold text-foreground font-display">Mulai dalam 4 Langkah</h2>
                <p class="text-muted-foreground mt-2 text-sm md:text-base">Proses sederhana untuk memulai perjalanan belajarmu</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8">
                <div class="text-center relative">
                    <div class="w-14 h-14 mx-auto mb-4 rounded-lg bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-blue-500/20">1</div>
                    <h4 class="font-bold text-foreground text-sm mb-1">Daftar Akun</h4>
                    <p class="text-muted-foreground text-xs leading-relaxed">Buat akun gratis dalam hitungan detik</p>
                    <div class="hidden lg:block absolute top-7 left-[60%] w-[calc(100%-40%)] h-px bg-gradient-to-r from-gray-200 to-transparent"></div>
                </div>
                <div class="text-center relative">
                    <div class="w-14 h-14 mx-auto mb-4 rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-purple-500/20">2</div>
                    <h4 class="font-bold text-foreground text-sm mb-1">Pilih Paket</h4>
                    <p class="text-muted-foreground text-xs leading-relaxed">Pilih paket sesuai jenjang dan kebutuhan</p>
                    <div class="hidden lg:block absolute top-7 left-[60%] w-[calc(100%-40%)] h-px bg-gradient-to-r from-gray-200 to-transparent"></div>
                </div>
                <div class="text-center relative">
                    <div class="w-14 h-14 mx-auto mb-4 rounded-lg bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-orange-500/20">3</div>
                    <h4 class="font-bold text-foreground text-sm mb-1">Bayar & Aktivasi</h4>
                    <p class="text-muted-foreground text-xs leading-relaxed">Bayar mudah & akses langsung aktif</p>
                    <div class="hidden lg:block absolute top-7 left-[60%] w-[calc(100%-40%)] h-px bg-gradient-to-r from-gray-200 to-transparent"></div>
                </div>
                <div class="text-center">
                    <div class="w-14 h-14 mx-auto mb-4 rounded-lg bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-green-500/20">4</div>
                    <h4 class="font-bold text-foreground text-sm mb-1">Mulai Belajar</h4>
                    <p class="text-muted-foreground text-xs leading-relaxed">Kerjakan soal & pantau perkembanganmu</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== TESTIMONIALS ==================== -->
    <section class="py-16 md:py-20 lg:py-24 bg-card" id="testimonials">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10 md:mb-14">
                <span class="section-badge bg-gold-50 text-gold-600 mb-3">💬 Testimoni</span>
                <h2 class="text-2xl md:text-3xl lg:text-4xl font-bold text-foreground font-display">Apa Kata Mereka?</h2>
                <p class="text-muted-foreground mt-2 text-sm md:text-base">Ulasan nyata dari pengguna platform kami</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-5">
                @php
                    $testimonials = [
                        ['name' => 'Ahmad Fauzi', 'initial' => 'A', 'color' => 'from-amber-400 to-amber-500', 'rating' => 5, 'text' => 'Platform ini sangat membantu saya dalam persiapan ujian. Soal-soalnya berkualitas dan pembahasan sangat detail! Nilai saya meningkat drastis.', 'time' => '2 minggu lalu'],
                        ['name' => 'Siti Rahma', 'initial' => 'S', 'color' => 'from-emerald-400 to-emerald-500', 'rating' => 5, 'text' => 'Saya suka dengan fitur latihan soalnya. Bisa diulang-ulang sampai benar-benar paham. Sangat recommended untuk semua siswa!', 'time' => '1 bulan lalu'],
                        ['name' => 'Budi Santoso', 'initial' => 'B', 'color' => 'from-cyan-400 to-blue-500', 'rating' => 4, 'text' => 'Akses mudah, tampilan modern, dan soal-soalnya update. Nilai saya meningkat signifikan setelah menggunakan platform ini.', 'time' => '2 bulan lalu'],
                        ['name' => 'Dewi Lestari', 'initial' => 'D', 'color' => 'from-violet-400 to-purple-500', 'rating' => 5, 'text' => 'Pembahasan soal sangat jelas dan mudah dipahami. Cocok untuk belajar mandiri sebelum ujian. Terima kasih KPM!', 'time' => '3 bulan lalu'],
                        ['name' => 'Rizky Pratama', 'initial' => 'R', 'color' => 'from-pink-400 to-rose-500', 'rating' => 4, 'text' => 'Harga terjangkau dengan kualitas konten yang luar biasa. Sudah berlangganan 3 bulan dan puas sekali dengan hasilnya!', 'time' => '4 bulan lalu'],
                        ['name' => 'Maya Sari', 'initial' => 'M', 'color' => 'from-orange-400 to-red-500', 'rating' => 5, 'text' => 'Fitur statistik belajar sangat membantu saya memantau perkembangan. Interface-nya juga sangat intuitif dan mudah digunakan.', 'time' => '5 bulan lalu'],
                    ];
                @endphp

                @foreach($testimonials as $i => $t)
                    <div class="testi-card flex flex-col">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="testi-avatar bg-gradient-to-br {{ $t['color'] }}">{{ $t['initial'] }}</div>
                            <div>
                                <p class="font-bold text-foreground text-sm">{{ $t['name'] }}</p>
                                <div class="flex gap-0.5 text-xs">
                                    @for($s = 1; $s <= 5; $s++)
                                        <span class="{{ $s <= $t['rating'] ? 'text-gold-400' : 'text-border' }}">★</span>
                                    @endfor
                                </div>
                            </div>
                        </div>
                        <div class="testi-quote flex-1">
                            <p class="text-muted-foreground text-sm leading-relaxed">{{ $t['text'] }}</p>
                        </div>
                        <p class="text-[11px] text-muted-foreground mt-3">{{ $t['time'] }}</p>
                    </div>
                @endforeach
            </div>

            <!-- Rating Summary -->
            <div class="mt-10 md:mt-14 card-modern p-6 md:p-8 max-w-2xl mx-auto text-center">
                <div class="flex flex-col sm:flex-row items-center justify-center gap-6 sm:gap-10">
                    <div>
                        <div class="text-4xl font-extrabold text-foreground">4.8</div>
                        <div class="flex justify-center text-sm gap-0.5 mt-1">
                            @for($s = 1; $s <= 5; $s++)<span class="text-gold-400">★</span>@endfor
                        </div>
                        <p class="text-xs text-muted-foreground mt-1">Dari 120+ ulasan</p>
                    </div>
                    <div class="hidden sm:block w-px h-12 bg-muted"></div>
                    <div>
                        <div class="text-3xl font-extrabold text-success-500">98%</div>
                        <p class="text-sm text-muted-foreground">Kepuasan Pengguna</p>
                    </div>
                    <div class="hidden sm:block w-px h-12 bg-muted"></div>
                    <div>
                        <div class="text-3xl">🏆</div>
                        <p class="text-sm text-muted-foreground">Platform Terbaik 2025</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== CTA ==================== -->
    <section class="cta-section py-16 md:py-20 lg:py-28">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <div class="inline-flex items-center gap-2 bg-card/[.07] border border-white/[.1] rounded-full px-4 py-1.5 mb-6 backdrop-blur-sm">
                <span class="text-white/70 text-sm font-medium">🎯 Mulai Sekarang</span>
            </div>
            <h2 class="text-2xl md:text-3xl lg:text-4xl font-bold text-white mb-4 font-display">Siap Meningkatkan Kemampuanmu?</h2>
            <p class="text-white/50 text-base md:text-lg mb-8 md:mb-10 max-w-lg mx-auto leading-relaxed">Bergabunglah sekarang dan dapatkan akses ke ribuan soal berkualitas dengan pembahasan lengkap</p>
            @auth
                <a href="{{ Auth::user()->role === 'admin' ? route('admin.dashboard') : route('packages.index') }}" class="btn-gold text-base md:text-lg">
                    Mulai Belajar Sekarang
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </a>
            @else
                <a href="{{ route('register') }}" class="btn-gold text-base md:text-lg">
                    Daftar Gratis Sekarang
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                </a>
            @endauth
        </div>
    </section>

    <!-- ==================== FOOTER ==================== -->
    <footer class="bg-[#0a0e27] text-white pt-12 md:pt-16 pb-6 md:pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 md:gap-10">
                <div>
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-9 h-9 rounded-md bg-gradient-to-br from-yellow-400 to-amber-500 flex items-center justify-center shadow-lg shadow-amber-500/20">
                            <span class="text-foreground font-extrabold text-sm font-display">K</span>
                        </div>
                        <div>
                            <span class="text-lg font-extrabold font-display">KPM</span>
                            <span class="text-[10px] block -mt-0.5 text-gold-400 font-medium">Belajar Online</span>
                        </div>
                    </div>
                    <p class="text-white/40 text-sm leading-relaxed">Platform belajar online terpercaya untuk mendukung pembelajaran dan persiapan ujian.</p>
                </div>
                <div>
                    <h4 class="font-semibold text-white mb-4 text-sm">Menu</h4>
                    <ul class="space-y-2.5 text-white/40 text-sm">
                        <li><a href="{{ url('/#packages') }}" class="hover:text-white transition">Paket Belajar</a></li>
                        <li><a href="{{ url('/#features') }}" class="hover:text-white transition">Keunggulan</a></li>
                        <li><a href="{{ url('/#testimonials') }}" class="hover:text-white transition">Testimoni</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-white mb-4 text-sm">Bantuan</h4>
                    <ul class="space-y-2.5 text-white/40 text-sm">
                        <li><a href="{{ route('pages.features') }}" class="hover:text-white transition">Fitur Unggulan</a></li>
                        <li><a href="{{ route('pages.guide') }}" class="hover:text-white transition">Panduan</a></li>
                        <li><a href="{{ route('pages.faq') }}" class="hover:text-white transition">FAQ</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-semibold text-white mb-4 text-sm">Kontak</h4>
                    <ul class="space-y-2.5 text-white/40 text-sm">
                        <li class="flex items-center gap-2">📧 info@pkalitbang.id</li>
                        <li class="flex items-center gap-2">📱 +62 812-3456-7890</li>
                        <li class="flex items-center gap-2">🏢 Bogor, Indonesia</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-white/5 mt-8 pt-6 text-center text-white/20 text-xs">
                &copy; {{ date('Y') }} KPM Belajar Online. Hak cipta dilindungi.
            </div>
        </div>
    </footer>

    <!-- ==================== AI CHAT ==================== -->
    <div class="fixed bottom-5 right-5 z-50 flex flex-col items-end gap-3" id="floatingChat">
        <div class="w-[340px] sm:w-[380px] max-w-[calc(100vw-40px)] max-h-[520px] bg-card rounded-lg shadow-2xl overflow-hidden hidden flex-col border border-border" id="chatWindow">
            <div class="bg-gradient-to-r from-[#161758] to-[#00a2e9] text-white p-4 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-md bg-card/20 backdrop-blur flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-semibold text-sm">Asisten AI KPM</h3>
                        <p class="text-[10px] text-white/70">Online · Siap membantu</p>
                    </div>
                </div>
                <button onclick="toggleChat()" class="text-white/70 hover:text-white w-8 h-8 rounded-lg hover:bg-card/10 flex items-center justify-center transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-4 space-y-3 min-h-[120px] max-h-[320px]" id="chatBody">
                <div class="flex items-start gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-[#161758] to-[#00a2e9] text-white flex items-center justify-center shrink-0 text-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                    </div>
                    <div class="bg-cyan-50 text-foreground rounded-lg rounded-tl-md p-3 max-w-[85%] text-sm leading-relaxed">
                        Halo! 👋 Saya asisten AI <strong>KPM Belajar Online</strong>. Ada yang bisa saya bantu?
                        <span class="text-[10px] text-muted-foreground block mt-1.5">Sekarang</span>
                    </div>
                </div>
                <div id="chatMessages"></div>
            </div>
            <div class="px-3 pb-2 flex flex-wrap gap-1.5" id="quickActions">
                <button onclick="quickAsk('Apa itu KPM Belajar Online?')" class="text-[11px] px-2.5 py-1.5 rounded-full border border-cyan-200 text-cyan-600 hover:bg-cyan-50 transition font-medium">Tentang KPM</button>
                <button onclick="quickAsk('Cara membeli paket membership?')" class="text-[11px] px-2.5 py-1.5 rounded-full border border-cyan-200 text-cyan-600 hover:bg-cyan-50 transition font-medium">Beli Paket</button>
                <button onclick="quickAsk('Cara melatih soal?')" class="text-[11px] px-2.5 py-1.5 rounded-full border border-cyan-200 text-cyan-600 hover:bg-cyan-50 transition font-medium">Latihan Soal</button>
            </div>
            <div class="p-3 border-t border-border bg-card flex-shrink-0">
                <div class="flex gap-2 items-end">
                    <textarea id="chatInput" rows="1" placeholder="Ketik pertanyaan Anda..."
                        oninput="autoResize(this)"
                        class="flex-1 border border-border rounded-md px-3.5 py-2.5 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-cyan-400/30 focus:border-cyan-400 transition min-h-[40px] max-h-[80px] leading-relaxed bg-muted/50"></textarea>
                    <button onclick="sendMessage()" id="chatSendBtn" class="w-10 h-10 rounded-md bg-gradient-to-r from-[#161758] to-[#00a2e9] text-white flex items-center justify-center hover:shadow-lg transition shrink-0 disabled:opacity-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                    </button>
                </div>
                <div class="flex items-center justify-between mt-2">
                    <button onclick="escalateToAdmin()" class="text-[11px] text-muted-foreground hover:text-cyan-500 transition flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0"/></svg>
                        Hubungi Admin
                    </button>
                    <span class="text-[10px] text-muted-foreground">Powered by AI</span>
                </div>
            </div>
        </div>

        <button onclick="toggleChat()" id="chatToggle" class="w-14 h-14 rounded-full bg-gradient-to-r from-[#161758] to-[#00a2e9] text-white shadow-xl shadow-cyan-500/25 hover:shadow-2xl hover:scale-110 transition-all duration-300 flex items-center justify-center relative group" style="animation: pulse-glow 3s ease-in-out infinite">
            <svg class="w-6 h-6 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
            <span class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-success-400 rounded-full border-2 border-white animate-pulse"></span>
        </button>
    </div>

    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const nav = document.getElementById('mainNav');
            if (nav) nav.classList.toggle('scrolled', window.scrollY > 50);
        });

        // Mobile Nav
        function toggleMobileNav() {
            const nav = document.getElementById('mobileNav');
            const o = document.getElementById('navOpen');
            const c = document.getElementById('navClose');
            if (nav.classList.contains('open')) {
                nav.classList.remove('open'); o.classList.remove('hidden'); c.classList.add('hidden');
            } else {
                nav.classList.add('open'); o.classList.add('hidden'); c.classList.remove('hidden');
            }
        }
        function closeMobileNav() {
            const nav = document.getElementById('mobileNav');
            nav.classList.remove('open');
            document.getElementById('navOpen')?.classList.remove('hidden');
            document.getElementById('navClose')?.classList.add('hidden');
        }
        document.addEventListener('click', function(e) {
            const nav = document.getElementById('mobileNav');
            const btn = document.getElementById('mobileMenuBtn');
            if (nav && !nav.contains(e.target) && nav.classList.contains('open')) closeMobileNav();
        });

        // AI Chat
        let chatOpen = false, chatSending = false;
        function toggleChat() {
            chatOpen = !chatOpen;
            const w = document.getElementById('chatWindow');
            if (w) { w.classList.toggle('hidden', !chatOpen); if (chatOpen) { w.classList.add('flex'); document.getElementById('chatInput')?.focus(); } else { w.classList.remove('flex'); } }
        }
        function autoResize(el) { if (el) { el.style.height = 'auto'; el.style.height = Math.min(el.scrollHeight, 80) + 'px'; } }
        function scrollToBottom() { const b = document.getElementById('chatBody'); if (b) b.scrollTop = b.scrollHeight; }
        function escapeHtml(t) { if (!t) return ''; const d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
        function formatAIResponse(t) { let h = escapeHtml(t); h = h.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>'); return h; }

        function addMessage(text, isUser) {
            const c = document.getElementById('chatMessages'); if (!c) return;
            const now = new Date().toLocaleTimeString('id-ID', {hour:'2-digit',minute:'2-digit'});
            const d = document.createElement('div');
            if (isUser) { d.className = 'flex items-start gap-2.5 justify-end'; d.innerHTML = `<div class="bg-[#161758] text-white rounded-2xl rounded-tr-md p-3 max-w-[85%] text-sm leading-relaxed">${escapeHtml(text)}<span class="text-[10px] opacity-50 block mt-1.5 text-right">${now}</span></div>`; }
            else { d.className = 'flex items-start gap-2.5'; d.innerHTML = `<div class="w-7 h-7 rounded-lg bg-gradient-to-br from-[#161758] to-[#00a2e9] text-white flex items-center justify-center shrink-0 text-xs"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg></div><div class="bg-cyan-50 text-foreground rounded-2xl rounded-tl-md p-3 max-w-[85%] text-sm leading-relaxed">${formatAIResponse(text)}<span class="text-[10px] text-gray-400 block mt-1.5">${now}</span></div>`; }
            c.appendChild(d); scrollToBottom();
        }

        function showTyping() {
            const c = document.getElementById('chatMessages'); if (!c) return;
            const d = document.createElement('div'); d.id = 'typingIndicator'; d.className = 'flex items-start gap-2.5';
            d.innerHTML = `<div class="w-7 h-7 rounded-lg bg-gradient-to-br from-[#161758] to-[#00a2e9] text-white flex items-center justify-center shrink-0 text-xs"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg></div><div class="bg-cyan-50 rounded-2xl rounded-tl-md px-4 py-3"><div class="flex gap-1"><span class="typing-dot w-2 h-2 bg-cyan-400/60 rounded-full"></span><span class="typing-dot w-2 h-2 bg-cyan-400/60 rounded-full" style="animation-delay:.15s"></span><span class="typing-dot w-2 h-2 bg-cyan-400/60 rounded-full" style="animation-delay:.3s"></span></div></div>`;
            c.appendChild(d); scrollToBottom();
        }
        function hideTyping() { document.getElementById('typingIndicator')?.remove(); }
        function quickAsk(q) { document.getElementById('chatInput').value = q; sendMessage(); }

        function sendMessage() {
            if (chatSending) return;
            const input = document.getElementById('chatInput');
            const message = input?.value.trim(); if (!message) return;
            chatSending = true;
            const btn = document.getElementById('chatSendBtn'); if (btn) btn.disabled = true;
            addMessage(message, true); input.value = ''; input.style.height = 'auto';
            document.getElementById('quickActions')?.classList.add('hidden'); showTyping();
            fetch('{{ route("chat.send") }}', { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}, body:JSON.stringify({message}) })
            .then(r => r.json()).then(data => { hideTyping(); addMessage(data.success ? data.data.message : 'Maaf, terjadi kesalahan. Silakan coba lagi.', false); })
            .catch(() => { hideTyping(); addMessage('Koneksi bermasalah. Silakan coba lagi.', false); })
            .finally(() => { chatSending = false; if (btn) btn.disabled = false; input?.focus(); });
        }

        function escalateToAdmin() {
            if (!confirm('Hubungi admin untuk bantuan lebih lanjut?')) return;
            fetch('{{ route("support.submit") }}', { method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content}, body:JSON.stringify({question:'Meminta bantuan langsung dari admin.'}) })
            .then(r => r.json()).then(d => addMessage(d.success ? '✅ Pesan Anda telah diteruskan ke admin.' : 'Gagal menghubungi admin.', false))
            .catch(() => addMessage('Gagal menghubungi admin.', false));
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('chatInput')?.addEventListener('keydown', function(e) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); } });
        });
    </script>
</body>
</html>
