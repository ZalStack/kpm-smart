{{-- admin/orders/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Detail Pesanan #' . ($order->order_number ?? ''))
@section('header-title', 'Detail Pesanan')
@section('header-sub', 'Informasi lengkap transaksi pesanan')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-sm text-muted-foreground flex-wrap">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-primary transition">Beranda</a>
        <span>/</span>
        <a href="{{ route('admin.orders.index') }}" class="hover:text-primary transition">Pesanan</a>
        <span>/</span>
        <span class="text-foreground font-medium">{{ $order->order_number ?? 'Detail' }}</span>
    </nav>

    <!-- Header Action -->
    <div class="admin-card flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 p-4">
        <div>
            <h2 class="text-xl font-bold text-brand-900 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-2.25-1.313M21 7.5v2.25m0-2.25l-2.25 1.313M3 7.5l2.25-1.313M3 7.5l2.25 1.313M3 7.5v2.25m9 3l2.25-1.313M12 12.75l-2.25-1.313M12 12.75V15m0 6.75l2.25-1.313M12 21.75V19.5m0 2.25l-2.25-1.313m0-16.875L12 2.25l2.25 1.313M21 14.25v2.25l-2.25 1.313m-13.5 0L3 16.5v-2.25"/></svg>
                Pesanan #{{ $order->order_number ?? 'N/A' }}
            </h2>
            <p class="text-sm text-muted-foreground mt-1">Dibuat: {{ isset($order->created_at) ? $order->created_at->format('d M Y H:i:s') : '-' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if (isset($order->payment_status) && $order->payment_status === 'pending')
                <form action="{{ route('admin.orders.verify', $order->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn-success" onclick="return confirm('Verifikasi pembayaran ini?')">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Verifikasi
                    </button>
                </form>
            @endif
            <a href="{{ route('admin.orders.index') }}" class="btn-secondary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                Kembali
            </a>
        </div>
    </div>

    <!-- Status Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
        <div class="stat-card stagger-item">
            <div class="flex items-center gap-4">
                <div class="stat-icon {{ isset($order->payment_status) && $order->payment_status === 'paid' ? 'bg-gradient-to-br from-success-500 to-success-600 text-white shadow-lg shadow-success-500/20' : (isset($order->payment_status) && $order->payment_status === 'pending' ? 'bg-gradient-to-br from-gold-400 to-gold-500 text-brand-900 shadow-lg shadow-gold-400/20' : 'bg-gradient-to-br from-danger-500 to-danger-600 text-white shadow-lg shadow-danger-500/20') }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
                </div>
                <div>
                    <p class="form-label">Status</p>
                    <p class="text-sm font-bold mt-1">
                        @if (isset($order->payment_status))
                            @if ($order->payment_status === 'paid') <span class="text-success-500">Lunas</span>
                            @elseif($order->payment_status === 'pending') <span class="text-gold-600">Pending</span>
                            @else <span class="text-danger-500">Gagal</span> @endif
                        @else <span class="text-muted-foreground">-</span> @endif
                    </p>
                </div>
            </div>
        </div>

        <div class="stat-card stagger-item">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-brand-900 to-brand-800 text-white shadow-lg shadow-brand-900/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="form-label">Total</p>
                    <p class="text-2xl font-bold text-brand-900 leading-tight">Rp {{ isset($order->total_price) ? number_format($order->total_price, 0, ',', '.') : '0' }}</p>
                </div>
            </div>
        </div>

        <div class="stat-card stagger-item">
            <div class="flex items-center gap-4">
                <div class="stat-icon {{ (isset($enrollment['unlocked']) && $enrollment['unlocked']) ? 'bg-gradient-to-br from-success-500 to-success-600 text-white shadow-lg shadow-success-500/20' : ((isset($enrollment['activated']) && $enrollment['activated']) ? 'bg-gradient-to-br from-brand-900 to-brand-800 text-white shadow-lg shadow-brand-900/20' : 'bg-gradient-to-br from-muted to-muted-foreground/30 text-muted-foreground shadow-lg shadow-muted/20') }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/></svg>
                </div>
                <div>
                    <p class="form-label">Enroll Key</p>
                    <p class="text-sm font-bold mt-1">
                        @if (isset($enrollment['unlocked']) && $enrollment['unlocked']) <span class="text-success-500">Terbuka</span>
                        @elseif (isset($enrollment['activated']) && $enrollment['activated']) <span class="text-brand-800">Siap Pakai</span>
                        @elseif (isset($enrollment['sent_by_admin']) && $enrollment['sent_by_admin']) <span class="text-gold-600">Terkirim</span>
                        @else <span class="text-muted-foreground">Belum</span> @endif
                    </p>
                </div>
            </div>
        </div>

        <div class="stat-card stagger-item">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-primary to-primary/80 text-white shadow-lg shadow-primary/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
                </div>
                <div>
                    <p class="form-label">Metode</p>
                    <p class="text-sm font-bold text-brand-900 mt-1 truncate max-w-[100px]">{{ isset($order->payment_type) ? ucfirst(str_replace('_', ' ', $order->payment_type)) : '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
        <div class="lg:col-span-2 space-y-4 md:space-y-6">
            <!-- User Information -->
            <div class="admin-card p-4 sm:p-6">
                <h3 class="text-xs font-bold text-brand-900 uppercase tracking-wide mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    Informasi Pengguna
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><p class="form-label">Nama</p><p class="font-medium mt-1">{{ isset($order->user) ? $order->user->name : 'Tidak Diketahui' }}</p></div>
                    <div><p class="form-label">Email</p><p class="font-medium mt-1 truncate">{{ isset($order->user) ? $order->user->email : '-' }}</p></div>
                    <div><p class="form-label">Telepon</p><p class="font-medium mt-1">{{ isset($order->user) ? ($order->user->phone ?? '-') : '-' }}</p></div>
                    <div><p class="form-label">Status</p><p class="font-medium mt-1">@if(isset($order->user) && ($order->user->is_active ?? true)) <span class="text-success-500">Aktif</span> @else <span class="text-danger-500">Nonaktif</span> @endif</p></div>
                </div>
                @if(isset($order->user_id))
                    <div class="mt-4 pt-3 border-t border-border"><a href="{{ route('admin.users.show', $order->user_id) }}" class="text-primary hover:text-brand-800 text-sm font-medium transition-colors">Lihat Profil Lengkap
                        <svg class="w-4 h-4 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                    </a></div>
                @endif
            </div>

            <!-- Package Information -->
            <div class="admin-card p-4 sm:p-6">
                <h3 class="text-xs font-bold text-brand-900 uppercase tracking-wide mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-2.25-1.313M21 7.5v2.25m0-2.25l-2.25 1.313M3 7.5l2.25-1.313M3 7.5l2.25 1.313M3 7.5v2.25m9 3l2.25-1.313M12 12.75l-2.25-1.313M12 12.75V15m0 6.75l2.25-1.313M12 21.75V19.5m0 2.25l-2.25-1.313m0-16.875L12 2.25l2.25 1.313M21 14.25v2.25l-2.25 1.313m-13.5 0L3 16.5v-2.25"/></svg>
                    {{ $order->isVideoOrder() ? 'Informasi Video' : 'Informasi Paket' }}
                </h3>
                @if ($order->isVideoOrder())
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div><p class="form-label">Judul Video</p><p class="font-medium mt-1">{{ $order->item_title }}</p></div>
                        <div><p class="form-label">Harga</p><p class="font-medium mt-1">Rp {{ number_format($order->total_price, 0, ',', '.') }}</p></div>
                        <div><p class="form-label">Durasi Akses</p><p class="font-medium mt-1">{{ $order->videoOrder?->video?->access_duration_days ?? '-' }} Hari</p></div>
                        <div>
                            <p class="form-label">Status Akses</p>
                            <p class="font-medium mt-1">
                                @if($order->videoOrder && $order->videoOrder->access_granted && $order->videoOrder->access_end && $order->videoOrder->access_end->isFuture())
                                    <span class="text-success-500">Aktif</span>
                                @elseif($order->payment_status === 'paid')
                                    <span class="text-gold-600">Menunggu Aktivasi Admin</span>
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                    </div>
                    @if($order->videoOrder)
                        <div class="mt-4 pt-3 border-t border-border">
                            <a href="{{ route('admin.video-orders.index') }}" class="btn-primary inline-flex">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                Kelola Akses Video
                            </a>
                        </div>
                    @endif
                @else
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div><p class="form-label">Nama Paket</p><p class="font-medium mt-1">{{ isset($order->package) ? $order->package->title : 'Tidak Diketahui' }}</p></div>
                    <div><p class="form-label">Harga</p><p class="font-medium mt-1">Rp {{ isset($order->package) ? number_format($order->package->price ?? 0, 0, ',', '.') : '0' }}</p></div>
                    <div><p class="form-label">Kategori</p><p class="font-medium mt-1">{{ isset($order->package) ? ($order->package->kelas ?? '-') : '-' }}</p></div>
                    <div><p class="form-label">Durasi</p><p class="font-medium mt-1">{{ isset($order->package) ? ($order->package->membership_duration_days ?? '-') : '-' }} Hari</p></div>
                </div>
                @if(isset($order->package_id))
                    <div class="mt-4 pt-3 border-t border-border">
                        <a href="{{ route('admin.packages.edit', $order->package_id) }}" class="btn-primary inline-flex">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                            Edit Paket
                        </a>
                    </div>
                @endif
                @endif
            </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-4 md:space-y-6">
            <!-- Enroll Key Management -->
            @if (!$order->isVideoOrder())
            <div class="admin-card p-4 sm:p-6">
                <h3 class="text-xs font-bold text-brand-900 uppercase tracking-wide mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/></svg>
                    Enroll Key
                </h3>
                @if (isset($order->payment_status) && $order->payment_status === 'paid')
                    <div class="space-y-4">
                        <div class="bg-muted rounded-md p-3">
                            <p class="form-label mb-2">Kode Enroll</p>
                            <div class="flex items-center gap-2">
                                <code class="bg-card px-3 py-2 rounded-md text-sm font-mono text-foreground flex-1 text-center border border-border break-all">{{ isset($enrollment['key']) ? $enrollment['key'] : 'Belum Dibuat' }}</code>
                                @if (isset($enrollment['key']))
                                    <button onclick="copyToClipboard('{{ $enrollment['key'] }}')" class="btn-secondary flex-shrink-0 px-3 py-2">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9.75a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184"/></svg>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center gap-3 p-2 rounded-md {{ (isset($enrollment['sent_by_admin']) && $enrollment['sent_by_admin']) ? 'bg-success-500/5' : 'bg-muted' }}">
                                <div class="w-6 h-6 rounded-full {{ (isset($enrollment['sent_by_admin']) && $enrollment['sent_by_admin']) ? 'bg-success-500' : 'bg-muted-foreground' }} flex items-center justify-center text-white text-xs flex-shrink-0">
                                    @if (isset($enrollment['sent_by_admin']) && $enrollment['sent_by_admin'])
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    @else 1 @endif
                                </div>
                                <div><p class="text-sm font-medium {{ (isset($enrollment['sent_by_admin']) && $enrollment['sent_by_admin']) ? 'text-success-500' : 'text-muted-foreground' }}">Dikirim Admin</p></div>
                            </div>
                            <div class="flex items-center gap-3 p-2 rounded-md {{ (isset($enrollment['activated']) && $enrollment['activated']) ? 'bg-success-500/5' : 'bg-muted' }}">
                                <div class="w-6 h-6 rounded-full {{ (isset($enrollment['activated']) && $enrollment['activated']) ? 'bg-success-500' : 'bg-muted-foreground' }} flex items-center justify-center text-white text-xs flex-shrink-0">
                                    @if (isset($enrollment['activated']) && $enrollment['activated'])
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    @else 2 @endif
                                </div>
                                <div><p class="text-sm font-medium {{ (isset($enrollment['activated']) && $enrollment['activated']) ? 'text-success-500' : 'text-muted-foreground' }}">Diaktifkan Admin</p></div>
                            </div>
                            <div class="flex items-center gap-3 p-2 rounded-md {{ (isset($enrollment['unlocked']) && $enrollment['unlocked']) ? 'bg-success-500/5' : 'bg-muted' }}">
                                <div class="w-6 h-6 rounded-full {{ (isset($enrollment['unlocked']) && $enrollment['unlocked']) ? 'bg-success-500' : 'bg-muted-foreground' }} flex items-center justify-center text-white text-xs flex-shrink-0">
                                    @if (isset($enrollment['unlocked']) && $enrollment['unlocked'])
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                    @else 3 @endif
                                </div>
                                <div><p class="text-sm font-medium {{ (isset($enrollment['unlocked']) && $enrollment['unlocked']) ? 'text-success-500' : 'text-muted-foreground' }}">Digunakan User</p></div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            @if (!isset($enrollment['sent_by_admin']) || !$enrollment['sent_by_admin'])
                                <form action="{{ route('admin.orders.send-enroll', $order->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full btn-warning">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                                        Kirim Enroll Key
                                    </button>
                                </form>
                            @endif
                            @if ((isset($enrollment['sent_by_admin']) && $enrollment['sent_by_admin']) && (!isset($enrollment['activated']) || !$enrollment['activated']))
                                <form action="{{ route('admin.orders.activate-enroll', $order->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full btn-primary" onclick="return confirm('Aktifkan Enroll Key ini?')">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/></svg>
                                        Aktivasi Enroll Key
                                    </button>
                                </form>
                            @endif
                            @if (isset($enrollment['activated']) && $enrollment['activated'])
                                <div class="bg-success-500/5 border border-success-500/20 rounded-md p-3 text-center">
                                    <p class="text-success-500 font-medium text-sm flex items-center justify-center gap-2">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Enroll Key Aktif
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="empty-state py-8">
                        <div class="empty-state-icon mx-auto">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                        </div>
                        <p class="empty-state-text">Enroll Key akan tersedia setelah pembayaran lunas</p>
                    </div>
                @endif
            </div>
            @endif

            <!-- Quick Actions -->
            <div class="admin-card p-4 sm:p-6">
                <h3 class="text-xs font-bold text-brand-900 uppercase tracking-wide mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                    Aksi Cepat
                </h3>
                <div class="space-y-2">
                    @if(isset($order->user_id))
                        <a href="{{ route('admin.users.show', $order->user_id) }}" class="btn-secondary w-full justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                            Lihat Profil Pengguna
                        </a>
                    @endif
                    @if(isset($order->package_id))
                        <a href="{{ route('admin.packages.edit', $order->package_id) }}" class="btn-secondary w-full justify-center">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                            Edit Paket
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => alert('Enroll Key berhasil disalin!'));
    } else {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        alert('Enroll Key berhasil disalin!');
    }
}
</script>
@endsection