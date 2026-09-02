@extends('layouts.admin')

@section('title', 'Hapus Paket - ' . $package->title)
@section('header-title', 'Konfirmasi Hapus Paket')
@section('header-sub', 'Tinjau data sebelum menghapus')

@section('content')
<div class="pb-10">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-sm text-muted-foreground mb-6">
        <a href="{{ route('admin.packages.index') }}" class="hover:text-primary transition-colors">Paket</a>
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
        </svg>
        <a href="{{ route('admin.packages.detail', $package) }}" class="hover:text-primary transition-colors truncate max-w-[200px]">{{ Str::limit($package->title, 30) }}</a>
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
        </svg>
        <span class="text-danger-500 font-medium">Hapus</span>
    </div>

    {{-- Warning Banner --}}
    <div class="bg-gradient-to-r from-danger-50 to-danger-100/50 border border-danger-200/60 rounded-lg p-4 sm:p-5 mb-6 anim-fade-in-up">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-md bg-danger-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                <svg class="w-5 h-5 text-danger-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-danger-700">Peringatan Penting</h3>
                <p class="text-xs text-danger-600 mt-1 leading-relaxed">Tindakan ini tidak dapat dibatalkan. Semua data terkait paket ini akan dihapus secara permanen dari sistem.</p>
            </div>
        </div>
    </div>

    {{-- Package Info Card --}}
    <div class="admin-card bg-card rounded-lg shadow-sm border border-border/80 overflow-hidden mb-6 anim-fade-in-up anim-delay-1">
        {{-- Package Thumbnail & Title --}}
        <div class="p-5 sm:p-6">
            <div class="flex items-start gap-4">
                @if($package->thumbnail)
                    <img src="{{ asset('storage/' . $package->thumbnail) }}" alt="{{ $package->title }}" class="w-16 h-16 sm:w-20 sm:h-20 rounded-lg object-cover border-2 border-border flex-shrink-0 shadow-md">
                @else
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-lg bg-gradient-to-br from-navy to-navy-light flex items-center justify-center flex-shrink-0 shadow-lg shadow-navy/15">
                        <svg class="w-8 h-8 sm:w-10 sm:h-10 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                        </svg>
                    </div>
                @endif
                <div class="min-w-0 flex-1">
                    <h2 class="text-lg sm:text-xl font-bold text-foreground leading-tight">{{ $package->title }}</h2>
                    <p class="text-sm text-muted-foreground mt-1 line-clamp-2">{{ $package->description ?? 'Tidak ada deskripsi' }}</p>
                    <div class="flex flex-wrap gap-2 mt-3">
                        @if($package->kelas)
                            <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-3 py-1 rounded-full bg-primary/10 text-primary">{{ $package->kelas }}</span>
                        @endif
                        @if($package->jenjang)
                            <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-3 py-1 rounded-full bg-navy/10 text-foreground">{{ $package->jenjang }}</span>
                        @endif
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-3 py-1 rounded-full {{ $package->is_active ? 'bg-success-500/10 text-success-500' : 'bg-muted text-muted-foreground' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $package->is_active ? 'bg-success-500 animate-pulse' : 'bg-muted-foreground' }}"></span>
                            {{ $package->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Data Impact Summary --}}
        <div class="border-t border-border bg-muted/50 p-5 sm:p-6">
            <h3 class="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-4">Data yang Akan Dihapus</h3>
            <div class="grid grid-cols-2 gap-3">
                {{-- Cards --}}
                <div class="flex items-center gap-3 bg-card rounded-md p-3.5 border border-border">
                    <div class="w-10 h-10 rounded-md bg-navy/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-foreground" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-foreground">{{ $totalCards }}</p>
                        <p class="text-[11px] text-muted-foreground font-medium">Card</p>
                    </div>
                </div>

                {{-- Questions --}}
                <div class="flex items-center gap-3 bg-card rounded-md p-3.5 border border-border">
                    <div class="w-10 h-10 rounded-md bg-primary/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-primary">{{ $totalQuestions }}</p>
                        <p class="text-[11px] text-muted-foreground font-medium">Soal</p>
                    </div>
                </div>

                {{-- Orders --}}
                <div class="flex items-center gap-3 bg-card rounded-md p-3.5 border border-border">
                    <div class="w-10 h-10 rounded-md bg-gold-400/15 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-gold-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-gold-600">{{ $totalOrders }}</p>
                        <p class="text-[11px] text-muted-foreground font-medium">Pesanan</p>
                    </div>
                </div>

                {{-- Practice Sessions --}}
                <div class="flex items-center gap-3 bg-card rounded-md p-3.5 border border-border">
                    <div class="w-10 h-10 rounded-md bg-success-500/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-success-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17l-5.1-5.1m0 0L11.42 4.97m-5.1 5.1H21M3 3v18" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xl font-bold text-success-500">{{ $totalPracticeSessions }}</p>
                        <p class="text-[11px] text-muted-foreground font-medium">Sesi Praktek</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="flex flex-col sm:flex-row gap-3 anim-fade-in-up anim-delay-2">
        <a href="{{ route('admin.packages.detail', $package) }}" class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3.5 bg-card hover:bg-muted text-muted-foreground font-semibold rounded-md border border-border hover:border-border hover:shadow-md transition-all duration-200 active:scale-[0.98]">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
            </svg>
            Batal, Kembali
        </a>
        <form action="{{ route('admin.packages.destroy', $package) }}" method="POST" class="flex-1">
            @csrf
            @method('DELETE')
            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-5 py-3.5 bg-gradient-to-r from-danger-500 to-[#c0392b] hover:from-[#c0392b] hover:to-[#962d22] text-white font-semibold rounded-md shadow-lg shadow-red-500/20 hover:shadow-xl hover:shadow-red-500/30 transition-all duration-200 active:scale-[0.98]">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
                Ya, Hapus Paket
            </button>
        </form>
    </div>

    {{-- Additional Info --}}
    <div class="mt-6 p-4 bg-muted rounded-md border border-border anim-fade-in-up anim-delay-3">
        <div class="flex items-start gap-3">
            <svg class="w-4 h-4 text-muted-foreground mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
            </svg>
            <p class="text-xs text-muted-foreground leading-relaxed">Menghapus paket ini akan menghapus juga semua kartu soal, bank soal, riwayat pesanan, dan sesi praktek yang terkait. Pengguna yang telah membeli paket ini juga akan kehilangan akses.</p>
        </div>
    </div>
</div>
@endsection
