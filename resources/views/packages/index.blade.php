{{-- packages/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Paket Bank Soal')

@section('content')

<style>
    .package-bg-purple { background: linear-gradient(135deg, #7c3aed, #4f46e5); }
    .package-bg-green { background: linear-gradient(135deg, #009a4b, #2E7D3E); }
    .package-bg-red { background: linear-gradient(135deg, #dc2626, #b91c1c); }
    .package-bg-yellow { background: linear-gradient(135deg, #FCC626, #f59e0b); }
    .package-bg-orange { background: linear-gradient(135deg, #f97316, #ea580c); }
    .package-bg-blue { background: linear-gradient(135deg, #00a2e9, #27438D); }
    .package-bg-pink { background: linear-gradient(135deg, #ec4899, #db2777); }
    .package-bg-teal { background: linear-gradient(135deg, #14b8a6, #0d9488); }
    .package-bg-indigo { background: linear-gradient(135deg, #6366f1, #4f46e5); }
    .package-bg-rose { background: linear-gradient(135deg, #f43f5e, #e11d48); }
    .package-bg-cyan { background: linear-gradient(135deg, #06b6d4, #0891b2); }
    .package-bg-amber { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .package-bg-emerald { background: linear-gradient(135deg, #10b981, #059669); }
    .package-bg-violet { background: linear-gradient(135deg, #8b5cf6, #6d28d9); }
</style>

<div class="space-y-6 pkg-stagger">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <span class="inline-block px-3 py-1 rounded-full bg-navy-light/10 text-navy-light text-xs font-semibold mb-2">📚 Bank Soal</span>
            <h1 class="text-2xl md:text-3xl font-bold text-foreground">Paket Bank Soal</h1>
            <p class="text-muted-foreground mt-1 text-sm md:text-base">Pilih paket yang sesuai dengan kebutuhan belajarmu</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-sm text-muted-foreground bg-card px-3 py-1.5 rounded-lg shadow-sm border border-border">
                {{ $packages->total() }} paket tersedia
            </span>
        </div>
    </div>

    <!-- Search/Filter -->
    <div class="bg-card rounded-lg p-4 shadow-sm border border-border">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground text-sm">🔍</span>
                <input type="text" id="searchPackage" placeholder="Cari paket..."
                       class="w-full pl-9 pr-4 py-2.5 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition text-sm md:text-base bg-muted/50 hover:bg-card focus:bg-card">
            </div>
            <div class="flex flex-wrap gap-2">
                <select id="filterJenjang" class="px-4 py-2.5 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition text-sm md:text-base bg-muted/50 hover:bg-card focus:bg-card appearance-none cursor-pointer pr-8">
                    <option value="all">Semua Jenjang</option>
                    @foreach($allJenjang as $j)
                        <option value="{{ $j }}">{{ $j }}</option>
                    @endforeach
                </select>
                <select id="filterKelas" class="px-4 py-2.5 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition text-sm md:text-base bg-muted/50 hover:bg-card focus:bg-card appearance-none cursor-pointer pr-8">
                    <option value="all">Semua Kelas</option>
                    @foreach($allKelas as $k)
                        <option value="{{ $k }}">{{ $k }}</option>
                    @endforeach
                </select>
                <select id="filterPackage" class="px-4 py-2.5 border border-border rounded-md focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary transition text-sm md:text-base bg-muted/50 hover:bg-card focus:bg-card appearance-none cursor-pointer pr-8">
                    <option value="all">Semua Paket</option>
                    <option value="active">Aktif</option>
                    <option value="popular">Terpopuler</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Packages List -->
    @php
        $packageColors = [
            'package-bg-purple', 'package-bg-green', 'package-bg-red', 'package-bg-yellow',
            'package-bg-orange', 'package-bg-blue', 'package-bg-pink', 'package-bg-teal',
            'package-bg-indigo', 'package-bg-rose', 'package-bg-cyan', 'package-bg-amber',
            'package-bg-emerald', 'package-bg-violet'
        ];
        $packageIcons = ['📖', '📚', '🎯', '💡', '🚀', '🌟', '🎓', '📊', '⚡', '🏆', '💎', '🔥', '✨', '🎯'];
        $startOffset = ($packages->currentPage() - 1) * $packages->perPage();
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 md:gap-6" id="packageGrid">
        @forelse($packages as $index => $package)
            @php
                $globalIndex = $startOffset + $index;
                $colorClass = $packageColors[$globalIndex % count($packageColors)];
                $icon = $packageIcons[$globalIndex % count($packageIcons)];
                $totalCards = count($package->cards ?? []);
                $totalQuestions = count($package->questions ?? []);
                $videoCount = $package->videos()->where('is_active', true)->count();
            @endphp
            <div class="relative bg-card rounded-lg overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-300 group package-item hover:-translate-y-1.5 border border-border/80 hover:border-primary/30 flex flex-col"
                 data-kelas="{{ mb_strtolower($package->kelas ?? '') }}"
                 data-jenjang="{{ mb_strtolower($package->jenjang ?? '') }}">
                {{-- Gradient Header --}}
                <div class="relative h-40 md:h-44 overflow-hidden">
                    @if($package->thumbnail)
                        <img src="{{ asset('storage/' . $package->thumbnail) }}" alt="{{ $package->title }}"
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" loading="lazy">
                    @else
                        <div class="w-full h-full flex items-center justify-center {{ $colorClass }} relative overflow-hidden">
                            <div class="absolute inset-0 opacity-10">
                                <svg class="w-full h-full" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="20" cy="20" r="15" fill="white" opacity="0.3"/>
                                    <circle cx="80" cy="30" r="20" fill="white" opacity="0.2"/>
                                    <circle cx="50" cy="70" r="25" fill="white" opacity="0.25"/>
                                </svg>
                            </div>
                            <div class="relative z-10 text-center">
                                <div class="text-5xl md:text-6xl mb-2 opacity-90 group-hover:scale-110 group-hover:-rotate-3 transition-transform duration-500">{{ $icon }}</div>
                            </div>
                            <div class="absolute top-0 right-0 w-32 h-32 bg-card/5 rounded-full -mr-16 -mt-16"></div>
                            <div class="absolute bottom-0 left-0 w-24 h-24 bg-card/5 rounded-full -ml-12 -mb-12"></div>
                        </div>
                    @endif

                    {{-- Overlay Badges --}}
                    <div class="absolute top-3 left-3 flex flex-wrap gap-1.5">
                        @if($package->is_pay_what_you_want)
                            <span class="bg-success-500 text-white text-[10px] md:text-[11px] font-bold px-2.5 py-1 rounded-full shadow-lg">💝 Seikhlasnya</span>
                        @elseif($package->hasDiscount())
                            <span class="bg-danger-500 text-white text-[10px] md:text-[11px] font-bold px-2.5 py-1 rounded-full shadow-lg">🔥 -{{ $package->discount_percent }}%</span>
                        @endif
                    </div>
                    <span class="absolute top-3 right-3 bg-card/90 backdrop-blur text-foreground text-[10px] md:text-[11px] font-semibold px-2.5 py-1 rounded-full shadow border border-border">⏳ {{ $package->membership_duration_label }}</span>
                </div>

                <div class="p-4 md:p-5 flex flex-col flex-1">
                    {{-- Title --}}
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <h3 class="text-base md:text-lg font-bold text-foreground flex-1 line-clamp-1">{{ $package->title }}</h3>
                        @if($package->is_active)
                            <span class="bg-success-500 text-white text-[9px] px-2 py-0.5 rounded-full flex-shrink-0 font-bold">Aktif</span>
                        @endif
                    </div>

                    {{-- Tags --}}
                    <div class="flex flex-wrap gap-1.5 mb-2.5">
                        @if($package->kelas)
                            <span class="inline-flex items-center gap-0.5 text-[10px] md:text-[11px] bg-primary/10 text-primary font-semibold px-2 py-0.5 rounded-full">🏫 {{ $package->kelas }}</span>
                        @endif
                        @if($package->jenjang)
                            <span class="inline-flex items-center gap-0.5 text-[10px] md:text-[11px] bg-navy/10 text-foreground font-semibold px-2 py-0.5 rounded-full">🎓 {{ $package->jenjang }}</span>
                        @endif
                        @if($package->hide_explanation)
                            <span class="inline-flex items-center gap-0.5 text-[10px] md:text-[11px] bg-gold-400/15 text-gold-600 font-semibold px-2 py-0.5 rounded-full">🔒 Tanpa Pembahasan</span>
                        @endif
                    </div>

                    <p class="text-muted-foreground text-xs md:text-sm line-clamp-2 mb-3 leading-relaxed">{{ $package->description }}</p>

                    {{-- Stats --}}
                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[10px] md:text-[11px] text-muted-foreground mb-3">
                        <span class="inline-flex items-center gap-1" title="Card Latihan">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            {{ $totalCards }} Card
                        </span>
                        <span class="w-px h-3 bg-border"></span>
                        <span class="inline-flex items-center gap-1" title="Total Soal">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z"/></svg>
                            {{ $totalQuestions }} Soal
                        </span>
                        @if($videoCount > 0)
                            <span class="w-px h-3 bg-border"></span>
                            <span class="inline-flex items-center gap-1" title="Video Pembelajaran">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/></svg>
                                {{ $videoCount }} Video
                            </span>
                        @endif
                        <span class="text-border">•</span>
                        <span>⏱️ {{ $package->time_limit_minutes > 0 ? $package->time_limit_minutes . 'm' : 'Bebas' }}</span>
                        <span class="text-border">•</span>
                        <span>{{ $package->membership_duration_label }}</span>
                    </div>

                    {{-- Price --}}
                    <div class="mt-auto pt-3 border-t border-border">
                        <div class="flex items-center justify-between mb-2.5">
                            @if($package->is_pay_what_you_want)
                                <div>
                                    <span class="text-sm md:text-base font-bold text-success-500">💝 Seikhlasnya</span>
                                    <div class="text-[10px] text-muted-foreground">Min. Rp {{ number_format($package->min_pay_amount ?? 0, 0, ',', '.') }}</div>
                                </div>
                            @elseif($package->hasDiscount())
                                <div class="flex items-end gap-2">
                                    <span class="text-lg md:text-xl font-bold text-danger-500">Rp {{ number_format($package->final_price, 0, ',', '.') }}</span>
                                    <div class="text-[10px] text-muted-foreground line-through">Rp {{ number_format($package->price, 0, ',', '.') }}</div>
                                </div>
                            @else
                                <span class="text-lg md:text-xl font-bold text-foreground">
                                    Rp {{ number_format($package->price, 0, ',', '.') }}
                                </span>
                            @endif
                        </div>

                        @auth
                            @php
                                $latestOrder = \App\Models\Order::latestPaidFor(Auth::id(), $package->id);
                                $mStatus = $latestOrder?->membershipStatus();
                                $hasAccess = $latestOrder
                                    && $latestOrder->enrollmentIsUnlocked()
                                    && $latestOrder->isMembershipActive();
                                $needsActivation = $latestOrder
                                    && $latestOrder->isMembershipActive()
                                    && !$latestOrder->enrollmentIsUnlocked();
                                $needsRenewal = $latestOrder && !$latestOrder->isMembershipActive();
                                $isExpiringSoon = $latestOrder && $latestOrder->isMembershipExpiringSoon();
                            @endphp

                            @if($hasAccess && $isExpiringSoon)
                                <div class="mb-2 text-[10px] text-gold-600 bg-gold-400/10 border border-gold-400/20 rounded-md px-2 py-1.5 text-center sm:text-left">
                                    ⏳ Sisa {{ $latestOrder->membershipDaysRemaining() }} hari
                                </div>
                            @endif

                            @if($hasAccess)
                                <a href="{{ route('packages.show', $package->id) }}"
                                   class="block w-full text-center bg-success-500 text-white py-2 md:py-2.5 rounded-md font-semibold hover:bg-success-600 hover:shadow-lg transition-all duration-300 text-xs md:text-sm">
                                    📖 Mulai Belajar
                                </a>
                            @elseif($needsActivation)
                                <a href="{{ route('packages.show', $package->id) }}"
                                   class="block w-full text-center bg-gold-400 text-foreground py-2 md:py-2.5 rounded-md font-semibold hover:bg-gold-500 hover:shadow-lg transition-all duration-300 text-xs md:text-sm">
                                    🔑 Aktivasi Paket
                                </a>
                            @elseif($needsRenewal)
                                <a href="{{ route('packages.show', $package->id) }}"
                                   class="block w-full text-center bg-danger-500 text-white py-2 md:py-2.5 rounded-md font-semibold hover:bg-danger-600 hover:shadow-lg transition-all duration-300 text-xs md:text-sm">
                                    ⏳ Perpanjang
                                </a>
                            @else
                                <a href="{{ route('packages.show', $package->id) }}"
                                   class="block w-full text-center bg-navy-light text-white py-2 md:py-2.5 rounded-md font-semibold hover:bg-navy hover:shadow-lg transition-all duration-300 text-xs md:text-sm">
                                    🛒 Beli Sekarang
                                </a>
                            @endif
                        @else
                            <a href="{{ route('login') }}"
                               class="block w-full text-center bg-navy-light text-white py-2 md:py-2.5 rounded-md font-semibold hover:bg-navy hover:shadow-lg transition-all duration-300 text-xs md:text-sm">
                                🔑 Masuk untuk Beli
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12 md:py-16 bg-card rounded-lg shadow-sm border border-border">
                <div class="text-6xl md:text-7xl mb-6">📭</div>
                <h3 class="text-xl md:text-2xl font-bold text-muted-foreground">Belum Ada Paket</h3>
                <p class="text-muted-foreground mt-2 text-sm md:text-base">Paket bank soal akan segera tersedia</p>
                <div class="mt-4 text-sm text-muted-foreground">💡 Admin sedang menyiapkan konten terbaik untukmu</div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($packages->hasPages())
        <div class="mt-8 md:mt-10">
            {{ $packages->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchPackage');
    const filterSelect = document.getElementById('filterPackage');
    const filterKelas = document.getElementById('filterKelas');
    const filterJenjang = document.getElementById('filterJenjang');
    const packages = document.querySelectorAll('.package-item');

    function filterPackages() {
        const search = searchInput.value.toLowerCase();
        const filter = filterSelect.value;
        const kelas = filterKelas.value.toLowerCase();
        const jenjang = filterJenjang.value.toLowerCase();

        packages.forEach(function(pkg) {
            const title = pkg.querySelector('h3').textContent.toLowerCase();
            const desc = pkg.querySelector('p').textContent.toLowerCase();
            let show = true;

            if (search && !title.includes(search) && !desc.includes(search)) {
                show = false;
            }

            if (filter === 'active' && show) {
                const badge = pkg.querySelector('[class*="bg-success-500"], [class*="bg-green-600"]');
                if (!badge) show = false;
            }

            if (kelas && show && pkg.dataset.kelas !== kelas) {
                show = false;
            }

            if (jenjang && show && pkg.dataset.jenjang !== jenjang) {
                show = false;
            }

            pkg.style.display = show ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterPackages);
    filterSelect.addEventListener('change', filterPackages);
    filterKelas.addEventListener('change', filterPackages);
    filterJenjang.addEventListener('change', filterPackages);
});
</script>
@endpush
@endsection
