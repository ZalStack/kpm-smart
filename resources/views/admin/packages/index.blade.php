@extends('layouts.admin')

@section('title', 'Manajemen Paket')
@section('header-title', 'Manajemen Paket')
@section('header-sub', 'Kelola semua paket bank soal')

@section('content')
@php
    $allPackagesForStats = \App\Models\Package::select('id', 'questions', 'is_active')->get();
    $totalPackages = $allPackagesForStats->count();
    $activeCount = $allPackagesForStats->where('is_active', true)->count();
    $inactiveCount = $totalPackages - $activeCount;
    $totalQuestions = $allPackagesForStats->sum(fn($p) => count($p->questions ?? []));
@endphp

<div class="space-y-6">

    {{-- ===================== STAT CARDS ===================== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        {{-- Total Paket --}}
        <div class="stat-card stagger-item group">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-navy to-navy-light shadow-lg shadow-navy/20 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Total Paket</p>
                    <p class="text-2xl font-bold text-foreground">{{ $totalPackages }}</p>
                </div>
            </div>
        </div>

        {{-- Paket Aktif --}}
        <div class="stat-card stagger-item group">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-success-500 to-[#00c853] shadow-lg shadow-success-500/20 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Paket Aktif</p>
                    <p class="text-2xl font-bold text-success-500">{{ $activeCount }}</p>
                </div>
            </div>
        </div>

        {{-- Paket Nonaktif --}}
        <div class="stat-card stagger-item group">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-danger-500 to-[#ff4444] shadow-lg shadow-danger-500/20 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Paket Nonaktif</p>
                    <p class="text-2xl font-bold text-danger-500">{{ $inactiveCount }}</p>
                </div>
            </div>
        </div>

        {{-- Total Soal --}}
        <div class="stat-card stagger-item group">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-gold-400 to-[#ffd54f] shadow-lg shadow-gold-400/20 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-navy" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Total Soal</p>
                    <p class="text-2xl font-bold text-navy">{{ $totalQuestions }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== TOOLBAR ===================== --}}
    <div class="admin-card bg-card rounded-lg p-3 sm:p-4 shadow-sm border border-border">
        <form action="{{ route('admin.packages.index') }}" method="GET" class="flex flex-col lg:flex-row gap-3">
            <div class="flex-1 relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari paket berdasarkan judul atau deskripsi..."
                       class="form-input w-full pl-11 pr-4 py-3 border border-border rounded-md text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 transition outline-none bg-muted/50 hover:bg-card focus:bg-card">
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <select name="status" class="form-select px-4 py-3 border border-border rounded-md text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 transition outline-none bg-muted/50 hover:bg-card focus:bg-card w-full sm:w-40 appearance-none cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
                <select name="kelas" class="form-select px-4 py-3 border border-border rounded-md text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 transition outline-none bg-muted/50 hover:bg-card focus:bg-card w-full sm:w-40 appearance-none cursor-pointer">
                    <option value="">Semua Kelas</option>
                    @foreach($allKelas as $k)
                        <option value="{{ $k }}" {{ request('kelas') == $k ? 'selected' : '' }}>{{ $k }}</option>
                    @endforeach
                </select>
                <select name="jenjang" class="form-select px-4 py-3 border border-border rounded-md text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 transition outline-none bg-muted/50 hover:bg-card focus:bg-card w-full sm:w-40 appearance-none cursor-pointer">
                    <option value="">Semua Jenjang</option>
                    @foreach($allJenjang as $j)
                        <option value="{{ $j }}" {{ request('jenjang') == $j ? 'selected' : '' }}>{{ $j }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary justify-center gap-2 !py-3 !px-6 whitespace-nowrap">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    Cari
                </button>
                @if(request('search') || request('status') || request('kelas') || request('jenjang'))
                    <a href="{{ route('admin.packages.index') }}" class="btn-secondary justify-center !py-3 !px-6 whitespace-nowrap">Reset</a>
                @endif
                <a href="{{ route('admin.packages.create') }}" class="btn-primary justify-center gap-2 !py-3 !px-6 whitespace-nowrap">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Paket
                </a>
            </div>
        </form>
    </div>

    {{-- ===================== DESKTOP TABLE ===================== --}}
    <div class="hidden md:block admin-card bg-card rounded-lg shadow-sm border border-border overflow-hidden">
        @if($packages->isEmpty())
            <div class="empty-state p-16 text-center">
                <div class="empty-state-icon">
                    <svg class="w-10 h-10 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25-2.25M12 13.875V7.5" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-muted-foreground">Belum Ada Paket</h3>
                <p class="text-muted-foreground mt-2">Mulai tambahkan paket bank soal pertama Anda</p>
                <a href="{{ route('admin.packages.create') }}" class="inline-flex items-center gap-2 mt-5 btn-primary">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Paket
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gradient-to-r from-muted to-muted/50 border-b border-border">
                            <th class="px-5 py-4 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider">#</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider">Paket</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider">Kelas</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider">Jenjang</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider">Harga</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider">Membership</th>
                            <th class="px-5 py-4 text-center text-xs font-semibold text-muted-foreground uppercase tracking-wider">Soal</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider">Status</th>
                            <th class="px-5 py-4 text-center text-xs font-semibold text-muted-foreground uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($packages as $index => $package)
                            @php
                                $qCount = count($package->questions ?? []);
                            @endphp
                            <tr class="hover:bg-primary/5 transition-colors duration-200 group">
                                <td class="px-5 py-4 text-muted-foreground font-mono text-sm">{{ $packages->firstItem() + $index }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        @if($package->thumbnail)
                                            <img src="{{ asset('storage/' . $package->thumbnail) }}" alt="" class="w-11 h-11 rounded-md object-cover border-2 border-border flex-shrink-0">
                                        @else
                                            <div class="w-11 h-11 rounded-md bg-gradient-to-br from-navy to-navy-light flex items-center justify-center flex-shrink-0 shadow-md shadow-navy/10">
                                                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                                </svg>
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="font-semibold text-navy truncate max-w-[200px] group-hover:text-navy-light transition">{{ $package->title }}</div>
                                            <div class="text-xs text-muted-foreground truncate max-w-[200px] mt-0.5">{{ Str::limit($package->description, 60) }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    @if($package->kelas)
                                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-full bg-primary/10 text-primary">{{ $package->kelas }}</span>
                                    @else
                                        <span class="text-xs text-muted-foreground">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if($package->jenjang)
                                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-full bg-navy/10 text-navy">{{ $package->jenjang }}</span>
                                    @else
                                        <span class="text-xs text-muted-foreground">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if($package->is_pay_what_you_want)
                                        <div class="flex flex-col">
                                            <span class="badge-success inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1 rounded-full w-fit">Seikhlasnya</span>
                                            <span class="text-[11px] text-muted-foreground mt-1">Min. Rp {{ number_format($package->min_pay_amount ?? 0, 0, ',', '.') }}</span>
                                        </div>
                                    @elseif($package->hasDiscount())
                                        <div class="flex flex-col leading-tight">
                                            <span class="text-xs text-muted-foreground line-through">Rp {{ number_format($package->price, 0, ',', '.') }}</span>
                                            <span class="font-bold text-danger-500 text-base">Rp {{ number_format($package->final_price, 0, ',', '.') }}</span>
                                            <span class="text-[10px] font-semibold badge-danger text-white px-2 py-0.5 rounded-full w-fit mt-0.5">-{{ $package->discount_percent }}%</span>
                                        </div>
                                    @else
                                        <span class="font-semibold text-foreground">Rp {{ number_format($package->price, 0, ',', '.') }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <span class="badge-info inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full whitespace-nowrap">{{ $package->membership_duration_label }}</span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[32px] px-2.5 py-1 rounded-full {{ $qCount > 0 ? 'bg-navy-light/10 text-navy-light' : 'badge-neutral' }} text-xs font-semibold">{{ $qCount }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    @if($package->is_active)
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
                                        <a href="{{ route('admin.packages.detail', $package->id) }}"
                                           class="w-9 h-9 rounded-md bg-navy/10 hover:bg-navy/20 text-navy flex items-center justify-center transition-all hover:scale-110 hover:shadow-md"
                                           title="Detail">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.packages.edit', $package->id) }}"
                                           class="w-9 h-9 rounded-md bg-primary/10 hover:bg-primary/20 text-primary flex items-center justify-center transition-all hover:scale-110 hover:shadow-md"
                                           title="Edit & Kelola Soal">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.packages.confirm-delete', $package->id) }}"
                                           class="w-9 h-9 rounded-md bg-muted hover:bg-danger-50 text-muted-foreground hover:text-danger-500 flex items-center justify-center transition-all hover:scale-110 hover:shadow-md"
                                           title="Hapus">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($packages->hasPages())
                <div class="border-t border-border">
                    {{ $packages->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- ===================== MOBILE CARDS ===================== --}}
    <div class="space-y-4 md:hidden">
        @forelse($packages as $package)
            @php
                $qCount = count($package->questions ?? []);
            @endphp
            <div class="bg-card rounded-lg border border-border shadow-sm hover:shadow-md transition-all duration-300 p-5">
                <div class="flex items-start gap-4">
                    @if($package->thumbnail)
                        <img src="{{ asset('storage/' . $package->thumbnail) }}" alt="" class="w-14 h-14 rounded-lg object-cover border-2 border-border flex-shrink-0">
                    @else
                        <div class="w-14 h-14 rounded-lg bg-gradient-to-br from-navy to-navy-light flex items-center justify-center flex-shrink-0 shadow-md shadow-navy/10">
                            <svg class="w-7 h-7 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                            </svg>
                        </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <h3 class="font-semibold text-navy truncate">{{ $package->title }}</h3>
                            @if($package->is_active)
                                <span class="badge-success text-[10px] font-semibold px-2.5 py-1 rounded-full whitespace-nowrap flex items-center gap-1">
                                    <span class="w-1 h-1 rounded-full bg-success-500 animate-pulse"></span> Aktif
                                </span>
                            @else
                                <span class="badge-neutral text-[10px] font-semibold px-2.5 py-1 rounded-full whitespace-nowrap">Nonaktif</span>
                            @endif
                        </div>
                        <p class="text-xs text-muted-foreground line-clamp-2 mt-1">{{ $package->description }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-border">
                    @if($package->kelas)
                        <span class="badge-info text-[10px] font-semibold px-3 py-1.5 rounded-full">{{ $package->kelas }}</span>
                    @endif
                    @if($package->jenjang)
                        <span class="text-[10px] font-semibold px-3 py-1.5 rounded-full bg-navy/10 text-navy">{{ $package->jenjang }}</span>
                    @endif
                    @if($package->is_pay_what_you_want)
                        <span class="badge-success text-[10px] font-semibold px-3 py-1.5 rounded-full">Seikhlasnya</span>
                    @elseif($package->hasDiscount())
                            <span class="text-xs font-bold px-3 py-1.5 rounded-md bg-danger-50 text-danger-500">
                            Rp {{ number_format($package->final_price, 0, ',', '.') }}
                            <span class="text-[10px] text-muted-foreground font-normal line-through ml-1">Rp {{ number_format($package->price, 0, ',', '.') }}</span>
                        </span>
                    @else
                        <span class="text-xs font-semibold px-3 py-1.5 rounded-md bg-muted text-foreground">Rp {{ number_format($package->price, 0, ',', '.') }}</span>
                    @endif
                    <span class="badge-info text-[10px] font-semibold px-3 py-1.5 rounded-full">{{ $package->membership_duration_label }}</span>
                    <span class="text-[10px] font-semibold px-3 py-1.5 rounded-full {{ $qCount > 0 ? 'bg-navy-light/10 text-navy-light' : 'badge-neutral' }}">{{ $qCount }} soal</span>
                </div>

                <div class="grid grid-cols-3 gap-2 mt-4 pt-4 border-t border-border">
                    <a href="{{ route('admin.packages.detail', $package->id) }}"
                       class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-md bg-navy/10 text-navy text-xs font-semibold transition hover:bg-navy/20 hover:scale-[1.02]">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                        Detail
                    </a>
                    <a href="{{ route('admin.packages.edit', $package->id) }}"
                       class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-md bg-primary/10 text-primary text-xs font-semibold transition hover:bg-primary/20 hover:scale-[1.02]">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                        </svg>
                        Kelola
                    </a>
                    <a href="{{ route('admin.packages.confirm-delete', $package->id) }}"
                       class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-md bg-danger-50 text-danger-500 text-xs font-semibold transition hover:bg-danger-100 hover:scale-[1.02]">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                        </svg>
                        Hapus
                    </a>
                </div>
            </div>
        @empty
            <div class="empty-state bg-card rounded-lg border-2 border-dashed border-border p-12 text-center">
                <div class="empty-state-icon">
                    <svg class="w-8 h-8 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25-2.25M12 13.875V7.5" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-muted-foreground">Belum Ada Paket</h3>
                <p class="text-sm text-muted-foreground mt-2">Mulai tambahkan paket bank soal pertama Anda</p>
                <a href="{{ route('admin.packages.create') }}" class="inline-flex items-center gap-2 mt-5 btn-primary">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Paket
                </a>
            </div>
        @endforelse
        @if(method_exists($packages, 'hasPages') && $packages->hasPages())
            <div class="bg-card rounded-lg border border-border shadow-sm">
                {{ $packages->links() }}
            </div>
        @endif
    </div>
</div>

@endsection
