{{-- admin/users/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Manajemen Users')
@section('header-title', 'Manajemen Users')
@section('header-sub', 'Kelola data siswa yang terdaftar')

@section('content')
@php
    $inactiveUsers = $totalUsers - $activeUsers;
    $newThisMonth = \App\Models\User::where('role', 'user')->where('created_at', '>=', now()->startOfMonth())->count();
@endphp

<div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="stat-card stagger-item">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-brand-900 to-brand-800 text-white shadow-lg shadow-brand-900/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                </div>
                <div>
                    <p class="text-[11px] text-muted-foreground uppercase font-semibold tracking-wider">Total Pengguna</p>
                    <p class="text-2xl font-bold text-brand-900 leading-tight">{{ $totalUsers }}</p>
                </div>
            </div>
        </div>
        <div class="stat-card stagger-item">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-success-500 to-success-600 text-white shadow-lg shadow-success-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-[11px] text-muted-foreground uppercase font-semibold tracking-wider">Akun Aktif</p>
                    <p class="text-2xl font-bold text-success-500 leading-tight">{{ $activeUsers }}</p>
                </div>
            </div>
        </div>
        <div class="stat-card stagger-item">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-danger-500 to-danger-600 text-white shadow-lg shadow-danger-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                </div>
                <div>
                    <p class="text-[11px] text-muted-foreground uppercase font-semibold tracking-wider">Nonaktif</p>
                    <p class="text-2xl font-bold text-danger-500 leading-tight">{{ $inactiveUsers }}</p>
                </div>
            </div>
        </div>
        <div class="stat-card stagger-item">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-primary to-primary/80 text-white shadow-lg shadow-primary/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z"/></svg>
                </div>
                <div>
                    <p class="text-[11px] text-muted-foreground uppercase font-semibold tracking-wider">Baru Bulan Ini</p>
                    <p class="text-2xl font-bold text-primary leading-tight">{{ $newThisMonth }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="admin-card p-4 sm:p-5 stagger-item">
        <div class="flex flex-col sm:flex-row gap-3">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row gap-3 flex-1">
                <div class="relative flex-1">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, email, siswa, atau sekolah..."
                           class="form-input pl-10">
                </div>
                <div class="flex gap-3">
                    <select name="status" class="form-select sm:w-44">
                        <option value="">Semua Status</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    <button type="submit" class="btn-primary px-6">Cari</button>
                    @if($search || $status)
                        <a href="{{ route('admin.users.index') }}" class="btn-secondary px-5">Reset</a>
                    @endif
                </div>
            </form>
            <a href="{{ route('admin.users.create') }}" class="btn-primary inline-flex items-center gap-2 px-5 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Tambah User
            </a>
        </div>
    </div>

    <!-- Users List -->
    <div class="admin-card overflow-hidden stagger-item">
        @if($users->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg class="w-10 h-10 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-muted-foreground">Tidak Ada User</h3>
                <p class="text-muted-foreground mt-1 text-sm">Belum ada user yang cocok dengan pencarian ini</p>
                <a href="{{ route('admin.users.index') }}" class="btn-secondary mt-6">Reset Pencarian</a>
            </div>
        @else
            <!-- Card list (mobile) -->
            <div class="md:hidden divide-y divide-border">
                @foreach($users as $user)
                    <div class="p-4 flex items-center gap-3 hover:bg-muted/50 transition-all duration-200">
                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="w-11 h-11 rounded-md object-cover ring-2 ring-border flex-shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-brand-900 truncate text-sm">{{ $user->name }}</p>
                            <p class="text-xs text-muted-foreground truncate">{{ $user->email }}</p>
                            <div class="flex items-center gap-2 mt-1.5">
                                @if($user->is_active)
                                    <span class="badge-success text-[10px] py-0.5 px-2">Aktif</span>
                                @else
                                    <span class="badge-neutral text-[10px] py-0.5 px-2">Nonaktif</span>
                                @endif
                                @if($user->school_name)
                                    <span class="text-[10px] text-muted-foreground truncate">{{ $user->school_name }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-2 flex-shrink-0">
                            <a href="{{ route('admin.users.show', $user->id) }}" class="text-primary text-xs font-semibold px-3 py-1.5 rounded-md bg-primary/10 hover:bg-primary/20 transition-colors">Detail</a>
                            <form action="{{ route('admin.users.toggle-active', $user->id) }}" method="POST" onsubmit="return confirm('{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }} akun ini?')">
                                @csrf
                                <button type="submit" class="text-[10px] font-medium {{ $user->is_active ? 'text-danger-500' : 'text-success-500' }} transition-colors">
                                    {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
                <div class="p-4 border-t border-border">
                    {{ $users->links() }}
                </div>
            </div>

            <!-- Table (md+) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full min-w-[900px] text-sm admin-table">
                    <thead>
                        <tr>
                            <th class="px-5 py-3.5 text-left">User</th>
                            <th class="px-5 py-3.5 text-left">Siswa</th>
                            <th class="px-5 py-3.5 text-left">Sekolah</th>
                            <th class="px-5 py-3.5 text-left">Status</th>
                            <th class="px-5 py-3.5 text-left">Terdaftar</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($users as $user)
                            <tr class="cursor-pointer hover:bg-brand-50/30 hover:shadow-sm transition-all duration-200" onclick="window.location='{{ route('admin.users.show', $user->id) }}'">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="w-10 h-10 rounded-md object-cover ring-2 ring-border flex-shrink-0">
                                        <div class="min-w-0">
                                            <p class="font-semibold text-brand-900 truncate">{{ $user->name }}</p>
                                            <p class="text-xs text-muted-foreground truncate">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-brand-900">{{ $user->student_class ?? '-' }}@if($user->bidang) · {{ $user->bidang }}@endif</p>
                                </td>
                                <td class="px-5 py-4 text-muted-foreground">
                                    @if($user->school_name)
                                        <span class="inline-flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg>
                                            {{ $user->school_name }}
                                        </span>
                                    @else
                                        <span class="text-muted-foreground">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if($user->is_active)
                                        <span class="badge-success">
                                            <span class="w-1.5 h-1.5 rounded-full bg-success-500"></span> Aktif
                                        </span>
                                    @else
                                        <span class="badge-neutral">
                                            <span class="w-1.5 h-1.5 rounded-full bg-muted-foreground"></span> Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-sm text-muted-foreground whitespace-nowrap">
                                    {{ $user->created_at->format('d M Y') }}
                                    <span class="text-[10px] text-muted-foreground block">{{ $user->created_at->diffForHumans() }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2" onclick="event.stopPropagation()">
                                        <a href="{{ route('admin.users.show', $user->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary/10 text-primary text-xs font-semibold rounded-md hover:bg-primary/20 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            Detail
                                        </a>
                                        <form action="{{ route('admin.users.toggle-active', $user->id) }}" method="POST" onsubmit="return confirm('{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }} akun ini?')">
                                            @csrf
                                            <button type="submit" title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}"
                                                class="w-8 h-8 inline-flex items-center justify-center rounded-md transition-all duration-200 {{ $user->is_active ? 'bg-danger-50 text-danger-500 hover:bg-danger-100' : 'bg-success-50 text-success-500 hover:bg-success-100' }}">
                                                @if($user->is_active)
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                @endif
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-border hidden md:block">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>
@endsection