{{-- packages/show.blade.php --}}
@extends('layouts.app')

@section('title', $package->title)

@section('content')
<div class="space-y-6 show-stagger">

    {{-- ============ HERO HEADER ============ --}}
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
                @if($package->kelas)
                    <span class="inline-flex items-center gap-1 text-xs md:text-sm bg-primary/10 text-primary font-semibold px-3 py-1 rounded-full">
                        🏫 {{ $package->kelas }}
                    </span>
                @endif
                @if($package->jenjang)
                    <span class="inline-flex items-center gap-1 text-xs md:text-sm bg-foreground/10 text-foreground font-semibold px-3 py-1 rounded-full">
                        🎓 {{ $package->jenjang }}
                    </span>
                @endif
                <span class="inline-flex items-center gap-1 text-xs md:text-sm bg-primary/10 text-primary font-semibold px-3 py-1 rounded-full">
                    ⏳ Masa Aktif {{ $package->membership_duration_label }}
                </span>
                @if($package->hasDiscount())
                    <span class="inline-flex items-center gap-1 text-xs md:text-sm bg-danger-500 text-white font-semibold px-3 py-1 rounded-full">
                        🔥 Diskon {{ $package->discount_percent }}%
                    </span>
                @endif
                @if($package->is_pay_what_you_want)
                    <span class="inline-flex items-center gap-1 text-xs md:text-sm bg-success-500 text-white font-semibold px-3 py-1 rounded-full">
                        💝 Seikhlasnya
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- ============ MAIN GRID ============ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ======= LEFT: DETAIL INFO ======= --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Thumbnail --}}
            @if($package->thumbnail)
                <div class="bg-card rounded-lg overflow-hidden shadow-sm border border-border">
                    <img src="{{ asset('storage/' . $package->thumbnail) }}" alt="{{ $package->title }}"
                         class="w-full h-48 sm:h-64 md:h-72 object-cover">
                </div>
            @endif

            {{-- Ringkasan Stat --}}
            <div class="grid grid-cols-3 gap-3 md:gap-4">
                <div class="bg-card rounded-lg p-4 shadow-sm border border-border text-center">
                    <div class="text-2xl md:text-3xl font-bold text-foreground">{{ $totalCards }}</div>
                    <div class="text-[11px] md:text-xs text-muted-foreground font-medium mt-1">📋 Card Latihan</div>
                </div>
                <div class="bg-card rounded-lg p-4 shadow-sm border border-border text-center">
                    <div class="text-2xl md:text-3xl font-bold text-primary">{{ $totalQuestions }}</div>
                    <div class="text-[11px] md:text-xs text-muted-foreground font-medium mt-1">❓ Total Soal</div>
                </div>
                <div class="bg-card rounded-lg p-4 shadow-sm border border-border text-center">
                    <div class="text-2xl md:text-3xl font-bold text-success-500">{{ $videos->count() }}</div>
                    <div class="text-[11px] md:text-xs text-muted-foreground font-medium mt-1">🎬 Video</div>
                </div>
            </div>

            {{-- Informasi Harga --}}
            <div class="bg-card rounded-lg shadow-sm border border-border overflow-hidden">
                <div class="p-4 md:p-5 border-b border-border">
                    <h2 class="text-sm md:text-base font-bold text-foreground flex items-center gap-2">💰 Informasi Harga</h2>
                </div>
                <div class="p-4 md:p-5">
                    @if($package->is_pay_what_you_want)
                        <div class="flex items-center justify-between py-2.5 border-b border-border">
                            <span class="text-xs md:text-sm text-muted-foreground">Tipe Harga</span>
                            <span class="text-xs md:text-sm font-semibold text-success-500">💝 Bayar Seikhlasnya</span>
                        </div>
                        @if($package->min_pay_amount > 0)
                            <div class="flex items-center justify-between py-2.5">
                                <span class="text-xs md:text-sm text-muted-foreground">Minimum Bayar</span>
                                <span class="text-xs md:text-sm font-semibold text-card-foreground">Rp {{ number_format($package->min_pay_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    @else
                        @if($package->hasDiscount())
                            <div class="flex items-center justify-between py-2.5 border-b border-border">
                                <span class="text-xs md:text-sm text-muted-foreground">Harga Normal</span>
                                <span class="text-xs md:text-sm text-muted-foreground line-through">Rp {{ number_format($package->price, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between py-2.5 border-b border-border">
                                <span class="text-xs md:text-sm text-muted-foreground">Harga Diskon</span>
                                <span class="text-xs md:text-sm font-bold text-danger-500">Rp {{ number_format($package->discount_price, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between py-2.5">
                                <span class="text-xs md:text-sm text-muted-foreground">Hemat</span>
                                <span class="inline-block text-[10px] md:text-xs bg-danger-500 text-white px-2 py-0.5 rounded-full font-bold">{{ $package->discount_percent }}%</span>
                            </div>
                        @else
                            <div class="flex items-center justify-between py-2.5">
                                <span class="text-xs md:text-sm text-muted-foreground">Harga</span>
                                <span class="text-sm md:text-base font-bold text-foreground">Rp {{ number_format($package->price, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Pengaturan Latihan --}}
            <div class="bg-card rounded-lg shadow-sm border border-border overflow-hidden">
                <div class="p-4 md:p-5 border-b border-border">
                    <h2 class="text-sm md:text-base font-bold text-foreground flex items-center gap-2">⚙️ Pengaturan Latihan</h2>
                </div>
                <div class="p-4 md:p-5">
                    <div class="flex items-center justify-between py-2.5 border-b border-border">
                        <span class="text-xs md:text-sm text-muted-foreground">Batas Waktu Per Soal</span>
                        <span class="text-xs md:text-sm font-semibold text-card-foreground">{{ $package->time_limit_label }}</span>
                    </div>
                    <div class="flex items-center justify-between py-2.5 border-b border-border">
                        <span class="text-xs md:text-sm text-muted-foreground">Pembahasan</span>
                        @if($package->hide_explanation)
                            <span class="inline-flex items-center gap-1 text-[10px] md:text-xs bg-gold-400/15 text-gold-600 font-semibold px-2 py-0.5 rounded-full">🔒 Disembunyikan</span>
                        @else
                            <span class="inline-flex items-center gap-1 text-[10px] md:text-xs bg-success-500/15 text-success-500 font-semibold px-2 py-0.5 rounded-full">✅ Ditampilkan</span>
                        @endif
                    </div>
                    <div class="flex items-center justify-between py-2.5">
                        <span class="text-xs md:text-sm text-muted-foreground">Masa Berlaku Membership</span>
                        <span class="text-xs md:text-sm font-semibold text-card-foreground">{{ $package->membership_duration_label }}</span>
                    </div>
                </div>
            </div>

            {{-- Video Pembelajaran --}}
            @if($videos->count() > 0)
                <div class="bg-card rounded-lg shadow-sm border border-border overflow-hidden">
                    <div class="p-4 md:p-5 border-b border-border flex items-center justify-between">
                        <h2 class="text-sm md:text-base font-bold text-foreground flex items-center gap-2">🎬 Video Pembelajaran</h2>
                        <span class="text-[10px] md:text-xs text-muted-foreground">{{ $videos->count() }} video</span>
                    </div>
                    <div class="p-4 md:p-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($videos as $video)
                                <div class="flex items-center gap-3 p-3 rounded-md border border-border hover:border-primary/30 hover:shadow-sm transition-all">
                                    <div class="w-10 h-10 rounded-lg bg-foreground/10 flex items-center justify-center flex-shrink-0">
                                        <span class="text-lg">🎬</span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-xs md:text-sm font-semibold text-card-foreground truncate">{{ $video->title }}</h4>
                                        <p class="text-[11px] text-muted-foreground truncate">{{ $video->description ?? 'Video pembelajaran' }}</p>
                                    </div>
                                    @if($video->price > 0)
                                        <span class="text-[10px] md:text-xs font-semibold text-foreground flex-shrink-0">
                                            Rp {{ number_format($video->price, 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-[10px] md:text-xs font-semibold text-success-500 flex-shrink-0">Gratis</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- ============ INFORMASI MEMBERSHIP ============ --}}
            @if($order)
                <div class="bg-card rounded-lg shadow-sm border border-border overflow-hidden">
                    <div class="p-4 md:p-5 border-b border-border flex flex-wrap items-center justify-between gap-2">
                        <h2 class="text-sm md:text-base font-bold text-foreground flex items-center gap-2">🗓️ Informasi Membership</h2>
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold
                            @if($order->membershipStatus() === 'active') bg-success-100 text-success-500
                            @elseif($order->membershipStatus() === 'expiring') bg-gold-400/15 text-gold-600
                            @else bg-danger-100 text-danger-500 @endif">
                            {{ $order->membershipStatusLabel() }}
                        </span>
                    </div>
                    <div class="p-4 md:p-5 grid grid-cols-2 sm:grid-cols-4 gap-3 md:gap-4">
                        <div class="bg-muted rounded-lg p-3">
                            <p class="text-[11px] md:text-xs text-muted-foreground">Tanggal Mulai</p>
                            <p class="text-xs md:text-sm font-semibold text-card-foreground mt-0.5">{{ $order->membership_start?->format('d M Y') ?? '-' }}</p>
                        </div>
                        <div class="bg-muted rounded-lg p-3">
                            <p class="text-[11px] md:text-xs text-muted-foreground">Tanggal Berakhir</p>
                            <p class="text-xs md:text-sm font-semibold text-card-foreground mt-0.5">{{ $order->membership_end?->format('d M Y') ?? '-' }}</p>
                        </div>
                        <div class="bg-muted rounded-lg p-3">
                            <p class="text-[11px] md:text-xs text-muted-foreground">Sisa Hari Aktif</p>
                            <p class="text-xs md:text-sm font-semibold {{ $order->isMembershipActive() ? 'text-success-500' : 'text-danger-500' }} mt-0.5">
                                {{ $order->isMembershipActive() ? $order->membershipDaysRemaining() . ' Hari' : 'Berakhir' }}
                            </p>
                        </div>
                        <div class="bg-muted rounded-lg p-3">
                            <p class="text-[11px] md:text-xs text-muted-foreground">Durasi Membership</p>
                            <p class="text-xs md:text-sm font-semibold text-card-foreground mt-0.5">{{ $order->membership_duration_days }} Hari</p>
                        </div>
                    </div>
                    @if($membershipExpired || $order->isMembershipExpiringSoon())
                        <div class="px-4 md:px-5 pb-4 md:pb-5">
                            <div class="text-xs md:text-sm p-3 rounded-lg border {{ $membershipExpired ? 'bg-danger-50 border-danger-100 text-danger-500' : 'bg-gold-400/10 border-gold-400/20 text-gold-600' }}">
                                @if($membershipExpired)
                                    ⛔ Akses ke soal latihan terkunci karena membership sudah kedaluwarsa. Silakan perpanjang di atas untuk membuka kembali akses.
                                @else
                                    ⏳ Membership akan segera berakhir. Perpanjang lebih awal agar akses tidak terputus.
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            {{-- ============ CARD LATIHAN ============ --}}
            <div>
                <h2 class="text-lg md:text-xl font-bold text-foreground mb-4">📋 Card Latihan</h2>
                @if($hasAccess)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 md:gap-4">
                        @php
                            $cards = $package->cards ?? [];
                        @endphp
                        @forelse($cards as $card)
                            @php
                                $cardQuestionCount = collect($package->questions ?? [])->where('card_id', $card['id'])->count();
                            @endphp
                            <div class="bg-card rounded-lg p-4 md:p-5 shadow-sm border-l-4 border-primary hover:shadow-lg transition-all duration-300 border border-border hover:-translate-y-0.5">
                                <h3 class="font-bold text-foreground text-sm md:text-base">{{ $card['title'] }}</h3>
                                <p class="text-xs md:text-sm text-muted-foreground mt-1 line-clamp-2">{{ $card['description'] }}</p>
                                <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                                    <span class="text-xs text-muted-foreground">❓ {{ $cardQuestionCount }} soal</span>
                                    <form action="{{ route('practice.start', $package->id) }}" method="POST" class="flex-shrink-0">
                                        @csrf
                                        <input type="hidden" name="card_id" value="{{ $card['id'] }}">
                                        <button type="submit" class="bg-success-500 text-white text-xs md:text-sm px-3 md:px-4 py-1.5 rounded-lg hover:bg-success-600 transition">
                                            Kerjakan →
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-8 text-muted-foreground text-sm md:text-base">
                                Belum ada card latihan tersedia
                            </div>
                        @endforelse
                    </div>
                @else
                    <div class="bg-card rounded-md p-6 md:p-8 text-center shadow-sm border border-border">
                        <div class="text-3xl md:text-4xl mb-3">🔒</div>
                        <h3 class="text-base md:text-lg font-bold text-muted-foreground">Akses Terkunci</h3>
                        @if($membershipExpired)
                            <p class="text-muted-foreground text-sm md:text-base">Membership paket ini sudah kedaluwarsa</p>
                            <p class="text-xs md:text-sm text-danger-500 mt-2">⏳ Perpanjang membership di atas untuk membuka kembali akses</p>
                        @else
                            <p class="text-muted-foreground text-sm md:text-base">Beli paket ini untuk mengakses latihan</p>
                            @if($order && $membershipActive)
                                <p class="text-xs md:text-sm text-gold-400 mt-2">
                                    @if($enrollmentReady)
                                        🔑 Masukkan Enroll Key di atas untuk membuka akses
                                    @else
                                        ⏳ Menunggu Enroll Key dari Admin
                                    @endif
                                </p>
                            @endif
                        @endif
                        @if($totalCards > 0 || $totalQuestions > 0)
                            <div class="mt-4 flex items-center justify-center gap-4 text-xs text-muted-foreground">
                                <span>📋 {{ $totalCards }} Card</span>
                                <span>❓ {{ $totalQuestions }} Soal</span>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

        </div>

        {{-- ======= RIGHT: SIDEBAR ======= --}}
        <div class="lg:col-span-1">
            <div class="bg-card rounded-lg p-4 md:p-6 shadow-sm border border-border lg:sticky lg:top-20">
                <div class="text-center">
                    @php
                        $enrollmentKey = ($order && $enrollmentReady) ? ($order->enrollment['key'] ?? null) : null;
                    @endphp

                    @if(session('error'))
                        <div class="mb-3 p-2 md:p-3 bg-danger-50 border border-danger-100 rounded-lg text-xs md:text-sm text-danger-500 text-left">
                            {{ session('error') }}
                        </div>
                    @endif
                    @if(session('success'))
                        <div class="mb-3 p-2 md:p-3 bg-success-50 border border-success-100 rounded-lg text-xs md:text-sm text-success-500 text-left">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($hasAccess)
                        {{-- ============ AKTIF ============ --}}
                        <div class="text-success-500 font-bold text-base md:text-lg mb-2">✅ Akses Aktif</div>
                        <p class="text-xs md:text-sm text-muted-foreground">Anda memiliki akses penuh ke paket ini</p>
                        @if($order && isset($order->enrollment['key']))
                            <div class="mt-3 p-2 md:p-3 bg-muted rounded-lg">
                                <p class="text-xs text-muted-foreground">Enroll Key</p>
                                <code class="text-xs md:text-sm font-mono text-foreground break-all">{{ $order->enrollment['key'] }}</code>
                                <button onclick="copyToClipboard('{{ $order->enrollment['key'] }}')"
                                        class="ml-2 text-primary hover:text-primary/80 text-sm">
                                    📋
                                </button>
                            </div>
                        @endif

                    @elseif($order && $membershipExpired)
                        {{-- ============ EXPIRED ============ --}}
                        <div class="text-danger-500 font-bold text-base md:text-lg mb-2">⛔ Membership Kedaluwarsa</div>
                        <p class="text-xs md:text-sm text-muted-foreground mb-4">
                            Masa aktif paket ini berakhir pada
                            <span class="font-semibold text-card-foreground">{{ $order->membership_end?->format('d M Y') }}</span>.
                            Perpanjang membership untuk membuka kembali akses soal.
                        </p>
                        @if($package->is_pay_what_you_want)
                            @php $minAmount = (int) ($package->min_pay_amount ?? 0); @endphp
                            <form action="{{ route('orders.create', $package->id) }}" method="POST" class="space-y-3 text-left">
                                @csrf
                                <div>
                                    <label class="block text-xs font-medium text-card-foreground mb-1">Nominal Perpanjangan</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground text-sm">Rp</span>
                                        <input type="number" name="amount" min="{{ $minAmount }}" step="1000"
                                               value="{{ old('amount', $minAmount) }}" required
                                               class="w-full pl-9 pr-3 py-2.5 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-danger-500 focus:border-danger-500 text-sm md:text-base">
                                    </div>
                                    @error('amount') <p class="text-xs text-danger-500 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <button type="submit" class="w-full bg-danger-500 text-white py-2.5 rounded-lg font-semibold hover:bg-danger-700 transition text-sm md:text-base">
                                    ⏳ Perpanjang Membership
                                </button>
                            </form>
                        @else
                            <form action="{{ route('orders.create', $package->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full bg-danger-500 text-white py-2.5 rounded-lg font-semibold hover:bg-danger-700 transition text-sm md:text-base">
                                    ⏳ Perpanjang — Rp {{ number_format($package->final_price, 0, ',', '.') }}
                                </button>
                            </form>
                        @endif

                    @elseif($order && $membershipActive)

                        @if($enrollmentReady)
                            <div class="text-gold-400 font-bold text-base md:text-lg mb-2">🔑 Enroll Key Dikirim</div>
                            <p class="text-xs md:text-sm text-muted-foreground mb-3">Masukkan Enroll Key untuk aktivasi</p>

                            @if($enrollmentKey)
                                <div class="mb-3 p-2 bg-muted rounded-lg">
                                    <p class="text-xs text-muted-foreground">Enroll Key Anda:</p>
                                    <code class="text-xs md:text-sm font-mono text-foreground break-all">{{ $enrollmentKey }}</code>
                                    <button onclick="copyToClipboard('{{ $enrollmentKey }}')"
                                            class="ml-2 text-primary hover:text-primary/80 text-sm">
                                        📋
                                    </button>
                                </div>
                            @endif

                            <form id="enrollForm" action="{{ route('orders.verify-enroll', $order->id) }}" method="POST">
                                @csrf
                                <div class="flex flex-col gap-2">
                                    <input type="text" name="enroll_key" id="enrollKeyInput"
                                           placeholder="Masukkan Enroll Key"
                                           class="w-full px-3 py-2 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary text-sm">
                                    <button type="submit" id="enrollSubmitBtn"
                                            class="bg-success-500 text-white text-xs md:text-sm py-2 rounded-lg font-semibold hover:bg-success-600 transition">
                                        🔓 Aktivasi Paket
                                    </button>
                                </div>
                            </form>
                            <div id="enrollMessage" class="mt-2 text-sm hidden"></div>

                        @else
                            <div class="text-muted-foreground font-bold text-base md:text-lg mb-2">⏳ Menunggu Enroll Key</div>
                            <div class="p-2 md:p-3 bg-gold-50 rounded-lg border border-gold-200">
                                <p class="text-xs md:text-sm text-gold-700">Pembayaran berhasil!</p>
                                <p class="text-xs text-muted-foreground mt-1">Admin akan mengirimkan &amp; mengaktifkan Enroll Key segera.</p>
                            </div>
                        @endif

                    @elseif($package->is_pay_what_you_want)
                        {{-- ============ SEIKHLASNYA ============ --}}
                        <p class="text-xs md:text-sm text-muted-foreground">Bayar Sesuai Keikhlasan</p>
                        <p class="text-lg md:text-xl font-bold text-success-500 mb-1">💝 Bayar Seikhlasnya</p>
                        <p class="text-xs text-muted-foreground mb-4">
                            Minimal Rp {{ number_format($package->min_pay_amount ?? 0, 0, ',', '.') }} • Akses {{ $package->membership_duration_label }}
                        </p>

                        @auth
                            @php $minAmount = (int) ($package->min_pay_amount ?? 0); @endphp
                            <form action="{{ route('orders.create', $package->id) }}" method="POST" class="space-y-3 text-left">
                                @csrf
                                <div>
                                    <label class="block text-xs font-medium text-card-foreground mb-1">Nominal Pembayaran</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground text-sm">Rp</span>
                                        <input type="number" id="amountInput" name="amount" min="{{ $minAmount }}" step="1000"
                                               value="{{ old('amount', $minAmount) }}" required
                                               class="w-full pl-9 pr-3 py-2.5 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-success-500 focus:border-success-500 text-sm md:text-base">
                                    </div>
                                    @error('amount') <p class="text-xs text-danger-500 mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div class="flex flex-wrap gap-2 justify-center">
                                    @foreach([$minAmount, $minAmount + 10000, $minAmount + 25000, $minAmount + 50000] as $suggestion)
                                        <button type="button"
                                                onclick="document.getElementById('amountInput').value={{ (int) $suggestion }}"
                                                class="px-3 py-1 text-[11px] md:text-xs bg-muted hover:bg-success-500/10 hover:text-success-500 rounded-full transition">
                                            Rp {{ number_format($suggestion, 0, ',', '.') }}
                                        </button>
                                    @endforeach
                                </div>

                                <button type="submit" class="w-full bg-success-500 text-white py-2.5 rounded-lg font-semibold hover:bg-success-600 transition text-sm md:text-base">
                                    Beli Sekarang 🛒
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="block w-full bg-navy-light text-white py-2 rounded-lg font-semibold mt-3 text-sm md:text-base">
                                Masuk untuk Beli
                            </a>
                        @endauth

                    @else
                        {{-- ============ HARGA NORMAL / DISKON ============ --}}
                        <p class="text-xs md:text-sm text-muted-foreground">Harga Paket • Akses {{ $package->membership_duration_label }}</p>
                        @if($package->hasDiscount())
                            <div class="flex items-center justify-center gap-2 flex-wrap mt-1">
                                <p class="text-xl md:text-2xl font-bold text-danger-500">Rp {{ number_format($package->final_price, 0, ',', '.') }}</p>
                            </div>
                            <p class="text-sm text-muted-foreground line-through">Rp {{ number_format($package->price, 0, ',', '.') }}</p>
                            <span class="inline-block mt-1 text-[10px] md:text-xs bg-danger-500 text-white px-2 py-0.5 rounded-full">Hemat {{ $package->discount_percent }}%</span>
                        @else
                            <p class="text-xl md:text-2xl font-bold text-foreground">Rp {{ number_format($package->price, 0, ',', '.') }}</p>
                        @endif

                        @auth
                            <form action="{{ route('orders.create', $package->id) }}" method="POST" class="mt-3">
                                @csrf
                                <button type="submit" class="w-full bg-navy-light text-white py-2 rounded-lg font-semibold hover:bg-navy transition text-sm md:text-base">
                                    Beli Sekarang 🛒
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="block w-full bg-navy-light text-white py-2 rounded-lg font-semibold mt-3 text-sm md:text-base">
                                Masuk untuk Beli
                            </a>
                        @endauth
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const enrollForm = document.getElementById('enrollForm');
    const enrollInput = document.getElementById('enrollKeyInput');
    const enrollMessage = document.getElementById('enrollMessage');
    const submitBtn = document.getElementById('enrollSubmitBtn');

    if (enrollForm) {
        enrollForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const key = enrollInput.value.trim();
            if (!key) {
                showMessage('Masukkan Enroll Key terlebih dahulu!', 'error');
                return;
            }
            submitBtn.disabled = true;
            submitBtn.textContent = '⏳ Memproses...';
            fetch(this.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ enroll_key: key })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage('✅ ' + data.message, 'success');
                    setTimeout(() => { window.location.href = data.redirect; }, 1500);
                } else {
                    showMessage('❌ ' + data.message, 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = '🔓 Aktivasi Paket';
                }
            })
            .catch(error => {
                showMessage('❌ Terjadi kesalahan. Silakan coba lagi.', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = '🔓 Aktivasi Paket';
            });
        });
    }

    function showMessage(msg, type) {
        enrollMessage.textContent = msg;
        enrollMessage.className = 'mt-2 text-xs md:text-sm p-2 rounded ' +
            (type === 'success' ? 'bg-success-50 text-success-500 border border-success-200' : 'bg-danger-50 text-danger-500 border border-danger-200');
        enrollMessage.classList.remove('hidden');
    }

    const amountInput = document.getElementById('amountInput');
    if (amountInput) {
        amountInput.addEventListener('blur', function() {
            const min = parseInt(this.min || '0', 10);
            const val = parseInt(this.value || '0', 10);
            if (isNaN(val) || val < min) {
                this.value = min;
            }
        });
    }
});

function copyToClipboard(text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(function() {
            alert('✅ Enroll Key berhasil disalin!');
        }).catch(function() {
            fallbackCopy(text);
        });
    } else {
        fallbackCopy(text);
    }
}

function fallbackCopy(text) {
    const textarea = document.createElement('textarea');
    textarea.value = text;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    document.body.appendChild(textarea);
    textarea.select();
    try {
        document.execCommand('copy');
        alert('✅ Enroll Key berhasil disalin!');
    } catch (err) {
        alert('❌ Gagal menyalin. Silakan salin secara manual.');
    }
    document.body.removeChild(textarea);
}
</script>
@endpush
@endsection
