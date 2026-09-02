{{-- admin/video-orders/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Pesanan Video')
@section('header-title', 'Pesanan Video')
@section('header-sub', 'Kelola pembelian dan akses video user')

@section('content')
@php
    $paidCount = \App\Models\VideoOrder::where('payment_status', 'paid')->count();
    $pendingCount = \App\Models\VideoOrder::where('payment_status', 'pending')->count();
    $needsActivationCount = \App\Models\VideoOrder::where('payment_status', 'paid')->where('access_granted', false)->count();
    $revenue = \App\Models\VideoOrder::where('payment_status', 'paid')->sum('total_price') ?? 0;
@endphp

<div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="stat-card stagger-item">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-brand-900 to-brand-800 text-white shadow-lg shadow-brand-900/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.836c-1.1 0-1.996.907-1.996 2.022v9.956c0 1.115.896 2.022 1.996 2.022h9.328c1.1 0 1.996-.907 1.996-2.022v-9.956c0-1.115-.896-2.022-1.996-2.022H8.25z"/></svg>
                </div>
                <div>
                    <p class="text-[11px] text-muted-foreground uppercase font-semibold tracking-wider">Total Pesanan</p>
                    <p class="text-2xl font-bold text-brand-900 leading-tight">{{ $videoOrders->total() }}</p>
                </div>
            </div>
        </div>
        <div class="stat-card stagger-item">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-success-500 to-success-600 text-white shadow-lg shadow-success-500/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-[11px] text-muted-foreground uppercase font-semibold tracking-wider">Lunas</p>
                    <p class="text-2xl font-bold text-success-500 leading-tight">{{ $paidCount }}</p>
                </div>
            </div>
        </div>
        <div class="stat-card stagger-item {{ $needsActivationCount > 0 ? 'ring-2 ring-danger-400/40' : '' }}">
            <a href="{{ route('admin.video-orders.index', array_filter(['filter' => 'needs_activation'] + request()->only('search'))) }}" class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-gold-400 to-gold-500 text-white shadow-lg shadow-gold-400/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-[11px] text-muted-foreground uppercase font-semibold tracking-wider">Perlu Aktivasi</p>
                    <p class="text-2xl font-bold {{ $needsActivationCount > 0 ? 'text-danger-500' : 'text-muted-foreground' }} leading-tight">{{ $needsActivationCount }}</p>
                </div>
            </a>
        </div>
        <div class="stat-card stagger-item">
            <div class="flex items-center gap-4">
                <div class="stat-icon bg-gradient-to-br from-accent-400 to-accent-500 text-white shadow-lg shadow-accent-400/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-[11px] text-muted-foreground uppercase font-semibold tracking-wider">Pendapatan</p>
                    <p class="text-xl font-bold text-primary leading-tight">Rp {{ number_format($revenue, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter -->
    <div class="admin-card p-4 sm:p-5 stagger-item">
        <form method="GET" action="{{ route('admin.video-orders.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email user..."
                       class="form-input pl-10">
            </div>
            <div class="flex gap-3">
                <select name="status" class="form-select sm:w-44">
                    <option value="">Semua Status</option>
                    @foreach(['pending' => 'Pending', 'paid' => 'Lunas', 'failed' => 'Gagal', 'expired' => 'Kedaluwarsa'] as $key => $label)
                        <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary px-6">Cari</button>
                @if(request('search') || request('status'))
                    <a href="{{ route('admin.video-orders.index') }}" class="btn-secondary px-5">Reset</a>
                @endif
            </div>
        </form>
    </div>

    <!-- Orders List -->
    <div class="admin-card overflow-hidden stagger-item">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-4 border-b border-border">
            <div>
                <h3 class="font-bold text-brand-900">Daftar Pesanan Video</h3>
                <p class="text-xs text-muted-foreground mt-0.5">Berikan akses video ke user yang sudah lunas</p>
            </div>
            <a href="{{ route('admin.videos.index') }}" class="btn-secondary text-xs px-4 py-2 self-start sm:self-auto">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z"/></svg>
                Kelola Video
            </a>
        </div>

        <!-- Quick filter tabs -->
        @php
            $tabs = [
                '' => 'Semua',
                'needs_activation' => '⏳ Perlu Aktivasi' . ($needsActivationCount > 0 ? " ({$needsActivationCount})" : ''),
                'paid_active' => '✓ Selesai',
                'pending' => 'Menunggu Bayar',
            ];
            $activeTab = request('filter') ?: (request('status') === 'pending' ? 'pending' : '');
        @endphp
        <div class="flex gap-2 overflow-x-auto px-5 py-3 border-b border-border bg-muted/50">
            @foreach($tabs as $key => $label)
                @if($key === 'pending')
                    <a href="{{ route('admin.video-orders.index', ['status' => 'pending'] + request()->only('search')) }}"
                       class="whitespace-nowrap text-xs font-semibold px-3.5 py-1.5 rounded-full transition-all duration-200 {{ $activeTab === $key ? 'bg-brand-900 text-white shadow-sm' : 'bg-card text-muted-foreground border border-border hover:border-brand-300 hover:text-brand-800' }}">
                        {{ $label }}
                    </a>
                @else
                    <a href="{{ route('admin.video-orders.index', array_filter(['filter' => $key] + request()->only('search'))) }}"
                       class="whitespace-nowrap text-xs font-semibold px-3.5 py-1.5 rounded-full transition-all duration-200 {{ $activeTab === $key ? 'bg-brand-900 text-white shadow-sm' : 'bg-card text-muted-foreground border border-border hover:border-brand-300 hover:text-brand-800' }}">
                        {{ $label }}
                    </a>
                @endif
            @endforeach
        </div>

        @if($videoOrders->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg class="w-10 h-10 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.836c-1.1 0-1.996.907-1.996 2.022v9.956c0 1.115.896 2.022 1.996 2.022h9.328c1.1 0 1.996-.907 1.996-2.022v-9.956c0-1.115-.896-2.022-1.996-2.022H8.25z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-muted-foreground">Belum Ada Pesanan</h3>
                @if(request('filter') || request('status') || request('search'))
                    <p class="text-muted-foreground mt-1 text-sm">Tidak ada pesanan yang cocok dengan filter ini</p>
                @else
                    <p class="text-muted-foreground mt-1 text-sm">Pesanan video akan muncul di sini setelah ada pembelian</p>
                @endif
            </div>
        @else
            <!-- Card list (mobile) -->
            <div class="md:hidden divide-y divide-border">
                @foreach($videoOrders as $order)
                    <div class="p-4 hover:bg-muted/50 transition-all duration-200">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-brand-900 truncate text-sm">{{ $order->user->name ?? 'User Dihapus' }}</p>
                                <p class="text-xs text-muted-foreground truncate">{{ $order->user->email ?? '-' }}</p>
                                <p class="text-xs text-muted-foreground mt-1.5 font-medium">🎬 {{ $order->video->title ?? 'Video Dihapus' }}</p>
                                <p class="text-[10px] text-muted-foreground font-mono mt-0.5">{{ $order->order_number }}</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="font-bold text-brand-900 text-sm">Rp {{ number_format($order->total_price, 0, ',', '.') }}</p>
                                @if($order->payment_status === 'paid')
                                    <span class="badge-success text-[10px] py-0.5 px-2 mt-1">Lunas</span>
                                @elseif($order->payment_status === 'pending')
                                    <span class="badge-warning text-[10px] py-0.5 px-2 mt-1">Pending</span>
                                @elseif($order->payment_status === 'expired')
                                    <span class="badge-neutral text-[10px] py-0.5 px-2 mt-1">Kedaluwarsa</span>
                                @else
                                    <span class="badge-danger text-[10px] py-0.5 px-2 mt-1">Gagal</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center justify-between mt-3 pt-3 border-t border-border">
                            <span class="text-[10px] text-muted-foreground">
                                {{ $order->created_at->translatedFormat('d M Y H:i') }}
                                @if($order->access_granted)
                                    • ✅ Akses aktif ({{ $order->accessDaysRemaining() }} hari lagi)
                                @endif
                            </span>
                            @if($order->payment_status === 'paid' && !$order->access_granted)
                                <form action="{{ route('admin.video-orders.grant', $order->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Beri akses video ini ke user?')"
                                            class="text-[11px] font-semibold text-white bg-gradient-to-r from-success-500 to-success-600 px-3 py-1.5 rounded-lg hover:shadow-md active:scale-95 transition-all">
                                        Beri Akses
                                    </button>
                                </form>
                            @elseif($order->access_granted)
                                <span class="text-[11px] font-semibold text-success-500">✓ Akses Diberikan</span>
                            @endif
                        </div>
                    </div>
                @endforeach
                {{ $videoOrders->links() }}
            </div>

            <!-- Table (md+) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full min-w-[950px] text-sm admin-table">
                    <thead>
                        <tr>
                            <th class="px-5 py-3.5 text-left">No. Pesanan</th>
                            <th class="px-5 py-3.5 text-left">User</th>
                            <th class="px-5 py-3.5 text-left">Video</th>
                            <th class="px-5 py-3.5 text-left">Total</th>
                            <th class="px-5 py-3.5 text-left">Status</th>
                            <th class="px-5 py-3.5 text-left">Akses</th>
                            <th class="px-5 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @foreach($videoOrders as $order)
                            <tr class="hover:shadow-sm transition-all duration-200">
                                <td class="px-5 py-4">
                                    <p class="font-mono text-xs font-semibold text-brand-900">{{ \Illuminate\Support\Str::limit($order->order_number, 22) }}</p>
                                    <p class="text-[10px] text-muted-foreground mt-0.5">{{ $order->created_at->translatedFormat('d M Y H:i') }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-foreground text-xs">{{ $order->user->name ?? 'User Dihapus' }}</p>
                                    <p class="text-[10px] text-muted-foreground">{{ $order->user->email ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-4 max-w-[200px]">
                                    <p class="text-xs text-foreground truncate">{{ $order->video->title ?? 'Video Dihapus' }}</p>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap font-semibold text-brand-900">Rp {{ number_format($order->total_price, 0, ',', '.') }}</td>
                                <td class="px-5 py-4">
                                    @if($order->payment_status === 'paid')
                                        <span class="badge-success">Lunas</span>
                                    @elseif($order->payment_status === 'pending')
                                        <span class="badge-warning">Pending</span>
                                    @elseif($order->payment_status === 'expired')
                                        <span class="badge-neutral">Kedaluwarsa</span>
                                    @else
                                        <span class="badge-danger">Gagal</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if($order->access_granted && $order->isAccessActive())
                                        <span class="badge-info">Aktif • {{ $order->accessDaysRemaining() }} hari</span>
                                    @elseif($order->access_granted)
                                        <span class="badge-neutral">Kedaluwarsa</span>
                                    @else
                                        <span class="badge-neutral">Belum</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    @if($order->payment_status === 'paid' && !$order->access_granted)
                                        <form action="{{ route('admin.video-orders.grant', $order->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" onclick="return confirm('Beri akses video ini ke user?')"
                                                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-white bg-gradient-to-r from-success-500 to-success-600 px-4 py-2 rounded-md hover:shadow-md active:scale-95 transition-all">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                                Beri Akses
                                            </button>
                                        </form>
                                    @elseif($order->access_granted)
                                        <span class="text-xs font-semibold text-success-500 inline-flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Selesai
                                        </span>
                                    @else
                                        <span class="text-xs text-muted-foreground">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $videoOrders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
