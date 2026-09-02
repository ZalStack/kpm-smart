{{-- packages/show.blade.php --}}
@extends('layouts.app')

@section('title', $package->title)

@section('content')
<div class="space-y-6 show-stagger">

    {{-- HERO HEADER --}}
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6">
        <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 md:gap-3 mb-3">
                <a href="{{ route('packages.index') }}" class="text-primary hover:text-primary/80 transition text-sm md:text-base font-medium">
                    ← Kembali
                </a>
                <span class="text-muted-foreground">|</span>
                <h1 class="text-xl md:text-2xl font-bold text-foreground truncate">{{ $package->title }}</h1>
            </div>
            <p class="text-muted-foreground text-sm md:text-base">{{ $package->description }}</p>
            <div class="flex flex-wrap items-center gap-2 mt-3">
                @if($package->bidang)
                    <span class="inline-flex items-center gap-1 text-xs md:text-sm bg-primary/10 text-primary font-semibold px-3 py-1 rounded-full">
                        📂 {{ $package->bidang }}
                    </span>
                @endif
                @if($package->level)
                    <span class="inline-flex items-center gap-1 text-xs md:text-sm bg-navy/10 text-foreground font-semibold px-3 py-1 rounded-full">
                        🎯 {{ $package->level }}
                    </span>
                @endif
                @if($package->kelas)
                    <span class="inline-flex items-center gap-1 text-xs md:text-sm bg-gold-400/15 text-gold-600 font-semibold px-3 py-1 rounded-full">
                        🏫 {{ $package->kelas }}
                    </span>
                @endif

                {{-- Schedule Status Badge --}}
                @php $scheduleStatus = $package->schedule_status; @endphp
                @if($scheduleStatus === 'active')
                    <span class="inline-flex items-center gap-1 text-xs md:text-sm bg-success-500/10 text-success-500 font-semibold px-3 py-1 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-success-500 animate-pulse"></span> Sedang Berlangsung
                    </span>
                @elseif($scheduleStatus === 'upcoming')
                    <span class="inline-flex items-center gap-1 text-xs md:text-sm bg-gold-400/15 text-gold-600 font-semibold px-3 py-1 rounded-full">
                        ⏳ Akan Datang
                    </span>
                @elseif($scheduleStatus === 'expired')
                    <span class="inline-flex items-center gap-1 text-xs md:text-sm bg-muted text-muted-foreground font-semibold px-3 py-1 rounded-full">
                        ⛔ Jadwal Berakhir
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 text-xs md:text-sm bg-primary/10 text-primary font-semibold px-3 py-1 rounded-full">
                        ♾️ Tanpa Batasan Waktu
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- JADWAL BERAKHIR WARNING --}}
    @if($scheduleStatus === 'expired')
        <div class="bg-danger-50 border border-danger-200 rounded-lg p-4 md:p-5">
            <div class="flex items-start gap-3">
                <div class="text-xl flex-shrink-0">⛔</div>
                <div>
                    <h3 class="font-bold text-danger-700 text-sm md:text-base">Jadwal Pengerjaan Telah Berakhir</h3>
                    <p class="text-danger-600 text-xs md:text-sm mt-1">Paket ini sudah tidak bisa dikerjakan. Jadwal pengerjaan: {{ $package->schedule_label }}</p>
                </div>
            </div>
        </div>
    @elseif($scheduleStatus === 'upcoming')
        <div class="bg-gold-400/10 border border-gold-400/30 rounded-lg p-4 md:p-5">
            <div class="flex items-start gap-3">
                <div class="text-xl flex-shrink-0">⏳</div>
                <div>
                    <h3 class="font-bold text-gold-600 text-sm md:text-base">Jadwal Belum Dimulai</h3>
                    <p class="text-gold-600 text-xs md:text-sm mt-1">Paket ini bisa dikerjakan mulai: {{ $package->schedule_label }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- MAIN GRID --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- LEFT: DETAIL INFO --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Thumbnail --}}
            @if($package->thumbnail)
                <div class="bg-card rounded-lg overflow-hidden shadow-sm border border-border">
                    <img src="{{ asset('storage/' . $package->thumbnail) }}" alt="{{ $package->title }}"
                         class="w-full h-48 sm:h-64 md:h-72 object-cover">
                </div>
            @endif

            {{-- Ringkasan Stat --}}
            <div class="grid grid-cols-2 gap-3 md:gap-4">
                <div class="bg-card rounded-lg p-4 shadow-sm border border-border text-center">
                    <div class="text-2xl md:text-3xl font-bold text-foreground">{{ $totalCards }}</div>
                    <div class="text-[11px] md:text-xs text-muted-foreground font-medium mt-1">📋 Card Latihan</div>
                </div>
                <div class="bg-card rounded-lg p-4 shadow-sm border border-border text-center">
                    <div class="text-2xl md:text-3xl font-bold text-primary">{{ $totalQuestions }}</div>
                    <div class="text-[11px] md:text-xs text-muted-foreground font-medium mt-1">❓ Total Soal</div>
                </div>
            </div>

            {{-- Jadwal Pengerjaan --}}
            <div class="bg-card rounded-lg shadow-sm border border-border overflow-hidden">
                <div class="p-4 md:p-5 border-b border-border">
                    <h2 class="text-sm md:text-base font-bold text-foreground flex items-center gap-2">📅 Jadwal Pengerjaan</h2>
                </div>
                <div class="p-4 md:p-5">
                    @if($package->start_date || $package->end_date)
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-4">
                            @if($package->start_date)
                                <div class="bg-muted/50 rounded-md p-3">
                                    <p class="text-[10px] text-muted-foreground font-medium uppercase tracking-wider mb-1">Mulai</p>
                                    <p class="text-sm font-semibold text-foreground">{{ $package->start_date->translatedFormat('d M Y') }}</p>
                                    @if($package->start_time)
                                        <p class="text-xs text-muted-foreground mt-0.5">🕐 {{ substr($package->start_time, 0, 5) }} WIB</p>
                                    @endif
                                </div>
                            @endif
                            @if($package->end_date)
                                <div class="bg-muted/50 rounded-md p-3">
                                    <p class="text-[10px] text-muted-foreground font-medium uppercase tracking-wider mb-1">Berakhir</p>
                                    <p class="text-sm font-semibold text-foreground">{{ $package->end_date->translatedFormat('d M Y') }}</p>
                                    @if($package->end_time)
                                        <p class="text-xs text-muted-foreground mt-0.5">🕐 {{ substr($package->end_time, 0, 5) }} WIB</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="flex items-center gap-2 text-sm text-muted-foreground">
                            <span>♾️</span>
                            <span>Tanpa batasan waktu — bisa dikerjakan kapan saja</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Pengaturan Soal --}}
            <div class="bg-card rounded-lg shadow-sm border border-border overflow-hidden">
                <div class="p-4 md:p-5 border-b border-border">
                    <h2 class="text-sm md:text-base font-bold text-foreground flex items-center gap-2">⚙️ Pengaturan Soal</h2>
                </div>
                <div class="p-4 md:p-5 space-y-2">
                    <div class="flex items-center justify-between py-2 border-b border-border">
                        <span class="text-xs md:text-sm text-muted-foreground">Kunci Jawaban (Benar/Salah)</span>
                        @if($package->show_answer_key)
                            <span class="inline-flex items-center gap-1 text-[10px] md:text-xs bg-success-500/15 text-success-500 font-semibold px-2.5 py-1 rounded-full">✅ Ditampilkan</span>
                        @else
                            <span class="inline-flex items-center gap-1 text-[10px] md:text-xs bg-muted text-muted-foreground font-semibold px-2.5 py-1 rounded-full">🔒 Disembunyikan</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between py-2 border-b border-border">
                        <span class="text-xs md:text-sm text-muted-foreground">Pembahasan</span>
                        @if($package->show_explanation)
                            <span class="inline-flex items-center gap-1 text-[10px] md:text-xs bg-success-500/15 text-success-500 font-semibold px-2.5 py-1 rounded-full">✅ Ditampilkan</span>
                        @else
                            <span class="inline-flex items-center gap-1 text-[10px] md:text-xs bg-muted text-muted-foreground font-semibold px-2.5 py-1 rounded-full">🔒 Disembunyikan</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-xs md:text-sm text-muted-foreground">Skor / Nilai</span>
                        @if($package->show_score)
                            <span class="inline-flex items-center gap-1 text-[10px] md:text-xs bg-success-500/15 text-success-500 font-semibold px-2.5 py-1 rounded-full">✅ Ditampilkan</span>
                        @else
                            <span class="inline-flex items-center gap-1 text-[10px] md:text-xs bg-muted text-muted-foreground font-semibold px-2.5 py-1 rounded-full">🔒 Disembunyikan</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Matching Info User (jika login) --}}
            @auth
                @php
                    $user = auth()->user();
                    $matchBidang = $user->bidang && $package->bidang && mb_strtolower($user->bidang) === mb_strtolower($package->bidang);
                    $matchLevel  = $user->level  && $package->level  && mb_strtolower($user->level)  === mb_strtolower($package->level);
                @endphp
                @if($package->bidang || $package->level)
                    <div class="bg-card rounded-lg shadow-sm border border-border overflow-hidden">
                        <div class="p-4 md:p-5 border-b border-border">
                            <h2 class="text-sm md:text-base font-bold text-foreground flex items-center gap-2">👤 Kesesuaian Profil Kamu</h2>
                        </div>
                        <div class="p-4 md:p-5 space-y-2">
                            @if($package->bidang)
                                <div class="flex items-center justify-between py-2 {{ $package->level ? 'border-b border-border' : '' }}">
                                    <div>
                                        <span class="text-xs md:text-sm text-muted-foreground">Bidang Paket</span>
                                        <span class="ml-2 text-xs font-semibold text-foreground">{{ $package->bidang }}</span>
                                    </div>
                                    @if($user->bidang)
                                        @if($matchBidang)
                                            <span class="inline-flex items-center gap-1 text-[10px] md:text-xs bg-success-500/10 text-success-500 font-semibold px-2.5 py-1 rounded-full">✅ Sesuai</span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[10px] md:text-xs bg-gold-400/15 text-gold-600 font-semibold px-2.5 py-1 rounded-full">⚠️ Berbeda (kamu: {{ $user->bidang }})</span>
                                        @endif
                                    @else
                                        <span class="text-[10px] md:text-xs text-muted-foreground">Bidang profil belum diisi</span>
                                    @endif
                                </div>
                            @endif
                            @if($package->level)
                                <div class="flex items-center justify-between py-2">
                                    <div>
                                        <span class="text-xs md:text-sm text-muted-foreground">Level Paket</span>
                                        <span class="ml-2 text-xs font-semibold text-foreground">{{ $package->level }}</span>
                                    </div>
                                    @if($user->level)
                                        @if($matchLevel)
                                            <span class="inline-flex items-center gap-1 text-[10px] md:text-xs bg-success-500/10 text-success-500 font-semibold px-2.5 py-1 rounded-full">✅ Sesuai</span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-[10px] md:text-xs bg-gold-400/15 text-gold-600 font-semibold px-2.5 py-1 rounded-full">⚠️ Berbeda (kamu: {{ $user->level }})</span>
                                        @endif
                                    @else
                                        <span class="text-[10px] md:text-xs text-muted-foreground">Level profil belum diisi</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            @endauth

            {{-- Status Banner --}}
            @auth
                @php
                    $completedSession = \App\Models\PracticeSession::where('user_id', auth()->id())
                        ->where('package_id', $package->id)
                        ->where('status', 'completed')
                        ->first();
                    $inProgressSession = \App\Models\PracticeSession::where('user_id', auth()->id())
                        ->where('package_id', $package->id)
                        ->where('status', 'in_progress')
                        ->first();
                @endphp

                @if($completedSession)
                    <div class="bg-success-50 border border-success-200 rounded-lg p-4 md:p-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-success-100 flex items-center justify-center flex-shrink-0">✅</div>
                            <div class="flex-1">
                                <h3 class="font-bold text-success-700 text-sm md:text-base">Sudah Dikerjakan</h3>
                                @if($package->show_score)
                                    <p class="text-success-600 text-xs md:text-sm">Skor: <strong>{{ number_format($completedSession->total_score, 0) }}%</strong> · Benar {{ $completedSession->correct_answer }}/{{ $completedSession->total_question }}</p>
                                @else
                                    <p class="text-success-600 text-xs md:text-sm">Kamu telah menyelesaikan latihan ini.</p>
                                @endif
                            </div>
                            <a href="{{ route('practice.show', $completedSession->id) }}" class="bg-success-500 text-white text-xs md:text-sm px-4 py-2 rounded-lg hover:bg-success-600 transition flex-shrink-0 font-semibold">
                                Lihat Hasil →
                            </a>
                        </div>
                    </div>
                @elseif($inProgressSession)
                    <div class="bg-gold-400/10 border border-gold-400/30 rounded-lg p-4 md:p-5">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gold-400/20 flex items-center justify-center flex-shrink-0">⏳</div>
                            <div class="flex-1">
                                <h3 class="font-bold text-gold-600 text-sm md:text-base">Sedang Berlangsung</h3>
                                <p class="text-gold-600 text-xs md:text-sm">Kamu memiliki latihan yang belum selesai</p>
                            </div>
                            <a href="{{ route('practice.show', $inProgressSession->id) }}" class="bg-gold-400 text-foreground text-xs md:text-sm px-4 py-2 rounded-lg hover:bg-gold-500 transition flex-shrink-0 font-semibold">
                                Lanjutkan →
                            </a>
                        </div>
                    </div>
                @endif
            @endauth

            {{-- CARD LATIHAN --}}
            <div>
                <h2 class="text-lg md:text-xl font-bold text-foreground mb-4">📋 Card Latihan</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-4">
                    @php $cards = $package->cards ?? []; @endphp
                    @forelse($cards as $card)
                        @php
                            $cardQuestionCount = collect($package->questions ?? [])->where('card_id', $card['id'])->count();
                        @endphp
                        <div class="bg-card rounded-lg p-4 md:p-5 shadow-sm border-l-4 border-primary hover:shadow-lg transition-all duration-300 border border-border hover:-translate-y-0.5">
                            <h3 class="font-bold text-foreground text-sm md:text-base">{{ $card['title'] }}</h3>
                            <p class="text-xs md:text-sm text-muted-foreground mt-1 line-clamp-2">{{ $card['description'] }}</p>
                            <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                                <span class="text-xs text-muted-foreground">❓ {{ $cardQuestionCount }} soal</span>
                                @auth
                                    @if(!empty($completedSession))
                                        <span class="text-xs text-success-500 font-semibold">✅ Selesai</span>
                                    @elseif($scheduleStatus === 'expired' || $scheduleStatus === 'upcoming')
                                        <span class="text-xs text-muted-foreground">{{ $scheduleStatus === 'expired' ? '⛔ Berakhir' : '⏳ Belum mulai' }}</span>
                                    @else
                                        <form action="{{ route('practice.start', $package->id) }}" method="POST" class="flex-shrink-0">
                                            @csrf
                                            <input type="hidden" name="card_id" value="{{ $card['id'] }}">
                                            <button type="submit" class="bg-success-500 text-white text-xs md:text-sm px-3 md:px-4 py-1.5 rounded-lg hover:bg-success-600 transition">
                                                Kerjakan →
                                            </button>
                                        </form>
                                    @endif
                                @endauth
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-8 text-muted-foreground text-sm md:text-base">
                            Belum ada card latihan tersedia
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- RIGHT: SIDEBAR --}}
        <div class="lg:col-span-1">
            <div class="bg-card rounded-lg p-4 md:p-6 shadow-sm border border-border lg:sticky lg:top-20">
                <div class="text-center">
                    @auth
                        @if(!empty($completedSession))
                            <div class="text-success-500 font-bold text-base md:text-lg mb-2">✅ Selesai</div>
                            <p class="text-xs md:text-sm text-muted-foreground mb-4">Kamu sudah menyelesaikan latihan ini</p>
                            <a href="{{ route('practice.show', $completedSession->id) }}" class="block w-full bg-success-500 text-white py-2.5 rounded-lg font-semibold hover:bg-success-600 transition text-sm md:text-base">
                                📊 Lihat Hasil
                            </a>
                        @elseif($scheduleStatus === 'expired')
                            <div class="text-muted-foreground font-bold text-base md:text-lg mb-2">⛔ Berakhir</div>
                            <p class="text-xs md:text-sm text-muted-foreground mb-4">Jadwal pengerjaan paket ini telah berakhir.</p>
                        @elseif($scheduleStatus === 'upcoming')
                            <div class="text-gold-600 font-bold text-base md:text-lg mb-2">⏳ Akan Datang</div>
                            <p class="text-xs md:text-sm text-muted-foreground mb-4">Paket ini belum bisa dikerjakan. Tunggu jadwal mulai.</p>
                            @if($package->start_date)
                                <div class="bg-gold-400/10 rounded-lg p-3 text-xs text-gold-600 font-semibold">
                                    Mulai: {{ $package->start_date->translatedFormat('d M Y') }}
                                    @if($package->start_time) · {{ substr($package->start_time, 0, 5) }} WIB @endif
                                </div>
                            @endif
                        @else
                            <div class="text-success-500 font-bold text-base md:text-lg mb-2">✅ Tersedia</div>
                            <p class="text-xs md:text-sm text-muted-foreground mb-4">Paket ini dapat diakses. Hanya bisa dikerjakan 1 kali per card.</p>

                            <form action="{{ route('practice.start', $package->id) }}" method="POST" class="mt-4">
                                @csrf
                                <div class="space-y-3" id="cardSelect">
                                    @php $cards = $package->cards ?? []; @endphp
                                    @if(count($cards) > 0)
                                        <label class="block text-xs font-medium text-card-foreground text-left">Pilih Card</label>
                                        <select name="card_id" class="w-full px-3 py-2.5 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm md:text-base" required>
                                            @foreach($cards as $card)
                                                @php $cardQuestionCount = collect($package->questions ?? [])->where('card_id', $card['id'])->count(); @endphp
                                                <option value="{{ $card['id'] }}">{{ $card['title'] }} ({{ $cardQuestionCount }} soal)</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="hidden" name="card_id" value="">
                                    @endif
                                </div>
                                <button type="submit" class="w-full mt-4 bg-success-500 text-white py-2.5 rounded-lg font-semibold hover:bg-success-600 transition text-sm md:text-base">
                                    🚀 Mulai Latihan
                                </button>
                            </form>
                        @endif
                    @else
                        <div class="text-navy-light font-bold text-base md:text-lg mb-2">🔑 Masuk Dulu</div>
                        <p class="text-xs md:text-sm text-muted-foreground mb-4">Silakan masuk untuk mengerjakan paket ini.</p>
                        <a href="{{ route('login') }}" class="block w-full bg-navy-light text-white py-2.5 rounded-lg font-semibold hover:bg-navy transition text-sm md:text-base">
                            🔑 Masuk / Daftar
                        </a>
                    @endauth
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
