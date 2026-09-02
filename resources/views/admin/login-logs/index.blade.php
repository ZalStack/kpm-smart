{{-- admin/login-logs/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Log Login')
@section('header-title', 'Log Login User')
@section('header-sub', 'Riwayat aktivitas login pengguna sistem')

@section('content')
<div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="stat-card stagger-item">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-brand-900 to-brand-800 text-white shadow-lg shadow-brand-900/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                </div>
                <div>
                    <p class="text-[11px] text-muted-foreground uppercase font-semibold tracking-wider">Total Login</p>
                    <p class="text-2xl font-bold text-brand-900 leading-tight">{{ $logs->total() }}</p>
                </div>
            </div>
        </div>
        <div class="stat-card stagger-item">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-success-500 to-success-600 text-white shadow-lg shadow-success-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-[11px] text-muted-foreground uppercase font-semibold tracking-wider">Login Hari Ini</p>
                    <p class="text-2xl font-bold text-success-500 leading-tight">{{ $todayCount }}</p>
                </div>
            </div>
        </div>
        <div class="stat-card stagger-item">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-accent-400 to-accent-500 text-white shadow-lg shadow-accent-400/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
                </div>
                <div>
                    <p class="text-[11px] text-muted-foreground uppercase font-semibold tracking-wider">Minggu Ini</p>
                    <p class="text-2xl font-bold text-primary leading-tight">{{ $weekCount }}</p>
                </div>
            </div>
        </div>
        <div class="stat-card stagger-item">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-gold-400 to-gold-500 text-white shadow-lg shadow-gold-400/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/></svg>
                </div>
                <div>
                    <p class="text-[11px] text-muted-foreground uppercase font-semibold tracking-wider">User Unik</p>
                    <p class="text-2xl font-bold text-gold-500 leading-tight">{{ \App\Models\LoginLog::distinct('user_id')->count('user_id') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="admin-card p-4 sm:p-5 stagger-item">
        <form method="GET" action="{{ route('admin.login-logs.index') }}" class="flex flex-col lg:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email user..."
                       class="form-input pl-10">
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex gap-3">
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input sm:w-40" title="Dari tanggal">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input sm:w-40" title="Sampai tanggal">
                </div>
                <button type="submit" class="btn-primary px-6">Filter</button>
                @if(request('search') || request('date_from') || request('date_to'))
                    <a href="{{ route('admin.login-logs.index') }}" class="btn-secondary px-5">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Logs List -->
    <div class="admin-card overflow-hidden stagger-item">
        @if($logs->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg class="w-10 h-10 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                </div>
                <h3 class="text-lg font-bold text-muted-foreground">Belum Ada Log Login</h3>
                <p class="text-muted-foreground mt-1 text-sm">Log akan tercatat setiap kali user berhasil login</p>
            </div>
        @else
            <!-- Card list (mobile) -->
            <div class="md:hidden divide-y divide-border">
                @foreach($logs as $log)
                    <div class="p-4 flex items-start gap-3 hover:bg-muted/50 transition-all duration-200">
                        <div class="w-10 h-10 rounded-md bg-gradient-to-br from-brand-900 to-accent-400 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                            {{ strtoupper(substr($log->user->name ?? '?', 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-brand-900 truncate text-sm">{{ $log->user->name ?? 'User Dihapus' }}</p>
                            <p class="text-xs text-muted-foreground truncate">{{ $log->user->email ?? '-' }}</p>
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1.5 text-[11px] text-muted-foreground">
                                <span class="inline-flex items-center gap-1">
                                    🕐 {{ $log->login_at->translatedFormat('d M Y, H:i') }}
                                </span>
                                @if($log->ip_address)
                                    <span class="inline-flex items-center gap-1 font-mono">🌐 {{ $log->ip_address }}</span>
                                @endif
                                @if($log->location)
                                    <span class="inline-flex items-center gap-1 truncate max-w-full">📍 {{ $log->location }}</span>
                                @endif
                            </div>
                        </div>
                        <span class="badge-success text-[10px] py-0.5 px-2 flex-shrink-0">Berhasil</span>
                    </div>
                @endforeach
                {{ $logs->links() }}
            </div>

            <!-- Table (md+) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full min-w-[850px] text-sm admin-table">
                    <thead>
                        <tr>
                            <th class="px-5 py-3.5 text-left">User</th>
                            <th class="px-5 py-3.5 text-left">Waktu Login</th>
                            <th class="px-5 py-3.5 text-left">IP Address</th>
                            <th class="px-5 py-3.5 text-left">Lokasi</th>
                            <th class="px-5 py-3.5 text-left">Perangkat / Browser</th>
                            <th class="px-5 py-3.5 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($logs as $log)
                            <tr class="hover:shadow-sm transition-all duration-200">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-md bg-gradient-to-br from-brand-900 to-accent-400 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                            {{ strtoupper(substr($log->user->name ?? '?', 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-brand-900 truncate text-xs">{{ $log->user->name ?? 'User Dihapus' }}</p>
                                            <p class="text-[10px] text-muted-foreground truncate">{{ $log->user->email ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <p class="text-xs font-medium text-foreground">{{ $log->login_at->translatedFormat('d M Y') }}</p>
                                    <p class="text-[10px] text-muted-foreground">{{ $log->login_at->format('H:i:s') }} WIB</p>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="font-mono text-xs text-muted-foreground bg-muted px-2 py-1 rounded-md">{{ $log->ip_address ?? '-' }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="text-xs text-muted-foreground">{{ $log->location ?? 'Tidak diketahui' }}</span>
                                </td>
                                <td class="px-5 py-4 max-w-[260px]">
                                    <p class="text-xs text-muted-foreground truncate" title="{{ $log->user_agent }}">
                                        {{ \App\Support\UserAgentFormatter::shorten($log->user_agent) }}
                                    </p>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="badge-success">Berhasil</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
