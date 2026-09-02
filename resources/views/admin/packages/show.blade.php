@extends('layouts.admin')

@section('title', 'Detail Paket - ' . $package->title)

@section('header-title', 'Detail Paket')
@section('header-sub', $package->title)

@section('content')
<div class="max-w-7xl mx-auto space-y-5 sm:space-y-6">

    {{-- Hero Header Card --}}
    <div class="admin-card overflow-hidden">
        <div class="relative">
            <div class="h-28 sm:h-36 md:h-44 bg-gradient-to-br from-navy via-navy-light to-accent-400 relative">
                <div class="absolute inset-0 opacity-10">
                    <svg class="w-full h-full" viewBox="0 0 400 200" fill="none">
                        <circle cx="350" cy="50" r="120" fill="white" opacity="0.1"/>
                        <circle cx="50" cy="180" r="80" fill="white" opacity="0.08"/>
                        <circle cx="200" cy="30" r="60" fill="white" opacity="0.05"/>
                    </svg>
                </div>
                <div class="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-white to-transparent"></div>
            </div>

            <div class="relative px-4 sm:px-6 lg:px-8 pb-5 sm:pb-6">
                <div class="flex flex-col sm:flex-row items-start gap-4 sm:gap-5 -mt-12 sm:-mt-14">
                    {{-- Thumbnail --}}
                    <div class="relative shrink-0">
                        @if($package->thumbnail)
                            <img src="{{ asset('storage/' . $package->thumbnail) }}"
                                 alt="{{ $package->title }}"
                                 class="w-20 h-20 sm:w-24 sm:h-24 lg:w-28 lg:h-28 rounded-lg object-cover border-4 border-card shadow-card-lg">
                        @else
                            <div class="w-20 h-20 sm:w-24 sm:h-24 lg:w-28 lg:h-28 rounded-lg bg-gradient-to-br from-gold-400 to-[#f59e0b] border-4 border-card shadow-card-lg flex items-center justify-center">
                                <svg class="w-9 h-9 sm:w-10 sm:h-10 lg:w-12 lg:h-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                                </svg>
                            </div>
                        @endif
                        <div class="absolute -bottom-1 -right-1">
                            @if($package->is_active)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-success-500 text-white shadow-sm">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-muted-foreground text-white shadow-sm">
                                    Nonaktif
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Title & Info --}}
                    <div class="flex-1 min-w-0 pt-2 sm:pt-4">
                        <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-foreground truncate">{{ $package->title }}</h1>
                        <p class="text-sm text-muted-foreground mt-1 line-clamp-2">{{ $package->description }}</p>
                        <div class="flex flex-wrap items-center gap-2 mt-3">
                            @if($package->kelas)
                                <span class="badge badge-info">{{ $package->kelas }}</span>
                            @endif
                            @if($package->jenjang)
                                <span class="badge badge-neutral">{{ $package->jenjang }}</span>
                            @endif
                            @if($package->is_pay_what_you_want)
                                <span class="badge badge-info">Bayar Seikhlasnya</span>
                            @elseif($package->hasDiscount())
                                <span class="badge badge-warning">Diskon {{ $package->discount_percent }}%</span>
                            @endif
                            <span class="badge badge-neutral">{{ $totalCards }} Card</span>
                            <span class="badge badge-neutral">{{ $totalQuestions }} Soal</span>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex items-center gap-2 pt-2 sm:pt-4 shrink-0 w-full sm:w-auto">
                        <a href="{{ route('admin.packages.edit.informasi', $package) }}"
                           class="btn-primary text-sm !px-4 !py-2 flex-1 sm:flex-none w-full sm:w-auto justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                            </svg>
                            Edit
                        </a>
                        <a href="{{ route('packages.show', $package) }}"
                           target="_blank"
                           class="btn-secondary text-sm !px-4 !py-2 flex-1 sm:flex-none w-full sm:w-auto justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                            </svg>
                            Lihat
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
        {{-- Total Card --}}
        <div class="admin-card stagger-item group p-4 sm:p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-md bg-primary/10 text-primary flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xl sm:text-2xl font-bold text-foreground">{{ $totalCards }}</p>
                    <p class="text-[10px] sm:text-[11px] text-muted-foreground font-medium">Total Card</p>
                </div>
            </div>
        </div>

        {{-- Total Soal --}}
        <div class="admin-card stagger-item group p-4 sm:p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-md bg-navy/10 text-navy flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xl sm:text-2xl font-bold text-foreground">{{ $totalQuestions }}</p>
                    <p class="text-[10px] sm:text-[11px] text-muted-foreground font-medium">Total Soal</p>
                </div>
            </div>
        </div>

        {{-- Total Pesanan --}}
        <div class="admin-card stagger-item group p-4 sm:p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-md bg-success-500/10 text-success-500 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xl sm:text-2xl font-bold text-foreground">{{ $totalOrders }}</p>
                    <p class="text-[10px] sm:text-[11px] text-muted-foreground font-medium">Pesanan</p>
                </div>
            </div>
        </div>

        {{-- Berhasil --}}
        <div class="admin-card stagger-item group p-4 sm:p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-md bg-gold-400/10 text-gold-400 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xl sm:text-2xl font-bold text-foreground">{{ $paidOrders }}</p>
                    <p class="text-[10px] sm:text-[11px] text-muted-foreground font-medium">Berhasil</p>
                </div>
            </div>
        </div>

        {{-- Praktek --}}
        <div class="admin-card stagger-item group col-span-2 sm:col-span-3 lg:col-span-1 p-4 sm:p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-md bg-purple-500/10 text-purple-500 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17l-5.1-5.1m0 0L11.42 4.97m-5.1 5.1H21M3 3v18"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xl sm:text-2xl font-bold text-foreground">{{ $totalPracticeSessions }}</p>
                    <p class="text-[10px] sm:text-[11px] text-muted-foreground font-medium">Praktek</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 sm:gap-6">

        {{-- Left Column: Detail Info --}}
        <div class="lg:col-span-1 space-y-5 sm:space-y-6">

            {{-- Informasi Harga --}}
            <div class="admin-card stagger-item p-4 sm:p-5">
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-7 h-7 rounded-md bg-primary/10 flex items-center justify-center">
                        <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-foreground">Informasi Harga</h3>
                </div>

                <div class="space-y-0">
                    @if($package->is_pay_what_you_want)
                        <div class="flex items-center justify-between py-2.5 border-b border-border last:border-0">
                            <span class="text-xs sm:text-sm text-muted-foreground">Tipe Harga</span>
                            <span class="badge badge-info">Bayar Seikhlasnya</span>
                        </div>
                        @if($package->min_pay_amount > 0)
                            <div class="flex items-center justify-between py-2.5 border-b border-border last:border-0">
                                <span class="text-xs sm:text-sm text-muted-foreground">Minimum Bayar</span>
                                <span class="text-xs sm:text-sm font-semibold text-foreground">Rp {{ number_format($package->min_pay_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    @else
                        @if($package->hasDiscount())
                            <div class="flex items-center justify-between py-2.5 border-b border-border last:border-0">
                                <span class="text-xs sm:text-sm text-muted-foreground">Harga Normal</span>
                                <span class="text-xs sm:text-sm text-muted-foreground line-through">Rp {{ number_format($package->price, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between py-2.5 border-b border-border last:border-0">
                                <span class="text-xs sm:text-sm text-muted-foreground">Harga Diskon</span>
                                <span class="text-xs sm:text-sm font-bold text-success-500">Rp {{ number_format($package->discount_price, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between py-2.5 last:border-0">
                                <span class="text-xs sm:text-sm text-muted-foreground">Diskon</span>
                                <span class="badge badge-warning">{{ $package->discount_percent }}%</span>
                            </div>
                        @else
                            <div class="flex items-center justify-between py-2.5 last:border-0">
                                <span class="text-xs sm:text-sm text-muted-foreground">Harga</span>
                                <span class="text-sm lg:text-base font-bold text-foreground">Rp {{ number_format($package->price, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Informasi Membership --}}
            <div class="admin-card stagger-item p-4 sm:p-5">
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-7 h-7 rounded-md bg-navy/10 flex items-center justify-center">
                        <svg class="w-4 h-4 text-navy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-foreground">Informasi Membership</h3>
                </div>

                <div class="space-y-0">
                    @if($package->kelas)
                        <div class="flex items-center justify-between py-2.5 border-b border-border">
                            <span class="text-xs sm:text-sm text-muted-foreground">Kelas</span>
                            <span class="text-xs sm:text-sm font-semibold text-foreground">{{ $package->kelas }}</span>
                        </div>
                    @endif
                    @if($package->jenjang)
                        <div class="flex items-center justify-between py-2.5 border-b border-border">
                            <span class="text-xs sm:text-sm text-muted-foreground">Jenjang</span>
                            <span class="text-xs sm:text-sm font-semibold text-foreground">{{ $package->jenjang }}</span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between py-2.5 border-b border-border last:border-0">
                        <span class="text-xs sm:text-sm text-muted-foreground">Durasi</span>
                        <span class="text-xs sm:text-sm font-semibold text-foreground">{{ $package->membership_duration_label }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2.5 border-b border-border">
                        <span class="text-xs sm:text-sm text-muted-foreground">Batas Waktu</span>
                        <span class="text-xs sm:text-sm font-semibold text-foreground">{{ $package->time_limit_label }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2.5 border-b border-border">
                        <span class="text-xs sm:text-sm text-muted-foreground">Pembahasan</span>
                        @if($package->hide_explanation)
                            <span class="badge badge-warning text-[10px]">Disembunyikan</span>
                        @else
                            <span class="badge badge-success text-[10px]">Ditampilkan</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between py-2.5 last:border-0">
                        <span class="text-xs sm:text-sm text-muted-foreground">Status</span>
                        @if($package->is_active)
                            <span class="badge badge-success">Aktif</span>
                        @else
                            <span class="badge badge-neutral">Nonaktif</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Pendapatan --}}
            <div class="admin-card stagger-item p-4 sm:p-5">
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-7 h-7 rounded-md bg-success-500/10 flex items-center justify-center">
                        <svg class="w-4 h-4 text-success-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-foreground">Pendapatan</h3>
                </div>

                <div class="text-center py-3">
                    <p class="text-xl sm:text-2xl font-bold text-success-500">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                    <p class="text-[10px] sm:text-[11px] text-muted-foreground mt-1">Total Pendapatan</p>
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="lg:col-span-2 space-y-5 sm:space-y-6">

            {{-- Cards List --}}
            <div class="admin-card stagger-item p-4 sm:p-5">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-md bg-primary/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-foreground">Daftar Card</h3>
                    </div>
                    <a href="{{ route('admin.packages.edit.cards', $package) }}" class="text-xs font-semibold text-primary hover:text-navy transition-colors">
                        Lihat Semua &rarr;
                    </a>
                </div>

                @if(empty($cards))
                    <div class="empty-state py-10">
                        <div class="empty-state-icon">
                            <svg class="w-8 h-8 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                            </svg>
                        </div>
                        <p class="empty-state-text">Belum ada card</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($cards as $cardIndex => $card)
                            @php
                                $cardQuestions = $questionsByCard[$card['id']] ?? collect();
                                $cardQuestionCount = $cardQuestions->count();
                            @endphp
                            <div class="group flex items-center gap-4 p-4 rounded-md border border-border hover:border-primary/30 hover:shadow-sm transition-all duration-200 bg-card">
                                <div class="w-10 h-10 rounded-md bg-gradient-to-br from-navy to-navy-light text-white flex items-center justify-center text-sm font-bold shadow-sm flex-shrink-0">
                                    {{ $cardIndex + 1 }}
                                </div>

                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-semibold text-foreground truncate">{{ $card['title'] }}</h4>
                                    <p class="text-xs text-muted-foreground truncate mt-0.5">{{ $card['description'] ?? 'Tidak ada deskripsi' }}</p>
                                </div>

                                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-md bg-primary/10 text-primary flex-shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/>
                                    </svg>
                                    <span class="text-xs font-bold">{{ $cardQuestionCount }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Quick Actions --}}
            <div class="admin-card stagger-item p-4 sm:p-5">
                <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-7 h-7 rounded-md bg-gold-400/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-gold-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/>
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-foreground">Aksi Cepat</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <a href="{{ route('admin.packages.edit.informasi', $package) }}"
                       class="flex items-center gap-3 p-3 rounded-md border border-border hover:border-primary/40 hover:bg-primary/5 transition-all duration-200 group">
                        <div class="w-10 h-10 rounded-md bg-primary/10 flex items-center justify-center group-hover:bg-primary/20 transition-colors">
                            <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-foreground">Edit Informasi</p>
                            <p class="text-[11px] text-muted-foreground">Ubah detail paket</p>
                        </div>
                    </a>

                    <a href="{{ route('admin.packages.edit.cards', $package) }}"
                       class="flex items-center gap-3 p-3 rounded-md border border-border hover:border-navy/40 hover:bg-navy/5 transition-all duration-200 group">
                        <div class="w-10 h-10 rounded-md bg-navy/10 flex items-center justify-center group-hover:bg-navy/20 transition-colors">
                            <svg class="w-5 h-5 text-navy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-foreground">Kelola Card</p>
                            <p class="text-[11px] text-muted-foreground">Tambah/hapus card</p>
                        </div>
                    </a>

                    <a href="{{ route('admin.packages.edit.questions', $package) }}"
                       class="flex items-center gap-3 p-3 rounded-md border border-border hover:border-success-500/40 hover:bg-success-500/5 transition-all duration-200 group">
                        <div class="w-10 h-10 rounded-md bg-success-500/10 flex items-center justify-center group-hover:bg-success-500/20 transition-colors">
                            <svg class="w-5 h-5 text-success-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-foreground">Kelola Soal</p>
                            <p class="text-[11px] text-muted-foreground">Tambah/edit soal</p>
                        </div>
                    </a>

                    <a href="{{ route('packages.show', $package) }}"
                       target="_blank"
                       class="flex items-center gap-3 p-3 rounded-md border border-border hover:border-gold-400/40 hover:bg-gold-400/5 transition-all duration-200 group">
                        <div class="w-10 h-10 rounded-md bg-gold-400/10 flex items-center justify-center group-hover:bg-gold-400/20 transition-colors">
                            <svg class="w-5 h-5 text-gold-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-foreground">Lihat Publik</p>
                            <p class="text-[11px] text-muted-foreground">Preview halaman user</p>
                        </div>
                    </a>
                </div>
            </div>

            {{-- Danger Zone --}}
            <div class="admin-card stagger-item border-danger-200 p-4 sm:p-5">
                <h3 class="text-sm font-bold text-danger-600 flex items-center gap-2 mb-3">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                    </svg>
                    Zona Bahaya
                </h3>
                <p class="text-xs text-muted-foreground mb-3">Menghapus paket akan menghapus semua data terkait secara permanen.</p>
                <a href="{{ route('admin.packages.confirm-delete', $package) }}" class="btn-danger text-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                    </svg>
                    Hapus Paket
                </a>
            </div>
        </div>
    </div>

    {{-- Back Link --}}
    <div class="flex justify-start">
        <a href="{{ route('admin.packages.index') }}" class="btn-secondary text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Kembali ke Daftar Paket
        </a>
    </div>
</div>
@endsection
