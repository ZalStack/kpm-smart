{{-- admin/packages/edit/cards.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Card - ' . $package->title)
@section('header-title', '📋 Kelola Card Latihan')
@section('header-sub', 'Atur card dan kelompok soal untuk paket ' . $package->title)

@section('content')
<div class="space-y-6 pb-10">
    {{-- Package Header --}}
    <div class="bg-gradient-to-r from-navy via-[#1a2070] to-navy-light rounded-lg overflow-hidden shadow-lg shadow-navy/20 mb-6">
        <div class="relative px-4 sm:px-6 py-4 sm:py-5">
            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="flex items-center gap-4">
                    @if($package->thumbnail)
                        <img src="{{ asset('storage/' . $package->thumbnail) }}" alt="Thumbnail"
                             class="w-16 h-16 rounded-lg object-cover ring-4 ring-white/20 shadow-xl flex-shrink-0">
                    @else
                        <div class="w-16 h-16 rounded-lg bg-white/10 ring-4 ring-white/20 flex items-center justify-center text-3xl flex-shrink-0 shadow-xl">📚</div>
                    @endif
                    <div>
                        <h1 class="text-xl font-bold text-white">{{ $package->title }}</h1>
                        <p class="text-sm text-white/70 line-clamp-1">{{ $package->description }}</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 sm:ml-auto">
                    <a href="{{ route('admin.packages.edit.informasi', $package) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md bg-white/10 hover:bg-white/20 text-white text-sm font-medium transition backdrop-blur">📄 Info</a>
                    <a href="{{ route('admin.packages.edit.cards', $package) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md bg-white/20 text-white text-sm font-semibold transition backdrop-blur ring-2 ring-white/30">📋 Card</a>
                    <a href="{{ route('admin.packages.edit.questions', $package) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md bg-white/10 hover:bg-white/20 text-white text-sm font-medium transition backdrop-blur">📝 Soal</a>
                    <a href="{{ route('admin.packages.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-md bg-white/10 hover:bg-white/20 text-white text-sm font-medium transition backdrop-blur">← Kembali</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
        <div class="admin-card p-4 text-center">
            <p class="text-2xl font-bold text-foreground">{{ count($package->cards ?? []) }}</p>
            <p class="text-xs text-muted-foreground font-medium uppercase tracking-wider">Total Card</p>
        </div>
        <div class="admin-card p-4 text-center">
            <p class="text-2xl font-bold text-navy-light">{{ count($package->questions ?? []) }}</p>
            <p class="text-xs text-muted-foreground font-medium uppercase tracking-wider">Total Soal</p>
        </div>
        <div class="admin-card p-4 text-center col-span-2 sm:col-span-1">
            <p class="text-2xl font-bold text-gold-400">{{ count($package->cards ?? []) > 0 ? floor(count($package->questions ?? []) / count($package->cards ?? [])) : 0 }}</p>
            <p class="text-xs text-muted-foreground font-medium uppercase tracking-wider">Rata-rata Soal/Card</p>
        </div>
    </div>

    {{-- Add Card Form --}}
    <div class="admin-card overflow-hidden mb-6">
        <div class="p-4 sm:p-6 border-b border-border bg-gradient-to-r from-gold-400/10 to-transparent">
            <h3 class="text-sm font-bold text-foreground flex items-center gap-2">➕ Tambah Card Baru</h3>
        </div>
        <form action="{{ route('admin.packages.add-card', $package->id) }}" method="POST" class="p-4 sm:p-6">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="form-group">
                    <label class="form-label">Judul Card <span class="text-danger-500">*</span></label>
                    <input type="text" name="card_title" required placeholder="mis. Matematika Dasar"
                           class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi <span class="text-danger-500">*</span></label>
                    <input type="text" name="card_description" required placeholder="Deskripsi singkat card"
                           class="form-input">
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <button type="submit" class="btn-success flex items-center gap-2">
                    <span>➕</span> Tambah Card
                </button>
            </div>
        </form>
    </div>

    {{-- Cards List --}}
    <div class="admin-card overflow-hidden">
        <div class="p-4 border-b border-border bg-muted/50 flex items-center justify-between">
            <h3 class="text-sm font-bold text-foreground flex items-center gap-2">📋 Daftar Card</h3>
            <span class="text-xs text-muted-foreground">{{ count($package->cards ?? []) }} card</span>
        </div>

        @if(empty($package->cards))
            <div class="p-6 md:p-8 text-center">
                <div class="text-5xl mb-3 opacity-50">📋</div>
                <h3 class="text-lg font-semibold text-muted-foreground">Belum Ada Card</h3>
                <p class="text-sm text-muted-foreground mt-1">Tambahkan card pertama Anda menggunakan form di atas</p>
            </div>
        @else
            <div class="divide-y divide-border">
                @foreach($package->cards ?? [] as $card)
                    @php
                        $questionCount = isset($questionsByCard[$card['id']]) ? count($questionsByCard[$card['id']]) : 0;
                    @endphp
                    <div class="p-4 sm:p-5 hover:bg-muted/50 transition group">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                            <div class="flex items-center gap-3 flex-1 min-w-0">
                                <div class="w-10 h-10 rounded-md bg-gradient-to-br from-gold-400/20 to-[#ffd54f]/20 flex items-center justify-center text-lg flex-shrink-0">📋</div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h4 class="font-semibold text-foreground truncate">{{ $card['title'] }}</h4>
                                        <span class="text-[10px] font-semibold px-2.5 py-0.5 rounded-full bg-primary/10 text-primary">{{ $questionCount }} soal</span>
                                    </div>
                                    <p class="text-sm text-muted-foreground truncate">{{ $card['description'] }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] text-muted-foreground">{{ \Carbon\Carbon::parse($card['created_at'] ?? null)->diffForHumans() ?? 'Baru saja' }}</span>
                                <form action="{{ route('admin.packages.remove-card', ['package' => $package->id, 'cardId' => $card['id']]) }}"
                                      method="POST"
                                      onsubmit="return confirm('Hapus Card &quot;{{ $card['title'] }}&quot; beserta {{ $questionCount }} soal di dalamnya?')"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn-danger w-8 h-8 flex items-center justify-center opacity-50 group-hover:opacity-100"
                                            title="Hapus Card">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Info --}}
    <div class="mt-6 p-4 bg-primary/10 rounded-md border border-primary/20 text-sm text-muted-foreground">
        <p class="font-medium text-navy-light flex items-center gap-2">💡 Tips</p>
        <ul class="list-disc list-inside text-xs space-y-0.5 mt-1 text-muted-foreground">
            <li>Card digunakan untuk mengelompokkan soal-soal berdasarkan topik atau bab</li>
            <li>Setiap soal harus memiliki card yang terkait</li>
            <li>Hapus card akan menghapus semua soal di dalamnya</li>
        </ul>
    </div>
</div>

@if(session('success'))
    <div class="fixed bottom-4 right-4 bg-success-500 text-white px-6 py-3 rounded-md shadow-lg z-50 animate-slideIn">
        {{ session('success') }}
    </div>
    <style>
        @keyframes slideIn {
            from { transform: translateY(100px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .animate-slideIn {
            animation: slideIn 0.3s ease-out;
        }
    </style>
@endif

@if($errors->any())
    <div class="fixed bottom-4 right-4 bg-danger-500 text-white px-6 py-3 rounded-md shadow-lg z-50 animate-slideIn">
        {{ $errors->first() }}
    </div>
@endif
@endsection
