@extends('layouts.admin')

@section('title', 'Video Pembahasan')
@section('header-title', 'Video Pembahasan')
@section('header-sub', 'Kelola video pembahasan soal')

@section('content')
@php
    $totalVideos = \App\Models\Video::count();
    $activeVideos = \App\Models\Video::where('is_active', true)->count();
    $totalRevenue = \App\Models\VideoOrder::where('payment_status', 'paid')->sum('total_price') ?? 0;
    $totalOrders = \App\Models\VideoOrder::where('payment_status', 'paid')->count();
@endphp

<div class="space-y-6">

    {{-- ===================== STAT CARDS ===================== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="stat-card stagger-item group">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-navy to-navy-light shadow-lg shadow-navy/20 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h1.5C5.496 19.5 6 18.996 6 18.375m-3.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-1.5A1.125 1.125 0 0118 18.375M20.625 4.5H3.375m17.25 0c.621 0 1.125.504 1.125 1.125M20.625 4.5h-1.5C18.504 4.5 18 5.004 18 5.625m3.75 0v1.5c0 .621-.504 1.125-1.125 1.125M3.375 4.5c-.621 0-1.125.504-1.125 1.125M3.375 4.5h1.5C5.496 4.5 6 5.004 6 5.625m-3.75 0v1.5c0 .621.504 1.125 1.125 1.125m0 0h1.5m-1.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m1.5-3.75C5.496 8.25 6 7.746 6 7.125v-1.5M4.875 8.25C5.496 8.25 6 8.754 6 9.375v1.5m0-5.25v5.25m0-5.25C6 5.004 6.504 4.5 7.125 4.5h9.75c.621 0 1.125.504 1.125 1.125m1.125 2.625h1.5m-1.5 0A1.125 1.125 0 0118 7.125v-1.5m1.125 2.625c-.621 0-1.125.504-1.125 1.125v1.5m2.625-2.625c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125M18 5.625v5.25M7.125 12h9.75m-9.75 0A1.125 1.125 0 016 10.875M7.125 12C6.504 12 6 12.504 6 13.125m0-2.25C6 11.496 5.496 12 4.875 12M18 10.875c0 .621-.504 1.125-1.125 1.125M18 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m-12 5.25v-5.25m0 5.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125m-12 0v-1.5c0-.621-.504-1.125-1.125-1.125M18 18.375v-5.25m0 5.25v-1.5c0-.621.504-1.125 1.125-1.125M18 13.125v1.5c0 .621.504 1.125 1.125 1.125M18 13.125c0-.621.504-1.125 1.125-1.125M6 13.125v1.5c0 .621-.504 1.125-1.125 1.125M6 13.125C6 12.504 5.496 12 4.875 12m-1.5 0h1.5m-1.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M19.125 12h1.5m0 0c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h1.5m14.25 0h1.5"/></svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Total Video</p>
                    <p class="text-2xl font-bold text-foreground">{{ $totalVideos }}</p>
                </div>
            </div>
        </div>

        <div class="stat-card stagger-item group">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-success-500 to-[#00c853] shadow-lg shadow-success-500/20 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Video Aktif</p>
                    <p class="text-2xl font-bold text-success-500">{{ $activeVideos }}</p>
                </div>
            </div>
        </div>

        <div class="stat-card stagger-item group">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-gold-400 to-[#ffd54f] shadow-lg shadow-gold-400/20 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-navy" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Pembelian</p>
                    <p class="text-2xl font-bold text-navy">{{ $totalOrders }}</p>
                </div>
            </div>
        </div>

        <div class="stat-card stagger-item group">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-accent-400 to-accent-500 shadow-lg shadow-accent-400/20 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Pendapatan</p>
                    <p class="text-sm font-bold text-navy truncate max-w-[120px]">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== TOOLBAR ===================== --}}
    <div class="admin-card bg-card rounded-lg p-3 sm:p-4 shadow-sm border border-border">
        <form method="GET" action="{{ route('admin.videos.index') }}" class="flex flex-col lg:flex-row gap-3">
            <div class="flex-1 relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul video..."
                       class="form-input w-full pl-11 pr-4 py-3 border border-border rounded-md text-sm focus:border-accent-400 focus:ring-2 focus:ring-accent-400/20 transition outline-none bg-muted/50 hover:bg-card focus:bg-card">
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <select name="status" class="form-select px-4 py-3 border border-border rounded-md text-sm focus:border-accent-400 focus:ring-2 focus:ring-accent-400/20 transition outline-none bg-muted/50 hover:bg-card focus:bg-card w-full sm:w-40 appearance-none cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                <button type="submit" class="btn-primary justify-center gap-2 !py-3 !px-6 whitespace-nowrap">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    Cari
                </button>
                @if(request('search') || request('status'))
                    <a href="{{ route('admin.videos.index') }}" class="btn-secondary justify-center !py-3 !px-6 whitespace-nowrap">Reset</a>
                @endif
                <a href="{{ route('admin.video-orders.index') }}" class="btn-secondary justify-center gap-2 !py-3 !px-6 whitespace-nowrap">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.836c-1.1 0-1.996.907-1.996 2.022v9.956c0 1.115.896 2.022 1.996 2.022h9.328c1.1 0 1.996-.907 1.996-2.022v-9.956c0-1.115-.896-2.022-1.996-2.022H8.25z"/></svg>
                    Pesanan
                </a>
                <a href="{{ route('admin.videos.create') }}" class="btn-primary justify-center gap-2 !py-3 !px-6 whitespace-nowrap">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Tambah Video
                </a>
            </div>
        </form>
    </div>

    {{-- ===================== DESKTOP TABLE ===================== --}}
    <div class="hidden md:block admin-card bg-card rounded-lg shadow-sm border border-border overflow-hidden">
        @if($videos->isEmpty())
            <div class="p-16 text-center">
                <div class="w-20 h-20 mx-auto rounded-lg bg-muted flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h1.5C5.496 19.5 6 18.996 6 18.375m-3.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-1.5A1.125 1.125 0 0118 18.375M20.625 4.5H3.375m17.25 0c.621 0 1.125.504 1.125 1.125M20.625 4.5h-1.5C18.504 4.5 18 5.004 18 5.625m3.75 0v1.5c0 .621-.504 1.125-1.125 1.125M3.375 4.5c-.621 0-1.125.504-1.125 1.125M3.375 4.5h1.5C5.496 4.5 6 5.004 6 5.625m-3.75 0v1.5c0 .621.504 1.125 1.125 1.125m0 0h1.5m-1.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m1.5-3.75C5.496 8.25 6 7.746 6 7.125v-1.5M4.875 8.25C5.496 8.25 6 8.754 6 9.375v1.5m0-5.25v5.25m0-5.25C6 5.004 6.504 4.5 7.125 4.5h9.75c.621 0 1.125.504 1.125 1.125m1.125 2.625h1.5m-1.5 0A1.125 1.125 0 0118 7.125v-1.5m1.125 2.625c-.621 0-1.125.504-1.125 1.125v1.5m2.625-2.625c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125M18 5.625v5.25M7.125 12h9.75m-9.75 0A1.125 1.125 0 016 10.875M7.125 12C6.504 12 6 12.504 6 13.125m0-2.25C6 11.496 5.496 12 4.875 12M18 10.875c0 .621-.504 1.125-1.125 1.125M18 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m-12 5.25v-5.25m0 5.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125m-12 0v-1.5c0-.621-.504-1.125-1.125-1.125M18 18.375v-5.25m0 5.25v-1.5c0-.621.504-1.125 1.125-1.125M18 13.125v1.5c0 .621.504 1.125 1.125 1.125M18 13.125c0-.621.504-1.125 1.125-1.125M6 13.125v1.5c0 .621-.504 1.125-1.125 1.125M6 13.125C6 12.504 5.496 12 4.875 12m-1.5 0h1.5m-1.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M19.125 12h1.5m0 0c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h1.5m14.25 0h1.5"/></svg>
                </div>
                <h3 class="text-2xl font-bold text-muted-foreground">Belum Ada Video</h3>
                <p class="text-muted-foreground mt-2">Mulai tambahkan video pembahasan pertama Anda</p>
                <a href="{{ route('admin.videos.create') }}" class="inline-flex items-center gap-2 mt-5 btn-primary">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Tambah Video
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gradient-to-r from-muted to-muted/50 border-b border-border">
                            <th class="px-5 py-4 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider">Video</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider">Sumber</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider">Harga</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider">Durasi</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider">Status</th>
                            <th class="px-5 py-4 text-center text-xs font-semibold text-muted-foreground uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($videos as $video)
                            <tr class="hover:bg-accent-50 transition-colors duration-200 group">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-14 h-10 rounded-md overflow-hidden bg-gradient-to-br from-navy to-navy-light flex items-center justify-center flex-shrink-0 shadow-md shadow-navy/10">
                                            @if($video->thumbnail)
                                                <img src="{{ asset('storage/' . $video->thumbnail) }}" alt="{{ $video->title }}" class="w-full h-full object-cover">
                                            @else
                                                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/></svg>
                                            @endif
                                        </div>
                                        <div class="min-w-0 max-w-[280px]">
                                            <p class="font-semibold text-navy truncate group-hover:text-navy-light transition">{{ $video->title }}</p>
                                            <p class="text-xs text-muted-foreground truncate mt-0.5">{{ $video->package->title ?? 'Tanpa Paket' }} &middot; {{ $video->created_at->translatedFormat('d M Y') }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    @if($video->video_file)
                                        <span class="badge-info inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-full">Upload File</span>
                                    @elseif($video->video_url)
                                        <a href="{{ $video->video_url }}" target="_blank" rel="noopener" class="badge-neutral inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-full hover:text-accent-400 transition-colors">Link Eksternal</a>
                                    @endif
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    @if($video->hasDiscount())
                                        <div class="flex flex-col leading-tight">
                                            <span class="text-xs text-muted-foreground line-through">Rp {{ number_format($video->price, 0, ',', '.') }}</span>
                                            <span class="font-bold text-danger-500 text-base">Rp {{ number_format($video->final_price, 0, ',', '.') }}</span>
                                            <span class="text-[10px] font-semibold badge-danger text-white px-2 py-0.5 rounded-full w-fit mt-0.5">-{{ $video->discount_label }}</span>
                                        </div>
                                    @else
                                        <span class="font-semibold text-foreground">Rp {{ number_format($video->price, 0, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-muted-foreground">
                                    <span class="badge-info inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full whitespace-nowrap">{{ $video->access_duration_days }} hari</span>
                                </td>
                                <td class="px-5 py-4">
                                    @if($video->is_active)
                                        <span class="badge-success inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-success-500 animate-pulse"></span> Aktif
                                        </span>
                                    @else
                                        <span class="badge-neutral inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-muted-foreground"></span> Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.videos.edit', $video->id) }}"
                                           class="w-9 h-9 rounded-md bg-accent-400/10 hover:bg-accent-400/20 text-accent-400 flex items-center justify-center transition-all hover:scale-110 hover:shadow-md"
                                           title="Edit">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/></svg>
                                        </a>
                                        <form action="{{ route('admin.videos.toggle', $video->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" title="{{ $video->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                                    class="w-9 h-9 rounded-md {{ $video->is_active ? 'bg-gold-50 text-gold-500 hover:bg-gold-100' : 'bg-success-50 text-success-500 hover:bg-success-100' }} flex items-center justify-center transition-all hover:scale-110 hover:shadow-md">
                                                @if($video->is_active)
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25v13.5m-7.5-13.5v13.5"/></svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/></svg>
                                                @endif
                                            </button>
                                        </form>
                                        <a href="{{ route('admin.videos.destroy', $video->id) }}"
                                           class="w-9 h-9 rounded-md bg-muted hover:bg-danger-50 text-muted-foreground hover:text-danger-500 flex items-center justify-center transition-all hover:scale-110 hover:shadow-md"
                                           title="Hapus"
                                           onclick="event.preventDefault(); if(confirm('Yakin ingin menghapus video ini?')) document.getElementById('delete-form-{{ $video->id }}').submit();">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                        </a>
                                        <form id="delete-form-{{ $video->id }}" action="{{ route('admin.videos.destroy', $video->id) }}" method="POST" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-border">
                {{ $videos->links() }}
            </div>
        @endif
    </div>

    {{-- ===================== MOBILE CARDS ===================== --}}
    <div class="space-y-4 md:hidden">
        @forelse($videos as $video)
            <div class="bg-card rounded-lg border border-border shadow-sm hover:shadow-md transition-all duration-300 p-5">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-10 rounded-md overflow-hidden bg-gradient-to-br from-navy to-navy-light flex items-center justify-center flex-shrink-0 shadow-md shadow-navy/10">
                        @if($video->thumbnail)
                            <img src="{{ asset('storage/' . $video->thumbnail) }}" alt="{{ $video->title }}" class="w-full h-full object-cover">
                        @else
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/></svg>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-navy truncate text-sm">{{ $video->title }}</p>
                        <p class="text-xs text-muted-foreground mt-0.5 line-clamp-2">{{ $video->description }}</p>
                        <div class="flex flex-wrap items-center gap-1.5 mt-2">
                            @if($video->is_active)
                                <span class="badge-success text-[10px] py-0.5 px-2">Aktif</span>
                            @else
                                <span class="badge-neutral text-[10px] py-0.5 px-2">Nonaktif</span>
                            @endif
                            <span class="badge-info text-[10px] py-0.5 px-2">Rp {{ number_format($video->final_price, 0, ',', '.') }}</span>
                            @if($video->hasDiscount())
                                <span class="badge-warning text-[10px] py-0.5 px-2">Diskon {{ $video->discount_label }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 mt-4 pt-4 border-t border-border">
                    <a href="{{ route('admin.videos.edit', $video->id) }}" class="flex-1 text-center text-accent-400 text-xs font-semibold px-3 py-2.5 rounded-md bg-accent-50 hover:bg-accent-100 transition-colors">Edit</a>
                    <form action="{{ route('admin.videos.toggle', $video->id) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full text-center text-xs font-semibold px-3 py-2.5 rounded-md transition-colors {{ $video->is_active ? 'bg-gold-50 text-gold-600 hover:bg-gold-100' : 'bg-success-50 text-success-500 hover:bg-success-100' }}">
                            {{ $video->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </form>
                    <form action="{{ route('admin.videos.destroy', $video->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus video ini? Video dengan riwayat pembelian tidak dapat dihapus.')" class="flex-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full text-center text-danger-500 text-xs font-semibold px-3 py-2.5 rounded-md bg-danger-50 hover:bg-danger-100 transition-colors">Hapus</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-card rounded-lg border border-border shadow-sm p-10 text-center">
                <div class="w-20 h-20 mx-auto rounded-lg bg-muted flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h1.5C5.496 19.5 6 18.996 6 18.375m-3.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-1.5A1.125 1.125 0 0118 18.375M20.625 4.5H3.375m17.25 0c.621 0 1.125.504 1.125 1.125M20.625 4.5h-1.5C18.504 4.5 18 5.004 18 5.625m3.75 0v1.5c0 .621-.504 1.125-1.125 1.125M3.375 4.5c-.621 0-1.125.504-1.125 1.125M3.375 4.5h1.5C5.496 4.5 6 5.004 6 5.625m-3.75 0v1.5c0 .621.504 1.125 1.125 1.125m0 0h1.5m-1.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m1.5-3.75C5.496 8.25 6 7.746 6 7.125v-1.5M4.875 8.25C5.496 8.25 6 8.754 6 9.375v1.5m0-5.25v5.25m0-5.25C6 5.004 6.504 4.5 7.125 4.5h9.75c.621 0 1.125.504 1.125 1.125m1.125 2.625h1.5m-1.5 0A1.125 1.125 0 0118 7.125v-1.5m1.125 2.625c-.621 0-1.125.504-1.125 1.125v1.5m2.625-2.625c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125M18 5.625v5.25M7.125 12h9.75m-9.75 0A1.125 1.125 0 016 10.875M7.125 12C6.504 12 6 12.504 6 13.125m0-2.25C6 11.496 5.496 12 4.875 12M18 10.875c0 .621-.504 1.125-1.125 1.125M18 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125m-12 5.25v-5.25m0 5.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125m-12 0v-1.5c0-.621-.504-1.125-1.125-1.125M18 18.375v-5.25m0 5.25v-1.5c0-.621.504-1.125 1.125-1.125M18 13.125v1.5c0 .621.504 1.125 1.125 1.125M18 13.125c0-.621.504-1.125 1.125-1.125M6 13.125v1.5c0 .621-.504 1.125-1.125 1.125M6 13.125C6 12.504 5.496 12 4.875 12m-1.5 0h1.5m-1.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M19.125 12h1.5m0 0c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h1.5m14.25 0h1.5"/></svg>
                </div>
                <h3 class="text-lg font-bold text-muted-foreground">Belum Ada Video</h3>
                <p class="text-muted-foreground mt-1 text-sm">Tambahkan video pembahasan pertama Anda</p>
                <a href="{{ route('admin.videos.create') }}" class="inline-flex items-center gap-2 mt-5 btn-primary text-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Tambah Video
                </a>
            </div>
        @endforelse
        @if(method_exists($videos, 'links') && $videos->hasPages())
            <div class="bg-card rounded-lg border border-border shadow-sm p-4">
                {{ $videos->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
