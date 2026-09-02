{{-- livewire/practice-statistics.blade.php --}}
<div wire:poll.5s="refreshData" class="space-y-6">
    <!-- Auto-reload indicator -->
    <div class="flex items-center justify-between">
        <div></div>
        <div class="flex items-center gap-2 text-xs text-muted-foreground bg-card rounded-md px-3 py-1.5 shadow-sm border border-border">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-success-500"></span>
            </span>
            <span>Auto-reload aktif</span>
            <span class="text-muted-foreground">|</span>
            <span>Updated {{ $lastUpdated }}</span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="admin-card stat-card stagger-item p-5">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-brand-900 to-brand-800 text-white shadow-lg shadow-brand-900/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                </div>
                <div>
                    <h3 class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">Total Sesi</h3>
                    <p class="text-lg font-bold text-brand-900">{{ number_format($stats['total_sessions'] ?? 0) }}</p>
                </div>
            </div>
        </div>
        <div class="admin-card stat-card stagger-item p-5">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-success-500 to-success-600 text-white shadow-lg shadow-success-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">Selesai</h3>
                    <p class="text-lg font-bold text-success-500">{{ number_format($stats['completed_sessions'] ?? 0) }}</p>
                </div>
            </div>
        </div>
        <div class="admin-card stat-card stagger-item p-5">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-gold-400 to-gold-500 text-brand-900 shadow-lg shadow-gold-400/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <h3 class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">Sedang Berlangsung</h3>
                    <p class="text-lg font-bold text-gold-600">{{ number_format($stats['in_progress_sessions'] ?? 0) }}</p>
                </div>
            </div>
        </div>
        <div class="admin-card stat-card stagger-item p-5">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-accent-400 to-accent-500 text-white shadow-lg shadow-accent-400/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg>
                </div>
                <div>
                    <h3 class="text-[11px] font-semibold text-muted-foreground uppercase tracking-wider">Rata-rata Nilai</h3>
                    <p class="text-lg font-bold text-brand-900">{{ number_format($stats['avg_score'] ?? 0, 1) }}%</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="admin-card p-4 sm:p-5">
        <form wire:submit.prevent="applyFilters" class="flex flex-col lg:flex-row gap-3">
            <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    <input type="text" wire:model.live="search" placeholder="Cari user atau paket..." class="form-input pl-10">
                </div>
                <input type="date" wire:model.live="startDate" class="form-input">
                <input type="date" wire:model.live="endDate" class="form-input">
            </div>
            <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <select wire:model.live="packageId" class="form-select">
                    <option value="">Semua Paket</option>
                    @foreach($packages as $package)
                        <option value="{{ $package->id }}">{{ $package->title }}</option>
                    @endforeach
                </select>
                <select wire:model.live="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="completed">Selesai</option>
                    <option value="in_progress">Sedang Berlangsung</option>
                </select>
            </div>
            <div class="flex gap-2 flex-shrink-0">
                <button type="submit" class="btn-primary">Filter</button>
                <button type="button" wire:click="$set('startDate', '{{ Carbon\Carbon::now()->startOfMonth()->format('Y-m-d') }}'); $set('endDate', '{{ Carbon\Carbon::now()->endOfMonth()->format('Y-m-d') }}'); $set('packageId', ''); $set('status', ''); $set('search', ''); $this->applyFilters()" class="btn-secondary">Reset</button>
                <div class="relative" id="exportWrap">
                    <button type="button" onclick="toggleExportDropdown()" class="btn-primary !bg-gradient-to-r !from-brand-900 !to-accent-400">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        Export
                    </button>
                    <div id="exportDropdown" class="hidden absolute right-0 mt-2 bg-card rounded-md shadow-xl border border-border min-w-[160px] py-1.5 z-50">
                        <a href="#" onclick="exportExcel()" class="flex items-center gap-3 px-4 py-2.5 text-sm text-foreground hover:bg-muted transition-colors">
                            <svg class="w-4 h-4 text-success-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            Export Excel
                        </a>
                        <a href="#" onclick="exportPdf()" class="flex items-center gap-3 px-4 py-2.5 text-sm text-foreground hover:bg-muted transition-colors">
                            <svg class="w-4 h-4 text-danger-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            Export PDF
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        <!-- Daily Activity Chart -->
        <div class="admin-card p-4 sm:p-5 md:p-6">
            <h3 class="text-sm font-semibold text-brand-900 mb-4">Aktivitas Harian</h3>
            <div class="h-64 relative" wire:ignore>
                <canvas id="dailyChart"></canvas>
            </div>
        </div>

        <!-- Package Distribution Chart -->
        <div class="admin-card p-4 sm:p-5 md:p-6">
            <h3 class="text-sm font-semibold text-brand-900 mb-4">Distribusi Berdasarkan Paket</h3>
            <div class="h-64 relative" wire:ignore>
                <canvas id="packageChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Users & Status Distribution -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        <!-- Top Users -->
        <div class="admin-card overflow-hidden">
            <div class="p-5 border-b border-border">
                <h3 class="text-sm font-semibold text-brand-900">Top Pengguna Teraktif</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($topUsers as $user)
                    <div class="flex items-center justify-between p-4 hover:bg-brand-50/30 transition">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-md bg-brand-50 flex items-center justify-center flex-shrink-0">
                                @if($loop->index === 0)
                                    <svg class="w-4 h-4 text-gold-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                @elseif($loop->index === 1)
                                    <svg class="w-4 h-4 text-muted-foreground" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                @elseif($loop->index === 2)
                                    <svg class="w-4 h-4 text-gold-600" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                @else
                                    <svg class="w-4 h-4 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                                @endif
                            </span>
                            <div>
                                <p class="font-semibold text-foreground text-sm">{{ $user->user?->name ?? 'Unknown' }}</p>
                                <p class="text-xs text-muted-foreground">{{ $user->session_count }} sesi &bull; {{ number_format($user->avg_score ?? 0, 1) }}% rata-rata</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-brand-900">{{ number_format($user->correct_answers ?? 0) }} benar</p>
                            <p class="text-xs text-muted-foreground">dari {{ $user->total_questions ?? 0 }} soal</p>
                        </div>
                    </div>
                @empty
                    <div class="p-10 text-center">
                        <div class="w-14 h-14 mx-auto bg-muted rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-7 h-7 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        </div>
                        <p class="text-muted-foreground text-sm font-medium mb-1">Belum ada data</p>
                        <p class="text-muted-foreground text-xs">Data pengguna teraktif akan muncul di sini</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Status Distribution & Recent Activity -->
        <div class="space-y-6">
            <!-- Status Distribution -->
            <div class="admin-card p-5">
                <h3 class="text-sm font-semibold text-brand-900 mb-4">Distribusi Status</h3>
                <div class="grid grid-cols-3 gap-3">
                    @php
                        $totalStatus = $statusDistribution->sum('count') ?: 1;
                    @endphp
                    @foreach($statusDistribution as $item)
                        @php
                            $percentage = round(($item->count / $totalStatus) * 100);
                            $colors = [
                                'completed' => 'bg-success-500',
                                'in_progress' => 'bg-gold-400',
                                'cancelled' => 'bg-danger-500'
                            ];
                            $color = $colors[$item->status] ?? 'bg-muted';
                            $labels = [
                                'completed' => 'Selesai',
                                'in_progress' => 'Berlangsung',
                                'cancelled' => 'Dibatalkan'
                            ];
                            $label = $labels[$item->status] ?? ucfirst($item->status);
                        @endphp
                        <div class="text-center">
                            <div class="text-2xl font-bold text-brand-900">{{ number_format($item->count) }}</div>
                            <div class="w-full bg-muted rounded-full h-2 mt-1">
                                <div class="h-2 rounded-full transition-all duration-500 {{ $color }}" style="width: {{ $percentage }}%"></div>
                            </div>
                            <p class="text-xs text-muted-foreground mt-1">{{ $label }} ({{ $percentage }}%)</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="admin-card overflow-hidden">
                <div class="p-5 border-b border-border">
                    <h3 class="text-sm font-semibold text-brand-900">Aktivitas Terbaru</h3>
                </div>
                <div class="divide-y divide-gray-100 max-h-60 overflow-y-auto">
                    @forelse($recentActivities as $activity)
                        <div class="flex items-center justify-between p-3 hover:bg-brand-50/30 transition">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-full overflow-hidden flex-shrink-0 border border-border">
                                    @if($activity->user && $activity->user->profile_photo)
                                        <img src="{{ asset('storage/' . $activity->user->profile_photo) }}" alt="{{ $activity->user->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full bg-brand-50 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-brand-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-foreground truncate">{{ $activity->user?->name ?? 'Unknown' }}</p>
                                    <p class="text-xs text-muted-foreground truncate">{{ $activity->package?->title ?? 'Deleted' }}</p>
                                </div>
                            </div>
                            <div class="text-right flex-shrink-0 ml-2">
                                @if($activity->status == 'completed')
                                    <span class="badge-success">{{ ucfirst(str_replace('_', ' ', $activity->status)) }}</span>
                                @elseif($activity->status == 'in_progress')
                                    <span class="badge-warning">{{ ucfirst(str_replace('_', ' ', $activity->status)) }}</span>
                                @else
                                    <span class="badge-danger">{{ ucfirst(str_replace('_', ' ', $activity->status)) }}</span>
                                @endif
                                <p class="text-xs text-muted-foreground mt-1">{{ $activity->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="p-10 text-center">
                            <div class="w-14 h-14 mx-auto bg-muted rounded-lg flex items-center justify-center mb-4">
                                <svg class="w-7 h-7 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <p class="text-muted-foreground text-sm font-medium mb-1">Belum ada aktivitas</p>
                            <p class="text-muted-foreground text-xs">Aktivitas terbaru akan muncul di sini</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="admin-card overflow-hidden">
        <div class="p-5 border-b border-border">
            <h3 class="text-sm font-semibold text-brand-900">Daftar Sesi Pengerjaan</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-sm admin-table">
                <thead>
                    <tr>
                        <th class="px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-left">User</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Paket</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Card</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Nilai</th>
                        <th class="px-4 py-3 text-left hidden xl:table-cell">Soal</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($sessions as $index => $session)
                        <tr>
                            <td class="px-4 py-3 text-center text-muted-foreground">{{ $sessions->firstItem() + $index }}</td>
                            <td class="px-4 py-3">
                                <span class="font-medium text-foreground">{{ $session->user?->name ?? 'Unknown' }}</span>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell text-muted-foreground">{{ $session->package?->title ?? 'Deleted' }}</td>
                            <td class="px-4 py-3 hidden lg:table-cell text-muted-foreground font-mono text-xs">{{ $session->card_id ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @if($session->status == 'completed')
                                    <span class="badge-success">{{ ucfirst(str_replace('_', ' ', $session->status)) }}</span>
                                @elseif($session->status == 'in_progress')
                                    <span class="badge-warning">{{ ucfirst(str_replace('_', ' ', $session->status)) }}</span>
                                @else
                                    <span class="badge-danger">{{ ucfirst(str_replace('_', ' ', $session->status)) }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 hidden sm:table-cell font-semibold text-brand-900">
                                {{ $session->total_score !== null ? number_format($session->total_score, 1) . '%' : '-' }}
                            </td>
                            <td class="px-4 py-3 hidden xl:table-cell text-muted-foreground text-xs">
                                <span class="text-success-500 font-semibold">{{ $session->correct_answer ?? 0 }}</span>
                                /
                                <span class="text-danger-500 font-semibold">{{ $session->wrong_answer ?? 0 }}</span>
                                /
                                <span class="text-muted-foreground">{{ $session->unanswered ?? 0 }}</span>
                            </td>
                            <td class="px-4 py-3 text-muted-foreground text-xs font-mono">{{ $session->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center">
                                    <a href="{{ route('admin.practice-statistics.show', $session->id) }}"
                                        class="w-8 h-8 rounded-md bg-muted hover:bg-brand-50 flex items-center justify-center transition hover:scale-105"
                                        title="Detail">
                                        <svg class="w-4 h-4 text-muted-foreground hover:text-brand-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-16 text-center">
                                <div class="w-14 h-14 mx-auto bg-muted rounded-lg flex items-center justify-center mb-4">
                                    <svg class="w-7 h-7 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/></svg>
                                </div>
                                <p class="text-muted-foreground text-sm font-medium mb-1">Belum ada data sesi pengerjaan</p>
                                <p class="text-muted-foreground text-xs">Data akan muncul setelah pengguna mengerjakan soal</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="text-sm text-muted-foreground">Menampilkan {{ $sessions->firstItem() ?? 0 }} sampai {{ $sessions->lastItem() ?? 0 }} dari {{ $sessions->total() }} data</div>
        <div>{{ $sessions->links() }}</div>
    </div>

    <!-- Export Form (hidden, inside root element) -->
    <form id="exportForm" method="GET" action="" class="hidden">
        <input type="hidden" name="start_date" value="{{ $startDate ?? '' }}">
        <input type="hidden" name="end_date" value="{{ $endDate ?? '' }}">
        <input type="hidden" name="package_id" value="{{ $packageId ?? '' }}">
        <input type="hidden" name="status" value="{{ $status ?? '' }}">
    </form>
</div>

@script
<script>
    let dailyChartInstance = null;
    let packageChartInstance = null;

    function initCharts() {
        const dailyData = @json($dailyActivity);
        const packageData = @json($packageDistribution);

        // Daily Activity Chart
        const dailyCtx = document.getElementById('dailyChart');
        if (dailyCtx) {
            if (dailyChartInstance) {
                dailyChartInstance.destroy();
            }
            dailyChartInstance = new Chart(dailyCtx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: dailyData.map(item => new Date(item.date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })),
                    datasets: [
                        {
                            label: 'Total Sesi',
                            data: dailyData.map(item => item.total),
                            backgroundColor: 'rgba(39, 67, 141, 0.6)',
                            borderColor: '#27438D',
                            borderWidth: 2,
                            borderRadius: 4,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Selesai',
                            data: dailyData.map(item => item.completed),
                            backgroundColor: 'rgba(0, 154, 75, 0.6)',
                            borderColor: '#009a4b',
                            borderWidth: 2,
                            borderRadius: 4,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Rata-rata Nilai',
                            data: dailyData.map(item => Math.round(item.avg_score || 0)),
                            type: 'line',
                            borderColor: '#FCC626',
                            backgroundColor: 'rgba(252, 198, 38, 0.1)',
                            borderWidth: 3,
                            pointBackgroundColor: '#FCC626',
                            pointBorderColor: '#161758',
                            tension: 0.3,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: { font: { size: 10 }, boxWidth: 12, padding: 10 }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            position: 'left',
                            ticks: { font: { size: 9 }, stepSize: 1 }
                        },
                        y1: {
                            beginAtZero: true,
                            position: 'right',
                            ticks: { font: { size: 9 }, callback: v => v + '%' },
                            grid: { drawOnChartArea: false }
                        },
                        x: {
                            ticks: { font: { size: 9 }, maxTicksLimit: 15 },
                            grid: { display: false }
                        }
                    }
                }
            });
        }

        // Package Distribution Chart
        const packageCtx = document.getElementById('packageChart');
        if (packageCtx) {
            if (packageChartInstance) {
                packageChartInstance.destroy();
            }
            const colors = ['#27438D', '#00a2e9', '#FCC626', '#009a4b', '#ec1d1d', '#8B5CF6', '#EC4899', '#14B8A6', '#F59E0B', '#6366F1'];
            packageChartInstance = new Chart(packageCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: packageData.map(item => item.package?.title || 'Deleted'),
                    datasets: [{
                        data: packageData.map(item => item.count),
                        backgroundColor: colors.slice(0, packageData.length),
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: { font: { size: 9 }, boxWidth: 12, padding: 8 }
                        }
                    }
                }
            });
        }
    }

    // Initialize charts on first load
    document.addEventListener('DOMContentLoaded', function() {
        initCharts();
    });

    // Re-init charts after Livewire updates (polling)
    Livewire.on('chartsUpdated', () => {
        setTimeout(() => initCharts(), 150);
    });

    // Export functions
    function toggleExportDropdown() {
        document.getElementById('exportDropdown').classList.toggle('hidden');
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.relative')) {
            document.getElementById('exportDropdown')?.classList.add('hidden');
        }
    });

    function exportExcel() {
        document.getElementById('exportForm').action = "{{ route('admin.practice-statistics.export-excel') }}";
        document.getElementById('exportForm').submit();
    }

    function exportPdf() {
        document.getElementById('exportForm').action = "{{ route('admin.practice-statistics.export-pdf') }}";
        document.getElementById('exportForm').submit();
    }
</script>
@endscript
