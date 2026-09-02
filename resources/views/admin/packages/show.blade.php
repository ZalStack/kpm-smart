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
                            @if($package->bidang)
                                <span class="badge bg-primary/10 text-primary border-0">📂 {{ $package->bidang }}</span>
                            @endif
                            @if($package->level)
                                <span class="badge bg-navy/10 text-navy border-0">🎯 {{ $package->level }}</span>
                            @endif
                            @if($package->kelas)
                                <span class="badge badge-info">🏫 {{ $package->kelas }}</span>
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
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4">
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

        {{-- Praktek --}}
        <div class="admin-card stagger-item group p-4 sm:p-5">
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

            {{-- Informasi Paket --}}
            <div class="admin-card stagger-item p-4 sm:p-5">
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-7 h-7 rounded-md bg-primary/10 flex items-center justify-center">
                        <svg class="w-4 h-4 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-foreground">Informasi Paket</h3>
                </div>

                <div class="space-y-0">
                    @if($package->bidang)
                        <div class="flex items-center justify-between py-2.5 border-b border-border">
                            <span class="text-xs sm:text-sm text-muted-foreground">Bidang</span>
                            <span class="text-xs sm:text-sm font-semibold text-foreground">{{ $package->bidang }}</span>
                        </div>
                    @endif
                    @if($package->level)
                        <div class="flex items-center justify-between py-2.5 border-b border-border">
                            <span class="text-xs sm:text-sm text-muted-foreground">Level</span>
                            <span class="text-xs sm:text-sm font-semibold text-foreground">{{ $package->level }}</span>
                        </div>
                    @endif
                    @if($package->kelas)
                        <div class="flex items-center justify-between py-2.5 border-b border-border">
                            <span class="text-xs sm:text-sm text-muted-foreground">Kelas</span>
                            <span class="text-xs sm:text-sm font-semibold text-foreground">{{ $package->kelas }}</span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between py-2.5 border-b border-border">
                        <span class="text-xs sm:text-sm text-muted-foreground">Harga</span>
                        <span class="text-sm font-bold text-foreground">Rp {{ number_format($package->price, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2.5">
                        <span class="text-xs sm:text-sm text-muted-foreground">Status</span>
                        @if($package->is_active)
                            <span class="badge badge-success">Aktif</span>
                        @else
                            <span class="badge badge-neutral">Nonaktif</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Jadwal Pengerjaan --}}
            <div class="admin-card stagger-item p-4 sm:p-5">
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-7 h-7 rounded-md bg-gold-400/10 flex items-center justify-center">
                        <svg class="w-4 h-4 text-gold-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-foreground">Jadwal Pengerjaan</h3>
                </div>

                @php $scheduleStatus = $package->schedule_status; @endphp
                <div class="mb-3">
                    @if($scheduleStatus === 'active')
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full bg-success-500/10 text-success-500">
                            <span class="w-1.5 h-1.5 rounded-full bg-success-500 animate-pulse"></span> Sedang Berlangsung
                        </span>
                    @elseif($scheduleStatus === 'upcoming')
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full bg-gold-400/15 text-gold-600">⏳ Akan Datang</span>
                    @elseif($scheduleStatus === 'expired')
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full bg-muted text-muted-foreground">⛔ Berakhir</span>
                    @else
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full bg-primary/10 text-primary">♾️ Tanpa Batasan</span>
                    @endif
                </div>

                @if($package->start_date || $package->end_date)
                    <div class="space-y-1 text-xs text-muted-foreground">
                        @if($package->start_date)
                            <p>📅 Mulai: <span class="font-semibold text-foreground">{{ $package->start_date->translatedFormat('d M Y') }}</span>
                                @if($package->start_time) · {{ substr($package->start_time, 0, 5) }} WIB @endif
                            </p>
                        @endif
                        @if($package->end_date)
                            <p>📅 Berakhir: <span class="font-semibold text-foreground">{{ $package->end_date->translatedFormat('d M Y') }}</span>
                                @if($package->end_time) · {{ substr($package->end_time, 0, 5) }} WIB @endif
                            </p>
                        @endif
                    </div>
                @else
                    <p class="text-xs text-muted-foreground">Tidak ada batasan waktu pengerjaan.</p>
                @endif

                <div class="mt-3 pt-3 border-t border-border">
                    <a href="{{ route('admin.packages.edit.informasi', $package) }}" class="text-xs font-semibold text-primary hover:underline">✏️ Edit Jadwal</a>
                </div>
            </div>

            {{-- Realtime Toggle Pengaturan Soal --}}
            <div class="admin-card stagger-item p-4 sm:p-5" id="settingsPanel">
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-7 h-7 rounded-md bg-navy/10 flex items-center justify-center">
                        <svg class="w-4 h-4 text-navy" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-bold text-foreground">Pengaturan Soal</h3>
                    <span id="settingsSaving" class="ml-auto text-xs text-muted-foreground hidden">⏳ Menyimpan...</span>
                    <span id="settingsSaved" class="ml-auto text-xs text-success-500 hidden">✅ Tersimpan</span>
                </div>

                <div class="space-y-3">
                    {{-- Toggle: Kunci Jawaban --}}
                    <div class="flex items-center justify-between p-3 bg-muted/50 rounded-md border border-border">
                        <div class="flex-1 mr-3">
                            <p class="text-xs font-semibold text-foreground">Kunci Jawaban (Benar/Salah)</p>
                            <p class="text-[10px] text-muted-foreground mt-0.5">User bisa lihat jawaban benar setelah mengerjakan</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                            <input type="checkbox" class="sr-only peer js-toggle-setting"
                                   data-field="show_answer_key"
                                   data-package="{{ $package->id }}"
                                   {{ $package->show_answer_key ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-border rounded-full peer peer-checked:bg-success-500 transition-colors duration-300"></div>
                            <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-card rounded-full shadow transition-transform duration-300 peer-checked:translate-x-5"></div>
                        </label>
                    </div>

                    {{-- Toggle: Pembahasan --}}
                    <div class="flex items-center justify-between p-3 bg-muted/50 rounded-md border border-border">
                        <div class="flex-1 mr-3">
                            <p class="text-xs font-semibold text-foreground">Pembahasan</p>
                            <p class="text-[10px] text-muted-foreground mt-0.5">User bisa lihat pembahasan soal setelah mengerjakan</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                            <input type="checkbox" class="sr-only peer js-toggle-setting"
                                   data-field="show_explanation"
                                   data-package="{{ $package->id }}"
                                   {{ $package->show_explanation ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-border rounded-full peer peer-checked:bg-success-500 transition-colors duration-300"></div>
                            <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-card rounded-full shadow transition-transform duration-300 peer-checked:translate-x-5"></div>
                        </label>
                    </div>

                    {{-- Toggle: Skor --}}
                    <div class="flex items-center justify-between p-3 bg-muted/50 rounded-md border border-border">
                        <div class="flex-1 mr-3">
                            <p class="text-xs font-semibold text-foreground">Skor / Nilai</p>
                            <p class="text-[10px] text-muted-foreground mt-0.5">User bisa lihat skor setelah mengerjakan</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                            <input type="checkbox" class="sr-only peer js-toggle-setting"
                                   data-field="show_score"
                                   data-package="{{ $package->id }}"
                                   {{ $package->show_score ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-border rounded-full peer peer-checked:bg-success-500 transition-colors duration-300"></div>
                            <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-card rounded-full shadow transition-transform duration-300 peer-checked:translate-x-5"></div>
                        </label>
                    </div>

                    {{-- Toggle: Status Aktif --}}
                    <div class="flex items-center justify-between p-3 bg-muted/50 rounded-md border border-border">
                        <div class="flex-1 mr-3">
                            <p class="text-xs font-semibold text-foreground">Paket Aktif</p>
                            <p class="text-[10px] text-muted-foreground mt-0.5">Paket bisa dilihat dan dikerjakan user</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                            <input type="checkbox" class="sr-only peer js-toggle-setting"
                                   data-field="is_active"
                                   data-package="{{ $package->id }}"
                                   {{ $package->is_active ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-border rounded-full peer peer-checked:bg-success-500 transition-colors duration-300"></div>
                            <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-card rounded-full shadow transition-transform duration-300 peer-checked:translate-x-5"></div>
                        </label>
                    </div>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const savingEl = document.getElementById('settingsSaving');
    const savedEl  = document.getElementById('settingsSaved');
    let savedTimer = null;

    function showSaved() {
        if (savingEl) savingEl.classList.add('hidden');
        if (savedEl) {
            savedEl.classList.remove('hidden');
            clearTimeout(savedTimer);
            savedTimer = setTimeout(() => savedEl.classList.add('hidden'), 2500);
        }
    }

    function showSaving() {
        if (savedEl) savedEl.classList.add('hidden');
        if (savingEl) savingEl.classList.remove('hidden');
    }

    // Realtime toggle pengaturan (show_answer_key, show_explanation, show_score, is_active)
    document.querySelectorAll('.js-toggle-setting').forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            const field     = this.dataset.field;
            const packageId = this.dataset.package;
            const newVal    = this.checked;
            showSaving();

            fetch(`/admin/packages/${packageId}/ajax/toggle-setting`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ field: field }),
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showSaved();
                } else {
                    // Revert toggle on error
                    this.checked = !newVal;
                    if (savingEl) savingEl.classList.add('hidden');
                    alert('Gagal menyimpan pengaturan: ' + (data.message ?? 'Error'));
                }
            })
            .catch(() => {
                this.checked = !newVal;
                if (savingEl) savingEl.classList.add('hidden');
                alert('Gagal terhubung ke server. Coba lagi.');
            });
        });
    });
});
</script>
@endpush
@endsection
