{{-- admin/users/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Detail User')
@section('header-title', 'Detail User')
@section('header-sub', $user->name)

@section('content')
<div class="space-y-6">
    <a href="{{ route('admin.users.index') }}" class="text-primary hover:text-brand-800 text-sm inline-flex items-center gap-2 transition-colors group">
        <svg class="w-4 h-4 transition-transform group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        Kembali ke Daftar Pengguna
    </a>

    <!-- Profile Header -->
    <div class="admin-card overflow-hidden">
        <div class="h-32 md:h-40 bg-gradient-to-r from-brand-950 via-brand-900 to-primary relative">
            <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 40%, rgba(255,255,255,.6) 0, transparent 40%), radial-gradient(circle at 80% 70%, rgba(255,255,255,.5) 0, transparent 35%);"></div>
            <div class="absolute bottom-0 left-4 md:left-8 pb-1 flex items-end gap-4">
                <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}"
                     class="w-24 h-24 md:w-32 md:h-32 rounded-lg object-cover border-4 border-card shadow-xl -translate-y-6 md:-translate-y-8">
            </div>
        </div>
        <div class="pt-14 md:pt-18 px-4 md:px-8 pb-6">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="min-w-0">
                    <div class="flex items-center flex-wrap gap-2">
                        <h2 class="text-xl md:text-2xl font-bold text-brand-900">{{ $user->name }}</h2>
                        @if($user->is_active)
                            <span class="badge-success">
                                <span class="w-1.5 h-1.5 rounded-full bg-success-500"></span> Aktif
                            </span>
                        @else
                            <span class="badge-neutral">
                                <span class="w-1.5 h-1.5 rounded-full bg-muted-foreground"></span> Nonaktif
                            </span>
                        @endif
                    </div>
                    <p class="text-sm text-muted-foreground mt-0.5">{{ $user->email }}</p>
                </div>
                <div class="sm:ml-auto">
                    <form action="{{ route('admin.users.toggle-active', $user->id) }}" method="POST" onsubmit="return confirm('{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }} akun ini?')">
                        @csrf
                        <button type="submit" class="w-full sm:w-auto {{ $user->is_active ? 'btn-danger' : 'btn-success' }}">
                            @if($user->is_active)
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                Nonaktifkan Akun
                            @else
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Aktifkan Akun
                            @endif
                        </button>
                    </form>
                </div>
            </div>
            <p class="text-xs text-muted-foreground mt-3 flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                Terdaftar sejak {{ $user->created_at->format('d M Y') }}
            </p>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="stat-card stagger-item">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-brand-900 to-brand-800 text-white shadow-lg shadow-brand-900/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div>
                    <p class="form-label">Total Pesanan</p>
                    <p class="text-2xl font-bold text-brand-900 leading-tight">{{ $stats['total_orders'] }}</p>
                </div>
            </div>
        </div>
        <div class="stat-card stagger-item">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-success-500 to-success-600 text-white shadow-lg shadow-success-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="form-label">Pesanan Lunas</p>
                    <p class="text-2xl font-bold text-success-500 leading-tight">{{ $stats['paid_orders'] }}</p>
                </div>
            </div>
        </div>
        <div class="stat-card stagger-item">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-gold-400 to-gold-500 text-brand-900 shadow-lg shadow-gold-400/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="form-label">Total Belanja</p>
                    <p class="text-2xl font-bold text-brand-900 leading-tight">Rp {{ number_format($stats['total_spent'], 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left column -->
        <div class="space-y-4">
            <div class="admin-card p-5 md:p-6">
                <h3 class="text-xs font-bold text-brand-900 uppercase tracking-wide mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg>
                    Data Siswa
                </h3>
                <div class="space-y-3.5 text-sm">
                    <div class="flex items-start justify-between gap-3">
                        <span class="form-label shrink-0 mb-0">Nama Siswa</span>
                        <span class="text-brand-900 font-medium text-right">{{ $user->student_name ?? '-' }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <span class="form-label shrink-0 mb-0">Kelas</span>
                        <span class="text-brand-900 font-medium text-right">{{ $user->student_class ?? '-' }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <span class="form-label shrink-0 mb-0">Jurusan</span>
                        <span class="text-brand-900 font-medium text-right">{{ $user->student_major ?? '-' }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <span class="form-label shrink-0 mb-0">Sekolah</span>
                        <span class="text-brand-900 font-medium text-right">{{ $user->school_name ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="admin-card p-5 md:p-6">
                <h3 class="text-xs font-bold text-brand-900 uppercase tracking-wide mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                    Info Kontak
                </h3>
                <div class="space-y-3.5 text-sm">
                    <div class="flex items-start justify-between gap-3">
                        <span class="form-label shrink-0 mb-0">No. HP/WA</span>
                        <span class="text-brand-900 font-medium text-right">{{ $user->phone ?? '-' }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <span class="form-label shrink-0 mb-0">Jenis Kelamin</span>
                        <span class="text-brand-900 font-medium text-right">{{ $user->gender ?? '-' }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <span class="form-label shrink-0 mb-0">Agama</span>
                        <span class="text-brand-900 font-medium text-right">{{ $user->religion ?? '-' }}</span>
                    </div>
                    <div class="flex items-start justify-between gap-3">
                        <span class="form-label shrink-0 mb-0">Alamat</span>
                        <span class="text-brand-900 font-medium text-right">{{ $user->address ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right column -->
        <div class="lg:col-span-2">
            <div class="admin-card overflow-hidden">
                <div class="px-5 md:px-6 py-5 flex items-center justify-between border-b border-border">
                    <h3 class="text-xs font-bold text-brand-900 uppercase tracking-wide flex items-center gap-2">
                        <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        Riwayat Pesanan
                    </h3>
                    @if(!$user->orders->isEmpty())
                        <span class="text-xs text-muted-foreground font-medium">{{ $user->orders->count() }} pesanan</span>
                    @endif
                </div>
                @if($user->orders->isEmpty())
                    <div class="empty-state py-12">
                        <div class="empty-state-icon mx-auto">
                            <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121 0 2.09-.773 2.34-1.872l1.836-8.046A1.125 1.125 0 0018.054 3H5.106m2.394 11.25l-1.5-6h13.5"/></svg>
                        </div>
                        <p class="empty-state-text">Belum ada pesanan</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[560px] text-sm admin-table">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left">No. Pesanan</th>
                                    <th class="px-6 py-3 text-left">Item</th>
                                    <th class="px-6 py-3 text-left">Harga</th>
                                    <th class="px-6 py-3 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @foreach($user->orders as $order)
                                    <tr>
                                        <td class="px-6 py-3.5 font-mono text-xs font-semibold text-brand-800">{{ $order->order_number }}</td>
                                        <td class="px-6 py-3.5 font-medium text-brand-900">@if($order->isVideoOrder())<span class="mr-1">🎬</span>@endif{{ $order->item_title }}</td>
                                        <td class="px-6 py-3.5 font-medium">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                                        <td class="px-6 py-3.5">
                                            @if($order->payment_status === 'paid')
                                                <span class="badge-success">Lunas</span>
                                            @elseif($order->payment_status === 'pending')
                                                <span class="badge-warning">Pending</span>
                                            @else
                                                <span class="badge-danger">Gagal</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection