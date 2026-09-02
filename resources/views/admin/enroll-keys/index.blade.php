@extends('layouts.admin')

@section('title', 'Enroll Keys - Admin')
@section('header-title', 'Manajemen Enroll Key')
@section('header-sub', 'Kelola kunci pendaftaran untuk pesanan berbayar')

@section('content')
<div class="space-y-6">

    {{-- ===================== STAT CARDS ===================== --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
        <div class="stat-card stagger-item group">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-navy to-navy-light shadow-lg shadow-navy/20 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Total Kunci</p>
                    <p class="text-2xl font-bold text-foreground">{{ $stats['total_keys'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="stat-card stagger-item group">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-success-500 to-[#00c853] shadow-lg shadow-success-500/20 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Aktif</p>
                    <p class="text-2xl font-bold text-success-500">{{ $stats['activated'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="stat-card stagger-item group">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-gold-400 to-[#ffd54f] shadow-lg shadow-gold-400/20 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-navy" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Belum Aktif</p>
                    <p class="text-2xl font-bold text-gold-600">{{ $stats['not_activated'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="stat-card stagger-item group hidden sm:block">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-accent-400 to-accent-500 shadow-lg shadow-accent-400/20 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Terkirim</p>
                    <p class="text-2xl font-bold text-navy">{{ $stats['sent_by_admin'] ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="stat-card stagger-item group hidden lg:block">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-purple-500 to-purple-600 shadow-lg shadow-purple-500/20 group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 119 0v3.75M3.75 21.75h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H3.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">Terbuka</p>
                    <p class="text-2xl font-bold text-navy">{{ $stats['unlocked'] ?? 0 }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ===================== TOOLBAR ===================== --}}
    <div class="admin-card bg-card rounded-lg p-3 sm:p-4 shadow-sm border border-border">
        <form action="{{ route('admin.enroll-keys.index') }}" method="GET" class="flex flex-col lg:flex-row gap-3">
            <div class="flex-1 relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                </span>
                <input type="text" name="search" placeholder="Cari pesanan, pengguna, atau kunci..." value="{{ request('search') }}" class="form-input w-full pl-11 pr-4 py-3 border border-border rounded-md text-sm focus:border-accent-400 focus:ring-2 focus:ring-accent-400/20 transition outline-none bg-muted/50 hover:bg-card focus:bg-card">
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <select name="enrollment_status" class="form-select px-4 py-3 border border-border rounded-md text-sm focus:border-accent-400 focus:ring-2 focus:ring-accent-400/20 transition outline-none bg-muted/50 hover:bg-card focus:bg-card w-full sm:w-40 appearance-none cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="activated" {{ request('enrollment_status') == 'activated' ? 'selected' : '' }}>Aktif</option>
                    <option value="not_activated" {{ request('enrollment_status') == 'not_activated' ? 'selected' : '' }}>Belum Aktif</option>
                    <option value="sent" {{ request('enrollment_status') == 'sent' ? 'selected' : '' }}>Terkirim</option>
                    <option value="not_sent" {{ request('enrollment_status') == 'not_sent' ? 'selected' : '' }}>Belum Terkirim</option>
                    <option value="unlocked" {{ request('enrollment_status') == 'unlocked' ? 'selected' : '' }}>Terbuka</option>
                </select>
                <select name="package_id" class="form-select px-4 py-3 border border-border rounded-md text-sm focus:border-accent-400 focus:ring-2 focus:ring-accent-400/20 transition outline-none bg-muted/50 hover:bg-card focus:bg-card w-full sm:w-48 appearance-none cursor-pointer">
                    <option value="">Semua Paket</option>
                    @foreach($packages as $package)
                        <option value="{{ $package->id }}" {{ request('package_id') == $package->id ? 'selected' : '' }}>{{ $package->title }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary justify-center gap-2 !py-3 !px-6 whitespace-nowrap">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    Terapkan
                </button>
                <a href="{{ route('admin.enroll-keys.index') }}" class="btn-secondary justify-center !py-3 !px-6 whitespace-nowrap">Reset</a>
                <button type="button" onclick="bulkSend()" class="btn-success justify-center gap-2 !py-3 !px-6 whitespace-nowrap">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                    Kirim Massal
                </button>
            </div>
        </form>
    </div>

    {{-- ===================== DESKTOP TABLE ===================== --}}
    <div class="hidden md:block admin-card bg-card rounded-lg shadow-sm border border-border overflow-hidden">
        @if($enrollKeys->isEmpty())
            <div class="p-16 text-center">
                <div class="w-20 h-20 mx-auto rounded-lg bg-muted flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/></svg>
                </div>
                <h3 class="text-2xl font-bold text-muted-foreground">Tidak Ada Enroll Key</h3>
                <p class="text-muted-foreground mt-2">Coba ubah filter atau kata kunci pencarian</p>
                <a href="{{ route('admin.enroll-keys.index') }}" class="inline-flex items-center gap-2 mt-5 btn-secondary">
                    Atur Ulang Filter
                </a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-sm">
                    <thead>
                        <tr class="bg-gradient-to-r from-muted to-muted/50 border-b border-border">
                            <th class="px-5 py-4 text-center w-12">
                                <input type="checkbox" id="selectAll" class="rounded border-border text-accent-400 focus:ring-accent-400">
                            </th>
                            <th class="px-5 py-4 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider">Pesanan</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider hidden sm:table-cell">Pengguna</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider hidden md:table-cell">Paket</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider">Kunci</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider hidden lg:table-cell">Status</th>
                            <th class="px-5 py-4 text-left text-xs font-semibold text-muted-foreground uppercase tracking-wider hidden xl:table-cell">Tanggal</th>
                            <th class="px-5 py-4 text-center text-xs font-semibold text-muted-foreground uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($enrollKeys as $enrollKey)
                            @php
                                $enrollment = $enrollKey->enrollment ?? [];
                                $isActivated = $enrollment['activated'] ?? false;
                                $isSent = $enrollment['sent_by_admin'] ?? false;
                                $key = $enrollment['key'] ?? '-';
                            @endphp
                            <tr class="hover:bg-accent-50 transition-colors duration-200 group">
                                <td class="px-5 py-4 text-center">
                                    <input type="checkbox" name="order_ids[]" value="{{ $enrollKey->id }}" class="order-checkbox rounded border-border text-accent-400 focus:ring-accent-400">
                                </td>
                                <td class="px-5 py-4">
                                    <span class="font-mono text-xs font-semibold text-navy bg-navy/5 px-2.5 py-1 rounded-md">{{ $enrollKey->order_number }}</span>
                                </td>
                                <td class="px-5 py-4 hidden sm:table-cell">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-md bg-gradient-to-br from-navy to-navy-light text-white flex items-center justify-center font-bold text-xs flex-shrink-0 shadow-md shadow-navy/10">
                                            {{ strtoupper(substr($enrollKey->user?->name ?? 'U', 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-navy truncate max-w-[120px] group-hover:text-navy-light transition">{{ $enrollKey->user?->name ?? 'Unknown' }}</p>
                                            <p class="text-xs text-muted-foreground truncate max-w-[120px]">{{ $enrollKey->user?->email ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 hidden md:table-cell text-muted-foreground">{{ $enrollKey->package?->title ?? 'Deleted' }}</td>
                                <td class="px-5 py-4">
                                    <code class="font-mono font-bold text-sm bg-muted px-2.5 py-1 rounded-md text-navy border border-border">{{ $key }}</code>
                                </td>
                                <td class="px-5 py-4 hidden lg:table-cell">
                                    <div class="flex flex-wrap gap-1.5">
                                        @if($isActivated)
                                            <span class="badge-success inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold">
                                                <span class="w-1.5 h-1.5 rounded-full bg-success-500 animate-pulse"></span> Aktif
                                            </span>
                                        @else
                                            <span class="badge-neutral inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold">
                                                <span class="w-1.5 h-1.5 rounded-full bg-muted-foreground"></span> Nonaktif
                                            </span>
                                        @endif
                                        @if($isSent)
                                            <span class="badge-info inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold">Terkirim</span>
                                        @else
                                            <span class="badge-neutral inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold">Belum Terkirim</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-4 hidden xl:table-cell">
                                    <div class="text-sm">
                                        <p class="font-medium text-navy">{{ $enrollKey->created_at->format('d/m/Y') }}</p>
                                        <p class="text-[10px] text-muted-foreground">{{ $enrollKey->created_at->format('H:i') }}</p>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.enroll-keys.show', $enrollKey->id) }}"
                                           class="w-9 h-9 rounded-md bg-navy/10 hover:bg-navy/20 text-navy flex items-center justify-center transition-all hover:scale-110 hover:shadow-md"
                                           title="Lihat">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        </a>
                                        @if(!$isActivated)
                                            <form action="{{ route('admin.enroll-keys.activate', $enrollKey->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="w-9 h-9 rounded-md bg-success-50 hover:bg-success-100 text-success-500 flex items-center justify-center transition-all hover:scale-110 hover:shadow-md" title="Aktifkan" onclick="return confirm('Aktifkan kunci ini?')">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                        @if(!$isSent)
                                            <form action="{{ route('admin.enroll-keys.send', $enrollKey->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="w-9 h-9 rounded-md bg-accent-50 hover:bg-accent-100 text-accent-400 flex items-center justify-center transition-all hover:scale-110 hover:shadow-md" title="Kirim" onclick="return confirm('Kirim kunci ini ke pengguna?')">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-border">
                <div>{{ $enrollKeys->links() }}</div>
            </div>
        @endif
    </div>

    {{-- ===================== MOBILE CARDS ===================== --}}
    <div class="space-y-4 md:hidden">
        @forelse($enrollKeys as $enrollKey)
            @php
                $enrollment = $enrollKey->enrollment ?? [];
                $isActivated = $enrollment['activated'] ?? false;
                $isSent = $enrollment['sent_by_admin'] ?? false;
                $key = $enrollment['key'] ?? '-';
            @endphp
            <div class="bg-card rounded-lg border border-border shadow-sm hover:shadow-md transition-all duration-300 p-5">
                <div class="flex items-start gap-3">
                    <div class="w-11 h-11 rounded-md bg-gradient-to-br from-navy to-navy-light text-white flex items-center justify-center font-bold text-sm flex-shrink-0 shadow-md shadow-navy/10">
                        {{ strtoupper(substr($enrollKey->user?->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs font-semibold text-navy bg-navy/5 px-2 py-0.5 rounded-md">{{ $enrollKey->order_number }}</span>
                            <div class="flex flex-wrap gap-1">
                                @if($isActivated)
                                    <span class="badge-success text-[10px] py-0.5 px-2">Aktif</span>
                                @else
                                    <span class="badge-neutral text-[10px] py-0.5 px-2">Nonaktif</span>
                                @endif
                                @if($isSent)
                                    <span class="badge-info text-[10px] py-0.5 px-2">Terkirim</span>
                                @else
                                    <span class="badge-neutral text-[10px] py-0.5 px-2">Belum</span>
                                @endif
                            </div>
                        </div>
                        <p class="font-semibold text-navy truncate text-sm mt-1">{{ $enrollKey->user?->name ?? 'Unknown' }}</p>
                        <p class="text-xs text-muted-foreground mt-0.5">{{ $enrollKey->package?->title ?? 'Deleted' }}</p>
                        <code class="block font-mono text-xs bg-muted px-2 py-1 rounded-md text-navy border border-border mt-2 w-fit">{{ $key }}</code>
                    </div>
                </div>
                <div class="flex items-center gap-2 mt-4 pt-4 border-t border-border">
                    <a href="{{ route('admin.enroll-keys.show', $enrollKey->id) }}" class="flex-1 text-center text-navy text-xs font-semibold px-3 py-2.5 rounded-md bg-navy/5 hover:bg-navy/10 transition-colors">Lihat</a>
                    @if(!$isActivated)
                        <form action="{{ route('admin.enroll-keys.activate', $enrollKey->id) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full text-center text-success-500 text-xs font-semibold px-3 py-2.5 rounded-md bg-success-50 hover:bg-success-100 transition-colors" onclick="return confirm('Aktifkan kunci ini?')">Aktifkan</button>
                        </form>
                    @endif
                    @if(!$isSent)
                        <form action="{{ route('admin.enroll-keys.send', $enrollKey->id) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full text-center text-accent-400 text-xs font-semibold px-3 py-2.5 rounded-md bg-accent-50 hover:bg-accent-100 transition-colors" onclick="return confirm('Kirim kunci ini ke pengguna?')">Kirim</button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-card rounded-lg border border-border shadow-sm p-10 text-center">
                <div class="w-20 h-20 mx-auto rounded-lg bg-muted flex items-center justify-center mb-4">
                    <svg class="w-10 h-10 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-muted-foreground">Tidak Ada Enroll Key</h3>
                <p class="text-muted-foreground mt-1 text-sm">Coba ubah filter atau kata kunci pencarian</p>
                <a href="{{ route('admin.enroll-keys.index') }}" class="inline-flex items-center gap-2 mt-5 btn-secondary text-sm">Atur Ulang Filter</a>
            </div>
        @endforelse
        @if(method_exists($enrollKeys, 'hasPages') && $enrollKeys->hasPages())
            <div class="bg-card rounded-lg border border-border shadow-sm p-4">
                {{ $enrollKeys->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Bulk Form -->
<form id="bulkForm" method="POST" action="{{ route('admin.enroll-keys.bulk-send') }}">
    @csrf
    <div id="bulkIds"></div>
</form>

<script>
    document.getElementById('selectAll')?.addEventListener('change', function() {
        document.querySelectorAll('.order-checkbox').forEach(cb => cb.checked = this.checked);
    });

    function bulkSend() {
        const selected = document.querySelectorAll('.order-checkbox:checked');
        if (selected.length === 0) return alert('Pilih setidaknya satu enroll key.');
        if (!confirm(`Kirim ${selected.length} enroll key ke pengguna?`)) return;
        const container = document.getElementById('bulkIds');
        container.innerHTML = '';
        selected.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'order_ids[]';
            input.value = cb.value;
            container.appendChild(input);
        });
        document.getElementById('bulkForm').submit();
    }
</script>
@endsection
